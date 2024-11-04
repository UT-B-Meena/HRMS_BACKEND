<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"> <!-- Include your CSS file -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> <!-- Bootstrap CSS -->
    <style>
        body {
            background-color: #f8f9fa; /* Light background color */
        }
        .container {
            margin-top: 50px; /* Space from the top */
            max-width: 600px; /* Limit the width */
            padding: 20px;
            background-color: white; /* White background for the content */
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }
        h1 {
            text-align: center; /* Center the heading */
            margin-bottom: 20px; /* Space below the heading */
        }
        .user-info {
            margin-bottom: 30px; /* Space below user info */
            padding: 15px;
            border: 1px solid #dee2e6; /* Light border */
            border-radius: 5px; /* Rounded corners */
            background-color: #e9ecef; /* Light gray background for user info */
        }
        .btn-danger {
            width: 100%; /* Full width logout button */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to the Dashboard</h1>

        <div class="user-info">
            <p><strong>User ID:</strong> {{ Auth::user()->employee_id }}</p>
            <p><strong>Name:</strong> {{ Auth::user()->name }}</p> <!-- Assuming you have a name field -->
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger">Logout</button>
        </form>
    </div>

    <script src="{{ asset('js/app.js') }}"></script> <!-- Include your JS file -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script> <!-- jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script> <!-- Popper.js -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> <!-- Bootstrap JS -->
</body>
</html>
