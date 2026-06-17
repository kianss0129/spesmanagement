<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SPES Interview Schedule</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">

    <h2 style="color: #2563eb;">SPES Interview Schedule Notification</h2>

    <p>Hello {{ $beneficiary->first_name ?? $user->name ?? 'Applicant' }},</p>

    <p>
        We are pleased to inform you that your interview for the
        <strong>Special Program for Employment of Students (SPES)</strong>
        has been successfully scheduled.
    </p>

    <hr>

    <p><strong>Interview Date & Time:</strong></p>
    <p>{{ \Carbon\Carbon::parse($interview->scheduled_at)->format('F d, Y h:i A') }}</p>

    @if(!empty($interview->meet_link))
        <p>
            <strong>Meeting Link:</strong><br>
            <a href="{{ $interview->meet_link }}" target="_blank">
                Join Interview
            </a>
        </p>
    @endif

    <hr>

    <p>
        Please be available at least 10 minutes before your scheduled interview time.
        Ensure that your internet connection and device are ready if the interview
        will be conducted online.
    </p>

    <p>
        If you have any questions or concerns, please contact the PESO/SPES Office.
    </p>

    <br>

    <p>
        Thank you and good luck!
    </p>

    <p>
        Regards,<br>
        <strong>SPES Management Team</strong>
    </p>

</body>
</html>