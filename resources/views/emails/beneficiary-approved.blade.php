<!DOCTYPE html>
<html>
<head>
    <title>SPES Account Approved</title>
</head>
<body>
    <h2>Hello, {{ $name }}!</h2>

    <p>Congratulations! Your SPES beneficiary account has been approved.</p>

    @if($password)
        <p>Your temporary password is: <strong>{{ $password }}</strong></p>
        <p>We recommend that you change it after your first login.</p>
    @endif

    <p>You can now access your full dashboard here: <a href="{{ $dashboardUrl }}">Go to Dashboard</a></p>

    <p>Thank you for using SPES Management System.</p>
</body>
</html>
