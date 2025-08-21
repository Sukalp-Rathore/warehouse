<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">

<table class="table table-bordered" id="datatable-basic">
    <thead>
        <tr>
            <th>S.No</th>
            <th>Product Name</th>
            <th>Current Quantity</th>
            <th>Update Quantity</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($products as $product)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $product->product_name }}</td>
                <td>{{ $product->quantity ?? 0 }}</td>
                <td>
                    <input type="number" class="form-control update-quantity" name="stock[{{ $loop->index }}][quantity]"
                        value="0" min="0">
                    <input type="hidden" name="stock[{{ $loop->index }}][id]" value="{{ $product->_id }}">
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<script src="assets/js/datatables.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", (event) => {
        $('#datatable-basic').DataTable({
            language: {
                searchPlaceholder: 'Search...',
                sSearch: '',
            },
            "pageLength": 10,
            // scrollX: true
        });
    });
</script>
