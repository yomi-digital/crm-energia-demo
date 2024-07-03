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
            $paperworks = $paperworks->orderBy('added_at', 'desc');
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
        $customer = \App\Models\Customer::find($id);

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        return response()->json($customer);
    }

    public function store(Request $request)
    {
        $customer = new \App\Models\Customer;

        $customer->fill($request->all());

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
}
