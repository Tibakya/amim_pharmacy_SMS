@extends('admin.layouts.app')


@push('page-css')

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
                                <label for="discount">Discount</label>
                                <input type="text" id="discount" name="discount" class="form-control">
                            </div>
                            </div>




                            <div class="col-4">
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type="number" value="1" class="form-control" name="quantity">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
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
                    $('#price').val(response.price || '');
                    $('#discount').val(response.discount || '');
                },
                error: function(xhr) {
                    console.error("An error occurred:", xhr.responseText);
                    $('#price').val('');
                    $('#discount').val('');
                }
            });
        } else {
            $('#price').val('');
            $('#discount').val('');
        }
    });
});
</script>

@endpush