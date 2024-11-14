<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $customers = new \App\Models\Customer;

        if ($request->get('id')) {
            $customers = $customers->where('id', $request->get('id'));
        }

        if ($request->filled('brand')) {
            // Need to get all customers that have a paperwork with products of the selected brand
            $customers = $customers->whereHas('paperworks', function ($query) use ($request) {
                $query->whereHas('product', function ($query) use ($request) {
                    $query->where('brand_id', $request->get('brand'));
                });
            });
        }

        if ($request->filled('city')) {
            $customers = $customers->where('city', $request->get('city'));
        }

        if ($request->get('q')) {
            $search = $request->get('q');
            $customers = $customers->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%")
                    ->orWhere('tax_id_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // If the looged in user has role 'agente', filter for only his customers
        if ($request->user()->hasRole('agente') || $request->user()->hasRole('struttura')) {
            $customers = $customers->where(function ($query) use ($request) {
                $query->whereHas('paperworks', function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                })->orWhere('added_by', $request->user()->id);
            });
        }

        if ($request->get('sortBy')) {
            $customers = $customers->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        }

        if ($request->has('export')) {
            $allCustomers = $customers->get();
            $csvPath = $this->transformEntriesToCSV($allCustomers);
            
            return response()->download($csvPath, 'customers_' . now()->format('Y-m-d_H-i-s') . '.csv');
        }

        if ($request->get('select') === '1') {
            $customers = $customers->select('id', 'name', 'last_name', 'business_name', 'vat_number', 'tax_id_code');
        }

        $customers = $customers->paginate($perPage);

        return response()->json([
            'customers' => $customers->getCollection(),
            'totalPages' => $customers->lastPage(),
            'totalCustomers' => $customers->total(),
            'page' => $customers->currentPage()
        ]);
    }

    public function show(Request $request, $id)
    {
        $customer = \App\Models\Customer::with(['addedByUser', 'confirmedByUser'])->whereId($id);

        if ($request->user()->hasRole('agente') || $request->user()->hasRole('struttura')) {
            $customer = $customer->where(function ($query) use ($request) {
                $query->whereHas('paperworks', function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                })->orWhere('added_by', $request->user()->id);
            });
        }

        $customer = $customer->first();

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        return response()->json($customer);
    }

    public function store(Request $request)
    {
        $customer = new \App\Models\Customer;

        $customer->fill($request->all());
        $customer->added_at = now()->format('Y-m-d');
        $customer->added_by = $request->user()->id;

        $customer->save();

        return response()->json($customer, 201);
    }

    public function update(Request $request, $id)
    {
        $customer = \App\Models\Customer::find($id);

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        $customer->fill($request->all());

        $customer->save();

        return response()->json($customer);
    }

    public function cities(Request $request)
    {
        $cities = \App\Models\Customer::select('city')
            ->groupBy('city')->orderBy('city', 'asc')
            ->get();

        return response()->json($cities);
    }

    public function confirm(Request $request, $id)
    {
        $customer = \App\Models\Customer::find($id);

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        $customer->confirmed_by = $request->user()->id;
        $customer->confirmed_at = now()->format('Y-m-d H:i:s');

        $customer->save();

        return response()->json($customer);
    }

    public function export(Request $request)
    {
        $customers = new \App\Models\Customer;

        if ($request->get('id')) {
            $customers = $customers->where('id', $request->get('id'));
        }

        if ($request->filled('brand')) {
            // Need to get all customers that have a paperwork with products of the selected brand
            $customers = $customers->whereHas('paperworks', function ($query) use ($request) {
                $query->whereHas('product', function ($query) use ($request) {
                    $query->where('brand_id', $request->get('brand'));
                });
            });
        }

        if ($request->filled('city')) {
            $customers = $customers->where('city', $request->get('city'));
        }

        if ($request->get('q')) {
            $search = $request->get('q');
            $customers = $customers->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%")
                    ->orWhere('tax_id_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $customers->get();

        $filename = 'customers_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
                'Nome',
                'Cognome',
                'Ragione Sociale',
                'P.IVA',
                'CF',
                'Email',
                'Telefono',
                'Città',
                'Indirizzo',
                'CAP',
                'Aggiunto da',
                'Aggiunto il',
                'Confermato da',
                'Confermato il',
            ]);

            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->id,
                    $customer->name,
                    $customer->last_name,
                    $customer->business_name,
                    $customer->vat_number,
                    $customer->tax_id_code,
                    $customer->email,
                    $customer->phone,
                    $customer->city,
                    $customer->address,
                    $customer->zip_code,
                    $customer->addedByUser ? $customer->addedByUser->name . ' ' . $customer->addedByUser->last_name : '',
                    $customer->added_at,
                    $customer->confirmedByUser ? $customer->confirmedByUser->name . ' ' . $customer->confirmedByUser->last_name : '',
                    $customer->confirmed_at,
                ]);
            }
        };

        return response()->stream($callback, 200, $headers);
    }

    private function transformEntriesToCSV($entries)
    {
        $headers = [
            'ID',
            'Nome',
            'Cognome',
            'Ragione Sociale',
            'P.IVA',
            'CF',
            'Email',
            'Telefono',
            'Città',
            'Indirizzo',
            'CAP',
            'Aggiunto da',
            'Aggiunto il',
            'Confermato da',
            'Confermato il',
        ];

        // Save csv to /tmp 
        $csvPath = tempnam(sys_get_temp_dir(), 'csv');
        $fp = fopen($csvPath, 'w');
        fputcsv($fp, $headers);

        foreach ($entries as $customer) {
            fputcsv($fp, [
                $customer->id,
                $customer->name,
                $customer->last_name,
                $customer->business_name,
                $customer->vat_number,
                $customer->tax_id_code,
                $customer->email,
                $customer->phone,
                $customer->city,
                $customer->address,
                $customer->zip_code,
                $customer->addedByUser ? $customer->addedByUser->name . ' ' . $customer->addedByUser->last_name : '',
                $customer->added_at,
                $customer->confirmedByUser ? $customer->confirmedByUser->name . ' ' . $customer->confirmedByUser->last_name : '',
                $customer->confirmed_at,
            ]);
        }

        fclose($fp);

        return $csvPath;
    }
}
