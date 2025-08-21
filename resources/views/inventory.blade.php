@extends('layout')
@section('content')
    <!-- jQuery Confirm CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-confirm@3.3.4/css/jquery-confirm.min.css">

    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-0">Inventory Management</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">All Products</div>
                    <div class="card-options">
                        <button type="button" class="btn btn-outline-primary my-1">Total Stock Amount <span
                                class="badge ms-2">{{ $totalStockAmount }}</span></button>
                        <a class="btn btn-primary btn-sm add-product">Add Product</a>
                        <a class="btn btn-secondary btn-sm update-stock">Update Stock</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="file-exports" class="table table-bordered text-nowrap w-100">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Product Name</th>
                                    <th>Selling Price</th>
                                    <th>Quantiy</th>
                                    <th>Stock Price</th>
                                    <th>View Image</th>
                                    <th>Options</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $p)
                                    @php
                                        $stockPrice = $p->selling_price * ($p->quantity ?? 0);
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $p->product_name }}</td>
                                        <td>{{ $p->selling_price }}</td>
                                        <td>{{ $p->quantity ?? '' }}</td>
                                        <td>{{ $stockPrice ?? 'N/A' }}</td>
                                        <td><button class="btn btn-secondary btn-sm viewProductImageBtn"
                                                data-id="{{ $p->_id }}">View Image</button></td>
                                        <td>
                                            <button class="btn btn-primary btn-sm editBtn"
                                                data-id="{{ $p->_id }}">Edit</button>
                                            <button class="btn btn-danger btn-sm deleteBtn"
                                                data-id="{{ $p->_id }}">Delete</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Modals for add product  --}}
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="exampleModalLgLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalLgLabel">Add Product</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="response">
                    <form id="addProductForm" method="POST" action="{{ route('inventory.create') }}">
                        {{-- CSRF Token --}}
                        @csrf
                        <div class="mb-3">
                            <label for="product_name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="product_name" name="product_name"
                                placeholder="Enter Product Name" required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="selling_price" class="form-label">Selling Price</label>
                            <input type="number" class="form-control" id="selling_price" placeholder="Enter Selling Price"
                                name="selling_price" autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="product_image" class="form-label">Add Image</label>
                            <input type="file" accept="image/*" class="form-control" id="product_image"
                                name="product_image" autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <button class="btn btn-secondary" type="submit">Add Product</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal to show image --}}
    <div class="modal fade" id="viewImageModal" tabindex="-1" aria-labelledby="exampleModalScrollable2"
        data-bs-keyboard="false" aria-hidden="true">
        <!-- Scrollable modal -->
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="staticBackdropLabel2">Product Image
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img src="" alt="product image" class="img-fluid" width="150" height="150">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save
                        Changes</button>
                </div>
            </div>
        </div>
    </div>
    {{-- Modals for edit vendor  --}}
    <div class="modal fade" id="editInventoryModal" tabindex="-1" aria-labelledby="exampleModalLgLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalLgLabel">Update Product</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="responsedd">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    {{-- Modals for stock updation  --}}
    <div class="modal fade" id="updateStockModal" tabindex="-1" aria-labelledby="exampleModalLgLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalLgLabel">Add Product Quantity</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="stockTableContainer">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveStockChanges">Update Stock</button>
                </div>
            </div>
        </div>
    </div>
    {{-- modal end --}}
@endsection


@section('js')
    <!-- jQuery Confirm JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery-confirm@3.3.4/js/jquery-confirm.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#file-exports').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'collection',
                    text: '<i class="fa fa-download"></i> Export',
                    buttons: [{
                            extend: 'copy',
                            text: '<i class="fa fa-copy"></i> Copy'
                        },
                        {
                            extend: 'csv',
                            text: '<i class="fa fa-file-csv"></i> CSV'
                        },
                        {
                            extend: 'excel',
                            text: '<i class="fa fa-file-excel"></i> Excel'
                        },
                        {
                            extend: 'pdf',
                            text: '<i class="fa fa-file-pdf"></i> PDF'
                        }
                    ]
                }],
                language: {
                    searchPlaceholder: 'Search...',
                    sSearch: '',
                }
            });

            $(document).on('click', '.add-product', function() {
                $("#addProductModal").modal('show');
            });

            $('#addProductForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    type: 'POST',
                    url: $(this).attr('action'),
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#addProductModal').modal('hide');
                        toastr.success(response.message);
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(xhr, status, error) {
                        var response = xhr.responseJSON;
                        if (response && response.errors) {
                            $.each(response.errors, function(key, value) {
                                toastr.error(value[0]);
                            });
                        }
                    }
                });
            });

            $(document).on('click', '.viewProductImageBtn', function() {
                let productId = $(this).attr('data-id');
                $.ajax({
                    url: "{{ route('inventory.show.image') }}",
                    type: 'POST',
                    data: {
                        productId: productId,
                    },
                    success: function(response) {
                        if (response.success == false) {
                            alert(response.message);
                        } else {
                            $('#viewImageModal img').attr('src', response.product_image);
                            $("#viewImageModal").modal('show');
                        };
                    },
                    error: function(xhr, status, error) {
                        // Handle error response
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('click', '.editBtn', function() {
                let productId = $(this).attr('data-id');
                $("#editInventoryModal").modal('show');
                $.ajax({
                    url: "{{ route('inventory.show.edit') }}",
                    type: 'POST',
                    data: {
                        productId: productId,
                    },
                    success: function(response) {
                        if (response.success == false) {
                            alert(response.message);
                        } else {
                            $("#responsedd").html(response);
                        };
                    },
                    error: function(xhr, status, error) {
                        // Handle error response
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('click', '.deleteBtn', function() {
                let productId = $(this).attr('data-id');

                $.confirm({
                    title: 'Confirm Deletion',
                    content: 'Are you sure you want to delete this product?',
                    buttons: {
                        confirm: {
                            text: 'Yes, Delete',
                            btnClass: 'btn-red',
                            action: function() {
                                $.ajax({
                                    url: "{{ route('inventory.delete') }}",
                                    type: 'POST',
                                    data: {
                                        productId: productId,
                                        _token: '{{ csrf_token() }}' // add CSRF token manually if needed
                                    },
                                    success: function(response) {
                                        if (response.success == false) {
                                            $.alert({
                                                title: 'Error',
                                                content: response.message,
                                                type: 'red'
                                            });
                                        } else {
                                            toastr.success(response.message);
                                            setTimeout(function() {
                                                window.location.reload();
                                            }, 1000);
                                        }
                                    },
                                    error: function(xhr, status, error) {
                                        console.error(xhr.responseText);
                                        $.alert({
                                            title: 'Ajax Error',
                                            content: 'Something went wrong while deleting.',
                                            type: 'red'
                                        });
                                    }
                                });
                            }
                        },
                        cancel: function() {
                            // Do nothing on cancel
                        }
                    }
                });
            });

            $(document).on('click', '.update-stock', function() {
                // Open the modal and fetch the stock table
                $("#updateStockModal").modal('show');
                $.ajax({
                    url: "{{ route('inventory.fetch.stock') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}' // Add CSRF token
                    },
                    success: function(response) {
                        $('#stockTableContainer').html(
                            response); // Load the table into the modal
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        toastr.error('Failed to load stock data.');
                    }
                });
            });

            $(document).on('click', '#saveStockChanges', function() {
                // Collect all stock data from the table
                let stockData = [];
                $('#stockTableContainer').find('tr').each(function() {
                    let quantity = $(this).find('.update-quantity').val();
                    let id = $(this).find('input[type="hidden"]').val();
                    if (id && quantity) {
                        stockData.push({
                            id: id,
                            quantity: parseInt(quantity)
                        });
                    }
                });

                // Send the stock data to the backend
                $.ajax({
                    url: "{{ route('inventory.update.stock') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        stock: stockData
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#updateStockModal').modal('hide');
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            toastr.error('Failed to update stock.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        toastr.error('An error occurred while updating stock.');
                    }
                });
            });
        });
    </script>
@endsection
