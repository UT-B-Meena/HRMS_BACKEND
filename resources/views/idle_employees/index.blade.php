@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Idle Employee List</h2>
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

    <!-- Employee Table -->
    <table id="idle_employeesTable" class="table table-bordered">
        <thead>
            <tr>
                <th>Sl</th>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Team</th>
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
                url: "{{ route('idle_employees.index') }}",
                data: function(data) {
                    data.team_id = $('#teamFilter').val();

                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false },
                { data: 'employee_id', name: 'employee_id' },
                { data: 'name', name: 'name' },
                { data: 'team.name', name: 'team.name' },
            ]
        });

        $('#teamFilter').change(function() {
            table.ajax.reload();
        });
    });
</script>

@endsection
