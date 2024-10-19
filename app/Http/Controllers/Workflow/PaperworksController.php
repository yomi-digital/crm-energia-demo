<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaperworksController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $paperworks = \App\Models\Paperwork::with(['customer', 'user', 'mandate', 'product', 'product.brand']);

        if ($request->filled('customer_id')) {
            $paperworks = $paperworks->where('customer_id', $request->get('customer_id'));
        }

        if ($request->filled('user_id')) {
            $paperworks = $paperworks->where('user_id', $request->get('user_id'));
        }

        if ($request->get('q')) {
            $search = $request->get('q');
            $paperworks = $paperworks->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('area', 'like', "%{$search}%");
            });
        }

        if ($request->get('sortBy')) {
            $paperworks = $paperworks->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        } else {
            $paperworks = $paperworks->orderBy('created_at', 'desc');
        }

        $paperworks = $paperworks->paginate($perPage);

        return response()->json([
            'paperworks' => $paperworks->getCollection(),
            'totalPages' => $paperworks->lastPage(),
            'totalPaperworks' => $paperworks->total(),
            'page' => $paperworks->currentPage()
        ]);
    }

    public function show($id)
    {
        $paperwork = \App\Models\Paperwork::with(['user', 'customer', 'customer.paperworks', 'mandate', 'product', 'documents', 'tickets', 'tickets.createdBy', 'createdByUser', 'confirmedByUser', 'events', 'events.user'])->whereId($id)->first();

        if (!$paperwork) {
            return response()->json(['error' => 'Paperwork not found'], 404);
        }

        $paperwork->payout = $this->calculatePaperworkPayout($paperwork);

        return response()->json($paperwork);
    }

    public function store(Request $request)
    {
        $paperwork = new \App\Models\Paperwork;

        if ($request->user()->hasRole('agent')) {
            $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'appointment_id' => 'exists:calendar,id|nullable',
                'product_id' => 'required|exists:products,id',
                'account_pod_pdr' => [
                    'nullable',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->category !== 'ALLACCIO' && $request->energy_type !== 'MOBILE') {
                            $fail('Account POD/PDR è obbligatorio per i contratti di energia che non siano ALLACCIO.');
                        }
                    },
                ],
                'annual_consumption' => 'nullable',
                'contract_type' => 'required',
                'category' => 'required',
                'type' => 'required',
                'energy_type' => 'required',
                'mobile_type' => 'required_if:energy_type,MOBILE|nullable',
                'coverage' => 'nullable',
                'previous_provider' => 'nullable',
                'notes' => 'nullable',
            ]);
            $fields = $request->only([
                'customer_id',
                'appointment_id',
                'product_id',
                'account_pod_pdr',
                'annual_consumption',
                'contract_type',
                'category',
                'type',
                'energy_type',
                'mobile_type',
                'coverage',
                'previous_provider',
                'notes',
            ]);
        } else {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'customer_id' => 'required|exists:customers,id',
                'appointment_id' => 'exists:calendar,id|nullable',
                'product_id' => 'required|exists:products,id',
                'account_pod_pdr' => 'required_unless:category,ALLACCIO|nullable',
                'annual_consumption' => 'nullable',
                'contract_type' => 'required',
                'category' => 'required',
                'type' => 'required',
                'energy_type' => 'required',
                'mobile_type' => 'required_if:energy_type,MOBILE|nullable',
                'coverage' => 'nullable',
                'previous_provider' => 'nullable',
                'notes' => 'nullable',
            ]);
            $fields = $request->all();
        }

        $paperwork->fill($fields);
        $paperwork->order_status = 'CARICATO';

        if ($request->user()->hasRole('agent')) {
            $agent = $request->user();
        } else {
            $agent = \App\Models\User::find($request->get('user_id'));
        }
        $paperwork->manager_id = $agent->manager_id;
        $paperwork->created_by = $request->user()->id;

        $paperwork->save();

        return response()->json($paperwork, 201);
    }

    public function update(Request $request, $id)
    {
        $paperwork = \App\Models\Paperwork::find($id);

        if (!$paperwork) {
            return response()->json(['error' => 'Paperwork not found'], 404);
        }

        if ($request->get('partner_outcome') && ! $paperwork->partner_outcome) {
            $paperwork->partner_outcome_at = now()->format('Y-m-d H:i:s');
        }

        $paperwork->fill($request->all());

        $paperwork->save();

        return response()->json($paperwork);
    }

    public function documents(Request $request, $id)
    {
        $paperwork = \App\Models\Paperwork::find($id);

        if (!$paperwork) {
            return response()->json(['error' => 'Paperwork not found'], 404);
        }

        foreach ($request->get('documents') as $document) {
            if (! isset($document['path'])) {
                continue;
            }
            $doc = new \App\Models\PaperworkDocument;
            $doc->paperwork_id = $paperwork->id;
            $doc->name = basename($document['path']);
            $doc->url = $document['path'];
            $doc->save();
        }

        return response()->json(null, 201);
    }

    public function confirm(Request $request, $id)
    {
        $paperwork = \App\Models\Paperwork::find($id);

        if (!$paperwork) {
            return response()->json(['error' => 'Paperwork not found'], 404);
        }

        $paperwork->order_status = 'CONFERMATO';
        $paperwork->confirmed_by = $request->user()->id;
        $paperwork->confirmed_at = now()->format('Y-m-d H:i:s');

        $paperwork->save();

        return response()->json($paperwork);
    }

    public function confirmPartnerSent(Request $request, $id)
    {
        $request->validate([
            'order_code' => 'required',
        ]);
        $paperwork = \App\Models\Paperwork::find($id);

        if (!$paperwork) {
            return response()->json(['error' => 'Paperwork not found'], 404);
        }

        $paperwork->order_status = 'INSERITO';

        $paperwork->order_code = $request->get('order_code');
        $paperwork->partner_sent_at = now()->format('Y-m-d H:i:s');

        $paperwork->save();

        return response()->json($paperwork);
    }

    private function calculatePaperworkPayout($paperwork)
    {
        // Get user fee band for this brand
        $brandUser = \App\Models\BrandUser::where('user_id', $paperwork->user_id)->where('brand_id', $paperwork->product->brand_id)->first();

        // Get product fee band for this product
        $feeband = \App\Models\Feeband::where('product_id', $paperwork->product_id)
            ->where('start_date', '<=', now())->where(function ($query) {
                $query->where('end_date', '>=', now())->orWhereNull('end_date');
            })->orderBy('start_date', 'asc')->first();

        $payout = 0;
        if ($brandUser && $feeband) {
            switch (strtolower($brandUser->pay_level)) {
                case 'management':
                    $productFee = $feeband->management_fee;
                    break;
                case 'top_partner':
                    $productFee = $feeband->top_partner_fee;
                    break;
                case 'top':
                    $productFee = $feeband->top_fee;
                    break;
                case 'partner':
                    $productFee = $feeband->partner_fee;
                    break;
                case 'smart':
                    $productFee = $feeband->smart_fee;
                    break;
                case 'collaborator':
                    $productFee = $feeband->collaborator_fee;
                    break;
                default:
                    $productFee = 0;
                    break;
            }

            if ($feeband->fee_type === 'FISSO') {
                $payout = $productFee;
            } elseif ($feeband->fee_type === 'PERCENTUALE') {
                $payout = $productFee * $paperwork->product->price / 100;
            } elseif ($feeband->fee_type === 'MESE') {
                $payout = $productFee * $paperwork->product->price;
            } elseif ($feeband->fee_type === 'CONSUMO') {
                $payout = $productFee * $paperwork->annual_consumption;
            }

            if ($brandUser->bonus) {
                // Add bonus percentage to payout
                $payout += $payout * $brandUser->bonus / 100;
            }
        }

        return $payout;
    }

    public function calculatePayout(Request $request, $id)
    {
        if (! $request->user()->hasRole('gestione')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        $paperwork = \App\Models\Paperwork::find($id);

        if (!$paperwork) {
            return response()->json(['error' => 'Paperwork not found'], 404);
        }

        $payout = $this->calculatePaperworkPayout($paperwork);

        return response()->json(['payout' => $payout]);
    }
}
