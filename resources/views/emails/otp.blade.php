<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Your verification code</title>
  <style>
    body { margin:0; padding:0; background:#f4f7fe; font-family:'Inter','Segoe UI',system-ui,sans-serif; }
    .wrap { max-width:520px; margin:40px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.07); }
    .header { background:linear-gradient(135deg,#4f46e5,#7c3aed); padding:36px 40px; text-align:center; }
    .header-icon { width:56px; height:56px; background:rgba(255,255,255,0.15); border-radius:14px; margin:0 auto 16px; display:flex; align-items:center; justify-content:center; }
    .header h1 { color:#fff; font-size:20px; font-weight:700; margin:0; }
    .header p  { color:rgba(255,255,255,0.7); font-size:13px; margin:6px 0 0; }
    .body { padding:40px 40px 32px; }
    .body p { font-size:15px; color:#374151; line-height:1.6; margin:0 0 20px; }
    .otp-box { background:#f0f0ff; border:2px dashed #a5b4fc; border-radius:12px; text-align:center; padding:24px 16px; margin:24px 0; }
    .otp-code { font-size:48px; font-weight:800; letter-spacing:12px; color:#4f46e5; font-family:'Courier New',monospace; }
    .otp-note { font-size:12px; color:#6b7280; margin-top:10px; }
    .warning { background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:14px 16px; font-size:13px; color:#92400e; margin-top:24px; }
    .footer { padding:20px 40px; background:#f9fafb; border-top:1px solid #f3f4f6; text-align:center; }
    .footer p { font-size:12px; color:#9ca3af; margin:0; line-height:1.6; }
  </style>
</head>
<body>
<div class="wrap">

  <div class="header">
    <div class="header-icon">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
        <path d="M20 2H4C2.9 2 2 2.9 2 4v12c0 1.1.9 2 2 2h4l4 4 4-4h4c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z" fill="rgba(255,255,255,0.9)"/>
        <path d="M8 10h8M8 14h5" stroke="#4f46e5" stroke-width="2" stroke-linecap="round"/>
      </svg>
    </div>
    <h1>ChatBot Nepal</h1>
    <p>Verification Code</p>
  </div>

  <div class="body">
    <p>Hi there,</p>

    @if($purpose === 'password_reset')
      <p>We received a request to reset the password on your ChatBot Nepal account. Use the code below to proceed:</p>
    @else
      <p>You requested to change the email address on your ChatBot Nepal account. Use the code below to confirm your new email:</p>
    @endif

    <div class="otp-box">
      <div class="otp-code">{{ $otp }}</div>
      <div class="otp-note">This code expires in <strong>15 minutes</strong></div>
    </div>

    <p>Enter this 6-digit code on the verification page. Do not share it with anyone — our team will never ask for it.</p>

    <div class="warning">
      <strong>Didn't request this?</strong> You can safely ignore this email. Your account remains secure and no changes have been made.
    </div>
  </div>

  <div class="footer">
    <p>© {{ date('Y') }} ChatBot Nepal by iSoftro &nbsp;·&nbsp; Kathmandu, Nepal<br>
    This is an automated message — please do not reply to this email.</p>
  </div>

</div>
</body>
</html>
