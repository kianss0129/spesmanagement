<!DOCTYPE html>
<html>
<head>
    <title>SPES Account Approved</title>
</head>
<body>
    <h2>Hello, {{ $name }}!</h2>

    <p>🎉 Congratulations! Your SPES beneficiary account has been approved.</p>

    <p><strong>Your temporary password:</strong></p>
    <p style="font-size:18px; font-weight:bold;">
        {{ $password }}
    </p>

    <p>⚠️ Please change your password after your first login.</p>

    <p>
        👉 <a href="{{ $loginUrl }}">Click here to login</a>
    </p>

    <p>Thank you for using the SPES Management System.</p>
</body>
</html>
