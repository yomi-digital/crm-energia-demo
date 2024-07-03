<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $calendar = \App\Models\Calendar::with('agent');

        if ($request->start) {
            $calendar = $calendar->where('start', '>=', $request->start);
        }
        if ($request->end) {
            $calendar = $calendar->where('end', '<=', $request->end);
        }

        $statuses = explode(',', $request->calendars);
        if (count($statuses)) {
            $calendar = $calendar->where(function ($query) use ($statuses) {
                if (in_array('N/A', $statuses)) {
                    $query->whereIn('status', $statuses)->orWhereNull('status');
                } else {
                    $query->whereIn('status', $statuses);
                }
            });
        }

        $calendar = $calendar->get();

        $calendar = $calendar->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title ?: $item->user_name,
                'start' => $item->start,
                'end' => $item->end,
                'allDay' => $item->all_day ?? false,
                'extendedProps' => [
                    'calendar' => $item->status,
                    'agent_id' => $item->agent_id,
                    'agent' => $item->agent,
                    'referent' => $item->referent,
                    'user_name' => $item->user_name,
                    'user_phone' => $item->user_phone,
                    'user_mobile' => $item->user_mobile,
                    'user_address' => $item->user_address,
                    'user_city' => $item->user_city,
                    'type' => $item->type,
                    'cost' => $item->cost,
                    'notes_call_center' => $item->notes_call_center,
                    'notes_agent' => $item->notes_agent,
                    'notes' => $item->notes,
                ],
            ];
            // $item->start = $item->start_date;
            // $item->end = $item->end_date;
            // return $item;
        });


        return response()->json($calendar);
    }

    public function store(Request $request)
    {
        $calendar = new \App\Models\Calendar;
        $calendar->title = $request->title;
        $calendar->start = \Carbon\Carbon::parse($request->start)->format('Y-m-d H:i:s');
        $calendar->end = \Carbon\Carbon::parse($request->end)->format('Y-m-d H:i:s');
        $calendar->all_day = $request->allDay;
        $calendar->status = $request->extendedProps['calendar'];
        $calendar->agent_id = $request->extendedProps['agent_id'];
        $calendar->referent = $request->extendedProps['referent'];
        $calendar->user_name = $request->extendedProps['user_name'];
        $calendar->user_phone = $request->extendedProps['user_phone'];
        $calendar->user_mobile = $request->extendedProps['user_mobile'];
        $calendar->user_address = $request->extendedProps['user_address'];
        $calendar->user_city = $request->extendedProps['user_city'];
        $calendar->type = $request->extendedProps['type'];
        if (isset($request->extendedProps['cost'])) {
            $calendar->cost = $request->extendedProps['cost'];
        }
        $calendar->notes_call_center = $request->extendedProps['notes_call_center'];
        $calendar->notes_agent = $request->extendedProps['notes_agent'];
        $calendar->notes = $request->extendedProps['notes'];
        $calendar->created_by = auth()->user()->id;

        $calendar->save();

        return response()->json($calendar);
    }

    public function update(Request $request, $id)
    {
        $calendar = \App\Models\Calendar::find($id);
        $calendar->title = $request->title;
        $calendar->start = \Carbon\Carbon::parse($request->start)->format('Y-m-d H:i:s');
        $calendar->end = \Carbon\Carbon::parse($request->end)->format('Y-m-d H:i:s');
        $calendar->all_day = $request->allDay;
        $calendar->status = $request->extendedProps['calendar'];
        $calendar->agent_id = $request->extendedProps['agent_id'];
        $calendar->referent = $request->extendedProps['referent'];
        $calendar->user_name = $request->extendedProps['user_name'];
        $calendar->user_phone = $request->extendedProps['user_phone'];
        $calendar->user_mobile = $request->extendedProps['user_mobile'];
        $calendar->user_address = $request->extendedProps['user_address'];
        $calendar->user_city = $request->extendedProps['user_city'];
        $calendar->type = $request->extendedProps['type'];
        if (isset($request->extendedProps['cost'])) {
            $calendar->cost = $request->extendedProps['cost'];
        }
        $calendar->notes_call_center = $request->extendedProps['notes_call_center'];
        $calendar->notes_agent = $request->extendedProps['notes_agent'];
        $calendar->notes = $request->extendedProps['notes'];

        $calendar->save();

        return response()->json($calendar);
    }

    public function destroy(Request $request, $id)
    {
        $event = \App\Models\Calendar::find($id);
        $event->delete();

        return response()->json(['message' => 'Calendar event removed']);
    }

    public function search(Request $request)
    {
        $search = $request->q;
        $calendar = \App\Models\Calendar::select(['id', 'title', 'start', 'end', 'status', 'referent', 'user_name', 'user_phone', 'user_mobile', 'user_address', 'user_city']);
        if ($request->agent_id) {
            $calendar = $calendar->where('agent_id', $request->agent_id);
        }
        $calendar = $calendar->where(function ($query) use ($search) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('user_name', 'like', "%{$search}%")
                ->orWhere('user_phone', 'like', "%{$search}%")
                ->orWhere('user_mobile', 'like', "%{$search}%")
                ->orWhere('user_address', 'like', "%{$search}%")
                ->orWhere('user_city', 'like', "%{$search}%");
        });

        $calendar = $calendar->orderBy('start', 'desc');

        return response()->json($calendar->take(20)->get());
    }
}
