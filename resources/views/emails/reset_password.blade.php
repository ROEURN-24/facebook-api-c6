<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body>
    <h2>Password Reset Request</h2>
    <p>Hello {{ $user->name }},</p>
    <p>We received a request to reset the password for your account. If you did not make this request, you can safely ignore this email.</p>
    <p>To reset your password, click on the following link (or copy and paste it into your browser):</p>
    <p><a href="{{ $resetLink }}">{{ $resetLink }}</a></p>
    <p>This link will expire in {{ $expires }} minutes.</p> 
    <p>If you have any questions or need further assistance, please feel free to contact us.</p>
    <p>Thank you,</p>
    <p>The {{ config('app.name') }} Team</p>
</body>
</html>
