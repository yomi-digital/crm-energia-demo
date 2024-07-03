<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $products = \App\Models\Product::with(['brand']);

        if ($request->get('q')) {
            $search = $request->get('q');
            $products = $products->where('name', 'like', "%{$search}%");
        }

        if ($request->get('sortBy')) {
            $products = $products->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        } else {
            $products = $products->orderBy('name', 'asc');
        }

        if ($request->get('select') === '1') {
            $products = $products->select('id', 'name');
        }

        $products = $products->paginate($perPage);

        return response()->json([
            'products' => $products->getCollection(),
            'totalPages' => $products->lastPage(),
            'totalProducts' => $products->total(),
            'page' => $products->currentPage()
        ]);
    }

    public function show($id)
    {
        $product = \App\Models\Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    public function store(Request $request)
    {
        $product = new \App\Models\Product;

        $product->fill($request->all());

        $product->save();

        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = \App\Models\Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $product->fill($request->all());

        $product->save();

        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = \App\Models\Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json($product);
    }
}
