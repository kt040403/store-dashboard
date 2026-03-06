<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Store;
use App\Models\MonthlyTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentMonth = now()->format('Y-m');
        $lastMonth = now()->subMonth()->format('Y-m');

        // KPI: 今月の売上合計
        $currentMonthSales = Sale::whereRaw("to_char(sale_date, 'YYYY-MM') = ?", [$currentMonth])
            ->sum('total');

        // KPI: 先月の売上合計
        $lastMonthSales = Sale::whereRaw("to_char(sale_date, 'YYYY-MM') = ?", [$lastMonth])
            ->sum('total');

        // KPI: 前月比
        $monthOverMonth = $lastMonthSales > 0
            ? round(($currentMonthSales - $lastMonthSales) / $lastMonthSales * 100, 1)
            : 0;

        // KPI: 今月の目標合計と達成率
        $currentTarget = MonthlyTarget::where('year_month', $currentMonth)
            ->sum('target_amount');
        $achievementRate = $currentTarget > 0
            ? round($currentMonthSales / $currentTarget * 100, 1)
            : 0;

        // KPI: 今月の売上件数
        $currentMonthCount = Sale::whereRaw("to_char(sale_date, 'YYYY-MM') = ?", [$currentMonth])
            ->count();

        // グラフ: 月別売上推移（直近12ヶ月）
        $monthlySales = Sale::select(
                DB::raw("to_char(sale_date, 'YYYY-MM') as month"),
                DB::raw('SUM(total) as total')
            )
            ->where('sale_date', '>=', now()->subMonths(12)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // グラフ: 店舗別売上（今月）
        $storeSales = Store::select('stores.name', DB::raw('COALESCE(SUM(sales.total), 0) as total'))
            ->leftJoin('sales', function ($join) use ($currentMonth) {
                $join->on('stores.id', '=', 'sales.store_id')
                    ->whereRaw("to_char(sales.sale_date, 'YYYY-MM') = ?", [$currentMonth]);
            })
            ->groupBy('stores.id', 'stores.name')
            ->orderByDesc('total')
            ->get();

        // グラフ: カテゴリ別売上構成比（今月）
        $categorySales = DB::table('sales')
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(sales.total) as total'))
            ->whereRaw("to_char(sales.sale_date, 'YYYY-MM') = ?", [$currentMonth])
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();

        return view('dashboard', compact(
            'currentMonthSales',
            'lastMonthSales',
            'monthOverMonth',
            'achievementRate',
            'currentTarget',
            'currentMonthCount',
            'monthlySales',
            'storeSales',
            'categorySales',
        ));
    }
}