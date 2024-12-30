@extends('admin.layouts.app')


@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">Create Sale</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
        <li class="breadcrumb-item active">Create Sale</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body custom-edit-service">
                <!-- Create Sale -->
                <form method="POST" action="{{route('sales.store')}}">
                    @csrf
                    <div class="row form-row">
                        <div class="col-4">

                            <!-- Create Sale Form -->
                            <div class="form-group">
                                <label for="product">Product</label>
                                <select class="select2 form-select form-control" name="product" id="product" class="form-control">
                                    <option value="">Select a product</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->purchase->product ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            </div>
                            <div class="col-4">
                            <div class="form-group">
                                <label for="price">Price</label>
                                <input type="text" id="price" name="price" class="form-control" readonly>
                            </div>
                            </div>


                            <div class="col-4">
                            <div class="form-group">
                                <label for="discount">Discount (%)</label>
                                <input type="text" id="discount" name="discount" class="form-control" readonly>
                            </div>
                            </div>




                            <div class="col-4">
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type="number" value="1" class="form-control" name="quantity">
                                </div>
                            </div>




                            <div class="col-8">
    <div class="form-group" >
        <label style="font-size: 19px; " >Total Cost</label>
        <p style=" font-size: 20px; font-weight: 700; " id="total-cost" class="form-control" readonly>0.0</p>
    </div>
</div>




                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Confirm Sale</button>
                </form>
                <!--/ Create Sale -->
            </div>
        </div>
    </div>
</div>
@endsection


@push('page-js')

<script>
    $(document).ready(function() {
        // Format money function (adds commas and two decimal places)
        function formatMoney(value) {
            return value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Function to calculate total cost
        function calculateTotal() {
            const price = parseFloat($('#price').val().replace(/,/g, '')) || 0;
            const discountPercentage = parseFloat($('#discount').val().replace(/,/g, '')) || 0;
            const quantity = parseInt($('input[name="quantity"]').val()) || 1;

            const discount = (discountPercentage / 100) * price; // Calculate discount amount
            const discountedPrice = price - discount; // Apply discount to price
            const total = discountedPrice * quantity; // Calculate total cost

            $('#total-cost').text(formatMoney(total)); // Display the total cost with formatting
        }

        // Fetch product details and update price/discount
        $('#product').on('change', function() {
            const productId = $(this).val();

            if (productId) {
                $.ajax({
                    url: "{{ route('products.details') }}",
                    type: "POST",
                    data: {
                        product_id: productId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        $('#price').val(formatMoney(response.price || 0));
                        $('#discount').val(response.discount || 0); // No formatting for percentage
                        calculateTotal(); // Recalculate total cost
                    },
                    error: function(xhr) {
                        console.error("An error occurred:", xhr.responseText);
                        $('#price').val('');
                        $('#discount').val('');
                        calculateTotal(); // Recalculate total cost
                    }
                });
            } else {
                $('#price').val('');
                $('#discount').val('');
                calculateTotal(); // Recalculate total cost
            }
        });

        // Update total cost on quantity change
        $('input[name="quantity"]').on('input', function() {
            calculateTotal();
        });

        // Update total cost on discount change
        $('#discount').on('input', function() {
            calculateTotal();
        });
    });
</script>


@endpush