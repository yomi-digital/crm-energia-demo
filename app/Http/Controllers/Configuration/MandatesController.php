<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MandatesController extends Controller
{
    public function index(Request $request)
    {
        // dd($request->user());
        // $user = \App\Models\User::find(1);
        // dd($user->roles()->first()->name);
        // $user = new \App\Models\User;
        // $user->name = 'Test User';
        // $user->email = 'admin@alfacom.com';
        // $user->password = bcrypt('password');
        // $user->legacy_id = 9999999;
        // $user->commercial_profile = 'asdfaf';
        // $user->save();
        // die;
        $perPage = $request->get('itemsPerPage', 10);

        $mandates = new \App\Models\Mandate;

        if ($request->get('q')) {
            $search = $request->get('q');
            $mandates = $mandates->where('name', 'like', "%{$search}%");
        }

        if ($request->get('sortBy')) {
            $mandates = $mandates->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        } else {
            $mandates = $mandates->orderBy('name', 'asc');
        }

        $mandates = $mandates->paginate($perPage);

        return response()->json([
            'mandates' => $mandates->getCollection(),
            'totalPages' => $mandates->lastPage(),
            'totalMandates' => $mandates->total(),
            'page' => $mandates->currentPage()
        ]);
    }

    public function show($id)
    {
        $mandate = \App\Models\Mandate::find($id);

        if (!$mandate) {
            return response()->json(['error' => 'Mandate not found'], 404);
        }

        return response()->json($mandate);
    }

    public function store(Request $request)
    {
        $mandate = new \App\Models\Mandate;

        $mandate->fill($request->all());

        $mandate->save();

        return response()->json($mandate);
    }

    public function update(Request $request, $id)
    {
        $mandate = \App\Models\Mandate::find($id);

        if (!$mandate) {
            return response()->json(['error' => 'Mandate not found'], 404);
        }

        $mandate->fill($request->all());

        $mandate->save();

        return response()->json($mandate);
    }

    public function destroy($id)
    {
        $mandate = \App\Models\Mandate::find($id);

        if (!$mandate) {
            return response()->json(['error' => 'Mandate not found'], 404);
        }

        $mandate->delete();

        return response()->json($mandate);
    }
}
