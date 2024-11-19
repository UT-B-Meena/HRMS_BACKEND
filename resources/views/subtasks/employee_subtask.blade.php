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
        @php
            $class = $activeTask ? '' : 'd-none';

        @endphp
        @include('subtasks.partials.active_task_section')


        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="ongoing-tab" data-toggle="tab" href="#ongoing" role="tab"
                    aria-controls="ongoing" aria-selected="true">Ongoing Task</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="closed-tab" data-toggle="tab" href="#closed" role="tab"
                    aria-controls="closed" aria-selected="false">Closed Task</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="ongoing" role="tabpanel" aria-labelledby="ongoing-tab">
                @include('subtasks.partials.ongoing_section')
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
    @include('subtasks.partials.end_task_modal')
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
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'id'
                    },
                    {
                        data: 'product',
                        name: 'product.name'
                    },
                    {
                        data: 'project',
                        name: 'project.name'
                    },
                    {
                        data: 'task',
                        name: 'task.name'
                    },
                    {
                        data: 'subtask',
                        name: 'name'
                    },
                    {
                        data: 'estimated_time',
                        name: 'estimated_hours'
                    },
                    {
                        data: 'time_taken',
                        name: 'total_hours_worked'
                    },
                    {
                        data: 'rating',
                        name: 'rating'
                    }
                ]
            });

            $('#globalSearch').on('keyup change', function() {
                table.ajax.reload();
            });
            $(document).on('click', '.start-task-btn, .pause-task-btn, .end-task-btn', function() {
                var action = $(this).data('action');

                var subtask_id = $(this).data('subtask-id');
                $('button.start-task-btn').attr('disabled', 'disabled');

                if (action == 'start') {

                    $('#detailDuration').text($(this).data('duration'));
                    $('#detailProduct').text($(this).data('product'));
                    $('#detailProject').text($(this).data('project'));
                    $('#detailSubtask').text($(this).data('subtask'));
                    $('#detailDescription').text($(this).data('description'));
                    $('#detailPriority').text($(this).data('priority'));

                    update_subtask_details(subtask_id, status = 1, active_status = 1, null);
                    startTimer($(this).data('duration'));
                    $('#taskDetail').removeClass('d-none');

                } else if (action == 'pause') {

                    clearInterval(countdown);

                    var last_start_time = $('#last_start_time').val();
                    var timeline_id = $('#timeline_id').val();
                    update_subtask_details(subtask_id, status = 1, active_status = 0, last_start_time,
                        timeline_id);
                    $('button.start-task-btn').removeAttr('disabled');
                    $('#taskDetail').addClass('d-none');
                } else {
                    $('#endTaskModal').modal('show');
                    $('#submitEndTask').attr('data-subtask-id', subtask_id);
                }


            });
            $(document).on('click', '#submitEndTask', function() {
                var last_start_time = $('#last_start_time').val();
                var timeline_id = $('#timeline_id').val();
                var subtask_id = $(this).data('subtask-id');
                var comment = $('#taskComment').val();

                update_subtask_details(subtask_id, status = 2, activeStatus = 0, last_start_time, timeline_id);
                $('#taskDetail').addClass('d-none');
                $('#endTaskModal').modal('hide');
                

            });

            function update_subtask_details(subtask_id, status, activeStatus, last_start_time = null, timeline_id =
                null) {
                $.ajax({
                    url: "{{ route('subtasks.update') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        subtask_id: subtask_id,
                        status: status,
                        active_status: activeStatus,
                        last_start_time: last_start_time,
                        timeline_id: timeline_id,
                        comment:$('#taskComment').val()
                    },
                    success: function(response) {

                        if (response.html) {
                            $('#ongoing').html(response.html);
                        }
                        if (response.timeline) {
                            $('#timeline_id').val(response.timeline.id);
                            $('#last_start_time').val(response.timeline.start_time);
                            $('.pause-task-btn').attr('data-subtask-id', response.timeline.subtask_id);
                            $('.end-task-btn').attr('data-subtask-id', response.timeline.subtask_id);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error updating status:", error);
                        alert("An error occurred. Please try again.");
                    }
                });
            }

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

                $('#countdownTimer').text(
                    `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`
                );

                if (totalSeconds <= 0) {
                    clearInterval(countdown);
                    $('span#countdownTimer').css('color', 'red');
                    alert("Time's up!");
                }
                totalSeconds--;
            }, 1000);
        }

        function pauseTimer() {
            clearInterval(countdown);
        }

        active_task();

        function active_task() {
            var time_left = $('span#countdownTimer').text();
            if (time_left === '00:00:00') {
                $('span#countdownTimer').css('color', 'red');
            } else {
                $('span#countdownTimer').css('color', '');
            }
            startTimer(time_left);
        }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>
