<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use App\Models\Category;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables; // use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index(){
        $title = 'dashboard';
        $total_purchases = Purchase::where('expiry_date','!=',Carbon::now())->count();
        $total_categories = Category::count();
        $total_suppliers = Supplier::count();
        $total_sales = Sale::count();
        
        $pieChart = app()->chartjs
                ->name('pieChart')
                ->type('pie')
                ->size(['width' => 400, 'height' => 200])
                ->labels(['Total Purchases', 'Total Suppliers','Total Sales'])
                ->datasets([
                    [
                        'backgroundColor' => ['#FF6384', '#36A2EB','#7bb13c'],
                        'hoverBackgroundColor' => ['#FF6384', '#36A2EB','#7bb13c'],
                        'data' => [$total_purchases, $total_suppliers,$total_sales]
                    ]
                ])
                ->options([]);
        
        $total_expired_products = Purchase::whereDate('expiry_date', '=', Carbon::now())->count();
        $latest_sales = Sale::whereDate('created_at','=',Carbon::now())->get();
        $today_sales = Sale::whereDate('created_at','=',Carbon::now())->sum('total_price');
        return view('admin.dashboard',compact(
            'title','pieChart','total_expired_products',
            'latest_sales','today_sales','total_categories'
        ));
    }

    public function fetchTodaysSalesData(Request $request)
    {
        if ($request->ajax()) {
            $sales = Sale::latest()
                ->with('product.purchase') // Eager load purchase
                ->whereDate('created_at', now()->toDateString()); // Filter for today's sales

            return DataTables::of($sales)
                ->addIndexColumn()
                ->addColumn('product', function($sale){
                    $image = '';
                    if (!empty($sale->product)) {
                        $image = null;
                        if (!empty($sale->product->purchase->image)) {
                            $image = '<span class="avatar avatar-sm mr-2">
                                <img class="avatar-img" src="'.asset("storage/purchases/".$sale->product->purchase->image).'" alt="image">
                                </span>';
                        }
                        return $sale->product->purchase->product. ' ' . $image;
                    }                 
                })
                ->addColumn('batch_no', function($sale){
                    return $sale->product->purchase->batch_number ?? 'N/A';  // Display batch number
                })
                ->addColumn('total_price',function($sale){
                    return settings('app_currency','$').' '. $sale->total_price;
                })
                ->addColumn('date', function($row){
                    return date_format(date_create($row->created_at),'d M, Y');
                })
                ->addColumn('action', function ($row) {
                    $editbtn = '<a href="'.route("sales.edit", $row->id).'" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                    $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('sales.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                    if (!auth()->user()->hasPermissionTo('edit-sale')) {
                        $editbtn = '';
                    }
                    if (!auth()->user()->hasPermissionTo('destroy-sale')) {
                        $deletebtn = '';
                    }
                    return $editbtn.' '.$deletebtn;
                })
                ->filter(function ($query) use ($request) {
                    $search = $request->get('search')['value'];
                    if (!empty($search)) {
                        // Filter by product name (medicine name)
                        $query->whereHas('product.purchase', function ($q) use ($search) {
                            $q->where('product', 'like', "%$search%");
                        });
                        // Filter by total price
                        $query->orWhere('total_price', 'like', "%$search%");
    
                        // Filter by date
                        $query->orWhere('created_at', 'like', "%$search%");
                    }
                })
                ->rawColumns(['product', 'action']) // Allow HTML for product and action columns
                ->make(true);
        }
    }
}
