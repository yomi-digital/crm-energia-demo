<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DocumentsController extends Controller
{
    public function index(Request $request)
    {
        // TODO Get the brands the user is allowed to see
        $brands = \App\Models\Brand::orderBy('name', 'asc')->get();
        $brandsWithoutFolder = $brands->pluck('name')->toArray();

        $path = $request->get('path', '');
        $data = \Storage::disk('do')->listContents('/documents/' . $path, false);
        $files = [];
        foreach ($data as $file) {
            $path = str_replace('documents/', '', $file['path']);
            $split = explode('/', $path);
            $name = $split[count($split) - 1];
            $brand = $split[0];
            if (in_array($brand, $brandsWithoutFolder)) {
                $brandsWithoutFolder = array_diff($brandsWithoutFolder, [$brand]);
            }
            $isAllowed = $brands->where('name', $brand)->count() > 0;
            if (! $isAllowed) {
                continue;
            }

            $breadcrumbs = [];
            $breadcrumbs[] = [
                'title' => 'Documenti',
                'path' => '',
            ];
            foreach ($split as $index => $folder) {
                $breadcrumbs[] = [
                    'title' => $folder,
                    'path' => implode('/', array_slice($split, 0, $index + 1)),
                ];
            }

            $files[] = [
                'type' => $file['type'],
                'path' => $path,
                'breadcrumbs' => $breadcrumbs,
                'title' => $name,
                'icon' => $file['type'] === 'dir' ? 'tabler-folder-filled' : 'tabler-file',
            ];
        }

        return response()->json([
            'documents' => $files,
            'brands_without_folder' => array_values($brandsWithoutFolder),
        ]);
    }

    public function newFolder(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $path = $request->get('path', '');
        $name = $request->get('name', '');

        \Storage::disk('do')->makeDirectory('/documents/' . $path . '/' . $name);

        return response()->json([
            'message' => 'Folder created'
        ]);
    }

    public function list(Request $request) {
        $path = $request->get('path', '');
        $data = \Storage::disk('do')->listContents('/documents/' . $path, false);
        $files = [];
        foreach ($data as $file) {
            $path = str_replace('documents/', '', $file['path']);
            $split = explode('/', $path);
            $name = end($split);

            $files[] = [
                'type' => $file['type'],
                'path' => $path,
                'name' => $name,
            ];
        }

        return response()->json([
            'files' => $files
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'path' => 'required',
        ]);

        $path = $request->get('path', '');

        // If folder, delete folder, otherwise delete file
        $isFolder = $request->get('type') === 'dir';
        if (! $isFolder) {
            \Storage::disk('do')->delete('/documents/' . $path);
        } else {
            \Storage::disk('do')->deleteDirectory('/documents/' . $path);
        }

        return response()->json([
            'message' => 'Deleted'
        ]);
    }

    public function download(Request $request)
    {
        $request->validate([
            'path' => 'required',
        ]);

        $path = $request->get('path', '');

        return \Storage::disk('do')->download('/documents/' . $path);
    }
}
