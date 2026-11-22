<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Password Reset Code</title></head>
<body style="margin:0;background:#f1f5f9;color:#1e293b;font:16px/1.5 Arial,sans-serif">
<div style="max-width:560px;margin:0 auto;padding:24px 16px">
    <p style="text-align:center"><a href="https://www.pscourt.gov.et/" style="color:#17365d;font-weight:bold">PSCOURT</a></p>
    <div style="background:#fff;padding:24px">
        <h1 style="font-size:22px">Hello {{ $name }},</h1>
        <p>You requested to reset your password. Use the code below:</p>
        <p style="font-size:32px;font-weight:bold;letter-spacing:3px;color:#17365d">{{ $code }}</p>
        <p>This code expires in <strong>{{ $minutes }} minutes</strong>.</p>
        <p>If you did not request a password reset, no further action is required.</p>
        <p>Regards,<br>PSCOURT</p>
    </div>
</div>
</body>
</html>
