<form id="editProductForm" method="POST" action="{{ route('inventory.update') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="product_id" value="{{ $product->_id }}">

    <div class="mb-3">
        <label for="product_name" class="form-label">Product Name</label>
        <input type="text" class="form-control" id="product_name" name="product_name"
            value="{{ $product->product_name }}" required>
    </div>

    <div class="mb-3">
        <label for="selling_price" class="form-label">Selling Price</label>
        <input type="number" class="form-control" id="selling_price" name="selling_price"
            value="{{ $product->selling_price }}" required>
    </div>

    <div class="mb-3">
        <label for="product_imagett" class="form-label">Product Image</label>
        <div class="mb-2">
            <img id="imagePreview" src="{{ $product->product_image }}" alt="Product Image"
                style="width: 150px; height: 150px; object-fit: cover;">
        </div>
        <input type="file" class="form-control" id="product_imagett" name="product_image" accept="image/*">
    </div>

    <button type="submit" class="btn btn-primary">Update Product</button>
</form>

<script>
    $(document).ready(function() {
        // Image preview
        $('#product_imagett').on('change', function() {
            const file = this.files[0];

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            }
        });

        // Form submit via AJAX
        $('#editProductForm').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#editInventoryModal').modal('hide');
                    toastr.success(response.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    var response = xhr.responseJSON;
                    if (response && response.errors) {
                        $.each(response.errors, function(key, value) {
                            toastr.error(value[0]);
                        });
                    }
                }
            });
        });
    });
</script>
