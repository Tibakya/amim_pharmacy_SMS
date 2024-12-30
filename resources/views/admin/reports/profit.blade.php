@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title" id="report-title">Profit Reports</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Generate Profit Reports</li>
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
        <!-- Profit Report -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="profit-table" class="datatable table table-hover table-center mb-0">
                        <thead>
                            <tr>
                                <th>Serial No</th>
                                <th>Product</th>
                                <th>Batch Number</th>
                                <th>Total Price</th>
                                <th>Cost Price</th>
                                <th>Profit</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $serial = 1; @endphp
                            @foreach ($sales as $sale)
                                @if (!empty($sale->product->purchase))
                                    @php 
                                        $costPrice = $sale->product->purchase->cost_price;
                                        $profit = $sale->total_price - ($costPrice * $sale->quantity); 
                                    @endphp
                                    <tr>
                                        <td>{{ $serial++ }}</td>
                                        <td>
                                            {{ $sale->product->purchase->product }}
                                            @if (!empty($sale->product->purchase->image))
                                                <span class="avatar avatar-sm mr-2">
                                                    <img class="avatar-img" src="{{ asset("storage/purchases/" . $sale->product->purchase->image) }}" alt="image">
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $sale->product->purchase->batch_number ?? 'N/A' }}</td>
                                        <td>{{ AppSettings::get('app_currency', '$ ') }} {{ number_format($sale->total_price, 2) }}</td>
                                        <td>{{ AppSettings::get('app_currency', '$ ') }} {{ number_format($costPrice, 2) }}</td>
                                        <td>{{ AppSettings::get('app_currency', '$ ') }} {{ number_format($profit, 2) }}</td>
                                        <td>{{ date_format(date_create($sale->created_at), "d M, Y") }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">Total</th>
                                <th>{{ AppSettings::get('app_currency', '$ ') }} {{ number_format($sales->sum('total_price'), 2) }}</th>
                                <th></th>
                                <th>{{ AppSettings::get('app_currency', '$ ') }} {{ number_format($totalProfit, 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <!-- / Profit Report -->
        @endisset
    </div>
</div>

<!-- Generate Modal -->
<div class="modal fade" id="generate_report" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="page-title" id="modal-report-title">Generate Profit Report</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="get" action="{{ route('admin.profit-report.generateReport') }}">
                    @csrf
                    <div class="row form-row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>From</label>
                                        <input type="date" name="from_date" class="form-control from_date" value="{{ old('from_date', $fromDate ?? '') }}">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>To</label>
                                        <input type="date" name="to_date" class="form-control to_date" value="{{ old('to_date', $toDate ?? '') }}">
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
        // Update the modal report title and export options dynamically with selected dates
        $('.submit_report').click(function() {
            var fromDate = $('.from_date').val();
            var toDate = $('.to_date').val();
            var title = 'Profit Report from ' + fromDate + ' to ' + toDate;
            $('#modal-report-title').text(title);  // Update the modal header title
        });

        // Initialize DataTable with export options
        $('#profit-table').DataTable({
            dom: 'Bfrtip',
            language: {
                emptyTable: "No Data! Please select a date range."
            },

            buttons: [
                {
                    extend: 'pdfHtml5',
                    title: function () {
                        var fromDate = $('.from_date').val();
                        var toDate = $('.to_date').val();
                        return 'Profit Report from ' + fromDate + ' to ' + toDate; // Dynamically set title for PDF
                    },
                    footer: true,
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: ':visible'
                    },
                    customize: function (doc) {
                        doc.content[1].table.widths = ['5%', '40%', '10%', '12%', '12%', '12%', '12%'];
                        doc.content.unshift({
                            text: 'Total Profit: {{ AppSettings::get("app_currency", "$") }} {{ number_format($totalProfit, 2) }}',
                            alignment: 'right',
                            margin: [0, 10, 20, 0]
                        });
                    }
                },
                {
                    extend: 'excelHtml5',
                    title: function () {
                        var fromDate = $('.from_date').val();
                        var toDate = $('.to_date').val();
                        return 'Profit Report from ' + fromDate + ' to ' + toDate; // Dynamically set title for Excel
                    },
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'csvHtml5',
                    title: function () {
                        var fromDate = $('.from_date').val();
                        var toDate = $('.to_date').val();
                        return 'Profit Report from ' + fromDate + ' to ' + toDate; // Dynamically set title for CSV
                    },
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'print',
                    title: function () {
                        var fromDate = $('.from_date').val();
                        var toDate = $('.to_date').val();
                        return 'Profit Report from ' + fromDate + ' to ' + toDate; // Dynamically set title for Print
                    },
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    },
                    customize: function (win) {
                        $(win.document.body).css('transform', 'rotate(-90deg)');
                        $(win.document.body).css('transform-origin', 'left top');
                        $(win.document.body).css('width', '100%');
                        $(win.document.body).css('height', '100%');
                        $(win.document.body).css('overflow', 'hidden');
                    }
                }
            ]
        });
    });
</script>
@endpush
