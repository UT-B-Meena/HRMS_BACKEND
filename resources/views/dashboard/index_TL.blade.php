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

        .streangth_section,
        .attendance_search,
        .name_section {
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
                    <div class="attendance" id="card_list">
                        <div class="header">
                            <div class="attendance_search">
                                <p>Team Attendance</p>
                                <input type="search" name="employee" id="employee_search" placeholder="Search..">
                            </div>
                            <div>
                                <p>Total :{{ $resultss['total_strength'] }}</p>
                                <p>Present :{{ $resultss['total_present_employees'] }}</p>
                                <p>Absent :{{ $resultss['total_absent_employees'] }}</p>
                            </div>
                        </div>
                        <div class="attendance_body">
                            @if ($resultss['attendance_list']->isEmpty())
                                <div class="no-records">No records found</div>
                            @else
                                @foreach ($resultss['attendance_list'] as $team)
                                    <div class="employee_section">
                                        <div class="name_section">
                                            <p>{{ $team['initials'] }}</p>
                                            <div class="ml-4">
                                                <span>{{ $team['employee_name'] }}</span>
                                                <span>SW-{{ $team['employee_id'] }}</span>
                                            </div>
                                        </div>
                                        <div
                                            class="status"style="color: {{ $team['status'] == 'Absent' ? 'red' : 'green' }}">
                                            {{ $team['status'] }}</div>
                                    </div>
                                @endforeach
                            @endif

                        </div>
                    </div>
                    <div class="product" id="card_list">
                        <div class="header">
                            <p>Product Status</p>

                            <p>
                            <div class="team_name" data-toggle="collapse" data-target="#collapseExample3" role="button"
                                aria-expanded="false" aria-controls="collapseExample3">
                                Product List..
                            </div>
                            </p>
                            <div class="collapse" id="collapseExample3">
                                <div class="card card-body">
                                    <div id="product_check"> <!-- Container for checkboxes -->
                                        @foreach ($products as $product)
                                            <label for="product_{{ $product->id }}">
                                                <input type="checkbox" name="product_list[]"
                                                    id="product_{{ $product->id }}" value="{{ $product->id }}">
                                                {{ $product->name }}
                                            </label><br>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="product_body">
                            @foreach ($project_Section['projects'] as $project)
                                <div class="project_section">
                                    <p>Product name:{{ $project['product_name'] }}</p>
                                    <p>Peoples:{{ $project['number_of_people'] }}</p>
                                    <p>Project Completion
                                        Rate:{{ $project['completion_rate'] }}%
                                    </p>
                                    <a href="{{ route('tl.product', ['id' => $project['product_id']]) }}"
                                        class="btn btn-primary">View Details</a>
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
        $('#employee_search').on('input', function() {
            var name = $(this).val();


            $.ajax({
                url: '/employeeAttendancelist',
                type: 'GET',
                data: {
                    name: name
                },
                success: function(response) {
                    $('.attendance_body').empty().append(response);
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching data:", error);
                }
            });
        });
        $('#product_check').on('change', function() {
            // Get the array of selected product IDs
            var productIds = $("input[name='product_list[]']:checked").map(function() {
                return $(this).val();
            }).get();

            // Check if any products are selected
            if (productIds.length > 0) {
                $.ajax({
                    url: '/tlproductdata',
                    type: 'GET',
                    data: {
                        productIds: productIds // Pass the array of selected product IDs
                    },
                    success: function(response) {
                        $('.product_body').empty().append(
                        response); // Update the content in the product_body container
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching data:", error);
                    }
                });
            } else {
                console.log("No products selected");
            }
        });
    });
</script>
