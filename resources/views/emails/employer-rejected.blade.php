<!DOCTYPE html>
<html>
<head>
    <title>Employer Application Update</title>
</head>
<body>

    <h2>Hello, {{ $employer->company_name }}!</h2>

    <p>
        We regret to inform you that your employer registration
        in the <strong>SPES Management System</strong>
        has been <strong>rejected</strong>.
    </p>

    @if($employer->rejection_reason)
        <p>
            <strong>Reason:</strong>
            {{ $employer->rejection_reason }}
        </p>
    @endif

    <p>
        You may review and update your registration details
        before submitting another application.
    </p>

    <p>
        You can log in here:
        <a href="{{ route('login') }}">Go to Login</a>
    </p>

    <p>
        Thank you for your interest in partnering with
        the SPES Management System.
    </p>

</body>
</html>