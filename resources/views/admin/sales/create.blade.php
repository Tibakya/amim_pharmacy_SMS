@extends('admin.layouts.app')

@push('page-css')
<!-- Add any additional CSS if necessary -->
@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">Create Sales</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Create Sales</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body custom-edit-service">
                <!-- Create Multiple Sales Form -->
                <form method="POST" action="{{ route('sales.storeMultiple') }}">
                    @csrf
                    <div id="sales-entries">
                        <div class="row form-row sale-entry">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="product_0">Product</label>
                                    <select name="sales[0][product_id]" id="product_0" class="select2 form-select form-control product-select" required>
                                        <option value="">Select a product</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                            {{ $product->purchase->product }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="quantity_0">Quantity</label>
                                    <input type="number" name="sales[0][quantity]" id="quantity_0" class="form-control quantity-input" min="1" value="1" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="discount_0">Discount (%)</label>
                                    <input type="number" name="sales[0][discount]" id="discount_0" class="form-control discount-input" min="0" max="100" value="0">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="total_price_0">Total Price</label>
                                    <input type="text" name="sales[0][total_price]" id="total_price_0" class="form-control total-price-field" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-danger btn-block remove-entry">Remove</button>
                            </div>
                        </div>
                    </div>

                    <!-- Total Sum Display -->
                    <div id="total-summary" class="mt-3">
                        <h4>Total Sum: <span id="total-sum">0.00</span></h4>
                    </div>
                    <!-- End Total Sum Display -->

                    <button type="button" id="add-sale" class="btn btn-success mt-3">Add Sale</button>
                    <button type="submit" class="btn btn-primary mt-3">Submit Sales</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
    let entryCount = 1;

    document.getElementById('add-sale').addEventListener('click', function () {
        const entry = document.createElement('div');
        entry.className = 'row form-row sale-entry';
        entry.innerHTML = `
            <div class="col-md-4">
                <div class="form-group">
                    <label for="product_${entryCount}">Product</label>
                    <select name="sales[${entryCount}][product_id]" id="product_${entryCount}" class="select2 form-select form-control product-select" required>
                        <option value="">Select a product</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                            {{ $product->purchase->product }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="quantity_${entryCount}">Quantity</label>
                    <input type="number" name="sales[${entryCount}][quantity]" id="quantity_${entryCount}" class="form-control quantity-input" min="1" value="1" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="discount_${entryCount}">Discount (%)</label>
                    <input type="number" name="sales[${entryCount}][discount]" id="discount_${entryCount}" class="form-control discount-input" min="0" max="100" value="0">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="total_price_${entryCount}">Total Price</label>
                    <input type="text" name="sales[${entryCount}][total_price]" id="total_price_${entryCount}" class="form-control total-price-field" readonly>
                </div>
            </div>
            <div class="col-md-2">
                <label>&nbsp;</label>
                <button type="button" class="btn btn-danger btn-block remove-entry">Remove</button>
            </div>
        `;
        document.getElementById('sales-entries').appendChild(entry);
        entryCount++;
        attachEventListeners();
    });

    function attachEventListeners() {
        document.querySelectorAll('.product-select, .quantity-input, .discount-input').forEach(input => {
            input.addEventListener('change', calculateTotals);
        });

        document.querySelectorAll('.remove-entry').forEach(button => {
            button.addEventListener('click', function () {
                button.closest('.sale-entry').remove();
                calculateTotals();
            });
        });
    }

    function calculateTotals() {
        let totalSum = 0;
        let valid = true;

        document.querySelectorAll('.sale-entry').forEach(entry => {
            const productSelect = entry.querySelector('.product-select');
            const quantityInput = entry.querySelector('.quantity-input');
            const discountInput = entry.querySelector('.discount-input');
            const totalPriceField = entry.querySelector('.total-price-field');

            const price = productSelect.options[productSelect.selectedIndex]?.getAttribute('data-price');
            if (!price) {
                totalPriceField.value = '';
                valid = false;
                return;
            }

            const quantity = parseInt(quantityInput.value) || 1;
            if (quantity <= 0) {
                valid = false;
                quantityInput.classList.add('is-invalid');
            } else {
                quantityInput.classList.remove('is-invalid');
            }

            const discount = parseInt(discountInput.value) || 0;
            if (discount < 0 || discount > 100) {
                valid = false;
                discountInput.classList.add('is-invalid');
            } else {
                discountInput.classList.remove('is-invalid');
            }

            let totalPrice = price * quantity;
            if (discount > 0) {
                totalPrice -= (totalPrice * (discount / 100));
            }

            totalPriceField.value = totalPrice.toFixed(2);
            totalSum += parseFloat(totalPrice);
        });

        document.getElementById('total-sum').innerText = totalSum.toFixed(2);

        // If any entry is invalid, prevent form submission
        if (!valid) {
            document.querySelector('button[type="submit"]').disabled = true;
        } else {
            document.querySelector('button[type="submit"]').disabled = false;
        }
    }

    // Confirmation before submitting the form
    document.querySelector('form').addEventListener('submit', function (event) {
        const isConfirmed = confirm("Are you sure you want to sale these products?");
        if (!isConfirmed) {
            event.preventDefault(); // Prevent form submission if not confirmed
        }
    });

    document.addEventListener('DOMContentLoaded', attachEventListeners);
</script>
@endpush
