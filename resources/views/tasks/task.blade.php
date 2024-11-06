<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <title>Document</title>
</head>

<body>
    <div class="container">
        <h2>Task</h2>
        <br>
        <a href="#" class="btn btn-secondary">Add Task</a>
        <br>
        <br>
        <div class="card p-4">
            <form id="taskForm" method="post" action="{{ route('task.store') }}" onsubmit="return validateForm()">
                @csrf
                <div class="row">
                    <div class="col-lg-3">
                        <label>Product</label>
                        <select class="form-control" name="product_id" id="product"
                            onchange="getDropdownProject(this)" required>
                            <option value="">select product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label>Project</label>
                        <select class="form-control" name="project_id" id="project" disabled required>
                            <option value="">select Project</option>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label>task</label>
                        <input class="form-control" type="text" name="name" id="name" required>
                        <span class="text-danger" id="task_error"></span>
                    </div>
                    <div class="col-lg-3">
                        <br>
                        <input class="btn btn-success" type="submit" value="add" id="submit">
                    </div>
                </div>
            </form>
        </div>
        <br>
        @if (Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
        @endif

        @if (Session::has('error'))
            <div class="alert alert-danger">{{ Session::get('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <table id="tasksTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Product</th>
                    <th>Project</th>
                    <th>Task</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>

    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <script>
        /* function validateForm() {
            const nameField = document.getElementById('name').value;
            const namePattern = /^[A-Za-z\s]+$/;

            if (!namePattern.test(nameField)) {
                $("#task_error").text('Invalid task name');
                return false;
            }
            return true;
        } */

        $(document).ready(function() {
            $('#tasksTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('tasks.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'product',
                        name: 'product'
                    },
                    {
                        data: 'project',
                        name: 'project'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function showTask(taskId) {
            $.ajax({
                url: '{{ route('task.show', ':id') }}'.replace(':id', taskId),

                type: 'GET',

                success: function(response) {

                    $('#taskForm').attr('action', `{{ route('task.update', ':id') }}`.replace(':id', response
                        .task.id));
                    $('#taskForm').append('<input type="hidden" name="_method" value="PUT">');
                    $('#submit').val('update');

                    $('#product').val(response.task.product_id);
                    let projectSelect = $('#project');
                    projectSelect.empty();
                    projectSelect.append('<option value="">Select Project</option>');

                    response.projects.forEach(project => {
                        projectSelect.append(`<option value="${project.id}">${project.name}</option>`);
                    });

                    projectSelect.val(response.task.project_id);
                    $('#name').val(response.task.name);

                },

                error: function() {
                    alert('Error loading task. Please try again.');
                }
            });
        }

        function deleteTask(taskId) {
            if (confirm("Are you sure you want to delete this task?")) {
                $.ajax({
                    url: '{{ route('task.destroy', ':id') }}'.replace(':id',
                        taskId),
                    type: 'DELETE',
                    success: function(response) {
                        alert(response.message);

                        $('#tasksTable').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON.message ||
                            'An error occurred while deleting the task.');
                    }
                });
            }
        }


        function getDropdownProject(selectElement) {
            let productId = selectElement.value;

            if (productId) {
                $.ajax({
                    url: '{{ route('task.create') }}',
                    type: 'GET',
                    data: {
                        product_id: productId
                    },
                    success: function(response) {

                        $('#project_id').empty();

                        if (Array.isArray(response) && response.length > 0) {
                            $('#project_id').append('<option value="">Select Project</option>');
                            response.forEach(project => {
                                $('#project_id').append(
                                    `<option value="${project.id}">${project.name}</option>`
                                );
                            });
                            $('#project_id').prop('disabled', false);
                        } else {
                            $('#project_id').append('<option value="">No projects found for this product.</option>');
                            $('#project_id').prop('disabled', true);
                        }

                    },
                    error: function() {
                        alert('Error loading projects. Please try again.');
                    }
                });
            }
        }
    </script>
</body>

</html>
