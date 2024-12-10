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
        } elseif ($scope === 'avatars') {
            $path = $file->storeAs($scope . '/' . $request->user()->id, $file->getClientOriginalName(), [
                'disk' => 'do',
                'visibility' => 'public'
            ]);
            $path = 'https://' . config('filesystems.disks.do.bucket') . '.' . str_replace('https://', '', config('filesystems.disks.do.endpoint')) . '/' . $path;
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
