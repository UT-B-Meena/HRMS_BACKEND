<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
</head>
<body>
    <h2>Change Password</h2>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <form action="{{ route('password.update') }}" method="POST">
        @csrf

        <div>
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" required>
            @error('current_password')
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" required>
            @error('new_password')
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="new_password_confirmation">Confirm New Password</label>
            <input type="password" id="new_password_confirmation" name="new_password_confirmation" required>
        </div>

        <button type="submit">Change Password</button>
    </form>
</body>
</html>
