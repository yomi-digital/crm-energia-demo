<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommunicationsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $communications = new \App\Models\Communication();

        if ($request->get('sortBy')) {
            $communications = $communications->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        } else {
            $communications = $communications->orderBy('created_at', 'desc');
        }

        $communications = $communications->paginate($perPage);

        return response()->json([
            'communications' => $communications->getCollection(),
            'totalPages' => $communications->lastPage(),
            'totalCommunications' => $communications->total(),
            'page' => $communications->currentPage()
        ]);
    }

    public function store(Request $request)
    {
        $communication = new \App\Models\Communication;

        $communication->fill($request->all());

        $communication->save();

        if ($request->get('send_email')) {
            // Here it should send an email to every user that has communications enabled.
            $users = \App\Models\User::where('communications_enabled', 1)->get();

            foreach ($users as $user) {
                // Mail::to($user->email)->send(new CommunicationEmail($communication));
            }
            $communication->sent_at = now();
            $communication->save();
        }

        return response()->json($communication, 201);
    }

    public function show(Request $request, $id)
    {
        $communication = \App\Models\Communication::findOrFail($id);

        if (!$communication) {
            return response()->json(['error' => 'Communication not found'], 404);
        }

        return response()->json($communication);
    }

    public function update(Request $request, $id)
    {
        $communication = \App\Models\Communication::findOrFail($id);

        $communication->fill($request->all());

        $communication->save();

        return response()->json($communication);
    }
}
