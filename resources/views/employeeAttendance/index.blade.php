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
    <!-- Filter Tabs -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <button id="filter-present" class="btn btn-primary me-2">Present</button>
            <button id="filter-absent" class="btn btn-secondary">Absent</button>
        </div>
        <div class="d-flex">
            <input type="date" class="form-control me-2" placeholder="Select Date" value="" id="dateInput">
            <input type="text" class="form-control" placeholder="Search..." id="searchValue">
        </div>
    </div>

    <!-- DataTable -->
    <table class="table  table-striped" id="attendance-table">
        <thead>
            <tr>
                <th><input type="checkbox" id="select-all"></th>
                <th>Sl No</th>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Status</th>
            </tr>
        </thead>
    </table>
    <div class="d-flex justify-content-end mb-3">
    <button id="mark-attendance" class="btn btn-danger">Absent</button>
</div>
<!-- Attendance Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog" aria-labelledby="attendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceModalLabel">Mark Attendance</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="attendanceType">Attendance Type:</label>
                    <div>
                        <input type="radio" name="attendanceType" value="1" id="fullDay" checked>
                        <label for="fullDay">Full Day</label>
                    </div>
                    <div>
                        <input type="radio" name="attendanceType" value="2" id="halfDay">
                        <label for="halfDay">Half Day</label>
                    </div>
                </div>
                <div id="halfDayOptions" style="display: none;">
                    <label for="halfDaySelect">Select Half Day:</label>
                    <div>
                        <input type="radio" name="halfDaySelect" value="1" id="firstHalf">
                        <label for="firstHalf">First Half</label>
                    </div>
                    <div>
                        <input type="radio" name="halfDaySelect" value="2" id="secondHalf">
                        <label for="secondHalf">Second Half</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" id="submitAttendance" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </div>
</div>

</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('dateInput').value = today;
    $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    let statusFilter = 'Present';
    let dateFilter = today;
    let searchValue = '';

    // Initialize DataTable
    const table = $('#attendance-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('employeeAttendance.index') }}", 
            data: function(d) {
                d.status = statusFilter;
                d.date=dateFilter;
                d.searchVal=searchValue; 
            }
        },
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'employee_id', name: 'employee_id' },
            { data: 'name', name: 'name' },
            { data: 'status', name: 'status', render: function(data) {
                const labelClass = data === 'Present' ? 'badge bg-success' : 'badge bg-danger';
                return `<span class="${labelClass}">${data}</span>`;
            }},
        ],
        order: [[1, 'asc']]
    });

    $('#filter-present').click(function() {
        statusFilter = 'Present';
        $('#filter-present').addClass('btn-primary').removeClass('btn-secondary');
        $('#filter-absent').addClass('btn-secondary').removeClass('btn-primary');
        $('#mark-attendance').text('Absent'); 
        table.ajax.reload();
    });

    $('#filter-absent').click(function() {
        statusFilter = 'Absent';
        $('#filter-absent').addClass('btn-primary').removeClass('btn-secondary');
        $('#filter-present').addClass('btn-secondary').removeClass('btn-primary');
        $('#mark-attendance').text('Present'); // Update button text
        table.ajax.reload();
    });
    $("#dateInput").change(function(){
        dateFilter=$(this).val();
        table.ajax.reload();
    })
    $("#searchValue").on('input',function(){
        searchValue=$(this).val();
        table.ajax.reload();
    })

    // Select/Deselect all checkboxes
    $('#select-all').on('click', function() {
        const isChecked = this.checked;
        $('input[type="checkbox"]').prop('checked', isChecked);
    });
    $(document).ready(function() {
    $('#mark-attendance').click(function() {
        const selectedRows = table.rows().nodes().filter(function(row) {
            return $(row).find('input[type="checkbox"]').is(':checked');
        });

        if (selectedRows.length === 0) {
            alert('Please select at least one employee.');
            return;
        }

        $('#attendanceModal').modal('show'); // Show the modal
        $('input[name="attendanceType"]').prop('checked', false);
        $('input[name="halfDaySelect"]').prop('checked', false);
        $('#halfDayOptions').hide();
        $('#submitAttendance').off('click').on('click', function() {
            const attendanceType = $('input[name="attendanceType"]:checked').val();
            const halfDay = attendanceType === '2' ? $('input[name="halfDaySelect"]:checked').val() : null;
   
            const ids = [];
            $(selectedRows).each(function() {
                const rowData = table.row(this).data();
                ids.push(rowData.id); // Assuming employee_id is the identifier
            });

            $.ajax({
                url: "{{ route('employeeAttendance.store') }}", // Update with your route for updating status
                type: 'POST',
                data: { ids: ids, date:dateFilter,statusFilter:statusFilter==="Present"?"Absent":"Present", attendanceType: attendanceType, halfDay: halfDay  },
                success: function(response) {
                    table.ajax.reload();
                    $('#attendanceModal').modal('hide'); // Hide the modal
                    alert('Status updated successfully.');
                },
                error: function(xhr) {
                    alert('An error occurred while updating status.');
                }
            });
        });
    });

    // Show/Hide Half Day options based on selection
    $('input[name="attendanceType"]').on('change', function() {
        if ($(this).val() === '2') {
            $('#halfDayOptions').show();
        } else {
            $('#halfDayOptions').hide();
        }
    });
});

});
</script>


    <!-- jQuery -->

    
</body>
</html>
