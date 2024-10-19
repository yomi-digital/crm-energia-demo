<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TicketsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $tickets = new \App\Models\Ticket();

        if ($request->filled('customer_id')) {
            $tickets = $tickets->where('customer_id', $request->get('customer_id'));
        }

        if ($request->filled('user_id')) {
            $tickets = $tickets->where('user_id', $request->get('user_id'));
        }

        if ($request->get('q')) {
            $search = $request->get('q');
            $tickets = $tickets->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('area', 'like', "%{$search}%");
            });
        }

        if ($request->get('sortBy')) {
            $tickets = $tickets->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        } else {
            $tickets = $tickets->orderBy('created_at', 'desc');
        }

        $tickets = $tickets->paginate($perPage);

        return response()->json([
            'tickets' => $tickets->getCollection(),
            'totalPages' => $tickets->lastPage(),
            'totalTickets' => $tickets->total(),
            'page' => $tickets->currentPage()
        ]);
    }

    public function store(Request $request)
    {
        $ticket = new \App\Models\Ticket;

        $ticket->fill($request->all());
        $ticket->created_by = $request->user()->id;
        if ($request->get('paperwork_id')) {
            // Should validate if paperwork exists, if the user is an "agent" and it's one of his paperworks.
            $ticket->paperwork_id = $request->get('paperwork_id');
        }

        $ticket->save();

        return response()->json($ticket, 201);
    }

    public function close(Request $request, $id)
    {
        $ticket = \App\Models\Ticket::find($id);

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        $ticket->status = 3;
        $ticket->closed_by = $request->user()->id;
        $ticket->closed_at = now()->format('Y-m-d H:i:s');

        $ticket->save();

        return response()->json($ticket);
    }

    public function comment(Request $request, $id)
    {
        $ticket = \App\Models\Ticket::find($id);

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }
        $ticket->status = 2;
        $ticket->save();

        $comment = new \App\Models\TicketComment;
        $comment->fill($request->all());
        $comment->ticket_id = $ticket->id;
        $comment->user_id = $request->user()->id;

        $comment->save();

        return response()->json($comment, 201);
    }

    public function show(Request $request, $id)
    {
        $ticket = \App\Models\Ticket::with(['createdBy', 'comments', 'comments.user'])->whereId($id)->first();

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        // $comments = $ticket->comments()->whereNull('read_by')->where('user_by', '!=', $request->user()->id)->update([
        //     'read_by' => $request->user()->id,
        //     'read_at' => now()->format('Y-m-d H:i:s')
        // ]);

        return response()->json($ticket);
    }
}
