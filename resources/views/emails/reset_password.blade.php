<!DOCTYPE html>
<html>
<head>
    <title>Password Reset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Reset Request</h2>
        <p>Hello {{ $user->name }},</p>
        <p>We received a request to reset the password for your account. If you did not make this request, you can safely ignore this email.</p>
        <p>Your OTP is <strong>{{ $otp }}</strong>.</p>
        <p>This OTP will expire in 2 minutes at <strong>{{ $expires }}</strong>.</p>
        <p>To reset your password, please use this OTP within the specified timeframe.</p>
        <p>If you have any questions or need further assistance, please feel free to contact us.</p>
        <p>Thank you,</p>
        <p>The {{ config('app.name') }} Team</p>
        <div class="footer text-center mt-4">
            <p class="text-muted">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
