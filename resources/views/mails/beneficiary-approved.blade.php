<!DOCTYPE html>
<html>
<body>
    <h1>Congratulations, {{ $beneficiary->name }}!</h1>

    <p>Your SPES account has been approved.</p>

    <p><strong>Login credentials:</strong></p>
    <ul>
        <li>Email: {{ $beneficiary->email }}</li>
        <li>Password: {{ $password }}</li>
    </ul>

    <p>Please change your password after your first login.</p>

    <p>— SPES Management System</p>
</body>
</html>
