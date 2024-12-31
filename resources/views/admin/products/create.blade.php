@extends('admin.layouts.app')

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">Add Product</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
        <li class="breadcrumb-item active">Add Product</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body custom-edit-service">
                <!-- Add Product -->
                <form method="post" enctype="multipart/form-data" id="update_service" action="{{route('products.store')}}">
                    @csrf

                    <!-- Row 1: Product and Batch Number -->
                    <div class="row mb-3">
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                <label>Product <span class="text-danger">*</span></label>
                                <select class="select2 form-select form-control" name="product">
                                    <option value="" selected disabled>Select a product</option>
                                    @foreach ($purchases as $purchase)
                                    <option value="{{$purchase->id}}">{{$purchase->product}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                <label>Batch Number <span class="text-danger">*</span></label>
                                <select class="select2 form-select form-control" name="batch_no">
                                    <option value="" selected disabled>Select a batch number</option>
                                    @foreach ($purchases as $purchase)
                                    <option value="{{$purchase->batch_number}}">{{$purchase->batch_number}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Selling Price and Discount -->
                    <div class="row mb-3">
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                <label>Selling Price <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="price" value="{{old('price')}}">
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                <label>Discount (%) <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="discount" value="0" readonly >
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Description -->
                    <div class="row mb-3">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Descriptions <span class="text-danger">*</span></label>
                                <textarea class="form-control service-desc" name="description">{{old('description')}}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="row">
                        <div class="col-lg-12 text-right">
                            <div class="submit-section">
                                <button class="btn btn-primary submit-btn" type="submit" name="form_submit" value="submit">Submit</button>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- /Add Product -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
    $(document).ready(function() {
        $('select[name="product"]').on('change', function() {
            var productId = $(this).val();
            if (productId) {
                $.ajax({
                    url: '{{ route("products.fetchBatchNumbers") }}',
                    type: 'GET',
                    data: { product_id: productId },
                    success: function(data) {
                        $('select[name="batch_no"]').empty().append('<option value="" selected disabled>Select a batch number</option>');
                        $.each(data, function(key, value) {
                            $('select[name="batch_no"]').append('<option value="' + key + '">' + value + '</option>');
                        });
                    }
                });
            } else {
                $('select[name="batch_no"]').empty().append('<option value="" selected disabled>Select a batch number</option>');
            }
        });
    });
</script>
@endpush
