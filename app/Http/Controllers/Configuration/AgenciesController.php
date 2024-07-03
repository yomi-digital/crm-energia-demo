<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgenciesController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $agencies = new \App\Models\Agency;

        if ($request->get('q')) {
            $search = $request->get('q');
            $agencies = $agencies->where('name', 'like', "%{$search}%");
        }

        if ($request->get('sortBy')) {
            $agencies = $agencies->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        } else {
            $agencies = $agencies->orderBy('name', 'asc');
        }

        $agencies = $agencies->paginate($perPage);

        return response()->json([
            'agencies' => $agencies->getCollection(),
            'totalPages' => $agencies->lastPage(),
            'totalAgencies' => $agencies->total(),
            'page' => $agencies->currentPage()
        ]);
    }

    public function show($id)
    {
        $agency = \App\Models\Agency::find($id);

        if (!$agency) {
            return response()->json(['error' => 'Agency not found'], 404);
        }

        return response()->json($agency);
    }

    public function store(Request $request)
    {
        $agency = new \App\Models\Agency;

        $agency->fill($request->all());

        $agency->save();

        return response()->json($agency);
    }

    public function update(Request $request, $id)
    {
        $agency = \App\Models\Agency::find($id);

        if (!$agency) {
            return response()->json(['error' => 'Agency not found'], 404);
        }

        $agency->fill($request->all());

        $agency->save();

        return response()->json($agency);
    }

    public function destroy($id)
    {
        $agency = \App\Models\Agency::find($id);

        if (!$agency) {
            return response()->json(['error' => 'Agency not found'], 404);
        }

        $agency->delete();

        return response()->json($agency);
    }
}
