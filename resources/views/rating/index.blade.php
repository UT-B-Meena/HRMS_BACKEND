<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rating</title>
    
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
    <h2>Rating</h2>
    
    <div class="row mb-3">
      <div class="col-md-6">
            <select class="form-control" id="team_id">
                <option value="all">All Teams</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <input type="text" id="searchBar" class="form-control" placeholder="Search by Employee Name or ID">
        </div>
        
    </div>
    
    <table class="table table-bordered" id="ratingTable">
        <thead>
            <tr>
                <th>S.No</th>
                <th>Employee Id</th>
                <th>Employee Name</th>
                <th>Team</th>
                <th>Average Rating</th>
                <th>Month Rating</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>
</div>


<div class="modal fade" id="ratingModel" tabindex="-1" role="dialog" aria-labelledby="ratingModelLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="ratingForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="ratingModelLabel">Add Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="user_id" name="user_id">
                    <div class="form-group">
                        <label for="name">Employee Name</label>
                        <input type="text" class="form-control" id="empName" name="empName" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Monthly Rating</label>
                        <input type="number" class="form-control" id="ratingValue" name="ratingValue" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
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
        let teamId=$("#team_id").val();
        const table = $('#ratingTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
            url: "{{ route('rating.index') }}", 
                data: function(d) {
                    d.teamId = teamId;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'user.employee_id', name: 'user.employee_id' },
                { data: 'user.name', name: 'user.name' },
                { data: 'user.team.name', name: 'user.team.name' },
                { data: 'average', name: 'average' },
                { data: 'rating', name: 'rating' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ]   
        });
        $("#team_id").change(function(){
            teamId=$(this).val();
            table.ajax.reload();
        })

        $('#createNewProduct').click(function() {
            $('#ratingForm')[0].reset();
            $('#user_id').val('');
            $('#ratingModel').modal('show');
        });

        $('#ratingForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('rating.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    $('#ratingModel').modal('hide');
                    table.ajax.reload();
                    alert(response.success);
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.responseJSON.message);
                }
            });
        });
        $('#ratingTable').on('click', '.edit-btn', function() {
            let userId = $(this).data('user-id'); 
            let empName = $(this).data('emp-name'); 
            let monthRate = $(this).data('month-rate'); 
            $('#user_id').val(userId);
            $('#empName').val(empName);
            $('#ratingValue').val(monthRate);
    
            $('#ratingModelLabel').text('Edit Rating');
            $('#ratingModel').modal('show');
          
    });


        
    });
    </script>
</body>
</html>
