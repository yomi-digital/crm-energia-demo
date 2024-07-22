<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BrandsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $brands = new \App\Models\Brand;

        if ($request->filled('with') && $request->get('with') == 'products') {
            if ($request->get('product_details') == '1') {
                $brands = $brands->with('products');
            } else {
                $brands = $brands->withCount('products');
            }
        }

        if ($request->filled('enabled')) {
            $brands = $brands->where('enabled', $request->get('enabled'));
        }

        if ($request->get('type')) {
            $brands = $brands->where('type', $request->get('type'));
        }
        if ($request->get('category')) {
            $brands = $brands->where('category', $request->get('category'));
        }


        if ($request->get('q')) {
            $search = $request->get('q');
            $brands = $brands->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->get('sortBy')) {
            $brands = $brands->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        } else {
            $brands = $brands->orderBy('name', 'asc');
        }

        if ($request->get('select') === '1') {
            $brands = $brands->select('id', 'name');
        }

        $brands = $brands->paginate($perPage);

        return response()->json([
            'brands' => $brands->getCollection(),
            'totalPages' => $brands->lastPage(),
            'totalBrands' => $brands->total(),
            'page' => $brands->currentPage()
        ]);
    }

    public function show($id)
    {
        $brand = \App\Models\Brand::find($id);

        if (!$brand) {
            return response()->json(['error' => 'Brand not found'], 404);
        }

        return response()->json($brand);
    }

    public function store(Request $request)
    {
        $brand = new \App\Models\Brand;

        $brand->fill($request->all());

        $brand->save();

        return response()->json($brand);
    }

    public function update(Request $request, $id)
    {
        $brand = \App\Models\Brand::find($id);

        if (!$brand) {
            return response()->json(['error' => 'Brand not found'], 404);
        }

        $brand->fill($request->all());

        $brand->save();

        return response()->json($brand);
    }

    public function destroy($id)
    {
        $brand = \App\Models\Brand::find($id);

        if (!$brand) {
            return response()->json(['error' => 'Brand not found'], 404);
        }

        $brand->delete();

        return response()->json($brand);
    }
}
