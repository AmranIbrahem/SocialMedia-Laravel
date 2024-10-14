<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #3b5998;
            padding: 10px;
            border-radius: 5px 5px 0 0;
            color: #ffffff;
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .content {
            color: #333333;
            font-size: 16px;
            line-height: 24px;
            margin-bottom: 10px;
        }

        .code {
            background-color: #3b5998;
            color: #ffffff;
            padding: 10px;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .support {
            color: #666666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .signature {
            color: #3b5998;
            font-weight: bold;
            font-size: 16px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        Email Verification
    </div>
    <div class="content">
        <p>Hi {{ $name }},</p>
        <p>Thank you for registering on our website.</p>
        <p>Please enter the following code to verify your email:</p>
    </div>
    <div class="code">
        {{ $recovery_code }}
    </div>
    <div class="content">
        <p>If you have any questions, feel free to contact our support team.</p>
        <p class="support">Thank you,</p>
        <p class="support">Support Team</p>
    </div>
    <div class="signature">
        &copy; 2023 Aleta Store
    </div>
</div>
</body>
</html>
