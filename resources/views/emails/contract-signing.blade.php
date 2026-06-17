<!DOCTYPE html>
<html>
<head>
    <title>SPES Contract Signing</title>
</head>
<body>

    <h2>
        Hello,
        {{ $application->beneficiary->first_name }}!
    </h2>

    <p>
        🎉 Congratulations!
        You are now scheduled for
        <strong>Contract Signing</strong>
        under the SPES Program.
    </p>

    <p>
        Please prepare the necessary requirements
        and attend on the scheduled date.
    </p>

    <hr>

    <p>
        <strong>Employer:</strong><br>
        {{ $application->jobListing->employer->company_name ?? 'N/A' }}
    </p>

    <p>
        <strong>Job Position:</strong><br>
        {{ $application->jobListing->title ?? 'N/A' }}
    </p>

    <p>
        Please wait for further instructions from PESO.
    </p>

    <p>
        Thank you for using the SPES Management System.
    </p>

</body>
</html>