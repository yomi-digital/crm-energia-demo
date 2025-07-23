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

        if ($request->filled('brand')) {
            $products->where('brand_id', $request->get('brand'));
        }

        if ($request->user()->hasRole('agente')) {
            $agentBrands = $request->user()->brands->pluck('id');
            $products = $products->whereIn('brand_id', $agentBrands);
        }

        if ($request->get('enabled')) {
            $products = $products->where('enabled', $request->get('enabled'));
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

    /**
     * Endpoint personalizzato che filtra automaticamente i prodotti in base ai brand assegnati all'utente corrente
     */
    public function personal(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $products = \App\Models\Product::with(['brand']);

        if ($request->get('q')) {
            $search = $request->get('q');
            $products = $products->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('brand')) {
            $products->where('brand_id', $request->get('brand'));
        }

        if ($request->get('enabled')) {
            $products = $products->where('enabled', $request->get('enabled'));
        }

        // FILTRO AUTOMATICO: mostra solo i prodotti dei brand assegnati all'utente corrente
        // ECCEZIONE: gestione e amministrazione vedono tutti i prodotti
        if ($request->user()->hasRole('gestione')) {
            // Admin roles: nessun filtro, vedono tutto
        } else {
            // Altri ruoli: filtro per brand assegnati
            $userBrands = $request->user()->brands->pluck('id');
            if ($userBrands->isNotEmpty()) {
                $products = $products->whereIn('brand_id', $userBrands);
            } else {
                // Se l'utente non ha brand assegnati, non vede nessun prodotto
                $products = $products->whereRaw('1 = 0');
            }
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
        $product = \App\Models\Product::with('brand')->whereId($id)->first();

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

    public function feebands(Request $request, $id)
    {
        $product = \App\Models\Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $perPage = $request->get('itemsPerPage', 10);
        $feeBands = $product->feebands()->orderBy('is_default', 'desc')->orderBy('start_date', 'desc')->paginate($perPage);

        return response()->json([
            'feebands' => $feeBands->getCollection(),
            'totalPages' => $feeBands->lastPage(),
            'totalFeebands' => $feeBands->total(),
            'page' => $feeBands->currentPage()
        ]);
    }

    public function addFeeband(Request $request, $id)
    {
        $product = \App\Models\Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $feeBand = new \App\Models\Feeband;
        $input = $request->all();
        if (isset($input['start_date'])) {
            $input['start_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $input['start_date']);
        }
        if (isset($input['end_date'])) {
            $input['end_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $input['end_date']);
        }
        $feeBand->fill($input);
        $feeBand->product_id = $product->id;
        $feeBand->save();

        return response()->json($feeBand);
    }

    public function destroyFeeband(Request $request, $id, $feeBandId)
    {
        $product = \App\Models\Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $feeBand = \App\Models\Feeband::find($feeBandId);

        if (!$feeBand) {
            return response()->json(['error' => 'Feeband not found'], 404);
        }

        $feeBand->delete();

        return response()->json($feeBand);
    }

    public function updateFeeband(Request $request, $id, $feeBandId)
    {
        $product = \App\Models\Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $feeBand = \App\Models\Feeband::find($feeBandId);

        if (!$feeBand) {
            return response()->json(['error' => 'Feeband not found'], 404);
        }

        $input = $request->all();
        if (isset($input['start_date'])) {
            $input['start_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $input['start_date']);
        }
        if (isset($input['end_date'])) {
            $input['end_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $input['end_date']);
            if ($input['end_date'] < $input['start_date']) {
                $input['end_date'] = null;
            }
        }
        $feeBand->fill($input);
        $feeBand->save();

        return response()->json($feeBand);
    }
}
