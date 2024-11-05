@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Productivity List</h2>
    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
        @csrf
        <button type="submit" class="btn btn-danger">Logout</button>
    </form>
    <p>Logged in as: {{ Auth::user()->name }} (ID: {{ Auth::user()->employee_id }})</p>

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
    <div class="form-row">
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
    <table id="idle_employeesTable" class="table table-bordered">
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
</script>

@endsection
