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
    <style>
        .extended_hours_div {
            border: 1px solid red;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Sub Task</h2>
        <br>
        <div class="user-info">
            <p><strong>User ID:</strong> {{ Auth::user()->employee_id }}</p>
            <p><strong>Name:</strong> {{ Auth::user()->name }}</p> <!-- Assuming you have a name field -->
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger">Logout</button>
        </form>

        <div class="row mt-1">
            <div class="col-lg-7">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#SubtaskModal">
                    Add Subtask
                </button>
            </div>
            <div class="col-lg-5">
                <div class="row">
                    <div class="col-lg-3">
                        <button class="btn btn-danger d-none" id="clear_btn" onclick="clearFilter()">Clear</button>
                    </div>
                    <div class="col-lg-3">
                        <select class="form-control" name="search_team_id" id="search_team_id"
                            onchange = "subTaskFilter()">
                            <option value="">All Team</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <select class="form-control" name="search_priority" id="search_priority"
                            onchange="subTaskFilter()">
                            <option value="">Priority</option>
                            <option value="low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <input class="form-control" type="text" id="search_value" placeholder="search..." oninput="subTaskFilter()">
                    </div>
                </div>
            </div>
        </div>


        <div class="modal" id="SubtaskModal">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h4 class="modal-title"><span class="modal_title">Add</span> Subtask</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <form id="subtaskForm" method="post" action="{{ route('subtask.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-lg-6">
                                    <label>Product</label>
                                    <select class="form-control" name="product_id" id="product_id"
                                        onchange="getDropdownProject(this)">
                                        <option value="">select product</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger" id="error-product_id"></span>
                                </div>
                                <div class="col-lg-6">
                                    <label>Project</label>
                                    <select class="form-control" name="project_id" id="project_id"
                                        onchange="getDropdownTask(this)" disabled>
                                        <option value="">Select Project</option>
                                    </select>
                                    <span class="text-danger" id="error-project_id"></span>
                                </div>
                                <div class="col-lg-6">
                                    <label>task</label>
                                    <select class="form-control" name="task_id" id="task_id" disabled>
                                        <option value="">Select Task</option>
                                    </select>
                                    <span class="text-danger" id="error-task_id"></span>
                                </div>
                                <div class="col-lg-6">
                                    <label>subtask</label>
                                    <input class="form-control" type="text" name="name" id="name">
                                    <span class="text-danger" id="error-name"></span>
                                </div>
                                <div class="col-lg-12">
                                    <label>Description</label>
                                    <textarea class="form-control" name="description" id="description"></textarea>
                                    <span class="text-danger" id="error-description"></span>
                                </div>
                                <div class="col-lg-6">
                                    <label>Associated Team</label>
                                    <select class="form-control" name="team_id" id="team_id"
                                        onchange = "getdropdownTeamEmp(this)">
                                        <option value="">Select Team</option>
                                        @foreach ($teams as $team)
                                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger" id="error-team_id"></span>
                                </div>
                                <div class="col-lg-6">
                                    <label>owner</label>
                                    <select class="form-control" name="assigned_user_id" id="assigned_user_id"
                                        readonly>
                                        <option value="">select owner</option>
                                        @foreach ($owners as $owner)
                                            <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger" id="error-assigned_user_id"></span>
                                </div>
                                <div class="col-lg-6">
                                    <label>Assignee</label>
                                    <select class="form-control" name="user_id" id="user_id" disabled>
                                        <option value="">Select Assignee</option>
                                    </select>
                                    <span class="text-danger" id="error-user_id"></span>
                                </div>
                                <div class="col-lg-6">
                                    <label>End Date</label>
                                    <input class="form-control" type="date" name="dead_line" id="dead_line">
                                    <span class="text-danger" id="error-dead_line"></span>
                                </div>
                                <div class="col-lg-6">
                                    <label>Hours</label>
                                    <input class="form-control" type="number" name="hours" id="hours"
                                        min="0">
                                    <span class="text-danger" id="error-hours"></span>
                                </div>
                                <div class="col-lg-6">
                                    <label>Minutes</label>
                                    <input class="form-control" type="number" name="minutes" id="minutes"
                                        min="0" max="59">
                                    <span class="text-danger" id="error-minutes"></span>
                                </div>
                                <div class="col-lg-12">
                                    <label>Priority</label>
                                    <select class="form-control" name="priority" id="priority">
                                        <option value="">Select Priority</option>
                                        <option value="low">Low</option>
                                        <option value="Medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                    <span class="text-danger" id="error-priority"></span>
                                    <input type="hidden" id="id">
                                </div>
                                <div class="col-lg-12">
                                    <br>
                                    <center><input class="btn btn-info add_data" type="button" value="Add"
                                            id="submit">
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


        <div class="row mt-2" id="subtask_div">
            @foreach ($groupedSubtasks as $key => $subtasks)
                <div class="col-lg-3  ">
                    <h4 class="p-4">{{ $key }} <span
                            class="badge bg-primary">{{ count($subtasks) }}</span></h4>
                    <div class="pending_div ">
                        @foreach ($subtasks as $subtask)
                            <div class="p-1 card m-1 @if ($subtask->extended_status == 1 && $subtask->status == 1) extended_hours_div @endif">
                                {{ $subtask->product->name }}<br>
                                Project: {{ $subtask->project->name }}<br>
                                Assinee: {{ $subtask->user->name }}
                                @if ($subtask->extended_status == 1 && $subtask->status == 1)
                                    <span class="text-danger">Time Extended </span>
                                @endif
                                @if ($subtask->status == 0)
                                    <br>
                                    Task: <br>
                                    Sub Task: {{ $subtask->name }}<br>
                                    Est. Duration: {{ $subtask->estimated_hours }} Hrs<br>
                                    Team: {{ $subtask->team->name }}<br>
                                    Reporting Person: {{ $subtask->assigned_user->name }}<br>
                                    Priority:{{ $subtask->priority }}
                                @endif
                                <br>
                                @if (Auth::user()->role_id == 2 && $subtask->reopen_status == 0)
                                    @if ($subtask->status == 0 || $subtask->status == 1)
                                        <button onclick="showSubTask({{ $subtask->id }}, {{ $subtask->status }})"
                                            class="btn editSubTask"><i class="fa fa-edit"></i></button>
                                        @endif @if ($subtask->status == 0)
                                            <button onclick="deleteSubTask({{ $subtask->id }})" class="btn"> <i
                                                    class="fa fa-trash"></i> </button>
                                        @endif
                                    @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

        </div>

    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script>
        function clearFilter(){
            $("#search_team_id, #search_priority, #search_value").val('');

            $("#clear_btn").addClass('d-none');
            subTaskFilter();
        }

        function subTaskFilter() {

            var team_id = $("#search_team_id").val();
            var priority = $("#search_priority").val();
            var search_value = $("#search_value").val();

            if(team_id || priority || search_value){
                $("#clear_btn").removeClass('d-none');
            }else{
                $("#clear_btn").addClass('d-none');
            }

            $.ajax({
                url: '{{ route('getSubtaskFilter') }}',
                type: 'GET',
                data: {
                    team_id: team_id,
                    priority: priority,
                    search_value: search_value
                },
                success: function(response) {
                    $('#subtask_div').empty().append(response);
                },

                error: function(xhr, status, error) {
                    console.error("Error fetching data:", error);
                }
            });
        }

        $("#submit").click(function() {
            $(".text-danger").text('');
            var formData = {
                product_id: $("#product_id").val(),
                project_id: $("#project_id").val(),
                task_id: $("#task_id").val(),
                name: $("#name").val(),
                description: $("#description").val(),
                team_id: $("#team_id").val(),
                assigned_user_id: $("#assigned_user_id").val(),
                user_id: $("#user_id").val(),
                dead_line: $("#dead_line").val(),
                hours: $("#hours").val(),
                minutes: $("#minutes").val(),
                priority: $("#priority").val(),
            };

            var id = $("#id").val();
            var url = ($(this).val() === 'Add') ? '{{ route('subtask.store') }}' :
                '{{ route('subtask.update', '') }}' + '/' + id;
            var method = ($(this).val() === 'Add') ? 'POST' : 'PUT';
            console.log(method + $(this).val());

            $.ajax({
                url: url,
                type: method,
                data: formData,
                success: function(response) {
                    alert(response.message);
                    window.location.href = '';
                },

                error: function(xhr) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;

                        for (var field in errors) {
                            $('#error-' + field).text(errors[field][0]);
                        }

                    } else {
                        alert('Error loading task. Please try again.');
                    }
                }
            });
        });

        $('#SubtaskModal').on('hidden.bs.modal', function() {
            $('#subtaskForm').find('#product_id, #name, #team_id, #assigned_user_id').prop('disabled', false);
            $('#subtaskForm').find('#project_id, #task_id, #user_id').prop('disabled', true);
            $('#submit').val('Add');
            $(".modal_title").text('Add');
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function showSubTask(id, status) {

            $.ajax({
                url: '{{ route('subtask.show', ':id') }}'.replace(':id', id),
                type: 'GET',
                success: function(response) {

                    if (status == 0) {
                        $('#project_id, #task_id, #user_id').prop('disabled', true);
                        $('#product_id, #name, #team_id').prop('disabled', false);
                    } else {
                        $('#product_id, #project_id, #task_id, #name, #team_id, #user_id')
                            .prop('disabled', true);
                    }

                    $('#product_id').val(response.subtask.product_id);
                    $("#id").val(id);

                    populateSelect($('#project_id'), response.projects, 'Select Project', response.subtask
                        .project_id);

                    populateSelect($('#task_id'), response.tasks, 'Select Task', response.subtask.task_id);

                    $('#name').val(response.subtask.name);
                    $('#description').val(response.subtask.description);
                    $('#team_id').val(response.subtask.team_id);
                    $('#assigned_user_id').val(response.subtask.assigned_user_id);

                    populateSelect($('#user_id'), response.users, 'Select Employee', response.subtask.user_id);
                    $('#dead_line').val(response.subtask.dead_line);

                    let estimated_hours = response.subtask.estimated_hours;
                    let [hours, minutes, seconds] = estimated_hours.split(':');

                    $('#hours').val(parseInt(hours, 10));
                    $('#minutes').val(parseInt(minutes, 10));
                    $('#priority').val(response.subtask.priority);

                    $('#submit').val('Update');
                    $(".modal_title").text('Edit');

                    $("#SubtaskModal").modal("show");

                },

                error: function() {
                    alert('Error loading task. Please try again.');
                }
            });
        }

        function populateSelect(selectElement, data, placeholder, selectedValue) {
            selectElement.empty().append(`<option value="">${placeholder}</option>`);
            data.forEach(item => {
                selectElement.append(`<option value="${item.id}">${item.name}</option>`);
            });
            selectElement.val(selectedValue);
        }

        function deleteSubTask(id) {
            $.ajax({
                url: '{{ route('subtask.destroy', ':id') }}'.replace(':id', id),
                type: 'DELETE',

                success: function(response) {
                    alert(response.message);
                    window.location.href = '';
                },
                error: function(xhr) {
                    alert('Please try again.');
                }
            });
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

                        if (Array.isArray(response) && response.length > 0) {
                            $('#project_id').append('<option value="">Select Project</option>');
                            response.forEach(project => {
                                $('#project_id').append(
                                    `<option value="${project.id}">${project.name}</option>`
                                );
                            });
                            $('#project_id').prop('disabled', false);
                        } else {
                            $('#project_id').html(
                                '<option value="">No projects found for this product.</option>').prop(
                                'disabled', true);
                        }
                    },

                    error: function() {
                        alert('Error loading projects. Please try again.');
                    }
                });
            } else {
                $('#project_id').html('<option value="">Select Project</option>').prop('disabled', true);
                $('#task_id').html('<option value="">Select Task</option>').prop('disabled', true);
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

                        if (Array.isArray(response) && response.length > 0) {
                            $('#task_id').append('<option value="">Select Task</option>');
                            response.forEach(task => {
                                $('#task_id').append(
                                    `<option value="${task.id}">${task.name}</option>`
                                );
                            });
                            $("#task_id").prop('disabled', false);
                        } else {
                            $('#task_id').html('<option value="">No tasks found for this project.</option>')
                                .prop('disabled', true);
                        }
                    },

                    error: function() {
                        alert('Error loading task. Please try again.');
                    }
                });
            } else {
                $('#task_id').html('<option value="">Select Task</option>').prop('disabled', true);
            }
        }

        function getdropdownTeamEmp(selectElement) {
            let teamId = selectElement.value;
            $('#user_id').empty();
            if (teamId) {
                $.ajax({
                    url: '{{ route('team_emp') }}',
                    type: 'GET',
                    data: {
                        team_id: teamId
                    },
                    success: function(response) {

                        if (Array.isArray(response) && response.length > 0) {
                            $('#user_id').append('<option value="">Select Employee</option>');
                            response.forEach(task => {
                                $('#user_id').append(
                                    `<option value="${task.id}">${task.name}</option>`
                                );
                            });
                            $("#user_id").prop('disabled', false);
                        } else {
                            $('#user_id').append('<option value="">Employee not found for this Team>').prop(
                                'disabled', true);
                        }
                    },

                    error: function() {
                        alert('Error loading employee. Please try again.');
                    }
                });
            } else {
                $('#user_id').empty().append('<option value="">Select Employee</option>').prop('disabled', true);
            }
        }
    </script>
</body>

</html>
