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
    $this->validate($request, [
        'product' => 'required',
        'quantity' => 'required|integer|min:1',
        'discount' => 'nullable|numeric|min:0|max:100', // Discount field
    ]);

    $sold_product = Product::find($request->product);

    // Update quantity of sold item from purchases
    $purchased_item = Purchase::find($sold_product->purchase->id);
    $new_quantity = ($purchased_item->quantity) - ($request->quantity);
    $notification = '';

    if (!($new_quantity < 0)) {
        $purchased_item->update([
            'quantity' => $new_quantity,
        ]);

        // Calculate total price
        $total_price = ($request->quantity) * ($sold_product->price);

        // Apply discount if provided
        if ($request->has('discount') && $request->discount > 0) {
            $discount = $request->discount;
            $total_price -= ($total_price * ($discount / 100));  // Apply discount
        }

        // Calculate final price after discount
        $final_price = $total_price;  // Final price is the same as total price with discount applied

        // Store the sale record with final_price
        Sale::create([
            'product_id' => $request->product,
            'quantity' => $request->quantity,
            'total_price' => $total_price,
            'final_price' => $final_price,  // Store the final price
        ]);

        $notification = notify("Product has been sold");
    }

    if ($new_quantity <= 1 && $new_quantity != 0) {
        // Send notification for low stock
        $product = Purchase::where('quantity', '<=', 1)->first();
        event(new PurchaseOutStock($product));
        $notification = notify("Product is running out of stock!!!");
    }

    return redirect()->route('sales.index')->with($notification);
}


/**
     * Handle storing multiple sales at once.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeMultipleSales(Request $request)
    {
        $this->validate($request, [
            'sales.*.product_id' => 'required|exists:products,id',
            'sales.*.quantity' => 'required|integer|min:1',
            'sales.*.discount' => 'nullable|numeric|min:0|max:100',
        ]);

        $salesData = $request->input('sales');
        $notifications = [];
        $errorOccurred = false;

        DB::transaction(function () use ($salesData, &$notifications, &$errorOccurred) {
            foreach ($salesData as $sale) {
                $product = Product::find($sale['product_id']);
                $purchase = $product->purchase;

                if ($purchase->quantity < $sale['quantity']) {
                    $errorOccurred = true;
                    $notifications[] = "Insufficient stock for product ID {$sale['product_id']}.";
                    continue;
                }

                // Update stock
                $purchase->update(['quantity' => $purchase->quantity - $sale['quantity']]);

                // Calculate total price
                $totalPrice = $sale['quantity'] * $product->price;

                // Apply discount
                if (isset($sale['discount']) && $sale['discount'] > 0) {
                    $totalPrice -= ($totalPrice * ($sale['discount'] / 100));
                }

                // Save sale
                Sale::create([
                    'product_id' => $sale['product_id'],
                    'quantity' => $sale['quantity'],
                    'total_price' => $totalPrice,
                ]);

                $notifications[] = "Product ID {$sale['product_id']} sold successfully.";

                // Trigger low stock notification if needed
                if ($purchase->quantity <= 1) {
                    event(new PurchaseOutStock($purchase));
                    $notifications[] = "Product ID {$sale['product_id']} is running low on stock!";
                }
            }
        });

        if ($errorOccurred) {
            return redirect()->route('sales.index')->withErrors($notifications);
        }

        return redirect()->route('sales.index')->with('message', implode(' ', $notifications));
    }



     // Method to display the profit report view
     public function profitReport()
     {
         return view('admin.reports.profit'); // Adjust the path based on where you store the view
     }
 
     // Method to generate the profit report
     public function generateProfitReport(Request $request)
     {
         // Get the data based on the form inputs
         $fromDate = $request->from_date;
         $toDate = $request->to_date;
 
         // Query the sales data between the selected date range
         $sales = Sale::whereBetween('created_at', [$fromDate, $toDate])->get();
 
         // Calculate the total profit or any other calculations
         $totalProfit = $sales->sum(function ($sale) {
             return $sale->total_price - $sale->cost_price; // Example calculation
         });
 
         // Return the report with the data
         return view('admin.reports.profit', compact('sales', 'totalProfit', 'fromDate', 'toDate'));
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
    $this->validate($request, [
        'product' => 'required',
        'quantity' => 'required|integer|min:1',
        'discount' => 'nullable|numeric|min:0|max:100', // Discount field
    ]);

    $sold_product = Product::find($request->product);

    // Update quantity of sold item from purchases
    $purchased_item = Purchase::find($sold_product->purchase->id);
    $new_quantity = ($purchased_item->quantity) - ($request->quantity);

    $notification = '';

    if (!($new_quantity < 0)) {
        $purchased_item->update([
            'quantity' => $new_quantity,
        ]);

        // Calculate total price
        $total_price = ($request->quantity) * ($sold_product->price);

        // Apply discount if provided
        if ($request->has('discount') && $request->discount > 0) {
            $discount = $request->discount;
            $total_price -= ($total_price * ($discount / 100));  // Apply discount
        }

        // Calculate final price after discount
        $final_price = $total_price;  // Final price is the same as total price with discount applied

        // Update the sale record with final_price
        $sale->update([
            'product_id' => $request->product,
            'quantity' => $request->quantity,
            'total_price' => $total_price,
            'final_price' => $final_price,  // Store the final price
        ]);

        $notification = notify("Product has been updated");
    }

    if ($new_quantity <= 1 && $new_quantity != 0) {
        // Send notification for low stock
        $product = Purchase::where('quantity', '<=', 1)->first();
        event(new PurchaseOutStock($product));
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