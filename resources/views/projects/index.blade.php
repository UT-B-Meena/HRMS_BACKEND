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
    <div class="container mt-5">
        <h2>Projects</h2>
        <button class="btn btn-success mb-2" data-toggle="modal" data-target="#createProjectModal">Add Project</button>
        
        <table id="projects-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Product</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be populated by DataTables -->
            </tbody>
        </table>

        <!-- Create Project Modal -->
        <div class="modal fade" id="createProjectModal" tabindex="-1" role="dialog" aria-labelledby="createProjectModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createProjectModalLabel">Create Project</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="createProjectForm">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="name">Project Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="product_id">Product ID</label>
                                <input type="text" class="form-control" name="product_id" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Project</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Project Modal -->
        <div class="modal fade" id="editProjectModal" tabindex="-1" role="dialog" aria-labelledby="editProjectModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProjectModalLabel">Edit Project</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <!-- form -->
                    <form id="editProjectForm">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="id">
                            <div class="form-group">
                                <label for="name">Project Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="product_id">Product ID</label>
                                <input type="text" class="form-control" name="product_id" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update Project</button>
                        </div>
                    </form>
                </div>
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
            // Initialize DataTable
            $('#projects-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("projects.index") }}',
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'product.name', name: 'product.name' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            // Create Project AJAX Request
            $('#createProjectForm').on('submit', function(e) {
                e.preventDefault();
                $.post('{{ route("projects.store") }}', $(this).serialize(), function(data) {
                    alert(data.success);
                    $('#createProjectModal').modal('hide');
                    $('#projects-table').DataTable().ajax.reload();
                });
            });

            // Edit Project
            $(document).on('click', '.edit-btn', function() {
                var id = $(this).data('id');
                $.get('/projects/' + id + '/edit', function(data) {
                    $('#editProjectModal input[name="name"]').val(data.name);
                    $('#editProjectModal input[name="product_id"]').val(data.product_id);
                    $('#editProjectModal input[name="id"]').val(data.id);
                    $('#editProjectModal').modal('show');
                });
            });

            // Update Project AJAX Request
            $('#editProjectForm').on('submit', function(e) {
                e.preventDefault();
                var id = $(this).find('input[name="id"]').val();
                $.ajax({
                    url: '/projects/' + id,
                    type: 'PUT',
                    data: $(this).serialize(),
                    success: function(data) {
                        alert(data.success);
                        $('#editProjectModal').modal('hide');
                        $('#projects-table').DataTable().ajax.reload();
                    }
                });
            });

            // Delete Project
            $(document).on('click', '.delete-btn', function() {
                var id = $(this).data('id');
                if (confirm('Are you sure you want to delete this project?')) {
                    $.ajax({
                        url: '/projects/' + id,
                        type: 'DELETE',
                        success: function(result) {
                            alert(result.success);
                            $('#projects-table').DataTable().ajax.reload();
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
