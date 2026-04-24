<!DOCTYPE html>
<html>
<head>
    <title>Application Received</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px; border-top: 4px solid #1a237e;">
        <h2 style="color: #1a237e;">Application Received</h2>
        
        <p>Dear {{ $applicant->name }},</p>
        
        <p>Thank you for your interest in joining the Boxleo team. We have received your application for the position of <strong>{{ $applicant->job->title }}</strong>.</p>
        
        <p>Our recruitment team will review your application and CV. We will contact you if your qualifications match our current requirements for this role.</p>
        
        <p>In the meantime, feel free to explore more about our culture and the impact we make at Boxleo by visiting our website.</p>
        
        <p style="margin-top: 30px;">
            Best regards,<br>
            <strong>The Boxleo Hiring Team</strong><br>
            <a href="https://boxleocourier.com" style="color: #1a237e;">boxleocourier.com</a>
        </p>
    </div>
</body>
</html>
