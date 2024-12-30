<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProfitReportController extends Controller
{
    /**
     * Show the profit report form view.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Profit Report';
        $sales = collect(); // Empty collection for no sales initially
        $totalProfit = 0; // Default value for total profit
        return view('admin.reports.profit', compact('title', 'sales', 'totalProfit'));
    }

    /**
     * Generate and display the profit report based on the date range.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generateReport(Request $request)
    {
        $this->validate($request, [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        $sales = Sale::whereBetween('created_at', [$fromDate, $toDate])
            ->with('product.purchase')
            ->get();

        $totalProfit = $sales->sum(function ($sale) {
            $costPrice = $sale->product->purchase->cost_price ?? 0;
            return $sale->total_price - ($costPrice * $sale->quantity);
        });

        return view('admin.reports.profit', compact('sales', 'totalProfit', 'fromDate', 'toDate'));
    }
}
