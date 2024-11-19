<div class="task-detail {{ $class }}" id="taskDetail">
    <h4>Ongoing Task Details</h4>
    <p><strong>Product:</strong> <span id="detailProduct">{{ $activeTask->product->name ?? 'N/A' }}</span></p>
    <p><strong>Project:</strong> <span id="detailProject">{{ $activeTask->project->name ?? 'N/A' }}</span></p>
    <p><strong>Sub Task:</strong> <span id="detailSubtask">{{ $activeTask->name ?? 'N/A' }}</span></p>
    <p><strong>Description:</strong> <span id="detailDescription">{{ $activeTask->description ?? 'N/A' }}</span></p>
    <p><strong>Priority:</strong> <span id="detailPriority">{{ $activeTask->priority ?? 'N/A' }}</span></p>
    <p><strong>Est. Duration:</strong> <span id="detailDuration">{{ $activeTask->estimated_hours ?? 'N/A' }}</span></p>
    <p><strong>Time Left:</strong> <span id="countdownTimer" class="countdown">{{ $timeLeft ?? '--:--:--' }}</span></p>
    <input type="hidden" id="last_start_time" name="last_start_time" value="{{$lastStartTime}}">
    <input type="hidden" id="timeline_id" name="timeline_id" value="{{$timeline_id}}">
    <button class="btn btn-warning pause-task-btn" data-action="pause" data-subtask-id="{{ $activeTask->id ?? 'N/A' }}" onclick="pauseTimer()">Pause</button>
    <button class="btn btn-success end-task-btn" data-action="end" data-subtask-id="{{ $activeTask->id ?? 'N/A' }}">End</button>
</div>