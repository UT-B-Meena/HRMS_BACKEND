<!-- resources/views/projects/edit_project_request.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project Request</title>
    <!-- Include Bootstrap CSS from CDN -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .form-control:disabled {
            background-color: #e9ecef; /* Make disabled fields look distinct */
        }
        h1 {
            color: #343a40; /* Dark color for the heading */
        }
        .btn-primary {
            background-color: #007bff; /* Bootstrap primary color */
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3; /* Darker shade on hover */
            border-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Project Request</h1>
        <form action="{{ route('project_request.update', $subtask->id) }}" method="POST">
            @csrf
            @method('PUT') <!-- Use PUT for updates -->
            <input type="hidden" name="subtask_id" value="{{ $subtask->id }}">

            <div class="form-group">
                <label for="subtaskName">Subtask Name</label>
                <input type="text" class="form-control" id="subtaskName" name="name" value="{{ $subtask->name }}" disabled>
            </div>

            <div class="form-group">
                <label for="projectName">Project Name</label>
                <input type="text" class="form-control" id="projectName" name="project_name" value="{{ $subtask->project->project_name }}" disabled>
            </div>

            <div class="form-group">
                <label for="assignedTo">Assigned To</label>
                <input type="text" class="form-control" id="assignedTo" name="assigned_to" value="{{ $subtask->user->assignee }}" disabled>
            </div>

            <div class="form-group">
                <label for="assignedBy">Assigned By</label>
                <input type="text" class="form-control" id="assignedBy" name="assigned_by" value="{{ $subtask->assigned_user->assigned_by }}" disabled>
            </div>

            <div class="form-group">
                <label for="estimatedHours">Estimated Hours</label>
                <input type="text" class="form-control" id="estimatedHours" name="estimated_hours" value="{{ $subtask->estimated_hours }}" disabled>
            </div>

            <div class="form-group">
                <label for="totalHoursWorked">Total Hours Worked</label>
                <input type="text" class="form-control" id="totalHoursWorked" name="total_hours_worked" value="{{ $subtask->total_hours_worked }}" disabled>
            </div>

            <div class="form-group">
                <label for="comment">Comment</label>
                <input type="text" class="form-control" id="comment" name="command" value="{{ $subtask->command }}" disabled>
            </div>

            <div class="form-group">
                <label for="remark">Remark</label>
                <input type="text" class="form-control" id="remark" name="remark" value="{{ $subtask->remark }}">
            </div>

            <div class="form-group">
                <label for="rating">Rating</label>
                <input type="number" class="form-control" id="rating" name="rating" value="{{ $subtask->rating }}">
            </div>

            <button type="submit" class="btn btn-warning" name="action" value="reopen">Re-open</button>
        <button type="submit" class="btn btn-danger" name="action" value="close">Close</button>
        </form>
    </div>

    <!-- Include Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
