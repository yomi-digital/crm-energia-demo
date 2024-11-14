<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\PaperworkTrait;

class StatementsController extends Controller
{
    use PaperworkTrait;

    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $brands = new \App\Models\Brand();

        if ($request->filled('brand_id')) {
            $brands = $brands->where('id', $request->get('brand_id'));
        }
        
        if ($request->get('sortBy')) {
            $brands = $brands->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        } else {
            $brands = $brands->orderBy('name', 'asc');
        }

        if ($request->has('export')) {
            $perPage = 99999999;
        }

        $brands = $brands->paginate($perPage);

        foreach ($brands->getCollection() as $brand) {
            // Need to get all paperworks within the timerange and status in ['Attivo', 'Storno'] with a product of this brand. The paperworks table doesn't have a brand_id, but it's connected through the products.
            $paperworks = \App\Models\Paperwork::whereHas('product', function ($query) use ($brand) {
                $query->where('brand_id', $brand->id);
            })->whereBetween('partner_outcome_at', [$request->get('from'), $request->get('to')])->whereIn('partner_outcome', ['ATTIVO', 'OK PAGABILE', 'STORNO'])->get();

            $brand->paperworks_count = count($paperworks);

            if (count($paperworks) > 0) {
                foreach ($paperworks as $paperwork) {
                    if ($paperwork->partner_outcome === 'STORNO') {
                        $brand->minus += $this->calculatePaperworkPayout($paperwork);
                    } else {
                        $brand->plus += $this->calculatePaperworkPayout($paperwork);
                    }
                }
                $brand->minus = abs($brand->minus);
                $brand->net = $brand->plus - $brand->minus;
            } else {
                $brand->plus = 0;
                $brand->minus = 0;
                $brand->net = 0;
            }
        }

        // Sort by net descending and reindex
        $brands->setCollection(
            $brands->getCollection()
                ->sortByDesc('net')
                ->values()
        );

        if ($request->has('export')) {
            $csvPath = $this->transformEntriesToCSV($brands->getCollection());
            
            return response()->download($csvPath, 'statements.csv');
        }

        return response()->json([
            'entries' => $brands->getCollection(),
            'totalPages' => $brands->lastPage(),
            'totalEntries' => $brands->total(),
            'page' => $brands->currentPage(),
        ]);
    }

    private function transformEntriesToCSV($entries)
    {
        $headers = [
            'Brand',
            'Pratiche',
            'Attivi',
            'Storno',
            'Netto',
        ];

        // Save csv to /tmp 
        $csvPath = tempnam(sys_get_temp_dir(), 'csv');
        $fp = fopen($csvPath, 'w');
        fputcsv($fp, $headers);

        foreach ($entries as $entry) {
            fputcsv($fp, [
                $entry->name,
                $entry->paperworks_count,
                $entry->plus,
                $entry->minus,
                $entry->net,
            ]);
        }
        fclose($fp);

        return $csvPath;
    }
}
