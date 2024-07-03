<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $users = \App\Models\User::with(['roles', 'manager', 'structure']);

        if ($request->get('role')) {
            $roleValue = $request->get('role');
            $users->whereHas('roles', function ($q) use ($roleValue) {
                $q->where('roles.name', $roleValue);
            });
        }

        if ($request->filled('enabled')) {
            $users->where('enabled', $request->get('enabled'));
        }

        if ($request->filled('isTeamLeader')) {
            $users->where('team_leader', $request->get('isTeamLeader'));
        }

        if ($request->get('q')) {
            $search = $request->get('q');
            $users->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('area', 'like', "%{$search}%");
            });
        }

        if ($request->get('sortBy')) {
            $users->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        }

        $users = $users->paginate($perPage);

        $users->getCollection()->transform(function ($user) {
            $user->role = $user->roles->first();
            return $user;
        });

        return response()->json([
            'users' => $users->getCollection(),
            'totalPages' => $users->lastPage(),
            'totalUsers' => $users->total(),
            'page' => $users->currentPage()
        ]);
    }

    public function agents(Request $request)
    {
        $agents = \App\Models\User::where('enabled', 1)
            ->role('agente')
            ->orderBy('name', 'asc');

        if ($request->get('select') === '1') {
            $agents = $agents->select('id', 'name', 'last_name');
        }

        return response()->json(['agents' => $agents->get()]);
    }
}
