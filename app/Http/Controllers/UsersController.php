<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $users = \App\Models\User::with(['roles', 'manager', 'agency']);

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

        if ($request->user()->hasRole('agente') || $request->user()->hasRole('telemarketing')) {
            $users->where('id', $request->user()->id);
        } elseif ($request->user()->hasRole('struttura') || $request->user()->hasRole('team leader')) {
            $relationships = \App\Models\UserRelationship::where('user_id', $request->user()->id)->get(['related_id']);
            $ids = $relationships->pluck('related_id')->merge([$request->user()->id]);
            $users->whereIn('id', $ids);
        }

        if ($request->get('q')) {
            $search = $request->get('q');
            $users->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(name, ' ', last_name) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("CONCAT(last_name, ' ', name) LIKE ?", ["%{$search}%"])
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('area', 'like', "%{$search}%");
            });
        }

        if ($request->get('relationships')) {
            $relationships = \App\Models\UserRelationship::where('user_id', $request->get('relationships'))->get(['related_id']);
            $users->whereIn('id', $relationships->pluck('related_id'));
        }

        if ($request->get('sortBy')) {
            $users->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        } else {
            $users->orderBy('name', 'asc');
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

    public function store(Request $request)
    {
        $request->validate([
            'area' => 'required|string',
        ], [
            'area.required' => 'Il campo area è obbligatorio.',
        ]);

        $user = new \App\Models\User($request->all());

        if ($request->filled('password')) {
            $user->password = bcrypt($request->get('password'));
            // Imposta flag per cambio password obbligatorio al primo login
            $user->must_change_password = true;
        }

        $role = $request->get('role');
        if (! $role) {
            $role = 'agente';
        }

        $user->save();
        $user->assignRole($role);

        return response()->json($user, 201);
    }

    public function agents(Request $request)
    {
        // Ruoli base: agenti e strutture
        $scopeRoles = ['agente', 'struttura'];

        // Se gestione=1 includiamo anche il ruolo "gestione" tra i risultati
        if ($request->get('gestione') === '1') {
            $scopeRoles[] = 'gestione';
        }

        // Se backoffice=1 includiamo anche il ruolo "backoffice" tra i risultati
        if ($request->get('backoffice') === '1') {
            $scopeRoles[] = 'backoffice';
        }

        $agents = \App\Models\User::where('enabled', 1)
            ->role($scopeRoles)
            ->orderBy('name', 'asc');

        if ($request->get('id')) {
            $agents = $agents->where('id', $request->get('id'));
        }

        if ($request->get('select') === '1') {
            $agents = $agents->select('id', 'name', 'last_name');
        }

        // If the looged in user has role 'agente', filter for only his customers
        if ($request->user()->hasRole('agente')) {
            $agents = $agents->where('id', $request->user()->id);
        } elseif ($request->user()->hasRole('struttura')) {
            $relationships = \App\Models\UserRelationship::where('user_id', $request->user()->id)->get(['related_id']);
            $ids = $relationships->pluck('related_id')->merge([$request->user()->id]);
            $agents = $agents->whereIn('id', $ids);
        }

        return response()->json(['agents' => $agents->get()]);
    }

    public function structures(Request $request)
    {
        $structures = \App\Models\User::where('enabled', 1)
            ->role('struttura')
            ->orderBy('name', 'asc');

        if ($request->get('select') === '1') {
            $structures = $structures->select('id', 'name', 'last_name');
        }

        return response()->json(['structures' => $structures->get()]);
    }

    public function show(Request $request, $id)
    {
        $user = \App\Models\User::with(['roles', 'manager', 'agency'])->whereId($id)->first();

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($request->user()->hasRole('agent') || $request->user()->hasRole('telemarketing')) {
            if ($user->id !== $request->user()->id) {
                return response()->json(['message' => 'User not found'], 404);
            }
        } elseif ($request->user()->hasRole('struttura') || $request->user()->hasRole('team leader')) {
            $hasUser = \App\Models\UserRelationship::where('user_id', $request->user()->id)->where('related_id', $id)->get();

            if ($user->id !== $request->user()->id && ! $hasUser->count()) {
                return response()->json(['message' => 'User not found'], 404);
            }
        }

        $user->related_users = \App\Models\UserRelationship::where('user_id', $id)->with('user', 'user.roles')->get();
        $user->related_to = \App\Models\UserRelationship::where('related_id', $id)->with('user', 'user.roles')->get();

        $user->role = $user->roles->first();

        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = \App\Models\User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->fill($request->all());

        // Aggiorna ruolo se presente nel payload
        if ($request->filled('role')) {
            $roleInput = $request->input('role');
            $roleModel = null;

            if (is_array($roleInput) && isset($roleInput['id'])) {
                $roleModel = Role::find($roleInput['id']);
            } elseif (is_string($roleInput)) {
                $roleModel = Role::where('name', $roleInput)->first();
            }

            if ($roleModel) {
                $user->syncRoles([$roleModel->name]);
            }
        }

        if ($request->filled('password')) {
            $user->password = bcrypt($request->get('password'));
        }

        $user->save();

        // Ritorna anche il ruolo aggiornato per il frontend
        $user->load(['roles', 'manager', 'agency']);
        $user->role = $user->roles->first();

        return response()->json($user);
    }

    public function destroyRelationship(Request $request, $id, $relatedId)
    {
        $relationship = \App\Models\UserRelationship::where('user_id', $id)->where('related_id', $relatedId)->first();

        if (! $relationship) {
            return response()->json(['message' => 'Relationship not found'], 404);
        }

        $relationship->delete();

        return response()->json(['message' => 'Relationship deleted']);
    }

    public function addRelationship(Request $request, $id)
    {
        $user = \App\Models\User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $relationships = $request->get('users');
        // Existing relationships
        $existing = \App\Models\UserRelationship::where('user_id', $id)->get(['related_id']);
        $users = \App\Models\User::with('roles')->whereIn('id', $relationships)->whereNotIn('id', $existing->pluck('related_id'))->get();

        foreach ($users as $related) {
            \App\Models\UserRelationship::create([
                'user_id' => $id,
                'related_id' => $related->id,
                'role' => $related->roles->first()->name,
            ]);
        }

        return response()->json(null, 201);
    }

    public function brands(Request $request, $id)
    {
        $user = \App\Models\User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $perPage = $request->get('itemsPerPage', 10);

        $brands = $user->brands()->paginate($perPage);

        return response()->json([
            'brands' => $brands->getCollection(),
            'totalPages' => $brands->lastPage(),
            'totalBrands' => $brands->total(),
            'page' => $brands->currentPage()
        ]);
    }

    /**
     * Restituisce i brand abilitati che l'utente NON ha ancora assegnati.
     */
    public function availableBrands(Request $request, $id)
    {
        $user = \App\Models\User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Brand già collegati all'utente
        $userBrandIds = $user->brands()->pluck('brands.id');

        // Solo brand enabled e non ancora assegnati all'utente
        $brands = \App\Models\Brand::where('enabled', 1)
            ->whereNotIn('id', $userBrandIds)
            ->orderBy('name', 'asc')
            ->select('id', 'name', 'type', 'category')
            ->get();

        return response()->json(['brands' => $brands]);
    }

    public function addBrand(Request $request, $id)
    {
        $user = \App\Models\User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $userBrands = $user->brands()->pluck('brand_id');

        $brands = \App\Models\Brand::whereIn('id', $request->get('brands'))
            ->whereNotIn('id', $userBrands)->get();

        foreach ($brands as $brand) {
            $user->brands()->attach($brand, [
                'race' => $request->get('race'),
                'bonus' => $request->get('bonus', 0),
                'pay_level' => $request->get('pay_level')
            ]);
        }

        return response()->json(null, 201);
    }

    public function destroyBrand(Request $request, $id, $brandId)
    {
        $user = \App\Models\User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $brand = \App\Models\Brand::find($brandId);

        if (! $brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }

        $user->brands()->detach($brand);

        return response()->json(null, 204);
    }

    public function updateBrand(Request $request, $id, $brandId)
    {
        $user = \App\Models\User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $brand = \App\Models\Brand::find($brandId);

        if (! $brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }

        $user->brands()->updateExistingPivot($brandId, ['pay_level' => $request->get('pay_level')]);

        return response()->json(null, 204);
    }

    public function appointments(Request $request, $id)
    {
        $user = \App\Models\User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $perPage = $request->get('itemsPerPage', 10);

        $appointments = \App\Models\Calendar::where(function ($query) use ($user) {
            $query->where('agent_id', $user->id)
                ->orWhere('created_by', $user->id);
        })
            ->orderBy('start', 'desc')
            ->paginate($perPage);

        $appointments->getCollection()->transform(function ($appointment) {
            $appointment->start = \Carbon\Carbon::parse($appointment->start)->format(config('app.datetime_format'));
            $appointment->end = \Carbon\Carbon::parse($appointment->end)->format(config('app.datetime_format'));
            return $appointment;
        });

        return response()->json([
            'appointments' => $appointments->getCollection(),
            'totalPages' => $appointments->lastPage(),
            'totalAppointments' => $appointments->total(),
            'page' => $appointments->currentPage()
        ]);
    }

    public function notifications(Request $request)
    {
        $user = $request->user();

        $perPage = $request->get('itemsPerPage', 30);

        $notifications = $user->notifications();

        // Filtro per tipo di notifica
        if ($request->filled('notification_type')) {
            $notificationType = $request->get('notification_type');
            
            if ($notificationType === 'Calendar') {
                $notifications = $notifications->whereIn('type', ['calendar-created', 'calendar-updated', 'calendar-deleted']);
            } elseif ($notificationType === 'Ticket') {
                $notifications = $notifications->whereIn('type', ['ticket-created', 'ticket-comment-created']);
            } elseif ($notificationType === 'Paperwork') {
                $notifications = $notifications->where('type', 'paperwork-created');
            }
        }

        $notifications = $notifications->paginate($perPage);

        // Format the notfications
        $notifications->getCollection()->transform(function ($notification) {
            $transformed = new \stdClass();
            $transformed->id = $notification->id;
            $transformed->type = $notification->type;
            if ($transformed->type === 'calendar-created') {
                $transformed->title = 'Nuova appuntamento';
                $transformed->subtitle = $notification->data['status'] . ' - ' . $notification->data['title'] . ' il ' . $notification->data['start'];
                $transformed->icon = 'tabler-calendar';
                $transformed->color = 'primary';
                $transformed->link = '/general/calendar?appointment=' . $notification->data['calendar_id'];
            } elseif ($transformed->type === 'calendar-updated') {
                $transformed->title = 'Appuntamento modificato';
                $transformed->subtitle = $notification->data['status'] . ' - ' . $notification->data['title'] . ' del ' . $notification->data['start'];
                $transformed->icon = 'tabler-calendar';
                $transformed->color = 'warning';
                $transformed->link = '/general/calendar?appointment=' . $notification->data['calendar_id'];
            } elseif ($transformed->type === 'calendar-deleted') {
                $transformed->title = 'Appuntamento eliminato';
                $transformed->subtitle = $notification->data['title'] . ' del ' . $notification->data['start'];
                $transformed->icon = 'tabler-calendar';
                $transformed->color = 'error';
                // Per gli appuntamenti eliminati non ha senso linkare, ma lo mettiamo comunque al calendario
                $transformed->link = '/general/calendar';
            } elseif ($transformed->type === 'paperwork-created') {
                $transformed->title = 'Nuova pratica';
                $transformed->subtitle = $notification->data['brand'] . ' | ' . $notification->data['product'];
                $transformed->icon = 'tabler-file-text';
                $transformed->color = 'success';
                $transformed->link = '/workflow/paperworks/' . $notification->data['paperwork_id'];
            } elseif ($transformed->type === 'paperwork-status-updated') {
                $transformed->title = 'Stato pratica aggiornato';
                $transformed->subtitle = 'Pratica #' . $notification->data['paperwork_id'] . ' - ' . ($notification->data['order_status'] ?? 'N/A');
                $transformed->icon = 'tabler-file-text';
                $transformed->color = 'warning';
                $transformed->link = '/workflow/paperworks/' . $notification->data['paperwork_id'];
            } elseif ($transformed->type === 'ticket-created') {
                $transformed->title = 'Nuovo ticket per la pratica #' . $notification->data['paperwork_id'];
                $transformed->subtitle = $notification->data['ticket_title'] . ' da ' . $notification->data['created_by_name'];
                $transformed->icon = 'tabler-mail-opened';
                $transformed->color = 'info';
                $transformed->link = '/workflow/tickets/' . $notification->data['ticket_id'];
            } elseif ($transformed->type === 'ticket-comment-created') {
                $transformed->title = $notification->data['user_name'] . ' ha risposto al ticket ' . $notification->data['ticket_title'];
                $transformed->subtitle = strlen($notification->data['comment_text']) > 50 ? substr($notification->data['comment_text'], 0, 50) . '...' : $notification->data['comment_text'];
                $transformed->icon = 'tabler-message-circle';
                $transformed->color = 'info';
                $transformed->link = '/workflow/tickets/' . $notification->data['ticket_id'];
            }
            $transformed->time = \Carbon\Carbon::parse($notification->created_at)->format(config('app.datetime_format'));
            $transformed->isSeen = $notification->read_at ? true : false;
            return $transformed;
        });

        return response()->json($notifications);
    }

    public function read(Request $request, $id)
    {
        $user = $request->user();
        $user->notifications()->where('id', $id)->update(['read_at' => \Carbon\Carbon::now()]);
        return response()->json(null, 204);
    }

    public function unread(Request $request, $id)
    {
        $user = $request->user();
        $user->notifications()->where('id', $id)->update(['read_at' => null]);
        return response()->json(null, 204);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $user->update($request->only(['name', 'last_name', 'email', 'phone', 'avatar']));
        
        return response()->json($user);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();
        
        // Se l'utente deve cambiare la password (primo login), non richiediamo la password corrente
        if (!$user->must_change_password) {
            // Check if the current password is correct
            if (!\Hash::check($request->get('current_password'), $request->user()->password)) {
                return response()->json(['message' => 'La password corrente non è corretta'], 400);
            }
        }

        // Check if the new password and the confirm password are the same
        if ($request->get('new_password') !== $request->get('confirm_password')) {
            return response()->json(['message' => 'La nuova password e la password di conferma non corrispondono'], 400);
        }

        // Aggiorna password e resetta il flag
        $user->update([
            'password' => bcrypt($request->get('new_password')),
            'must_change_password' => false
        ]);
        
        return response()->json($user);
    }

    public function loginLogs(Request $request)
    {
        // Controllo che l'utente sia admin
        if (!$request->user()->hasRole(['gestione', 'backoffice', 'amministrazione'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $perPage = min($request->get('itemsPerPage', 25), 100); // Limite massimo 100
        $page = $request->get('page', 1);

        $users = \App\Models\User::select([
            'id',
            'name',
            'last_name',
            'last_login_at',
            'last_logout_at',
            'ip'
        ])
        ->with('roles') // Include i ruoli per le icone
        ->whereNotNull('last_login_at') // Solo utenti che hanno fatto almeno un login
        ->orderBy('last_login_at', 'desc');

        $users = $users->paginate($perPage, ['*'], 'page', $page);

        $users->getCollection()->transform(function ($user) {
            $user->username = trim(implode(' ', [$user->name, $user->last_name]));
            return $user;
        });

        return response()->json([
            'users' => $users->getCollection(),
            'totalPages' => $users->lastPage(),
            'totalUsers' => $users->total(),
            'page' => $users->currentPage()
        ]);
    }
}
