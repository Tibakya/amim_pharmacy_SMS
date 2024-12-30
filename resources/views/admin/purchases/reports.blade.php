@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
    
    <h3 class="page-title">{{ $title }}</h3> <!-- Dynamically display the title -->
  
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Generate Purchase Reports</li>
    </ul>
</div>
<div class="col-sm-5 col">
    <a href="#generate_report" data-toggle="modal" class="btn btn-primary float-right mt-2">Generate Report</a>
</div>
@endpush

@section('content')
@isset($purchases)
<div class="row">
    <div class="col-md-12">
        <!-- Purchases reports-->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="purchase-table" class="datatable table table-hover table-center mb-0">
                        <thead>
                            <tr>
                                <th>Medicine Name</th>
                                <th>Batch No.</th> <!-- Added Batch No. column -->
                                <th>Category</th>
                                <th>Supplier</th>
                                <th>Purchase Cost</th>
                                <th>Quantity</th>
                                <th>Expire Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalCost = 0; @endphp <!-- Initialize total cost variable -->
                            @foreach ($purchases as $purchase)
                                @if(!empty($purchase->supplier) && !empty($purchase->category))
                                    <tr>
                                        <td>
                                            <h2 class="table-avatar">
                                                @if(!empty($purchase->image))
                                                    <span class="avatar avatar-sm mr-2">
                                                        <img class="avatar-img" src="{{ asset('storage/purchases/'.$purchase->image) }}" alt="product image">
                                                    </span>
                                                @endif
                                                {{$purchase->product}}
                                            </h2>
                                        </td>
                                        <td>{{$purchase->batch_number}}</td> <!-- Displaying the Batch No. -->
                                        <td>{{$purchase->category->name}}</td>
                                        <td>{{$purchase->supplier->name}}</td>
                                        <td>{{ AppSettings::get('app_currency', '$ ') }}{{ number_format($purchase->cost_price, 2) }}</td>
                                        <td>{{$purchase->quantity}}</td>
                                        <td>{{ date_format(date_create($purchase->expiry_date), "d M, Y") }}</td>
                                    </tr>
                                    @php
                                        $totalCost += $purchase->cost_price * $purchase->quantity; // Add cost to total
                                    @endphp
                                @endif
                            @endforeach                         
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4"><strong>Total Purchase Cost</strong></td>
                                <td colspan="3">
                                    <strong>{{ AppSettings::get('app_currency', '$ ') }}{{ number_format($totalCost, 2) }}</strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <!-- /Purchases Report -->
    </div>
</div>
@endisset

<!-- Generate Modal -->
<div class="modal fade" id="generate_report" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('purchases.report') }}">
                    @csrf
                    <div class="row form-row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>From</label>
                                        <input type="date" name="from_date" class="form-control from_date">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>To</label>
                                        <input type="date" name="to_date" class="form-control to_date">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block submit_report">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /Generate Modal -->
@endsection

@push('page-js')
<script>
    $(document).ready(function() {
        // Calculate the total cost
        var totalCost = 0;
        @foreach ($purchases as $purchase)
            @if(!empty($purchase->supplier) && !empty($purchase->category))
                totalCost += {{$purchase->cost_price}} * {{$purchase->quantity}};
            @endif
        @endforeach

        // Format total cost with commas
        var formattedTotalCost = totalCost.toLocaleString('en-US', { minimumFractionDigits: 2 });

        // Display formatted total cost
        $('#totalCostDisplay').text('Total Purchase Cost: {{ AppSettings::get("app_currency", "$") }}' + formattedTotalCost);

        // Initialize DataTable with export options
        $('#purchase-table').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'collection',
                    text: 'Export Data',
                    buttons: [
                        {
                            extend: 'pdf',
                            exportOptions: { columns: ':visible' },
                            customize: function(doc) {
                                doc.content.push({
                                    text: 'Total Purchase Cost: {{ AppSettings::get("app_currency", "$ ") }}' + formattedTotalCost,
                                    alignment: 'right',
                                    margin: [0, 10, 20, 0]
                                });
                            }
                        },
                        {
                            extend: 'excel',
                            exportOptions: { columns: ':visible' }
                        },
                        {
                            extend: 'csv',
                            exportOptions: { columns: ':visible' }
                        },
                        {
                            extend: 'print',
                            exportOptions: { columns: ':visible' }
                        }
                    ]
                }
            ]
        });
    });
</script>

@endpush
