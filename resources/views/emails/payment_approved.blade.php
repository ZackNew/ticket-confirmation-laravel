<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Approved</title>
</head>
<body style="font-family: sans-serif; background-color: #f8f9fa; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 8px;">
        <div style="text-align: center;">
            <img src="https://hips.hearstapps.com/hmg-prod/images/dog-puppy-on-garden-royalty-free-image-1586966191.jpg?crop=0.752xw:1.00xh;0.175xw,0&resize=1200:*" alt="Banner" style="width: 200px; height: auto;"/>
        </div>

        <h2>Hello {{ $name }},</h2>

        <p>
            Thank you for submitting your payment. Your payment has been approved. Your ticket numbers are:
            <strong>
                @if(is_array($tickets))
                    {{ implode(', ', $tickets) }}
                @else
                    {{ $tickets }}
                @endif
            </strong>
        </p>

        <p style="margin-top: 30px;">Best regards,<br><strong>The Team</strong></p>
    </div>
</body>
</html>