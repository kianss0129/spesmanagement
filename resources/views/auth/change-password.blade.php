<form method="POST" action="{{ route('password.update') }}">
    @csrf
    <label>New Password:</label>
    <input type="password" name="password" required>
    
    <label>Confirm Password:</label>
    <input type="password" name="password_confirmation" required>

    <button type="submit">Change Password</button>
</form>
