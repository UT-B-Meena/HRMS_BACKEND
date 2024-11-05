<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>product</title>
     <!-- Include your CSS file -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>
<body>

    <div class="container">
        <h1>Products</h1>
        <div>
            <label for="project-filter">Project:</label>
            <select id="project-filter">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </select>
        
            <label for="team-filter">Team:</label>
            <select id="team-filter">
                <option value="">All Team</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
        
            <label for="date-filter">Date:</label>
            <input type="date" id="date-filter" />

            <input type="hidden" id="product_id" value="{{$id}}"/>
            <p>Project Completion Rate: {{$rating}}%</p>
        </div>
        
        <table id="products-table" class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Employee Name</th>
                    <th>Team</th>
                    <th>Project</th>
                    <th>Subtask</th>
                    <th>Status</th>
                </tr>
            </thead>
        </table>
        
    </div>
    
</body>
</html>
 <!-- Include your JS file -->
   
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
 <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
 <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script>

$(document).ready(function() {
    var id = $("#product_id").val(); // Get the product ID from the input/select

    // Initialize the DataTable
    var table = $('#products-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('pm.products', ':id') }}'.replace(':id', id), // Ensure to pass the product id here
            data: function(d) {
                // Additional parameters for filtering
                d.project_id = $('#project-filter').val(); // Assuming there's an input/select for project
                d.team_id = $('#team-filter').val();       // Assuming there's an input/select for team
                d.date = $('#date-filter').val();          // Assuming there's an input for date
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'date', name: 'date' },
            { data: 'employee_name', name: 'employee_name' },
            { data: 'team_name', name: 'team_name' },
            { data: 'project_name', name: 'project_name' },
            { data: 'name', name: 'name' }, 
            { data: 'status', name: 'status' }
        ]
    });

    // Optional: Bind filter change events to reload DataTable
    $('#project-filter, #team-filter, #date-filter').change(function() {
        table.draw(); // Redraw the DataTable with new filters
    });
});

    
</script>