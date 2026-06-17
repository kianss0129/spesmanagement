<!DOCTYPE html>
<html>
<head>
    <title>Employer Application Approved</title>
</head>
<body>

    <h2>Hello, {{ $employer->company_name }}!</h2>

    <p>
        🎉 Congratulations! Your employer registration
        in the <strong>SPES Management System</strong>
        has been <strong>approved</strong>.
    </p>

    <p>
        You may now access your employer account
        and start posting job opportunities.
    </p>

    <p>
        👉 <a href="{{ route('login') }}">
            Click here to login
        </a>
    </p>

    <p>
        Thank you for partnering with the
        SPES Management System.
    </p>

</body>
</html>