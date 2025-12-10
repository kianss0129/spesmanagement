<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Interview Scheduled</title>
</head>
<body>
  <h2>SPES Interview Scheduled</h2>
  <p>Hi,</p>

  <p>Your interview has been scheduled.</p>

  <p><strong>Date:</strong> {{ $interview->scheduled_at }}</p>
  <p>
    <a href="{{ $interview->meet_link }}" target="_blank">Join Interview</a>
  </p>

  <p>Thank you,<br/>SPES Team</p>
</body>
</html>
