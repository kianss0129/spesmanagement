<!DOCTYPE html>
<html>
<head>
    <title>SPES Account Update</title>
</head>
<body>
    <h2>Hello, {{ $beneficiary->first_name }}!</h2>

    <p>
        We regret to inform you that your SPES beneficiary application
        has been <strong>rejected</strong>.
    </p>

    @if($beneficiary->rejection_reason)
        <p>
            <strong>Reason:</strong>
            {{ $beneficiary->rejection_reason }}
        </p>
    @endif

    <p>
        You may update and resubmit your application in the future.
    </p>

    <p>
        You can log in here:
        <a href="{{ route('login') }}">Go to Login</a>
    </p>

    <p>
        Thank you for your interest in the SPES Management System.
    </p>
</body>
</html>