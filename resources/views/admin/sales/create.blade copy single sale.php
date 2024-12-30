@extends('admin.layouts.app')

@push('page-css')
<!-- Add any additional CSS if necessary -->
@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">Create Sale</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Create Sale</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body custom-edit-service">
                <!-- Create Sale Form -->
                <form method="POST" action="{{ route('sales.store') }}">
                    @csrf
                    <div class="row form-row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="product">Product</label>
                                <select name="product" id="product" class="select2 form-select form-control" required>
                                    <option value="">Select a product</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                        {{ $product->purchase->product }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="quantity">Quantity</label>
                                <input type="number" name="quantity" id="quantity" class="form-control" min="1" value="1" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="discount">Discount (%)</label>
                                <input type="number" name="discount" id="discount" class="form-control" min="0" max="100" value="0">
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label for="total_price" style=" font-size: 30px;">Total Price</label>
                                <input style="background-color:rgb(91, 86, 86); color:white; font-size: 30px;" class="form-control" type="text" name="total_price" id="total_price"  readonly>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Create Sale</button>
                    </div>
                </form>
            </div>
            <!--/ Create Sale Form -->
        </div>
    </div>
</div>
</div>
@endsection

@push('page-js')
<script>
    // Trigger calculation when product, quantity or discount changes
    document.getElementById('product').addEventListener('change', calculateTotal);
    document.getElementById('quantity').addEventListener('input', calculateTotal);
    document.getElementById('discount').addEventListener('input', calculateTotal);

    // Default calculation on page load
    window.addEventListener('load', calculateTotal);

    function calculateTotal() {
        const productSelect = document.getElementById('product');
        const quantityInput = document.getElementById('quantity');
        const discountInput = document.getElementById('discount');
        const totalPriceField = document.getElementById('total_price');

        const price = productSelect.options[productSelect.selectedIndex]?.getAttribute('data-price');
        
        // If no product is selected, prevent calculation
        if (!price) {
            totalPriceField.value = '';
            return;
        }

        const quantity = parseInt(quantityInput.value) || 1; // Default quantity is 1
        const discount = parseInt(discountInput.value) || 0; // Default discount is 0

        let totalPrice = price * quantity;

        // Apply discount if there is one
        if (discount > 0) {
            totalPrice -= (totalPrice * (discount / 100));
        }

        totalPriceField.value = totalPrice.toFixed(2);
    }
</script>
@endpush
