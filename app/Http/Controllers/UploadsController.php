<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadsController extends Controller
{
    public function index(Request $request)
    {
        $file = $request->file('file');
        $scope = $request->get('scope', 'uploads');
        // If scope starts with "documents", set it to "documents"
        if (strpos($scope, 'documents') === 0) {
            // Set visibility as public for documents
            $path = $file->storeAs($scope, $file->getClientOriginalName(), [
                'disk' => 'do',
                'visibility' => 'public'
            ]);
        } else {
            // Set visibility as private for everything else
            $path = $file->storeAs($scope, $file->getClientOriginalName(), [
                'disk' => 'do',
                'visibility' => 'private'
            ]);
        }

        return response()->json(['path' => $path]);
    }
}
