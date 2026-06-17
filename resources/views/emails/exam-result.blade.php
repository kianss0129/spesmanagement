<!DOCTYPE html>
<html>
<head>
    <title>SPES Exam Result</title>
</head>
<body>

    <h2>Hello, {{ $exam->application->beneficiary->first_name }}!</h2>

    @if($result === 'passed')

        <p>
            🎉 Congratulations! You have
            <strong>PASSED</strong>
            the SPES examination.
        </p>

        <p>
            Please wait for further instructions regarding your application.
        </p>

    @else

        <p>
            We regret to inform you that you have
            <strong>FAILED</strong>
            the SPES examination.
        </p>

        <p>
            You may re-apply again in the future.
        </p>

    @endif

    <hr>

    <p>
        <strong>Exam Date:</strong>
        {{ \Carbon\Carbon::parse($exam->exam_date)->format('F d, Y h:i A') }}
    </p>

    <p>
        <strong>Location:</strong>
        {{ $exam->location ?? 'TBA' }}
    </p>

    <p>
        Thank you for using the SPES Management System.
    </p>

</body>
</html>