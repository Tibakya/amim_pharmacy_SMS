@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title">Profit Reports</h3>
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
                                @php $serial = 1; $totalProfit = 0; @endphp
                                @foreach ($sales as $sale)
                                    @if (!empty($sale->product->purchase))
                                        @php 
                                            $costPrice = $sale->product->purchase->cost_price;
                                            $profit = $sale->total_price - ($costPrice * $sale->quantity); 
                                            $totalProfit += $profit;
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
                <h3 class="page-title">Generate Profit Report</h3>
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
    $(document).ready(function () {
        $('#profit-table').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'collection',
                    text: 'Export Data',
                    buttons: [
                        {
                            extend: 'pdf',
                            title: 'Profit Report from ' + $('.from_date').val() + ' to ' + $('.to_date').val(),
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            },
                            customize: function (doc) {
                                doc.content[1].table.widths = ['10%', '15%', '15%', '15%', '15%', '15%', '15%'];
                                doc.styles.tableHeader = {
                                    bold: true,
                                    fontSize: 11,
                                    color: 'black',
                                    fillColor: '#d3d3d3',
                                    alignment: 'center'
                                };
                                // Add the footer totals to the PDF
                                var totalPriceFooter = '$' + $('#profit-table tfoot th:nth-child(4)').text().trim();
                                var totalProfitFooter = '$' + $('#profit-table tfoot th:nth-child(6)').text().trim();
                                doc.content.push({
                                    text: `\nTotal Price: ${totalPriceFooter}\nTotal Profit: ${totalProfitFooter}`,
                                    style: 'footer'
                                });
                            }
                        },
                        {
                            extend: 'excel',
                            title: 'Profit Report from ' + $('.from_date').val() + ' to ' + $('.to_date').val(),
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            }
                        },
                        {
                            extend: 'csv',
                            title: 'Profit Report from ' + $('.from_date').val() + ' to ' + $('.to_date').val(),
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            }
                        },
                        {
                            extend: 'print',
                            title: 'Profit Report from ' + $('.from_date').val() + ' to ' + $('.to_date').val(),
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            },
                            customize: function (win) {
                                $(win.document.body).find('table').addClass('compact').css('font-size', 'inherit');
                                $(win.document.body).find('tfoot').show(); // Ensure the footer is visible
                            }
                        }
                    ]
                }
            ],
            footerCallback: function (row, data, start, end, display) {
                var api = this.api();

                // Helper function to parse numbers
                var intVal = function (i) {
                    return typeof i === 'string' ?
                        parseFloat(i.replace(/[^0-9.-]+/g, '')) || 0 :
                        typeof i === 'number' ? i : 0;
                };

                // Calculate total price
                var total = api.column(3).data().reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

                // Calculate total profit
                var totalProfit = api.column(5).data().reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

                // Update the footer with totals
                $(api.column(3).footer()).html('$' + total.toFixed(2));
                $(api.column(5).footer()).html('$' + totalProfit.toFixed(2));
            }
        });
    });
</script>


@endpush
