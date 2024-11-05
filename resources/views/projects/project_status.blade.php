<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Status</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</head>
<body>
    <style>
       
        h1 {
            text-align: center; /* Center the heading */
            margin-bottom: 20px; /* Space below the heading */
        }
        .filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .table-container {
            overflow-x: auto;
        }
    </style>
    <div class="container mt-5">
        <h2>Project Status</h2>
        
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" id="statusTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="todo-tab" data-status="0" href="#">To-Do</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="in-progress-tab" data-status="1" href="#">In Progress</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="completed-tab" data-status="3" href="#">Completed</a>
            </li>
        </ul>

        <div class="filters">
            <div class="form-group">
                <label for="productFilter">Product</label>
                <select id="productFilter" class="form-control" name="product_id">
                    <option value="">Select Product</option>
                    @foreach($products as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="projectFilter">Project</label>
                <select id="projectFilter" class="form-control" name="project_id">
                    <option value="">Select Project</option>
                    @foreach($projects as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label for="employeeFilter">Employee</label>
                <select id="employeeFilter" class="form-control" name="user_id">
                    <option value="">Select Employee</option>
                    @foreach($users as $user)
                        <option value="{{ $user['id'] }}">{{ $user['display'] }}</option>
                    @endforeach
                </select>
            </div>
            

            <div class="form-group">
                <label for="dateFilter">Date</label>
                <input type="date" id="dateFilter" class="form-control">
            </div>
        </div>

        <!-- Single Table -->
        <table class="table table-bordered mt-3" id="project_status">
            <thead>
                <tr>
                    <th>Sl. No</th>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Project</th>
                    <th>Subtask</th>
                    <th>Assignee</th>
                    <th>Estimated Time</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Task Duration</th>
                </tr>
            </thead>
            <tbody id="status-tbody">
            </tbody>
        </table>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css"> <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            let status = 0;
        
            var table = $('#project_status').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('project.status.data') }}",
                    data: function (d) {
                        d.status = status; 
                        d.product_id = $('#productFilter').val();
                        d.project_id = $('#projectFilter').val();
                        d.user_id = $('#employeeFilter').val();
                        d.date = $('#dateFilter').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'date', name: 'created_at' },
                    { data: 'product', name: 'product.name' },
                    { data: 'project', name: 'project.name' },
                    { data: 'subtask', name: 'subtask.name' },
                    { data: 'user', name: 'user.name' },
                    { data: 'estimated_hours', name: 'subtask.estimated_hours' },
                    { data: 'start_time', name: 'start_time' },
                    { data: 'end_time', name: 'end_time' },
                    { data: 'task_duration', name: 'subtask.total_hours_worked' }
                ]
            });

            $('#productFilter, #projectFilter, #employeeFilter, #dateFilter').change(function() {
              table.ajax.reload();
             });
        
            $('#statusTabs .nav-link').on('click', function(e) {
                e.preventDefault();
                $('#statusTabs .nav-link').removeClass('active');
                $(this).addClass('active');
        
                status = $(this).data('status'); 
                table.ajax.reload(); 
            });
        });

        </script>
        
</body>
</html>
