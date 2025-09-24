<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visit;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get current date and date ranges
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        
        // Total counts
        $totalPatients = Patient::count();
        $totalVisits = Visit::count();
        $totalUsers = User::count();
        
        // Today's data
        $todayVisits = Visit::whereDate('visit_date', $today)->count();
        $todayRevenue = (float) (Visit::whereDate('visit_date', $today)
            ->sum('total_cost') ?? 0);
        
        // This month's data
        $monthlyVisits = Visit::where('visit_date', '>=', $thisMonth)->count();
        $monthlyRevenue = (float) (Visit::where('visit_date', '>=', $thisMonth)
            ->sum('total_cost') ?? 0);
        
        // Last month's data
        $lastMonthVisits = Visit::whereBetween('visit_date', [$lastMonth, $lastMonthEnd])->count();
        $lastMonthRevenue = (float) (Visit::whereBetween('visit_date', [$lastMonth, $lastMonthEnd])
            ->sum('total_cost') ?? 0);
        
        // Treatment status breakdown
        $pendingTreatments = Visit::where('treatment_status', 'pending')->count();
        $inProgressTreatments = Visit::where('treatment_status', 'in_progress')->count();
        $completedTreatments = Visit::where('treatment_status', 'completed')->count();
        
        // Payment status breakdown
        $pendingPayments = Visit::where('payment_status', 'pending')->count();
        $partialPayments = Visit::where('payment_status', 'partial')->count();
        $completedPayments = Visit::where('payment_status', 'completed')->count();
        
        // Recent visits (last 10)
        $recentVisits = Visit::orderBy('visit_date', 'desc')
            ->limit(10)
            ->get();
        
        // Top patients by visits
        $topPatients = Visit::selectRaw('name, phone, COUNT(*) as visit_count')
            ->groupBy('name', 'phone')
            ->orderBy('visit_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->name,
                    'phone' => $item->phone,
                    'visit_count' => (int) $item->visit_count
                ];
            });
        
        // Monthly revenue trend (last 6 months)
        $revenueTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $revenue = (float) (Visit::whereBetween('visit_date', [$monthStart, $monthEnd])
                ->sum('total_cost') ?? 0);
            
            $revenueTrend[] = [
                'month' => $month->format('M Y'),
                'revenue' => $revenue
            ];
        }
        
        // Calculate growth percentages
        $visitGrowth = $lastMonthVisits > 0 ? 
            round((($monthlyVisits - $lastMonthVisits) / $lastMonthVisits) * 100, 1) : 0;
        
        $revenueGrowth = $lastMonthRevenue > 0 ? 
            round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) : 0;
        
        return view('dashboard', compact(
            'totalPatients',
            'totalVisits', 
            'totalUsers',
            'todayVisits',
            'todayRevenue',
            'monthlyVisits',
            'monthlyRevenue',
            'lastMonthVisits',
            'lastMonthRevenue',
            'pendingTreatments',
            'inProgressTreatments',
            'completedTreatments',
            'pendingPayments',
            'partialPayments',
            'completedPayments',
            'recentVisits',
            'topPatients',
            'revenueTrend',
            'visitGrowth',
            'revenueGrowth'
        ));
    }
}