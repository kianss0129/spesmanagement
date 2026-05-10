<!DOCTYPE html>
<html>
<head>
    <title>Welcome to SPES System</title>
</head>
<body>
    <h1>Hello, {{ $user->name }}!</h1>
    <p>
        Welcome to SPES System. Your registration as 
        {{ $user->beneficiary_type ?? 'Employer' }} is complete.
    </p>
    <p>
        Please <a href="{{ route('verification.notice') }}">verify your email</a> to start using the system.
    </p>
</body>
</html>
