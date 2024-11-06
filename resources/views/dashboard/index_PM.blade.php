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

        .utilization .header {
            display: flex;
            justify-content: space-between;
        }

        .team_name {
            border: 1px solid black;
            padding: 4px;
            border-radius: 5px;
        }

        .employee_section {
            display: flex;
            justify-content: space-between;
        }

        .streangth_section {
            display: flex;
            justify-content: space-between;
        }

        .no-records {
            text-align: center;
            margin-top: 12px;
            color: red;
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
                            <p>Total No: Products <span>{{ $products->count() }}</span></p>
                        </div>
                        <div class="body">
                            @foreach ($products as $product)
                                <div class="product_section">
                                    <p>Product name:{{ $product->name }}</p>
                                    <p>Peoples:</p>
                                    <p>Project Completion
                                        Rate:{{ round(\App\Models\SubTask::where('product_id', $product->id)->avg('rating')) }}%
                                    </p>
                                    <a href="{{ route('pm.product', ['id' => $product->id]) }}"
                                        class="btn btn-primary">View Details</a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="utilization" id="card_list">
                        <div class="header">
                            <div>Total No: Products</div>
                            <label for="project-filter">Product:</label>
                            <select id="product-filter">
                                <option value="">All</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="utilization_body">
                        </div>
                    </div>
                    <div class="attendance" id="card_list">
                        <div class="header">
                            <div>Team Attendance</div>
                            <div>
                                <p>Total Employee:{{$result['total_strength']}}</p>
                                <p>Present Employee:{{$result['total_present_employees']}}</p>
                                <p>Absent Employee:{{$result['total_absent_employees']}}</p>
                            </div>
                        </div>
                        <div class="attendance_body">
                            @foreach ($result['team_wise_attendance'] as $team)
                            <div class="team_based">
                                <p>
                                <div class="team_name" data-toggle="collapse" data-target="#collapseExample{{$team['team_id']}}"
                                    role="button" aria-expanded="false" aria-controls="collapseExample{{$team['team_id']}}">
                                    {{$team['team_name']}}
                                </div>
                                </p>
                                <div class="collapse" id="collapseExample{{$team['team_id']}}">
                                    <div class="card card-body">
                                        <div class="streangth_section">
                                            <p>Total Strength:{{$team['total_team_count']}} </p>
                                            <p>Present: {{$team['team_present_count']}}</p>
                                            <p>Absent: {{$team['team_absent_count']}}</p>
                                        </div>
                            
                                        <div class="employee_details">
                                            <div class="header">Absent Employees</div>
                                            @foreach($team['absent_employees'] as $employee)
                                                <div class="employee_section">
                                                    <div class="name_section">{{$employee['employee_name']}}</div>
                                                    <div class="employee_id">SW-{{$employee['employee_id']}}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
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
    $(document).ready(function() {
        $('#product-filter').change(function() {
            var productId = $(this).val(); // Get the selected product ID

            // AJAX request to fetch team-based data for the selected product
            $.ajax({
                url: '/utilizeteamdata', // Update with your actual route
                type: 'GET',
                data: {
                    product_id: productId
                },
                success: function(response) {
                    // Append the response data to the target div
                    $('.utilization_body').empty().append(response);
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching data:", error);
                }
            });
        });
    });
</script>
