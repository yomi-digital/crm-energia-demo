<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DocumentsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $documents = \App\Models\Document::with('brand');

        if ($request->get('q')) {
            $search = $request->get('q');
            $documents->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->get('sortBy')) {
            $documents->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        }

        $documents = $documents->paginate($perPage);

        return response()->json([
            'documents' => $documents->getCollection(),
            'totalPages' => $documents->lastPage(),
            'totalDocuments' => $documents->total(),
            'page' => $documents->currentPage()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'category' => 'required',
            'file_path' => 'required'
        ]);


        $document = new \App\Models\Document;
        $document->name = $request->name;
        $document->category = $request->category;
        $document->url = $request->file_path;
        if ($request->brand_id) {
            $document->brand_id = $request->brand_id;
        }
        $document->added_at = now();

        $document->save();

        return response()->json($document);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'category' => 'required',
        ]);

        $document = \App\Models\Document::find($id);
        $document->name = $request->name;
        $document->category = $request->category;
        if ($request->brand_id) {
            $document->brand_id = $request->brand_id;
        }

        $document->save();

        return response()->json($document);
    }

    public function destroy(Request $request, $id)
    {
        $document = \App\Models\Document::find($id);
        $document->delete();

        return response()->json(['message' => 'Document removed']);
    }

    public function download(Request $request, $id)
    {
        $document = \App\Models\Document::find($id);
        $extension = pathinfo($document->url, PATHINFO_EXTENSION);

        if ($request->inline) {
            return response()->download(storage_path('app/' . $document->url), $document->name . '.' . $extension, [], 'inline');
        } else {
            return response()->download(storage_path('app/' . $document->url), $document->name . '.' . $extension);
        }
    }
}
