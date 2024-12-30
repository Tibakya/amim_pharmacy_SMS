@extends('admin.layouts.app')

@push('page-css')
<!-- Add any necessary styles here -->
@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">Edit Sale</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
        <li class="breadcrumb-item active">Edit Sale</li>
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
                            <div class="form-group">
                                <label>Product <span class="text-danger">*</span></label>
                                <select class="select2 form-select form-control" name="product" id="productSelect"> 
                                    @foreach ($products as $product)
                                        @if (!empty($product->purchase))
                                            @if (!($product->purchase->quantity <= 0))
                                                <option value="{{$product->id}}" data-price="{{$product->purchase->price}}">{{$product->purchase->product}}</option>
                                            @endif
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label>Quantity</label>
                                <input type="number" value="1" class="form-control" name="quantity" id="quantityInput">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label>Discount (%)</label>
                                <input type="number" value="0" class="form-control" name="discount" id="discountInput">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success" id="addToCart">Add to Cart</button>
                    
                    <!-- Cart Table -->
                    <h4 class="mt-4">Cart</h4>
                    <table class="table table-bordered" id="cartTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Discount (%)</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Cart items will be added here dynamically -->
                        </tbody>
                    </table>

                    <!-- Total Amount -->
                    <h4 class="mt-3">Total: <span id="totalAmount">0</span></h4>

                    <!-- Hidden Field for Cart Data -->
                    <input type="hidden" name="cart_data" id="cartDataInput">
                    
                    <button type="submit" class="btn btn-primary btn-block">Complete Sale</button>
                </form>
                <!--/ Create Sale -->
            </div>
        </div>
    </div>            
</div>
@endsection

@push('page-js')
<script>
    let cart = [];

    document.getElementById('addToCart').addEventListener('click', function() {
        const productId = document.getElementById('productSelect').value;
        const productName = document.querySelector(`#productSelect option[value="${productId}"]`).text;
        const price = parseFloat(document.querySelector(`#productSelect option[value="${productId}"]`).dataset.price);
        const quantity = parseInt(document.getElementById('quantityInput').value);
        const discount = parseFloat(document.getElementById('discountInput').value);

        // Calculate the subtotal for the item
        const subtotal = (price * quantity) * (1 - discount / 100);

        // Add the item to the cart
        cart.push({ productId, productName, price, quantity, discount, subtotal });

        // Update the cart table and total
        updateCartTable();
        updateTotalAmount();

        // Update the hidden field with cart data
        document.getElementById('cartDataInput').value = JSON.stringify(cart);
    });

    function updateCartTable() {
        const tableBody = document.querySelector('#cartTable tbody');
        tableBody.innerHTML = ''; // Clear current table rows

        cart.forEach((item, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.productName}</td>
                <td>${item.price.toFixed(2)}</td>
                <td>
                    <input type="number" value="${item.quantity}" class="form-control" data-index="${index}" onchange="updateItemQuantity(${index}, this)">
                </td>
                <td>
                    <input type="number" value="${item.discount}" class="form-control" data-index="${index}" onchange="updateItemDiscount(${index}, this)">
                </td>
                <td>${item.subtotal.toFixed(2)}</td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeFromCart(${index})">Remove</button></td>
            `;
            tableBody.appendChild(row);
        });
    }

    function updateItemQuantity(index, input) {
        const newQuantity = parseInt(input.value);
        cart[index].quantity = newQuantity;

        // Recalculate the subtotal
        cart[index].subtotal = (cart[index].price * newQuantity) * (1 - cart[index].discount / 100);
        
        // Update the cart and total
        updateCartTable();
        updateTotalAmount();

        // Update the hidden field
        document.getElementById('cartDataInput').value = JSON.stringify(cart);
    }

    function updateItemDiscount(index, input) {
        const newDiscount = parseFloat(input.value);
        cart[index].discount = newDiscount;

        // Recalculate the subtotal
        cart[index].subtotal = (cart[index].price * cart[index].quantity) * (1 - newDiscount / 100);
        
        // Update the cart and total
        updateCartTable();
        updateTotalAmount();

        // Update the hidden field
        document.getElementById('cartDataInput').value = JSON.stringify(cart);
    }

    function removeFromCart(index) {
        cart.splice(index, 1); // Remove item from cart array
        updateCartTable(); // Update the cart table display
        updateTotalAmount(); // Recalculate total amount
        document.getElementById('cartDataInput').value = JSON.stringify(cart); // Update hidden field
    }

    function updateTotalAmount() {
        const totalAmount = cart.reduce((total, item) => total + item.subtotal, 0);
        document.getElementById('totalAmount').textContent = totalAmount.toFixed(2);
    }
</script>
@endpush
