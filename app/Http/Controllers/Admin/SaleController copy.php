<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Events\PurchaseOutStock;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

//     public function index(Request $request)
// {
//     $title = 'sales';
//     if ($request->ajax()) {
//         $sales = Sale::latest()->with('product.purchase'); // Eager load purchase

//         return DataTables::of($sales)
//             ->addIndexColumn()
//             ->addColumn('product', function($sale){
//                 $image = '';
//                 if (!empty($sale->product)) {
//                     $image = null;
//                     if (!empty($sale->product->purchase->image)) {
//                         $image = '<span class="avatar avatar-sm mr-2">
//                             <img class="avatar-img" src="'.asset("storage/purchases/".$sale->product->purchase->image).'" alt="image">
//                             </span>';
//                     }
//                     return $sale->product->purchase->product. ' ' . $image;
//                 }                 
//             })
//             ->addColumn('batch_no', function($sale){
//                 return $sale->product->purchase->batch_number ?? 'N/A';  // Display batch number
//             })
//             ->addColumn('total_price',function($sale){
//                 return settings('app_currency','$').' '. $sale->total_price;
//             })
//             ->addColumn('date', function($row){
//                 return date_format(date_create($row->created_at),'d M, Y');
//             })
//             ->addColumn('action', function ($row) {
//                 $editbtn = '<a href="'.route("sales.edit", $row->id).'" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
//                 $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('sales.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
//                 if (!auth()->user()->hasPermissionTo('edit-sale')) {
//                     $editbtn = '';
//                 }
//                 if (!auth()->user()->hasPermissionTo('destroy-sale')) {
//                     $deletebtn = '';
//                 }
//                 return $editbtn.' '.$deletebtn;
//             })
//             ->rawColumns(['product', 'batch_no', 'action'])
//             ->make(true);
//     }

//     $products = Product::get();
//     return view('admin.sales.index', compact('title', 'products'));
// }

public function index(Request $request)
{
    $title = 'sales';
    if ($request->ajax()) {
        $sales = Sale::latest()->with('product.purchase'); // Eager load purchase

        return DataTables::of($sales)
            ->addIndexColumn()
            ->addColumn('product', function ($sale) {
                $image = '';
                if (!empty($sale->product)) {
                    if (!empty($sale->product->purchase->image)) {
                        $image = '<span class="avatar avatar-sm mr-2">
                            <img class="avatar-img" src="' . asset("storage/purchases/" . $sale->product->purchase->image) . '" alt="image">
                            </span>';
                    }
                    return $sale->product->purchase->product . ' ' . $image;
                }
                return 'N/A'; // Handle cases where product or purchase is missing
            })
            ->addColumn('batch_no', function ($sale) {
                return $sale->product->purchase->batch_number ?? 'N/A'; // Display batch number
            })
            ->addColumn('total_price', function ($sale) {
                return settings('app_currency', '$') . ' ' . $sale->total_price;
            })
            ->addColumn('date', function ($row) {
                return date_format(date_create($row->created_at), 'd M, Y');
            })
            ->addColumn('action', function ($row) {
                $editbtn = '<a href="' . route("sales.edit", $row->id) . '" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                $deletebtn = '<a data-id="' . $row->id . '" data-route="' . route('sales.destroy', $row->id) . '" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                if (!auth()->user()->hasPermissionTo('edit-sale')) {
                    $editbtn = '';
                }
                if (!auth()->user()->hasPermissionTo('destroy-sale')) {
                    $deletebtn = '';
                }
                return $editbtn . ' ' . $deletebtn;
            })
            ->filter(function ($query) use ($request) {
                $search = $request->get('search')['value'];
                if (!empty($search)) {
                    // Filter by product name (medicine name)
                    $query->whereHas('product.purchase', function ($q) use ($search) {
                        $q->where('product', 'like', "%$search%");
                    });

                    // Filter by batch number
                    $query->orWhereHas('product.purchase', function ($q) use ($search) {
                        $q->where('batch_number', 'like', "%$search%");
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

    $products = Product::get();
    return view('admin.sales.index', compact('title', 'products'));
}


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'create sales';
        $products = Product::get();
        return view('admin.sales.create',compact(
            'title','products'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'product'=>'required',
            'quantity'=>'required|integer|min:1'
        ]);
        $sold_product = Product::find($request->product);
        
        /**update quantity of
            sold item from
         purchases
        **/
        $purchased_item = Purchase::find($sold_product->purchase->id);
        $new_quantity = ($purchased_item->quantity) - ($request->quantity);
        $notification = '';
        if (!($new_quantity < 0)){

            $purchased_item->update([
                'quantity'=>$new_quantity,
            ]);

            /**
             * calcualting item's total price
            **/
            $total_price = ($request->quantity) * ($sold_product->price);
            Sale::create([
                'product_id'=>$request->product,
                'quantity'=>$request->quantity,
                'total_price'=>$total_price,
            ]);

            $notification = notify("Product has been sold");
        } 
        if($new_quantity <=1 && $new_quantity !=0){
            // send notification 
            $product = Purchase::where('quantity', '<=', 1)->first();
            event(new PurchaseOutStock($product));
            // end of notification 
            $notification = notify("Product is running out of stock!!!");
            
        }

        return redirect()->route('sales.index')->with($notification);
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Sale $sale
     * @return \Illuminate\Http\Response
     */
    public function edit(Sale $sale)
    {
        $title = 'edit sale';
        $products = Product::get();
        return view('admin.sales.edit',compact(
            'title','sale','products'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Sale $sale
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sale $sale)
    {
        $this->validate($request,[
            'product'=>'required',
            'quantity'=>'required|integer|min:1'
        ]);
        $sold_product = Product::find($request->product);
        /**
         * update quantity of sold item from purchases
        **/
        $purchased_item = Purchase::find($sold_product->purchase->id);
        if(!empty($request->quantity)){
            $new_quantity = ($purchased_item->quantity) - ($request->quantity);
        }
        $new_quantity = $sale->quantity;
        $notification = '';
        if (!($new_quantity < 0)){
            $purchased_item->update([
                'quantity'=>$new_quantity,
            ]);

            /**
             * calcualting item's total price
            **/
            if(!empty($request->quantity)){
                $total_price = ($request->quantity) * ($sold_product->price);
            }
            $total_price = $sale->total_price;
            $sale->update([
                'product_id'=>$request->product,
                'quantity'=>$request->quantity,
                'total_price'=>$total_price,
            ]);

            $notification = notify("Product has been updated");
        } 
        if($new_quantity <=1 && $new_quantity !=0){
            // send notification 
            $product = Purchase::where('quantity', '<=', 1)->first();
            event(new PurchaseOutStock($product));
            // end of notification 
            $notification = notify("Product is running out of stock!!!");
            
        }
        return redirect()->route('sales.index')->with($notification);
    }

    /**
     * Generate sales reports index
     *
     * @return \Illuminate\Http\Response
     */
    public function reports(Request $request){
        $title = 'sales reports';
        return view('admin.sales.reports',compact(
            'title'
        ));
    }

    /**
     * Generate sales report form post
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateReport(Request $request){
        $this->validate($request, [
            'from_date' => 'required',
            'to_date' => 'required',
        ]);
    
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
    
        // Set the title with the date range
        $title = 'Sales Report from ' . date('d M, Y', strtotime($fromDate)) . ' to ' . date('d M, Y', strtotime($toDate));
    
        // Fetch sales within the given date range
        $sales = Sale::whereBetween(DB::raw('DATE(created_at)'), [$fromDate, $toDate])->get();
      // Calculate the total price for the report
      $totalPrice = $sales->sum('total_price');  // Summing the total_price field from the fetched sales

      // Pass the sales data and totalPrice to the view
      return view('admin.sales.reports', compact('sales', 'title', 'fromDate', 'toDate', 'totalPrice'));
  }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        return Sale::findOrFail($request->id)->delete();
    }
}
