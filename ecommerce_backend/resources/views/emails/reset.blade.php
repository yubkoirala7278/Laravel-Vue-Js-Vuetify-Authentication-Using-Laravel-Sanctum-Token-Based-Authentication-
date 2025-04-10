<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333333;
            font-size: 24px;
        }

        p {
            color: #666666;
            line-height: 1.6;
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }

        .button:hover {
            background-color: #0056b3;
        }

        hr {
            border: 0;
            border-top: 1px solid #eeeeee;
            margin: 20px 0;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #999999;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Reset Your Password</h1>
        <hr>
        <p>Hello,</p>
        <p>We received a request to reset your password. Click the button below
            to reset it. This link is valid for 60
            minutes.</p>
        <p>
            <a href="{{ $frontendUrl }}/reset-password?token={{ $token }}&email={{ urlencode($email) }}"
                class="button">
                Reset Password
            </a>
        </p>
        <p>If you did not request a password reset, please ignore this email or
            contact support if you have concerns.
        </p>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ env('APP_NAME') }}. All rights
                reserved.</p>
        </div>
    </div>
</body>

</html>
