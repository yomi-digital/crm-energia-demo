<?php

namespace App\Http\Controllers;

use App\Models\Paperwork;
use App\Models\Brand;
use App\Models\User;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Search paperworks with filters
     */
    public function searchPaperwork(Request $request)
    {
        $query = Paperwork::query()
            ->with(['product.brand', 'user', 'customer', 'tickets'])
            ->latest(); // Always order by latest first

        // Only apply filters if they are explicitly set
        if ($request->has('agent_id') && $request->filled('agent_id')) {
            $query->where('user_id', $request->agent_id);
        }

        if ($request->has('customer_id') && $request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('tax_id') && $request->filled('tax_id')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('vat_number', 'like', '%' . $request->tax_id . '%')
                    ->orWhere('fiscal_code', 'like', '%' . $request->tax_id . '%');
            });
        }

        if ($request->has('phone') && $request->filled('phone')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('phone', 'like', '%' . $request->phone . '%')
                    ->orWhere('mobile', 'like', '%' . $request->phone . '%');
            });
        }

        if ($request->has('order_number') && $request->filled('order_number')) {
            $query->where('order_number', 'like', '%' . $request->order_number . '%');
        }

        $query->where(function($q) {
            $q->whereNull('order_status')
              ->orWhereIn('order_status', ['', 'DA LAVORARE', 'SOSPESO', 'INVIATO', 'INSERITO']);
        });

        $loggedInUserId = $request->user()->id;

        return response()->json([
            'data' => $query->paginate(10)->through(function ($paperwork) use ($loggedInUserId) {
                return [
                    'id' => $paperwork->id,
                    'customer' => $paperwork->customer->business_name ?: implode(' ', [$paperwork->customer->name, $paperwork->customer->last_name]),
                    'customer_id' => $paperwork->customer->id,
                    'agent' => $paperwork->user->name . ' ' . $paperwork->user->last_name,
                    'agent_id' => $paperwork->user->id,
                    'brand' => $paperwork->product->brand->name ?? 'N/A',
                    'brand_id' => $paperwork->product->brand->id,
                    'product' => $paperwork->product->name ?? 'N/A',
                    'product_id' => $paperwork->product->id,
                    'state' => $paperwork->order_status,
                    'created_at' => $paperwork->created_at->format(config('app.date_format')),
                    'hasTicket' => $paperwork->tickets->contains(function ($ticket) use ($loggedInUserId) {
                        return $ticket->comments->where('user_id', '!=', $loggedInUserId)->isNotEmpty();
                    }),
                ];
            }),
        ]);
    }

    /**
     * Get paperwork statistics by period
     */
    public function getPaperworkStats(Request $request)
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Get stats for each period
        $stats = [
            'today' => $this->getPeriodStats($today, Carbon::today()->endOfDay(), $request->all()),
            'currentMonth' => $this->getPeriodStats($thisMonth, Carbon::now()->endOfMonth(), $request->all()),
            'previousMonth' => $this->getPeriodStats($lastMonth, $lastMonth->copy()->endOfMonth(), $request->all()),
        ];

        return response()->json($stats);
    }

    /**
     * Get brand statistics
     */
    public function getBrandStats(Request $request)
    {
        $periods = [
            'today' => [Carbon::today(), Carbon::today()->endOfDay()],
            'thisMonth' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'lastMonth' => [
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth(),
            ],
        ];

        $stats = [];
        foreach ($periods as $period => [$start, $end]) {
            $stats[$period] = DB::table('paperworks')
                ->join('products', 'paperworks.product_id', '=', 'products.id')
                ->join('brands', 'products.brand_id', '=', 'brands.id')
                ->whereBetween('paperworks.created_at', [$start, $end])
                ->when($request->brandId, function ($query) use ($request) {
                    return $query->where('brands.id', $request->brandId);
                })
                ->groupBy('brands.id', 'brands.name', 'paperworks.order_status')
                ->select(
                    'brands.id',
                    'brands.name',
                    'paperworks.order_status as status',
                    DB::raw('count(*) as count')
                )
                ->get();
        }

        return response()->json($stats);
    }

    /**
     * Get time series data for paperworks
     */
    public function getTimeSeriesData(Request $request)
    {
        // Today's data (by hour)
        $todayData = $this->getHourlyData(
            Carbon::today(), 
            Carbon::today()->endOfDay(),
            $request->all()
        );

        // This month's data (by day)
        $thisMonthData = $this->getDailyData(
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth(),
            $request->all()
        );

        // Last month's data (by day)
        $lastMonthData = $this->getDailyData(
            Carbon::now()->subMonth()->startOfMonth(),
            Carbon::now()->subMonth()->endOfMonth(),
            $request->all()
        );

        return response()->json([
            'today' => $todayData,
            'thisMonth' => $thisMonthData,
            'lastMonth' => $lastMonthData,
        ]);
    }

    /**
     * Helper function to get stats for a specific period
     */
    private function getPeriodStats($start, $end, array $filters)
    {
        $query = DB::table('paperworks')
            ->join('products', 'paperworks.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->whereBetween('paperworks.created_at', [$start, $end])
            ->when(isset($filters['brandId']), function ($query) use ($filters) {
                return $query->where('brands.id', $filters['brandId']);
            })
            ->when(isset($filters['productId']), function ($query) use ($filters) {
                return $query->where('products.id', $filters['productId']);
            })
            ->when(isset($filters['agentId']), function ($query) use ($filters) {
                return $query->where('paperworks.user_id', $filters['agentId']);
            });

        $stats = $query->groupBy('paperworks.order_status')
            ->select(
                'paperworks.order_status',
                DB::raw('count(*) as count')
            )
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->order_status ?: 'NUOVO' => $item->count];
            });

        return $stats;
    }

    /**
     * Get hourly data with brand breakdown
     */
    private function getHourlyData($start, $end, array $filters)
    {
        // Get total counts
        $totalQuery = DB::table('paperworks')
            ->whereBetween('created_at', [$start, $end])
            ->when(isset($filters['agentId']), function ($query) use ($filters) {
                return $query->where('user_id', $filters['agentId']);
            })
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->select(
                DB::raw('HOUR(created_at) as period'),
                DB::raw('count(*) as count')
            );

        // Get brand-specific counts
        $brandQuery = DB::table('paperworks')
            ->join('products', 'paperworks.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->whereBetween('paperworks.created_at', [$start, $end])
            ->when(isset($filters['agentId']), function ($query) use ($filters) {
                return $query->where('paperworks.user_id', $filters['agentId']);
            })
            ->groupBy('brands.id', 'brands.name', DB::raw('HOUR(paperworks.created_at)'))
            ->select(
                'brands.id',
                'brands.name as brand',
                DB::raw('HOUR(paperworks.created_at) as period'),
                DB::raw('count(*) as count')
            );

        return [
            'total' => $totalQuery->get(),
            'byBrand' => $brandQuery->get()
        ];
    }

    /**
     * Get daily data with brand breakdown
     */
    private function getDailyData($start, $end, array $filters)
    {
        // Get total counts
        $totalQuery = DB::table('paperworks')
            ->whereBetween('created_at', [$start, $end])
            ->when(isset($filters['agentId']), function ($query) use ($filters) {
                return $query->where('user_id', $filters['agentId']);
            })
            ->groupBy(DB::raw('DATE(created_at)'))
            ->select(
                DB::raw('DATE(created_at) as period'),
                DB::raw('count(*) as count')
            );

        // Get brand-specific counts
        $brandQuery = DB::table('paperworks')
            ->join('products', 'paperworks.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->whereBetween('paperworks.created_at', [$start, $end])
            ->when(isset($filters['agentId']), function ($query) use ($filters) {
                return $query->where('paperworks.user_id', $filters['agentId']);
            })
            ->groupBy('brands.id', 'brands.name', DB::raw('DATE(paperworks.created_at)'))
            ->select(
                'brands.id',
                'brands.name as brand',
                DB::raw('DATE(paperworks.created_at) as period'),
                DB::raw('count(*) as count')
            );

        return [
            'total' => $totalQuery->get(),
            'byBrand' => $brandQuery->get()
        ];
    }

    /**
     * Get list of agents for search
     */
    public function getAgents()
    {
        return response()->json(
            User::select('id', 'name')
                ->orderBy('name')
                ->get()
        );
    }

    /**
     * Get list of customers for search
     */
    public function getCustomers(Request $request)
    {
        $query = Customer::query()
            ->select('id', DB::raw("CASE 
                WHEN business_name IS NOT NULL THEN business_name 
                ELSE CONCAT(name, ' ', last_name) 
            END as name"));

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('last_name', 'like', "%{$request->search}%")
                    ->orWhere('business_name', 'like', "%{$request->search}%");
            });
        }

        return response()->json(
            $query->orderBy('name')->get()
        );
    }
} 
