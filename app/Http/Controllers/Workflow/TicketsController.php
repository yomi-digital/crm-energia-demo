<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TicketsController extends Controller
{
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
