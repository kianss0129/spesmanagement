<!DOCTYPE html>
<html>
<body>
    <h1>Hello, {{ $name }}!</h1>

    <p>We regret to inform you that your submitted documents were incomplete or invalid.</p>

    @if($reason)
        <p><strong>Reason:</strong> {{ $reason }}</p>
    @endif

    @if($resubmissionDate)
        <p>Please resubmit your documents by <strong>{{ $resubmissionDate }}</strong> to continue your application.</p>
    @else
        <p>Please resubmit your documents within <strong>5 days</strong> to continue your application.</p>
    @endif

    <p>Thank you for using the SPES Management System.</p>
</body>
</html>
