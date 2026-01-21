<!DOCTYPE html>
<html>
<head>
    <title>SPES Account Update</title>
</head>
<body>
    <h2>Hello, {{ $name }}!</h2>

    <p>We regret to inform you that your SPES beneficiary application has been <strong>rejected</strong>.</p>

    @if($reason)
        <p><strong>Reason:</strong> {{ $reason }}</p>
    @endif

    @if($resubmissionDate)
        <p>You may resubmit your application starting from: <strong>{{ $resubmissionDate }}</strong></p>
    @endif

    <p>You can check your application status or log in here: <a href="{{ $dashboardUrl }}">Go to Dashboard</a></p>

    <p>Thank you for your interest in the SPES Management System. We encourage you to apply again once eligible.</p>
</body>
</html>

