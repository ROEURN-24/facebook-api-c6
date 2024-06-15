
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
    </style>
</head>
<body>
    <div class="container bg-white rounded shadow-sm p-4" style="max-width: 600px;">
        <h2 class="text-dark mb-4">Password Reset Request</h2>
        <p class="text-secondary">Hello {{ $user->name }},</p>
        <p class="text-secondary">We received a request to reset the password for your account. If you did not make this request, you can safely ignore this email.</p>
        <p class="text-secondary">To reset your password, click on the following link (or copy and paste it into your browser):</p>
        <p><a href="{{ $resetLink }}" class="btn btn-primary">{{ $resetLink }}</a></p>
        <p class="text-secondary">This link will expire in {{ $expires }}.</p>
        <p class="text-secondary">If you have any questions or need further assistance, please feel free to contact us.</p>
        <p class="text-secondary">Thank you,</p>
        <p class="text-secondary">The {{ config('app.name') }} Team</p>
        <div class="footer text-center mt-4">
            <p class="text-muted">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>

</body>
</html>
