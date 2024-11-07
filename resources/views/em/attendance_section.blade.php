@if ($resultss['attendance_list']->isEmpty())
    <div class="no-records">No records found</div>
@else
@foreach ($resultss['attendance_list'] as $team)
<div class="employee_section">
    <div class="name_section">
        <p>{{ $team['initials'] }}</p>
        <div class="ml-4">
            <span>{{ $team['employee_name'] }}</span>
            <span>SW-{{ $team['employee_id'] }}</span>
        </div>
    </div>
    <div class="status"style="color: {{ $team['status'] == 'Absent' ? 'red' : 'green' }}">
        {{ $team['status'] }}</div>
</div>
@endforeach
@endif
