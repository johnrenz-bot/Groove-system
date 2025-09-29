<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verify Your Groove Account</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background-color: #1b1b1b; color: #fff; padding: 30px; border-radius: 8px;">
        <h2>Hello {{ $client->firstname }},</h2>
        <p>Thank you for registering on Groove!</p>
        <p>Please verify your email by using the code below:</p>
        <h3 style="background: #333; padding: 10px; border-radius: 4px; text-align: center;">{{ $code }}</h3>
        <p>Or click the button below to verify your email:</p>
        <a href="{{ url('/verify-email/'.$code) }}" style="display: inline-block; padding: 10px 20px; background-color: #9b5de5; color: #fff; text-decoration: none; border-radius: 4px;">Verify Email</a>
        <p style="margin-top: 20px; font-size: 12px; color: #aaa;">If you did not create an account, ignore this email.</p>
    </div>
</body>
</html>
