<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
        .cardbody {
            display: flex;
            gap: 39px;
        }

        div#card_list {
            width: 50%;
        }
        .product_section {
    border: 1px solid grey;
    margin-bottom: 12px;
    padding: 5px;
    border-radius: 5px;
}
.time {
    width: 100px;
}
.breakdown_section {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 19px;
    border: 1px solid grey;
    padding: 10px;
    border-radius: 5px;
}
.breakdown_header {
    display: flex;
    justify-content: space-between;
}
.task_name {
    width: 238px;
}
    </style>
</head>

<body>
    <div class="container">
        <div class="main">
            <div class="main_section">
                <div class="user-info">
                    <p><strong>User ID:</strong> {{ Auth::user()->employee_id }}</p>
                    <p><strong>Name:</strong> {{ Auth::user()->name }}</p> <!-- Assuming you have a name field -->
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>

                <div class="cardbody mt-4">
                    <div class="product" id="card_list">
                        <div class="header">
                            <p>Pending Task</p>
                        </div>
                        <div class="body">
                            @if($subTasks->isEmpty())
                            <div class="no-records" style="text-align: center;color:red">No records found</div>
                            @else
                            @foreach ($subTasks as $subtask)
                                <div class="product_section">
                                    <p>Project name: <b>{{$subtask['project_name']}}</b></p>
                                    <p>Subtask Name: <b>{{$subtask['subtask_name']}}</b></p>
                                    <p>Remaining Hours: <b>{{$subtask['remaining_hours']}}</b></p>
                                    
                                </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="breakdown" id="card_list">
                        <div class="breakdown_header">
                            <p>Daily breakdown</p>
                            <p>{{now()->format('d-m-Y')}}</p>
                        </div>
                        <div class="body">
                            <p style="text-align:center"><b>8 hrs</b></p>
                            @if($dailyBreakdown->isEmpty())
                            <div class="no-records" style="text-align: center;color:red">No records found</div>
                            @else
                            @foreach ($dailyBreakdown as $breakDown)
                                <div class="breakdown_section">
                                    <div class="time">
                                        <p><b>{{$breakDown['startTime']}}</b></p>
                                        <p>to</p>
                                        <p><b>{{$breakDown['endTime']}}</b></p>
                                    </div>
                                    <div class="task_name">
                                        <p>Project name: <b>{{$breakDown['project_name']}}</b></p>  
                                        <p>Subtask Name: <b>{{$breakDown['subtask_name']}}</b></p>
                                    </div>
                                    <div class="duration">
                                        <p>Duration: <b>{{$breakDown['duration']}}</b></p>
                                    </div>
                                    
                                </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="attendance" id="card_list">
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> <!-- Bootstrap JS -->

<script>
    
</script>
