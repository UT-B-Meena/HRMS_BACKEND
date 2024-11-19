<div class="row mt-3">
    <!-- Sample Task Card -->
    @php
        $disabled = $activeTask ? 'disabled' : '';
    @endphp
    <div class="col-md-4">
        @if (isset($groupedSubtasks['To-Do']) && $groupedSubtasks['To-Do']->isNotEmpty())
            @foreach ($groupedSubtasks['To-Do'] as $subtaskData)
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">To-Do</h5>
                        <p class="card-text"><strong>Product:</strong>{{ $subtaskData['subtask']->product->name ?? 'N/A' }}</p>
                        <p class="card-text"><strong>Sub-Task:</strong> {{ $subtaskData['subtask']->name ?? 'N/A' }}</p>
                        <p class="card-text"><strong>Priority:</strong> {{ $subtaskData['subtask']->priority }}</p>
                        <button class="btn btn-primary start-task-btn" data-product="{{ $subtaskData['subtask']->product->name ?? 'N/A' }}" {{$disabled}}
                            data-project="{{ $subtaskData['subtask']->project->name ?? 'N/A' }}" data-subtask="{{ $subtaskData['subtask']->name ?? 'N/A' }}" data-action="start"
                            data-description="{{ $subtaskData['subtask']->description }}" data-priority="{{ $subtaskData['subtask']->priority }}" data-subtask-id="{{ $subtaskData['subtask']->id ?? 'N/A' }}"
                            data-duration="{{ $subtaskData['time_left'] }}">Start</button>
                    </div>
                </div>
            @endforeach
        @else
            <p>No To-Do tasks available.</p>
        @endif

    </div>
    <div class="col-md-4">
        @if (isset($groupedSubtasks['Reopen']) && $groupedSubtasks['Reopen']->isNotEmpty())
            @foreach ($groupedSubtasks['Reopen'] as $subtaskData)
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Reopen</h5>
                        <p class="card-text"><strong>Product:</strong>{{ $subtaskData['subtask']->product->name ?? 'N/A' }}</p>
                        <p class="card-text"><strong>Sub-Task:</strong> {{ $subtaskData['subtask']->name ?? 'N/A' }}</p>
                        <p class="card-text"><strong>Priority:</strong> {{ $subtaskData['subtask']->priority }}</p>
                        <p class="card-text"><strong>Time Left:</strong> {{ $subtaskData['time_left'] }}</p>
                        <button class="btn btn-primary start-task-btn" data-product="{{ $subtaskData['subtask']->product->name ?? 'N/A' }}" {{$disabled}}
                            data-project="{{ $subtaskData['subtask']->project->name ?? 'N/A' }}" data-subtask="{{ $subtaskData['subtask']->name ?? 'N/A' }}"
                            data-description="{{ $subtaskData['subtask']->description }}" data-priority="{{ $subtaskData['subtask']->priority }}" data-action="pause"
                            data-duration="{{ $subtaskData['time_left'] }}">Pause</button>
                    </div>
                </div>
            @endforeach
        @else
            <p>No Reopen tasks available.</p>
        @endif
    </div>
    
    <div class="col-md-4">
        @if (isset($groupedSubtasks['Pending-Approval']) && $groupedSubtasks['Pending-Approval']->isNotEmpty())
            @foreach ($groupedSubtasks['Pending-Approval'] as $subtaskData)
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Pending-Approval</h5>
                        <p class="card-text"><strong>Product:</strong>{{ $subtaskData['subtask']->product->name ?? 'N/A' }}</p>
                        <p class="card-text"><strong>Sub-Task:</strong> {{ $subtaskData['subtask']->name ?? 'N/A' }}</p>
                        <p class="card-text"><strong>Priority:</strong> {{ $subtaskData['subtask']->priority }}</p>
                        <p class="card-text"><strong>Time Left:</strong> {{ $subtaskData['time_left'] }}</p>
                        <button class="btn btn-success" disabled>Closed</button>
                    </div>
                </div>
            @endforeach
        @else
            <p>No Pending-Approval tasks available.</p>
        @endif
    </div>
    
</div>