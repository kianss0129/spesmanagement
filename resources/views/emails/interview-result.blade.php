<!DOCTYPE html>
<html>
<head>
    <title>SPES Interview Result</title>
</head>
<body>

    <h2>
        Hello,
        {{ $interview->beneficiary->first_name }}!
    </h2>

    @if($result === 'passed')

        <p>
            🎉 Congratulations!
            You have successfully
            <strong>PASSED</strong>
            the SPES interview.
        </p>

        <p>
            You will proceed to the Contract signing phase.
        </p>

    @else

        <p>
            We regret to inform you that you have
            <strong>FAILED</strong>
            the SPES interview.
        </p>

        <p>
            Thank you for your interest in the SPES program.
        </p>

    @endif

    <hr>

    <p>
        <strong>Interview Schedule:</strong><br>
        {{ \Carbon\Carbon::parse($interview->scheduled_at)->format('F d, Y h:i A') }}
    </p>

    <p>
        <strong>Meeting Link:</strong><br>
        <a href="{{ $interview->meet_link }}">
            Join Interview
        </a>
    </p>

    <p>
        Thank you for using the SPES Management System.
    </p>

</body>
</html>
