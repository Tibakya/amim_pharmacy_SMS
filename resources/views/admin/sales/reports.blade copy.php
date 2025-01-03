@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
    
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title">Sales Reports</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
        <li class="breadcrumb-item active">Generate Sales Reports</li>
    </ul>
</div>
<div class="col-sm-5 col">
    <a href="#generate_report" data-toggle="modal" class="btn btn-primary float-right mt-2">Generate Report</a>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        @isset($sales)
            <!-- Sales Report -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="sales-table" class="datatable table table-hover table-center mb-0">
                            <thead>
                                <tr>
                                    <th>Serial No</th>
                                    <th>Medicine Name</th>
                                    <th>Batch Number</th>
                                    <th>Quantity</th>
                                    <th>Total Price</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $serial = 1; $totalPrice = 0; @endphp
                                @foreach ($sales as $sale)
                                    @if (!empty($sale->product->purchase))
                                        <tr>
                                            <td>{{ $serial++ }}</td>
                                            <td>
                                                {{$sale->product->purchase->product}}
                                                @if (!empty($sale->product->purchase->image))
                                                    <span class="avatar avatar-sm mr-2">
                                                        <img class="avatar-img" src="{{asset("storage/purchases/".$sale->product->purchase->image)}}" alt="image">
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $sale->product->purchase->batch_number ?? 'N/A' }}</td>
                                            <td>{{$sale->quantity}}</td>
                                            <td>{{AppSettings::get('app_currency', '$ ')}} {{number_format($sale->total_price, 2)}}</td>
                                            <td>{{date_format(date_create($sale->created_at), "d M, Y")}}</td>
                                        </tr>
                                        @php $totalPrice += $sale->total_price; @endphp
                                    @endif
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" style="text-align:right">Total:</th>
                                    <th>{{AppSettings::get('app_currency', '$ ')}} {{number_format($totalPrice, 2)}}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <!-- / Sales Report -->
        @endisset
    </div>
</div>

<!-- Generate Modal -->
<div class="modal fade" id="generate_report" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="page-title">{{ $title ?? 'Generate Sales Report' }}</h3> <!-- Dynamically display the title -->
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('sales.report') }}">
                    @csrf
                    <div class="row form-row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>From</label>
                                        <input type="date" name="from_date" class="form-control from_date" value="{{ $fromDate ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>To</label>
                                        <input type="date" name="to_date" class="form-control to_date" value="{{ $toDate ?? '' }}">
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
    $(document).ready(function(){
        $('#sales-table').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'collection',
                    text: 'Export Data',
                    buttons: [
                        {
                            extend: 'pdf',
                            title: 'Sales Report from ' + $('.from_date').val() + ' to ' + $('.to_date').val(),
                            exportOptions: {
                                columns: "thead th:not(.action-btn)"
                            },
                            customize: function (doc) {
                                doc.content[1].table.widths = ['10%', '20%', '20%', '20%', '20%', '10%'];
                            }
                        },
                        {
                            extend: 'excel',
                            title: 'Sales Report from ' + $('.from_date').val() + ' to ' + $('.to_date').val(),
                            exportOptions: {
                                columns: "thead th:not(.action-btn)"
                            }
                        },
                        {
                            extend: 'csv',
                            title: 'Sales Report from ' + $('.from_date').val() + ' to ' + $('.to_date').val(),
                            exportOptions: {
                                columns: "thead th:not(.action-btn)"
                            }
                        },
                        {
                            extend: 'print',
                            title: 'Sales Report from ' + $('.from_date').val() + ' to ' + $('.to_date').val(),
                            exportOptions: {
                                columns: "thead th:not(.action-btn)"
                            },
                            customize: function (win) {
                                $(win.document.body).find('table').addClass('compact').css('font-size', 'inherit');
                            }
                        }
                    ]
                }
            ]
        });
    });
</script>
@endpush
