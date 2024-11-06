@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Productivity List</h2>
    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
        @csrf
        <button type="submit" class="btn btn-danger">Logout</button>
    </form>
    <p>Logged in as: {{ Auth::user()->name }} (ID: {{ Auth::user()->employee_id }})</p>


    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home"
                type="button" role="tab" aria-controls="pills-home" aria-selected="true">Teamwise Split</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile"
                type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Individual Status</button>
        </li>

    </ul>
    <!-- tab pill home -->
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
            <!-- Dropdown filter for teams -->
            @if(Auth::user()->role_id != 3)
            <div class="form-group">
                <label for="team_id">Filter by Team:</label>
                <select name="team_id" id="teamFilter" class="form-control">
                    <option value="">All Teams</option>
                    @foreach ($teams as $team)
                    <option value="{{ $team->id }}" {{ request('team_id')==$team->id ? 'selected' : '' }}>
                        {{ $team->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif



            <!-- Month and Year Filters -->
            <div class="form-row d-flex align-items-center mb-4">
                <div class="form-group col-md-6">
                    <label for="monthFilter">Filter by Month:</label>
                    <select name="month" id="monthFilter" class="form-control">
                        <option value="">All Months</option>
                        @foreach (range(1, 12) as $month)
                        <option value="{{ $month }}">{{ \Carbon\Carbon::create()->month($month)->format('F') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="yearFilter">Filter by Year:</label>
                    <select name="year" id="yearFilter" class="form-control">
                        <option value="">All Years</option>
                        @foreach (range(date('Y') - 5, date('Y')) as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>


            <!-- Employee Table -->
            <table id="idle_employeesTable" class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>Sl</th>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Total Hours Given</th>
                        <th>Total Hours Taken</th>
                        <th>Exceeded Hours</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTables will populate this -->
                </tbody>
            </table>
        </div>

        <!-- tab pill profile -->
        <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
            <div class="form-group mb-4">
                <label for="teamFilter">Filter by Team:</label>
                <select name="IndividualTeamFilter" id="IndividualTeamFilter" class="form-control">
                    <option value="">All Teams</option>
                    @foreach ($teams as $team)
                    <option value="{{ $team->id }}" {{ request('team_id')==$team->id ? 'selected' : '' }}>
                        {{ $team->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Placeholder for dynamically loaded content -->
            <div class="individual-status"></div>
        </div>






    </div>

    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>

    <script type="text/javascript">
        $(function() {
        var table = $('#idle_employeesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('Productivity.index') }}",
                data: function(data) {
                    data.team_id = $('#teamFilter').val();
                    data.month = $('#monthFilter').val();
                    data.year = $('#yearFilter').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false },
                { data: 'user.employee_id', name: 'user.employee_id' },
                { data: 'user.name', name: 'user.name' },
                { data: 'total_estimated_hours', name: 'total_estimated_hours' },
                { data: 'total_hours_worked', name: 'total_hours_worked' },
                { data: 'total_extended_hours', name: 'total_extended_hours' },
            ]
        });

        $('#teamFilter, #monthFilter, #yearFilter').change(function() {
            table.ajax.reload();
        });
    });



    $(document).ready(function() {
        $('#pills-profile-tab').on('click', function(e) {
            e.preventDefault();
            IndividualEmployeeStatus();
        });

        $('#IndividualTeamFilter').change(function() {
            IndividualEmployeeStatus();
        });

        function IndividualEmployeeStatus() {
            var team_id = $('#IndividualTeamFilter').val() || '';
            $.ajax({
                url: "{{ route('productivity.individualStatus') }}",
                method: 'GET',
                data: { team_id: team_id },
                success: function(response) {
                    console.log(response);

                    if (response.status === 'success') {
                        let content = '';

                        // Loop through each employee data and create a card for each
                        $.each(response.data, function(index, employee) {
                            content += `
                                <div class="col">
                                    <div class="card h-100">
                                        <div class="card-header text-center">
                                            <h5 class="card-title mb-1">Employee Name: ${employee.employee_name}</h5>
                                            <small class="text-muted">Employee ID: ${employee.employee_id}</small>
                                        </div>
                                        <div class="card-body text-center">
                                            <p><strong>Assigned Subtasks:</strong> ${employee.assigned_subtasks}</p>
                                            <p><strong>Ongoing Subtasks:</strong> ${employee.ongoing_subtasks}</p>
                                            <p><strong>Completed Subtasks:</strong> ${employee.completed_subtasks}</p>
                                        </div>
                                        <div class="card-footer text-center">
                                            <small><strong>Working Days:</strong> ${employee.working_days || 'N/A'}</small><br>
                                            <small><strong>Leave Days:</strong> ${employee.leave_days || 'N/A'}</small>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        // Insert the generated content into the indididual-status div
                        $('.individual-status').html(`
                            <div class="row row-cols-1 row-cols-md-5 g-4">
                                ${content}
                            </div>
                        `);
                    } else {
                        $('.individual-status').html('<p>No data found.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    $('.individual-status').html('<p>Error loading content.</p>');
                }
            });
        }
    });


    </script>

    @endsection
