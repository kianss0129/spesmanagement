<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Temporary Password</title>
</head>
<body>
    <p>Hello <strong>{{ $name }}</strong>,</p>
    <p>Your account has been <strong>approved</strong> by the admin.</p>
    <p>
        <strong>Temporary Password:</strong> {{ $tempPassword }}<br>
        <strong>Login Here:</strong> <a href="{{ $loginLink }}">{{ $loginLink }}</a>
    </p>
    <p>Please change your password immediately after logging in.</p>
    <p>Thank you,<br>SPES System Team</p>
</body>
</html>
