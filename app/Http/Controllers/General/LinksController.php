<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LinksController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $links = \App\Models\Link::with('brand');

        if ($request->get('q')) {
            $search = $request->get('q');
            $links->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->get('sortBy')) {
            $links->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        }

        $links = $links->paginate($perPage);

        return response()->json([
            'links' => $links->getCollection(),
            'totalPages' => $links->lastPage(),
            'totalLinks' => $links->total(),
            'page' => $links->currentPage()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'url' => 'required',
        ]);


        $link = new \App\Models\Link;
        $link->name = $request->name;
        $link->url = $request->url;
        if ($request->brand_id) {
            $link->brand_id = $request->brand_id;
        }

        $link->save();

        return response()->json($link);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'url' => 'required',
        ]);

        $link = \App\Models\Link::find($id);
        $link->name = $request->name;
        $link->url = $request->url;
        if ($request->brand_id) {
            $link->brand_id = $request->brand_id;
        }

        $link->save();

        return response()->json($link);
    }

    public function destroy(Request $request, $id)
    {
        $link = \App\Models\Link::find($id);
        $link->delete();

        return response()->json(['message' => 'Link removed']);
    }
}
