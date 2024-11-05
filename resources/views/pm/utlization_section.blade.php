
@if($groupedTeams->isEmpty())
    <div class="no-records">No records found</div>
@else
@foreach ($groupedTeams as $team)
<div class="team_based">
    <p>
    <div class="team_name" data-toggle="collapse" data-target="#collapseExample{{ $team['team_id'] }}"
        role="button" aria-expanded="false" aria-controls="collapseExample{{ $team['team_id'] }}">
        {{ $team['team_name'] ?? 'N/A' }}
    </div>
    </p>
    <div class="collapse" id="collapseExample{{ $team['team_id'] }}">
        <div class="card card-body">
            <div class="streangth_section">
                <p>Total Strength: {{ $team['total_user_count'] ?? 0 }}</p>
                <p>Working: {{ $team['user_count'] ?? 0 }}</p>
            </div>

            <div class="employee_details">
                <div class="header">Working Employees</div>
                @foreach($team['users'] as $employee)
                    <div class="employee_section">
                        <div class="name_section">{{ $employee['name'] }}</div>
                        <div class="employee_id">SW-{{ $employee['user_id'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endforeach
@endif

