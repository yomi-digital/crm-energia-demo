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


        if ($request->filled('enabled')) {
            $customers = $customers->where('enabled', $request->get('enabled'));
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

        if ($request->get('sortBy')) {
            $customers = $customers->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
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

    public function show($id)
    {
        $customer = \App\Models\Customer::with('addedByUser')->whereId($id)->first();

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
}
