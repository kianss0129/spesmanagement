<!DOCTYPE html>
<html>
<head>
    <title>SPES Examination Schedule</title>
</head>
<body>

    <h2>Hello, {{ $beneficiary->first_name }}!</h2>

    <p>
        📢 Your SPES examination has been successfully scheduled.
    </p>

    <p>
        Please see the examination details below:
    </p>

    <table cellpadding="8" cellspacing="0" border="1">
        <tr>
            <td><strong>Date</strong></td>
            <td>{{ $exam_date }}</td>
        </tr>

        <tr>
            <td><strong>Time</strong></td>
            <td>{{ $exam_time }}</td>
        </tr>

        <tr>
            <td><strong>Venue</strong></td>
            <td>{{ $exam_venue }}</td>
        </tr>
    </table>

    <p>
        ⚠️ Please arrive at least 30 minutes before your scheduled examination.
    </p>

    <p>
        Bring the following:
    </p>

    <ul>
        <li>Valid ID</li>
        <li>Ballpen</li>
        <li>Required SPES documents</li>
    </ul>

    <p>
        Thank you and good luck!
    </p>

</body>
</html>