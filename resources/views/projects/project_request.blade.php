<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
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
</head>
<body>
    <div class="container">
        <h1>Project Requests</h1>
        
        <!-- Filters Section -->
        <div class="filters">
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

            <div class="form-group">
                <label for="globalSearch">Search</label>
                <input type="text" id="globalSearch" class="form-control" placeholder="Search all fields">
            </div>
        </div>

        <!-- Table Section -->
        <div class="table-container">
            <table class="table table-bordered" id="project-table">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Project Name</th>
                        <th>Subtask</th>
                        <th>Assignee</th>
                        <th>Team</th>
                        <th>Date</th>
                        <th>Assigned By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                       
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
   
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> <!-- DataTables CSS --> <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css"> <!-- DataTables JS --> <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            
        var table =  $('#project-table').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ajax: {
                url: "{{ route('project_request') }}",
                data: function(d) {
                    d.project_id = $('#projectFilter').val();
                    d.user_id = $('#employeeFilter').val();
                    d.date = $('#dateFilter').val();
                    d.search = $('#globalSearch').val();
                }
            },
        columns: [
            { data: 'DT_RowIndex', name: 'id' }, 
            { data: 'project_name', name: 'project_name' },
            { data: 'subtask_name', name: 'name' }, 
            { data: 'assignee', name: 'assignee' },
            { data: 'team_name', name: 'team_name' },
            { data: 'date', name: 'date' },
            { data: 'assigned_by', name: 'assigned_by' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $('#projectFilter, #employeeFilter, #dateFilter').change(function() {
        table.ajax.reload();
    });

    $('#globalSearch').on('keyup change', function() {
        table.ajax.reload(); 
    });

    $(document).on('click', '.edit-button', function() {
    var subtaskId = $(this).data('id'); 

    $.ajax({
       url: "{{ route('projectRequest.data', ':id') }}".replace(':id', subtaskId),
        method: 'GET',
        success: function(data) {
           
            if (data.message) {
                alert(data.message); 
            } else {
               
                $('#subtaskId').val(data.id);
            $('#subtaskName').val(data.name);
            $('#projectName').val(data.project.project_name);
            $('#assignedTo').val(data.user.assignee);
            $('#assignedBy').val(data.assigned_user.assigned_by);
            $('#estimatedHours').val(data.estimated_hours);
            $('#totalHoursWorked').val(data.total_hours_worked);
            $('#comment').val(data.command);
            $('#remark').val(data.remark);
            $('#rating').val(data.rating);
            
            
            $('#updateProjectRequestModal').modal('show');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error fetching subtask data:', textStatus, errorThrown);
            alert('An error occurred while fetching data.');
        }
    });

    $('#updateProjectRequestModal').modal({
    show: false
});
});

});
    </script>
</body>
</html>
