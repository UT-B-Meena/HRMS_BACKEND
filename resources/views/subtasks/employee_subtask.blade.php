<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</head>
<body>
    <style>
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .table-container {
            overflow-x: auto;
        }
        .task-detail {
            background-color: #343a40;
            color: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .countdown {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
        }
    </style>
    
    <div class="container mt-5">
        <h1>Task Management</h1>

        <!-- Task Detail Section -->
        <div class="task-detail d-none" id="taskDetail">
            <h4>Ongoing Task Details</h4>
            <p><strong>Product:</strong> <span id="detailProduct"></span></p>
            <p><strong>Project:</strong> <span id="detailProject"></span></p>
            <p><strong>Sub Task:</strong> <span id="detailSubtask"></span></p>
            <p><strong>Description:</strong> <span id="detailDescription"></span></p>
            <p><strong>Priority:</strong> <span id="detailPriority"></span></p>
            <p><strong>Est. Duration:</strong> <span id="detailDuration"></span></p>
            <p><strong>Time Left:</strong> <span id="countdownTimer" class="countdown">--:--:--</span></p>
            <button class="btn btn-warning" onclick="pauseTimer()">Pause</button>
            <button class="btn btn-success" onclick="endTask()">End</button>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="ongoing-tab" data-toggle="tab" href="#ongoing" role="tab" aria-controls="ongoing" aria-selected="true">Ongoing Task</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="closed-tab" data-toggle="tab" href="#closed" role="tab" aria-controls="closed" aria-selected="false">Closed Task</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="ongoing" role="tabpanel" aria-labelledby="ongoing-tab">
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
                                <button class="btn btn-primary start-task-btn" 
                                        data-product="{{ $subtask->product->name ?? 'N/A' }}" 
                                        data-project="{{ $subtask->project->name ?? 'N/A' }}"
                                        data-subtask="{{ $subtask->name ?? 'N/A' }}"
                                        data-description="{{ $subtask->description }}"
                                        data-priority="{{ $subtask->priority }}"
                                        data-duration="02:30:00">Start</button>
                            </div>
                        </div>
                        @endforeach
                        @else
                            <p>No To-Do tasks available.</p>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Reopen</h5>
                                <p class="card-text">Description of Task 1.</p>
                                <button class="btn btn-primary start-task-btn" 
                                        data-product="Lootrix" 
                                        data-project="Lootrix Website"
                                        data-subtask="UI Correction"
                                        data-description="Lorem ipsum dolor sit amet."
                                        data-priority="High"
                                        data-duration="02:30:00">Start</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Closed Task Tab -->
            <div class="tab-pane fade" id="closed" role="tabpanel" aria-labelledby="closed-tab">
                <div class="filters">
                    <div class="form-group">
                        <label for="globalSearch">Search</label>
                        <input type="text" id="globalSearch" class="form-control" placeholder="Search all fields">
                    </div>
                </div>
                <table class="table table-bordered mt-3" id="closedTasksTable">
                    <thead>
                        <tr>
                            <th>Sl. No</th>
                            <th>Product</th>
                            <th>Project</th>
                            <th>Task</th>
                            <th>Subtask</th>
                            <th>Estimated Time</th>
                            <th>Time Taken</th>
                            <th>Rating</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // DataTable initialization
            var table = $('#closedTasksTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: "{{ route('tasks.closed') }}",
                    data: function(d) {
                        d.search = $('#globalSearch').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'id' },
                    { data: 'product', name: 'product.name' },
                    { data: 'project', name: 'project.name' },
                    { data: 'task', name: 'task.name' },
                    { data: 'subtask', name: 'name' },
                    { data: 'estimated_time', name: 'estimated_hours' },
                    { data: 'time_taken', name: 'total_hours_worked' },
                    { data: 'rating', name: 'rating' }
                ]
            });

            $('#globalSearch').on('keyup change', function() {
                table.ajax.reload();
            });

            // Event listener for Start buttons
            $('.start-task-btn').on('click', function() {
                $('#taskDetail').removeClass('d-none');

                // Set task details
                $('#detailProduct').text($(this).data('product'));
                $('#detailProject').text($(this).data('project'));
                $('#detailSubtask').text($(this).data('subtask'));
                $('#detailDescription').text($(this).data('description'));
                $('#detailPriority').text($(this).data('priority'));
                $('#detailDuration').text($(this).data('duration'));

                // Start countdown timer
                startTimer($(this).data('duration'));
            });
        });

        let countdown;
        function startTimer(duration) {
            clearInterval(countdown);
            let [hours, minutes, seconds] = duration.split(':').map(Number);
            let totalSeconds = hours * 3600 + minutes * 60 + seconds;

            countdown = setInterval(() => {
                let h = Math.floor(totalSeconds / 3600);
                let m = Math.floor((totalSeconds % 3600) / 60);
                let s = totalSeconds % 60;

                $('#countdownTimer').text(`${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`);
                
                if (totalSeconds <= 0) {
                    clearInterval(countdown);
                    alert("Time's up!");
                }
                totalSeconds--;
            }, 1000);
        }

        function pauseTimer() {
            clearInterval(countdown);
        }

        function endTask() {
            clearInterval(countdown);
            alert("Task ended.");
            $('#taskDetail').addClass('d-none');
        }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
