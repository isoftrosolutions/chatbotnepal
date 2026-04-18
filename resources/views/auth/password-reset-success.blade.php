<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Password Reset Successful — ChatBot Nepal</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter','Segoe UI',system-ui,sans-serif; min-height:100vh; display:flex; background:#0f0e2a; }
  .left { flex:1; background:linear-gradient(160deg,#1e1b4b 0%,#1a1a35 40%,#0f0e2a 100%); padding:48px 56px; display:flex; flex-direction:column; position:relative; overflow:hidden; min-height:100vh; }
  .left::before { content:''; position:absolute; top:-80px; right:-80px; width:360px; height:360px; background:radial-gradient(circle,rgba(99,102,241,0.18) 0%,transparent 70%); pointer-events:none; }
  .logo-row { display:flex; align-items:center; gap:12px; margin-bottom:72px; }
  .logo-icon { width:48px; height:48px; background:#4f46e5; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .logo-icon svg { width:26px; height:26px; }
  .logo-name { font-size:20px; font-weight:700; color:#ffffff; letter-spacing:-0.3px; }
  .hero-heading { font-size:40px; font-weight:800; line-height:1.12; color:#fff; margin-bottom:20px; letter-spacing:-0.5px; }
  .hero-sub { font-size:15px; color:rgba(255,255,255,0.5); line-height:1.65; max-width:400px; }
  .right { width:500px; flex-shrink:0; background:#fff; display:flex; flex-direction:column; min-height:100vh; }
  .right-inner { flex:1; display:flex; flex-direction:column; justify-content:center; padding:56px 52px; text-align:center; }
  .success-icon { width:80px; height:80px; background:#f0fdf4; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 24px; }
  .success-icon svg { width:40px; height:40px; color:#10b981; }
  .card-title { font-size:28px; font-weight:800; color:#0f172a; letter-spacing:-0.4px; margin-bottom:12px; }
  .card-sub { font-size:15px; color:#64748b; margin-bottom:32px; line-height:1.6; }
  .countdown-text { font-size:13px; color:#9ca3af; margin-bottom:24px; }
  .countdown-text span { font-weight:700; color:#4f46e5; }
  .btn-primary { display:inline-block; padding:14px 32px; background:#4f46e5; border:none; border-radius:8px; font-size:15px; font-weight:700; color:#fff; text-decoration:none; cursor:pointer; transition:background 0.2s,box-shadow 0.2s; font-family:inherit; }
  .btn-primary:hover { background:#4338ca; box-shadow:0 4px 16px rgba(79,70,229,0.35); }
  .right-footer { padding:20px 52px; border-top:1px solid #f1f5f9; }
  .right-footer .copy { font-size:11px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; }
  @media(max-width:900px) { body{flex-direction:column;} .left{min-height:auto;padding:40px 32px;} .hero-heading{font-size:28px;} .right{width:100%;min-height:auto;} .right-inner{padding:40px 32px;} .right-footer{padding:16px 32px;} }
  @media(max-width:480px) { .left{padding:32px 24px;} .right-inner{padding:32px 24px;} }
</style>
</head>
<body>

<div class="left">
  <div class="logo-row">
    <div class="logo-icon">
      <svg viewBox="0 0 24 24" fill="none"><path d="M20 2H4C2.9 2 2 2.9 2 4v12c0 1.1.9 2 2 2h4l4 4 4-4h4c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z" fill="rgba(255,255,255,0.15)"/><path d="M8 10h8M8 14h5" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
    </div>
    <span class="logo-name">ChatBot Nepal</span>
  </div>
  <h2 class="hero-heading">All set!</h2>
  <p class="hero-sub">Your password has been reset successfully. You can now access your account with your new password.</p>
</div>

<div class="right">
  <div class="right-inner">
    <div class="success-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
        <polyline points="22 4 12 14.01 9 11.01"/>
      </svg>
    </div>
    <h1 class="card-title">Password Reset!</h1>
    <p class="card-sub">Your password has been reset successfully.<br/>Redirecting you to dashboard...</p>
    <p class="countdown-text">Redirecting in <span id="countdown">3</span> seconds</p>
    <a href="{{ $redirectUrl }}" class="btn-primary">Go to Dashboard Now →</a>
  </div>
  <div class="right-footer">
    <span class="copy">© {{ date('Y') }} ChatBot Nepal by iSoftro</span>
  </div>
</div>

<script>
let seconds = 3;
const countdownEl = document.getElementById('countdown');
const interval = setInterval(() => {
  seconds--;
  countdownEl.textContent = seconds;
  if (seconds <= 0) {
    clearInterval(interval);
    window.location.href = '{{ $redirectUrl }}';
  }
}, 1000);
</script>

</body>
</html>