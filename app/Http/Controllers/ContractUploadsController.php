<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AIPaperwork;
use Illuminate\Support\Facades\Storage;

class ContractUploadsController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'contract' => 'required|file|mimes:pdf|max:20480', // 20MB max
        ]);

        try {
            $file = $request->file('contract');
            
            // Store file in Digital Ocean Spaces with original name
            $path = Storage::disk('do')->putFileAs(
                'ai_contracts',
                $file,
                $file->getClientOriginalName()
            );

            // Create new AI Paperwork record
            $aiPaperwork = AIPaperwork::create([
                'user_id' => auth()->id(),
                'filepath' => $path,
                'original_filename' => basename($path),
                'status' => 0, // Pending
            ]);

            return response()->json([
                'message' => 'Contract uploaded successfully',
                'id' => $aiPaperwork->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to upload contract: ' . $e->getMessage()
            ], 500);
        }
    }
} 
