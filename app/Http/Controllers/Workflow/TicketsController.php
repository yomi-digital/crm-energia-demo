<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TicketsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $tickets = \App\Models\Ticket::with(['createdBy', 'paperwork', 'paperwork.customer', 'paperwork.user', 'paperwork.product', 'paperwork.product.brand'])->withCount('attachments');

        // If the user has role agente, filter only for tickets related to his paperworks.
        if ($request->user()->hasRole('agente') || $request->user()->hasRole('struttura')) {
            // Get all paperworks of the agent.
            $paperworks = \App\Models\Paperwork::where('user_id', $request->user()->id)->pluck('id');
            $tickets = $tickets->whereIn('paperwork_id', $paperworks);
        } elseif ($request->user()->hasRole('backoffice')) {
            // Per il backoffice mostra solo i ticket associati alle pratiche
            // il cui brand è abilitato per l'utente corrente
            $brandIds = $request->user()->brands->pluck('id');

            $tickets = $tickets->whereHas('paperwork.product.brand', function ($query) use ($brandIds) {
                $query->whereIn('id', $brandIds);
            });
            
            // Filtro per area: il backoffice vede ticket associati a pratiche create da utenti con la stessa area
            // O da utenti senza area (null o stringa vuota) - queste sono visibili a tutti i backoffice
            /* if ($request->user()->area) {
                $tickets = $tickets->whereHas('paperwork.user', function ($query) use ($request) {
                    $query->where('area', $request->user()->area)
                          ->orWhereNull('area')
                          ->orWhere('area', '');
                });
            } */
        }

        if ($request->get('q')) {
            $search = $request->get('q');
            $tickets = $tickets->where(function ($query) use ($search) {
                $query->where('paperwork_id', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->get('status')) {
            $statuses = explode(',', $request->get('status'));
            $tickets = $tickets->whereIn('status', $statuses);
        }

        // Filtro per escludere i ticket risolti (status = 3)
        $tickets = $tickets->where('status', '!=', 3);

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

        // Handle attachments if present

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Upload file using the existing upload mechanism
                $scope = 'tickets/' . $ticket->id;
                $path = $file->storeAs($scope, $file->getClientOriginalName(), [
                    'disk' => 'do',
                    'visibility' => 'private'
                ]);

                // Create attachment record
                $attachment = new \App\Models\TicketAttachment;
                $attachment->ticket_id = $ticket->id;
                $attachment->paperwork_id = $ticket->paperwork_id;
                $attachment->name = $file->getClientOriginalName();
                $attachment->url = $path;
                $attachment->mime_type = $file->getMimeType();
                $attachment->size = $file->getSize();
                $attachment->save();
            }
        }

        $ticket->load('attachments');

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
        $ticket = \App\Models\Ticket::with(['createdBy', 'comments', 'comments.user', 'paperwork', 'paperwork.product', 'paperwork.customer', 'attachments'])->withCount('attachments')->whereId($id);
        
        // Filtro per backoffice: vedere solo ticket associati a pratiche visibili
        if ($request->user()->hasRole('backoffice')) {
            $brandIds = $request->user()->brands->pluck('id');
            
            $ticket = $ticket->whereHas('paperwork.product.brand', function ($query) use ($brandIds) {
                $query->whereIn('id', $brandIds);
            });
            
            // Filtro per area: il backoffice vede ticket associati a pratiche create da utenti con la stessa area
            // O da utenti senza area (null o stringa vuota) - queste sono visibili a tutti i backoffice
            /* if ($request->user()->area) {
                $ticket = $ticket->whereHas('paperwork.user', function ($query) use ($request) {
                    $query->where('area', $request->user()->area)
                          ->orWhereNull('area')
                          ->orWhere('area', '');
                });
            } */
        }
        
        $ticket = $ticket->first();

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        // $comments = $ticket->comments()->whereNull('read_by')->where('user_by', '!=', $request->user()->id)->update([
        //     'read_by' => $request->user()->id,
        //     'read_at' => now()->format('Y-m-d H:i:s')
        // ]);

        return response()->json($ticket);
    }

    public function downloadAttachment(Request $request, $id, $attachmentId)
    {
        $ticket = \App\Models\Ticket::find($id);

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        $attachment = \App\Models\TicketAttachment::find($attachmentId);

        if (!$attachment) {
            return response()->json(['error' => 'Attachment not found'], 404);
        }

        // Verifica che l'allegato appartenga al ticket
        if ($attachment->ticket_id != $ticket->id) {
            return response()->json(['error' => 'Attachment does not belong to this ticket'], 403);
        }

        return \Storage::disk('do')->download($attachment->url);
    }

    public function addAttachments(Request $request, $id)
    {
        $ticket = \App\Models\Ticket::find($id);

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        $attachments = [];

        if ($request->hasFile('attachments')) {
            
            foreach ($request->file('attachments') as $index => $file) {

                try {
                    // Upload file using the existing upload mechanism
                    $scope = 'tickets/' . $ticket->id;
                    $path = $file->storeAs($scope, $file->getClientOriginalName(), [
                        'disk' => 'do',
                        'visibility' => 'private'
                    ]);

                    // Create attachment record
                    $attachment = new \App\Models\TicketAttachment;
                    $attachment->ticket_id = $ticket->id;
                    $attachment->paperwork_id = $ticket->paperwork_id;
                    $attachment->name = $file->getClientOriginalName();
                    $attachment->url = $path;
                    $attachment->mime_type = $file->getMimeType();
                    $attachment->size = $file->getSize();
                    $attachment->save();

                    $attachments[] = $attachment;
                } catch (\Exception $e) {
                    return response()->json([
                        'error' => 'Error processing file',
                        'message' => $e->getMessage()
                    ], 500);
                }
            }
        } else {
            return response()->json([
                'error' => 'No attachments provided',
                'message' => 'Please select at least one file to upload'
            ], 400);
        }

        $ticket->load('attachments');

        return response()->json([
            'message' => 'Attachments added successfully',
            'attachments' => $attachments,
            'ticket' => $ticket
        ], 201);
    }
}
