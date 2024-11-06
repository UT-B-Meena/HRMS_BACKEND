<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Project Management</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
    
    <style>
        /* Add any custom styles here */
    </style>
</head>
<body>
<div class="container">
    <h2>Products</h2>
    
    <button class="btn btn-success mb-3" id="createNewProduct">Add New Product</button>
    
    <table class="table table-bordered" id="productsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Create/Edit Products Modal -->
<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="productForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Add Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="productId" name="id">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
    
    <script>
   $(document).ready(function() {
         $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var table = $('#productsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("products.index") }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'status', name: 'status' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ]
        });

        $('#createNewProduct').click(function() {
            $('#productForm')[0].reset();
            $('#productId').val('');
            $('#productModalLabel').text('Add Product');
            $('#productModal').modal('show');
        });

        $('#productForm').on('submit', function(e) {
            e.preventDefault();
            const createUrl = "{{ route('products.store') }}"; // Store route for creating products
            const updateUrl = "{{ route('products.update', ':id') }}";
            let id = $('#productId').val();
            let url = id ? updateUrl.replace(':id', id) : createUrl; // Replace placeholder with the actual ID
            let method = id ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: $(this).serialize(),
                success: function(response) {
                    $('#productModal').modal('hide');
                    table.ajax.reload();
                    alert(response.success);
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.responseJSON.message);
                }
            });
        });

        $('#productsTable').on('click', '.edit-btn', function() {
            let id = $(this).data('id');
            $.get('/products/' + id + '/edit', function(data) {
                $('#productModalLabel').text('Edit Product');
                $('#productId').val(data.id);
                $('#name').val(data.name);
                $('#productModal').modal('show');
            });
        });

        $('#productsTable').on('click', '.delete-btn', function() {
            let id = $(this).data('id');
            if(confirm('Are you sure you want to delete this product?')) {
                $.ajax({
                    type: 'DELETE',
                    url: '/products/' + id,
                    success: function(response) {
                        table.ajax.reload();
                        alert(response.success);
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseJSON.message);
                    }
                });
            }
        });
    });
    </script>
</body>
</html>
