<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $title = 'purchases';
        if ($request->ajax()) {
            $purchases = Purchase::get();
            return DataTables::of($purchases)
                ->addColumn('product', function ($purchase) {
                    $image = '';
                    if (!empty($purchase->image)) {
                        $image = '<span class="avatar avatar-sm mr-2">
                            <img class="avatar-img" src="' . asset("storage/purchases/" . $purchase->image) . '" alt="product">
                        </span>';
                    }
                    return $purchase->product . ' ' . $image;
                })
                ->addColumn('batch_number', function ($purchase) {
                    return $purchase->batch_number;
                })
                ->addColumn('category', function ($purchase) {
                    if (!empty($purchase->category)) {
                        return $purchase->category->name;
                    }
                })
                ->addColumn('cost_price', function ($purchase) {
                    return settings('app_currency', '$') . ' ' . $purchase->cost_price;
                })
                ->addColumn('supplier', function ($purchase) {
                    return $purchase->supplier->name;
                })
                ->addColumn('expiry_date', function ($purchase) {
                    return date_format(date_create($purchase->expiry_date), 'd M, Y');
                })
                
                ->addColumn('action', function ($row) {
                    $editbtn = '<a href="' . route("purchases.edit", $row->id) . '" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                    $deletebtn = '<a data-id="' . $row->id . '" data-route="' . route('purchases.destroy', $row->id) . '" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                    if (!auth()->user()->hasPermissionTo('edit-purchase')) {
                        $editbtn = '';
                    }
                    if (!auth()->user()->hasPermissionTo('destroy-purchase')) {
                        $deletebtn = '';
                    }
                    return $editbtn . ' ' . $deletebtn;
                })
                ->rawColumns(['product', 'action'])
                ->make(true);
        }
        return view('admin.purchases.index', compact('title'));
    }

    public function create()
    {
        $title = 'create purchase';
        $categories = Category::get();
        $suppliers = Supplier::get();
        return view('admin.purchases.create', compact('title', 'categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'product' => 'required|max:200',
            'batch_number' => 'required|unique:purchases,batch_number',
            'category' => 'required',
            'cost_price' => 'required|min:1',
            'quantity' => 'required|min:1',
            'expiry_date' => 'required',
            'supplier' => 'required',
            'image' => 'file|image|mimes:jpg,jpeg,png,gif',
            
        ]);

        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('storage/purchases'), $imageName);
        }

        Purchase::create([
            'product' => $request->product,
            'batch_number' => $request->batch_number,
            'category_id' => $request->category,
            'supplier_id' => $request->supplier,
            'cost_price' => $request->cost_price,
            'quantity' => $request->quantity,
            'expiry_date' => $request->expiry_date,
            'image' => $imageName,
            
        ]);

        return redirect()->route('purchases.index')->with('success', 'Purchase added successfully');
    }

    public function edit(Purchase $purchase)
    {
        $title = 'edit purchase';
        $categories = Category::get();
        $suppliers = Supplier::get();
        return view('admin.purchases.edit', compact('title', 'purchase', 'categories', 'suppliers'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $this->validate($request, [
            'product' => 'required|max:200',
            'batch_number' => 'required|unique:purchases,batch_number,' . $purchase->id,
            'category' => 'required',
            'cost_price' => 'required|min:1',
            'quantity' => 'required|min:1',
            'expiry_date' => 'required',
            'supplier' => 'required',
            'image' => 'file|image|mimes:jpg,jpeg,png,gif',
           
        ]);

        $imageName = $purchase->image;
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('storage/purchases'), $imageName);
        }

        $purchase->update([
            'product' => $request->product,
            'batch_number' => $request->batch_number,
            'category_id' => $request->category,
            'supplier_id' => $request->supplier,
            'cost_price' => $request->cost_price,
            'quantity' => $request->quantity,
            'expiry_date' => $request->expiry_date,
            'image' => $imageName,
            
        ]);

        return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully');
    }


public function reports()
{
    $title = 'purchase reports';
    $purchases = Purchase::with(['category', 'supplier'])->get();
    return view('admin.purchases.reports', compact('title', 'purchases'));
}


    // public function generateReport(Request $request){
    //     $this->validate($request,[
    //         'from_date' => 'required',
    //         'to_date' => 'required'
    //     ]);
    //     $title = 'purchases reports';
    //     $purchases = Purchase::whereBetween(DB::raw('DATE(created_at)'), array($request->from_date, $request->to_date))->get();
    //     return view('admin.purchases.reports',compact(
    //         'purchases','title'
    //     ));
    // }

    public function generateReport(Request $request)
{
    $this->validate($request, [
        'from_date' => 'required',
        'to_date' => 'required',
    ]);

    $fromDate = $request->from_date;
    $toDate = $request->to_date;

    // Set the title with the date range
    $title = 'Purchases Report from ' . date('d M, Y', strtotime($fromDate)) . ' to ' . date('d M, Y', strtotime($toDate));
    
    // Fetch purchases within the given date range
    $purchases = Purchase::whereBetween(DB::raw('DATE(created_at)'), [$fromDate, $toDate])->get();

    return view('admin.purchases.reports', compact('purchases', 'title', 'fromDate', 'toDate'));
}


    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function destroy(Request $request)
    {
        return Purchase::findOrFail($request->id)->delete();
    }
}
