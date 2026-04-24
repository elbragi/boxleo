<!DOCTYPE html>
<html>
<head>
    <title>New Job Application</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;">
        <h2 style="color: #2c3e50;">New Job Application Received</h2>
        <p>A new application has been submitted via the external website.</p>
        
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; width: 30%;">Position:</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ $applicant->job->title }}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Applicant Name:</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ $applicant->name }}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Email:</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">
                    <a href="mailto:{{ $applicant->email }}">{{ $applicant->email }}</a>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Phone:</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ $applicant->phone }}</td>
            </tr>
        </table>
        
        @if($applicant->message)
        <div style="margin-top: 20px; background-color: #f9f9f9; padding: 15px; border-radius: 4px;">
            <p style="margin-top: 0; font-weight: bold;">Cover Letter / Message:</p>
            <p style="white-space: pre-line; margin-bottom: 0;">{{ $applicant->message }}</p>
        </div>
        @endif
        
        <p style="margin-top: 30px; font-size: 0.9em; color: #7f8c8d;">
            The candidate's CV is attached to this email. You can also view this application in the HRMS dashboard.
        </p>
    </div>
</body>
</html>
