<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadsController extends Controller
{
    public function index(Request $request)
    {
        $file = $request->file('file');
        $scope = $request->get('scope', 'uploads');
        $path = $file->storeAs($scope, $file->getClientOriginalName(), 'do');

        return response()->json(['path' => $path]);
    }
}
