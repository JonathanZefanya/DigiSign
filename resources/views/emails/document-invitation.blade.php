<!DOCTYPE html>
<html>
<head>
    <title>Document {{ ucfirst($action) }} Request</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">Document {{ ucfirst($action) }} Request</h2>
        
        <p>Hello {{ $recipient->name ?: $recipient->email }},</p>
        
        <p>You have been invited to <strong>{{ $action }}</strong> the following document:</p>
        
        <div style="background-color: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Document:</strong> {{ $document->title }}</p>
            <p style="margin: 5px 0;"><strong>From:</strong> {{ $document->user->name }}</p>
            <p style="margin: 5px 0;"><strong>Role:</strong> {{ $recipient->role }}</p>
        </div>
        
        @if($recipient->isSigner())
        <p>Please click the button below to sign this document:</p>
        @else
        <p>Please click the button below to view this document:</p>
        @endif
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}" 
               style="background-color: #2563eb; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; display: inline-block;">
                {{ $recipient->isSigner() ? 'Sign Document' : 'View Document' }}
            </a>
        </div>
        
        <p style="color: #6b7280; font-size: 14px;">
            If you cannot click the button, copy and paste this link into your browser:<br>
            <a href="{{ $url }}" style="color: #2563eb;">{{ $url }}</a>
        </p>
        
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        
        <p style="color: #9ca3af; font-size: 12px;">
            This is an automated message from Digital Signature Application. Please do not reply to this email.
        </p>
    </div>
</body>
</html>
