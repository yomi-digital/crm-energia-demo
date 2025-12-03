<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DocumentsController extends Controller
{
    public function index(Request $request)
    {
        // TODO Get the brands the user is allowed to see
        if ($request->user()->hasRole('agente') || $request->user()->hasRole('struttura')) {
            $brands = $request->user()->brands()->orderBy('name', 'asc')->get();
        } else {
            $brands = \App\Models\Brand::orderBy('name', 'asc')->get();
        }
        $brandsWithoutFolder = $brands->pluck('name')->toArray();

        $path = $request->get('path', '');
        $data = \Storage::disk('do')->listContents('/documents/' . $path, false);
        $files = [];
        $cdnPath = 'https://' . config('filesystems.disks.do.bucket') . '.' . (str_replace('https://', '', config('filesystems.disks.do.endpoint'))) . '/';
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
                'url' => $cdnPath . $file['path'],
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

    public function list(Request $request)
    {
        $path = $request->get('path', '');
        $data = \Storage::disk('do')->listContents('/documents/' . $path, false);
        $files = [];
        $cdnPath = 'https://' . config('filesystems.disks.do.bucket') . '.' . (str_replace('https://', '', config('filesystems.disks.do.endpoint'))) . '/';
        foreach ($data as $file) {
            $path = str_replace('documents/', '', $file['path']);
            $split = explode('/', $path);
            $name = end($split);

            $files[] = [
                'type' => $file['type'],
                'path' => $path,
                'url' => $cdnPath . $file['path'],
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

    public function rename(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'new_name' => 'required|string',
        ], [
            'path.required' => 'Il percorso della cartella è obbligatorio',
            'new_name.required' => 'Il nuovo nome è obbligatorio',
        ]);

        // 1. Prelevo il path della cartella vecchia e il nuovo nome
        $path = $request->get('path', '');
        $newName = $request->get('new_name', '');

        // 2. Verifica che il nome della cartella nuova non sia identico alla vecchia
        $oldFileName = basename($path);
        if ($oldFileName === $newName) {
            return response()->json(['error' => 'Il nuovo nome della cartella è identico alla vecchia'], 400);
        }

        //3. Verifica che la cartella che si sta tentando di rinominare esista
        $oldFullPath = '/documents/' . $path;
        $parentPath = dirname($path);
        $parentPath = $parentPath === '.' ? '' : $parentPath;
        if (!\Storage::disk('do')->directoryExists($oldFullPath)) {
            return response()->json(['error' => 'Cartella non trovata'], 404);
        }
        
        //4. Verifica che la cartella se rinominata non abbia conflitti con un'altra cartella con medesimo nome
        $newFullPath = '/documents/' . ($parentPath ? $parentPath . '/' : '') . $newName;
        try {
            $newContentsIterator = \Storage::disk('do')->listContents($newFullPath, false);
            $newContents = iterator_to_array($newContentsIterator);
            
            if (count($newContents) > 0) {
                return response()->json([
                    'error' => 'Una cartella con questo nome esiste già', 
                    'new_full_path' => $newFullPath, 
                    'new_contents' => array_map(fn($item) => $item->path(), $newContents)
                ], 409);
            }
        } catch (\Exception $e) {
            // Se il path non esiste, va bene - possiamo procedere
        }

        //5. Creo la cartella nuova con detro un .keeps
        \Storage::disk('do')->makeDirectory($newFullPath);
        \Storage::disk('do')->put($newFullPath . '/.keep', '');

        //6. Copio i file dalla cartella vecchia alla nuova mantenendo la struttura delle sottocartelle
        $files = \Storage::disk('do')->allFiles($oldFullPath);
        $copiedFiles = [];
        $destPaths = [];
        foreach ($files as $file) {
            $relativePath = str_replace(ltrim($oldFullPath, '/') . '/', '', $file);
            $copiedFiles[] = $relativePath;
            $destPath = $newFullPath . '/' . $relativePath;
            $destPaths[] = $destPath;
            \Storage::disk('do')->copy($file, $destPath);
        }

        //7. Rimuovo il .keep nella nuova cartella
        \Storage::disk('do')->delete($newFullPath . '/.keep');

        //8. Rimuovo la vecchia cartella con tutti i file dentro
        //\Storage::disk('do')->deleteDirectory($oldFullPath);

        return response()->json([
            'message' => 'Cartella rinominata con successo',
            'old_full_path' => $oldFullPath,
            'new_full_path' => $newFullPath,
            'copied_files' => $copiedFiles,
            'dest_paths' => $destPaths,
        ], 200);
    }
}
