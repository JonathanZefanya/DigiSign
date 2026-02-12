<!DOCTYPE html>
<html>
<head>
    <title>Email Verification - OTP Code</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: inline-block; padding: 15px 30px; border-radius: 10px;">
                <h2 style="color: white; margin: 0;">🔐 Email Verification</h2>
            </div>
        </div>
        
        <p>Hello <strong>{{ $recipient->name ?: $recipient->email }}</strong>,</p>
        
        <p>You are attempting to {{ $recipient->isSigner() ? 'sign' : 'view' }} the following document:</p>
        
        <div style="background-color: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>📄 Document:</strong> {{ $document->title }}</p>
            <p style="margin: 5px 0;"><strong>👤 From:</strong> {{ $document->user->name }}</p>
            <p style="margin: 5px 0;"><strong>✉️ Your Email:</strong> {{ $recipient->email }}</p>
        </div>
        
        <p>To verify your identity and access this document, please use the following One-Time Password (OTP):</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; display: inline-block;">
                <div style="font-size: 14px; margin-bottom: 5px; opacity: 0.9;">Your OTP Code</div>
                <div style="font-size: 42px; font-weight: bold; letter-spacing: 8px; font-family: 'Courier New', monospace;">
                    {{ $otp }}
                </div>
                <div style="font-size: 12px; margin-top: 5px; opacity: 0.8;">Valid for 10 minutes</div>
            </div>
        </div>
        
        <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; color: #92400e;">
                <strong>⚠️ Security Notice:</strong> This code will expire in 10 minutes. Do not share this code with anyone. If you did not request this verification, please ignore this email.
            </p>
        </div>
        
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        
        <p style="color: #9ca3af; font-size: 12px; text-align: center;">
            This is an automated security verification from {{ $appName ?? 'DigiSign' }}.<br>
            Please do not reply to this email.
        </p>
    </div>
</body>
</html>
