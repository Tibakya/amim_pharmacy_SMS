@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title">Sales Reports</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
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
                                            {{ $sale->product->purchase->product }}
                                            @if (!empty($sale->product->purchase->image))
                                                <span class="avatar avatar-sm mr-2">
                                                    <img class="avatar-img" src="{{ asset("storage/purchases/" . $sale->product->purchase->image) }}" alt="image">
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $sale->product->purchase->batch_number ?? 'N/A' }}</td>
                                        <td>{{ $sale->quantity }}</td>
                                        <td>{{ AppSettings::get('app_currency', '$ ') }} {{ number_format($sale->total_price, 2) }}</td>
                                        <td>{{ date_format(date_create($sale->created_at), "d M, Y") }}</td>
                                    </tr>
                                    @php $totalPrice += $sale->total_price; @endphp
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" style="text-align:right">Total:</th>
                                <th>{{ AppSettings::get('app_currency', '$ ') }} {{ number_format($totalPrice, 2) }}</th>
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
                <h3 class="page-title" id="report-title">{{ $title ?? 'Generate Sales Report' }}</h3>
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
    $(document).ready(function() {
        // Update report title with dynamic date range
        $('.submit_report').click(function() {
            var fromDate = $('.from_date').val();
            var toDate = $('.to_date').val();
            var title = 'Sales Report from ' + fromDate + ' to ' + toDate;
            $('#report-title').text(title);
        });

        // Initialize DataTable with export options
        $('#sales-table').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    title: function () {
                        // Dynamically update the title for PDF export
                        var fromDate = $('.from_date').val();
                        var toDate = $('.to_date').val();
                        return 'Sales Report from ' + fromDate + ' to ' + toDate;
                    },
                    footer: true,
                    orientation: 'landscape', // Set the orientation to landscape
                    pageSize: 'A4', // Set page size to A4
                    exportOptions: {
                        columns: ':visible'
                    },
                    customize: function (doc) {
                        // Set the table width to occupy the full landscape page width
                        doc.content[1].table.widths = [ '5%', '40%', '10%', '12%', '12%', '12%' ];  // Adjust column widths
                    }
                },
                {
                    extend: 'excelHtml5',
                    title: function () {
                        // Dynamically update the title for Excel export
                        var fromDate = $('.from_date').val();
                        var toDate = $('.to_date').val();
                        return 'Sales Report from ' + fromDate + ' to ' + toDate;
                    },
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'csvHtml5',
                    title: function () {
                        // Dynamically update the title for CSV export
                        var fromDate = $('.from_date').val();
                        var toDate = $('.to_date').val();
                        return 'Sales Report from ' + fromDate + ' to ' + toDate;
                    },
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'print',
                    title: function () {
                        // Dynamically update the title for print export
                        var fromDate = $('.from_date').val();
                        var toDate = $('.to_date').val();
                        return 'Sales Report from ' + fromDate + ' to ' + toDate;
                    },
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }
            ]
        });
    });
</script>
@endpush
