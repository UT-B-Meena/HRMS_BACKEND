<div class="row mt-3">
    <!-- Sample Task Card -->
    <div class="col-md-4">
        @if (isset($groupedSubtasks['To-Do']) && $groupedSubtasks['To-Do']->isNotEmpty())
    @foreach ($groupedSubtasks['To-Do'] as $subtask)
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">To-Do</h5>
                <p class="card-text"><strong>Product:</strong>{{ $subtask->product->name ?? 'N/A' }}</p>
                <p class="card-text"><strong>Sub-Task:</strong> {{ $subtask->name ?? 'N/A' }}</p>
                <p class="card-text"><strong>Priority:</strong> {{ $subtask->priority }}</p>
                <button class="btn btn-primary start-task-btn" data-product="{{ $subtask->product->name ?? 'N/A' }}"
                    data-project="{{ $subtask->project->name ?? 'N/A' }}" data-subtask="{{ $subtask->name ?? 'N/A' }}"
                    data-description="{{ $subtask->description }}" data-priority="{{ $subtask->priority }}"
                    data-duration="{{ timeDifference($subtask->estimated_hours, $subtask->total_hours_worked) }}">Start</button>
            </div>
        </div>
    @endforeach
@else
    <p>No To-Do tasks available.</p>
@endif

    </div>
    <div class="col-md-4">
        @if (isset($groupedSubtasks['To-Do']) && $groupedSubtasks['To-Do']->isNotEmpty())
        @foreach ($groupedSubtasks['To-Do'] as $subtask)
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">To-Do</h5>
                    <p class="card-text"><strong>Product:</strong>{{ $subtask->product->name ?? 'N/A' }}</p>
                    <p class="card-text"><strong>Sub-Task:</strong> {{ $subtask->name ?? 'N/A' }}</p>
                    <p class="card-text"><strong>Priority:</strong> {{ $subtask->priority }}</p>
                    <button class="btn btn-primary start-task-btn" data-product="{{ $subtask->product->name ?? 'N/A' }}"
                        data-project="{{ $subtask->project->name ?? 'N/A' }}" data-subtask="{{ $subtask->name ?? 'N/A' }}"
                        data-description="{{ $subtask->description }}" data-priority="{{ $subtask->priority }}"
                        data-duration="{{ timeDifference($subtask->estimated_hours, $subtask->total_hours_worked) }}">Start</button>
                </div>
            </div>
        @endforeach
    @else
        <p>No To-Do tasks available.</p>
    @endif
    
    </div>
    <div class="col-md-4">
        @if (isset($groupedSubtasks['To-Do']) && $groupedSubtasks['To-Do']->isNotEmpty())
    @foreach ($groupedSubtasks['To-Do'] as $subtask)
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">To-Do</h5>
                <p class="card-text"><strong>Product:</strong>{{ $subtask->product->name ?? 'N/A' }}</p>
                <p class="card-text"><strong>Sub-Task:</strong> {{ $subtask->name ?? 'N/A' }}</p>
                <p class="card-text"><strong>Priority:</strong> {{ $subtask->priority }}</p>
                <button class="btn btn-primary start-task-btn" data-product="{{ $subtask->product->name ?? 'N/A' }}"
                    data-project="{{ $subtask->project->name ?? 'N/A' }}" data-subtask="{{ $subtask->name ?? 'N/A' }}"
                    data-description="{{ $subtask->description }}" data-priority="{{ $subtask->priority }}"
                    data-duration="{{ timeDifference($subtask->estimated_hours, $subtask->total_hours_worked) }}">Start</button>
            </div>
        </div>
    @endforeach
@else
    <p>No To-Do tasks available.</p>
@endif

    </div>
</div>