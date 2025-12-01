<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
            // Trasforma RESIDENZIALE -> Residenziale, BUSINESS -> Business
            $formattedType = ucfirst(strtolower($request->get('type')));
            $brands = $brands->where('type', $formattedType);
        }
        
        if ($request->get('category')) {
            // Trasforma ENERGIA -> Energia, TELEFONIA -> Telefonia
            $formattedCategory = ucfirst(strtolower($request->get('category')));
            $brands = $brands->where('category', $formattedCategory);
        }

        if ($request->get('agent')) {
            $agentBrands = \App\Models\User::whereId($request->get('agent'))->first()->brands->pluck('id');
            $brands = $brands->whereIn('id', $agentBrands);
        } else {
            if ($request->user()->hasRole('agente')) {
                $agentBrands = $request->user()->brands->pluck('id');
                $brands = $brands->whereIn('id', $agentBrands);
            }
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
            $brands = $brands->select('id', 'name', 'type', 'category');
        }


        $brands = $brands->paginate($perPage);

        return response()->json([
            'brands' => $brands->getCollection(),
            'totalPages' => $brands->lastPage(),
            'totalBrands' => $brands->total(),
            'page' => $brands->currentPage()
        ]);
    }

    /**
     * Endpoint personalizzato che filtra automaticamente i brand in base all'utente corrente
     */
    public function personal(Request $request)
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
            // Trasforma RESIDENZIALE -> Residenziale, BUSINESS -> Business
            $formattedType = ucfirst(strtolower($request->get('type')));
            $brands = $brands->where('type', $formattedType);
        }
        
        if ($request->get('category')) {
            // Trasforma ENERGIA -> Energia, TELEFONIA -> Telefonia
            $formattedCategory = ucfirst(strtolower($request->get('category')));
            $brands = $brands->where('category', $formattedCategory);
        }

        // FILTRO AUTOMATICO: mostra solo i brand assegnati all'utente corrente
        // ECCEZIONE: gestione e amministrazione vedono tutti i brand
        if ($request->user()->hasRole('gestione')) {
            // Admin roles: nessun filtro, vedono tutto
        } else {
            // Altri ruoli: filtro per brand assegnati
            $userBrands = $request->user()->brands->pluck('id');
            if ($userBrands->isNotEmpty()) {
                $brands = $brands->whereIn('id', $userBrands);
            } else {
                // Se l'utente non ha brand assegnati, non vede nessun brand
                $brands = $brands->whereRaw('1 = 0');
            }
        }

        if ($request->get('q')) {
            $search = $request->get('q');
            $brands = $brands->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->get('sortBy')) {
            $brands = $brands->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        } else {
            $brands = $brands->orderBy('name', 'asc');
        }

        if ($request->get('select') === '1') {
            $brands = $brands->select('id', 'name', 'type', 'category');
        }

        $brands = $brands->paginate($perPage);

        return response()->json([
            'brands' => $brands->getCollection(),
            'totalPages' => $brands->lastPage(),
            'totalBrands' => $brands->total(),
            'page' => $brands->currentPage()
        ]);
    }

    /**
     * Endpoint che restituisce i brand NON assegnati all'utente corrente (opposto di personal)
     */
    public function notPersonal(Request $request)
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
            // Trasforma RESIDENZIALE -> Residenziale, BUSINESS -> Business
            $formattedType = ucfirst(strtolower($request->get('type')));
            $brands = $brands->where('type', $formattedType);
        }
        
        if ($request->get('category')) {
            // Trasforma ENERGIA -> Energia, TELEFONIA -> Telefonia
            $formattedCategory = ucfirst(strtolower($request->get('category')));
            $brands = $brands->where('category', $formattedCategory);
        }

        // FILTRO AUTOMATICO: mostra tutti i brand TRANNE quelli assegnati all'utente corrente
        // ECCEZIONE: gestione vede sempre tutti i brand (come in personal)
        if ($request->user()->hasRole('gestione')) {
            // Admin roles: vedono sempre tutti i brand
        } else {
            // Altri ruoli: filtro per brand NON assegnati
            $userBrands = $request->user()->brands->pluck('id');
            if ($userBrands->isNotEmpty()) {
                $brands = $brands->whereNotIn('id', $userBrands);
            }
            // Se l'utente non ha brand assegnati, vede tutti i brand (nessun filtro)
        }

        if ($request->get('q')) {
            $search = $request->get('q');
            $brands = $brands->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->get('sortBy')) {
            $brands = $brands->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        } else {
            $brands = $brands->orderBy('name', 'asc');
        }

        if ($request->get('select') === '1') {
            $brands = $brands->select('id', 'name', 'type', 'category');
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

        // Salviamo il vecchio nome per capire se dobbiamo rinominare la cartella documenti
        $oldName = $brand->name;

        // Impediamo di usare un nome già esistente (anche se il brand è disabilitato)
        // Escludiamo solo il brand corrente dall'unicità
        $request->validate([
            'name' => [
                'sometimes',
                'required',
                // Il nome non può coincidere con NESSUN altro brand (inclusi quelli soft-deleted)
                Rule::unique('brands', 'name')->ignore($brand->id),
            ],
        ]);

        $brand->fill($request->all());

        $nameHasChanged = $brand->isDirty('name');
        $newName = $brand->name;

        // Se il nome è cambiato, prima proviamo a rinominare su DigitalOcean;
        if ($nameHasChanged && $oldName && $newName && $oldName !== $newName) {
            try {
                $disk = Storage::disk('do');

                $oldPrefix = 'documents/' . $oldName;
                $newPrefix = 'documents/' . $newName;

                $files = $disk->allFiles($oldPrefix);

                foreach ($files as $filePath) {
                    $newPath = preg_replace(
                        '#^' . preg_quote($oldPrefix, '#') . '#',
                        $newPrefix,
                        $filePath,
                        1
                    );

                    if ($newPath && $newPath !== $filePath) {
                        $disk->move($filePath, $newPath);
                    }
                }

                // Puliamo eventuali "directory" vuote rimaste con il vecchio nome
                $disk->deleteDirectory($oldPrefix);
            } catch (\Throwable $e) {
                return response()->json([
                    'error' => 'Impossibile rinominare la cartella documenti. Il nome del brand non può essere modificato.',
                ], 500);
            }
        }

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
