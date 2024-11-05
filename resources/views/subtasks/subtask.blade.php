<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>SubTask</title>
</head>

<body>
    <div class="container">
        <h2>Sub Task</h2>
        <br>

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal">
            Open modal
        </button>

        <!-- The Modal -->
        <div class="modal" id="myModal">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">Add Subtask</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body">
                        <form id="subtaskForm" method="post" action="{{ route('subtask.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-lg-6">
                                    <label>Product</label>
                                    <select class="form-control" name="product_id" id="product_id"
                                        onchange="getDropdownProject(this)">
                                        <option>select product</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label>Project</label>
                                    <select class="form-control" name="project_id" id="project_id"
                                        onchange="getDropdownTask(this)">
                                        <option>select Project</option>
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label>task</label>
                                    <select class="form-control" name="task_id" id="task_id">
                                        <option>select Task</option>
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label>subtask</label>
                                    <input class="form-control" type="text" name="name" id="name">
                                </div>
                                <div class="col-lg-12">
                                    <label>Description</label>
                                    <textarea class="form-control" name="description" id="description"></textarea>
                                </div>
                                <div class="col-lg-6">
                                    <label>Associated Team</label>
                                    <select class="form-control" name="team_id" id="team_id"
                                        onchange = "getdropdownTeamEmp(this)">
                                        <option>select Team</option>
                                        @foreach ($teams as $team)
                                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label>owner</label>
                                    <select class="form-control" name="assigned_user_id" id="assigned_user_id" readonly>
                                        <option>select owner</option>
                                        @foreach ($owners as $owner)
                                            <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                                        @endforeach
                                    </select>
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label>Assignee</label>
                                    <select class="form-control" name="user_id" id="user_id">
                                        <option>select Assignee</option>
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label>End Date</label>
                                    <input class="form-control" type="date" name="dead_line" id="dead_line">
                                </div>
                                <div class="col-lg-6">
                                    <label>Hours</label>
                                    <input class="form-control" type="number" name="hours" id="hours">
                                </div>
                                <div class="col-lg-6">
                                    <label>Minutes</label>
                                    <input class="form-control" type="number" name="minutes" id="minutes">
                                </div>
                                <div class="col-lg-12">
                                    <label>Priority</label>
                                    <select class="form-control" name="priority" id="priority">
                                        <option value="">Select Priority</option>
                                        <option value="low">Low</option>
                                        <option value="Medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                                <div class="col-lg-12">
                                    <br>
                                    <center><input class="btn btn-info" type="submit" value="add" id="submit">
                                    </center>
                                </div>
                            </div>
                        </form>
                    </div>


                </div>
            </div>
        </div>



        @if (Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
        @endif

        @if (Session::has('error'))
            <div class="alert alert-danger">{{ Session::get('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <table id="subTasksTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Product</th>
                    <th>Project</th>
                    <th>Task</th>
                    <th>Sub Task</th>
                    <th>Description</th>
                    <th>Associated Team</th>
                    <th>Owner</th>
                    <th>Assignee</th>
                    <th>Dead line</th>
                    <th>Estimated Hours</th>
                    <th>Priority</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table><pre>

        <div class="row">
            @foreach ($groupedSubtasks as $key=>$subtask)
            <div class="col-lg-3">
                <h4>{{ $key }}</h4>
                <div class="pending_div" style="border:1px solid black">
                    @foreach ($subtask as $pending)
                        <div> {{ $pending->product->name }}<br> Project: {{ $pending->project->name }}<br> Assinee: {{ $pending->user->name }}@if($key=='To-Do')
                            <br> Task: {{ $pending->task->name }}<br> Sub Task: {{ $pending->name }}<br> Est. Duration: {{ $pending->estimated_hours }} Hrs<br> Team: {{ $pending->team->name }}<br> Reporting Person: {{ $pending->assigned_user->name }}<br> Priority:{{ $pending->priority }}
                        @endif
                        <br> <button onclick="showSubTask{{ $pending->id }}" class="btn editSubTask"><i class="fa fa-edit"></i></button> @if($key=='To-Do')<button onclick="deleteSubTask{{ $pending->id }}" class="btn deleteSubTask"><i class="fa fa-trash"></i></button>@endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endforeach

        </div>

    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script>

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function showTask(taskId) {
            $.ajax({
                url: '{{ route('task.show', ':id') }}'.replace(':id', taskId),

                type: 'GET',

                success: function(response) {

                    $('#taskForm').attr('action', `{{ route('task.update', ':id') }}`.replace(':id', response
                        .task.id));
                    $('#taskForm').append('<input type="hidden" name="_method" value="PUT">');
                    $('#submit').val('update');

                    $('#product').val(response.task.product_id);
                    let projectSelect = $('#project');
                    projectSelect.empty();
                    projectSelect.append('<option value="">Select Project</option>');

                    response.projects.forEach(project => {
                        projectSelect.append(`<option value="${project.id}">${project.name}</option>`);
                    });

                    projectSelect.val(response.task.project_id);
                    $('#name').val(response.task.name);

                },

                error: function() {
                    alert('Error loading task. Please try again.');
                }
            });
        }

        function deleteTask(taskId) {
            if (confirm("Are you sure you want to delete this task?")) {
                $.ajax({
                    url: '{{ route('task.destroy', ':id') }}'.replace(':id',
                        taskId),
                    type: 'DELETE',
                    success: function(response) {
                        alert(response.message);

                        $('#tasksTable').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON.message ||
                            'An error occurred while deleting the task.');
                    }
                });
            }
        }


        function getDropdownProject(selectElement) {
            let productId = selectElement.value;

            if (productId) {
                $.ajax({
                    url: '{{ route('task.create') }}',
                    type: 'GET',
                    data: {
                        product_id: productId
                    },
                    success: function(response) {

                        $('#project_id').empty();
                        $('#project_id').append('<option value="">Select Project</option>');

                        if (Array.isArray(response) && response.length > 0) {
                            response.forEach(project => {
                                $('#project_id').append(
                                    `<option value="${project.id}">${project.name}</option>`
                                );
                            });
                        } else {
                            alert('No projects found for this product.');
                        }
                    },

                    error: function() {
                        alert('Error loading projects. Please try again.');
                    }
                });
            } else {
                $('#project').empty().append('<option value="">Select Project</option>');
            }
        }

        function getDropdownTask(selectElement) {

            let projectId = selectElement.value;

            if (projectId) {
                $.ajax({
                    url: '{{ route('subtask.create') }}',
                    type: 'GET',
                    data: {
                        project_id: projectId
                    },
                    success: function(response) {

                        $('#task_id').empty();
                        $('#task_id').append('<option value="">Select Task</option>');

                        if (Array.isArray(response) && response.length > 0) {
                            response.forEach(task => {
                                $('#task_id').append(
                                    `<option value="${task.id}">${task.name}</option>`
                                );
                            });
                        } else {
                            alert('No task found for this project.');
                        }
                    },

                    error: function() {
                        alert('Error loading task. Please try again.');
                    }
                });
            } else {
                $('#task').empty().append('<option value="">Select Task</option>');
            }
        }

        function getdropdownTeamEmp(selectElement) {
            let teamId = selectElement.value;

            if (teamId) {
                $.ajax({
                    url: '{{ route('team_emp') }}',
                    type: 'GET',
                    data: {
                        team_id: teamId
                    },
                    success: function(response) {

                        $('#user_id').empty();
                        $('#user_id').append('<option value="">Select Employee</option>');

                        if (Array.isArray(response) && response.length > 0) {
                            response.forEach(task => {
                                $('#user_id').append(
                                    `<option value="${task.id}">${task.name}</option>`
                                );
                            });
                        } else {
                            alert('Employee not found for this Team.');
                        }
                    },

                    error: function() {
                        alert('Error loading employee. Please try again.');
                    }
                });
            } else {
                $('#user_id').empty().append('<option value="">Select Employee</option>');
            }
        }
    </script>
</body>

</html>
