<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Forgot Password — ChatBot Nepal</title>
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
  .right-inner { flex:1; display:flex; flex-direction:column; justify-content:center; padding:56px 52px; }
  .card-title { font-size:28px; font-weight:800; color:#0f172a; letter-spacing:-0.4px; margin-bottom:6px; }
  .card-sub { font-size:14px; color:#64748b; margin-bottom:36px; line-height:1.5; }
  .alert-success { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:12px 14px; font-size:13.5px; color:#15803d; margin-bottom:24px; display:flex; align-items:center; gap:8px; }
  .alert-error { background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:12px 14px; font-size:13.5px; color:#dc2626; margin-bottom:24px; display:flex; align-items:center; gap:8px; }
  .alert-success svg, .alert-error svg { width:16px; height:16px; flex-shrink:0; }
  .field { margin-bottom:20px; }
  .field label { display:block; font-size:11px; font-weight:700; letter-spacing:1.1px; text-transform:uppercase; color:#374151; margin-bottom:8px; }
  .input-wrap { position:relative; display:flex; align-items:center; }
  .input-wrap svg.prefix { position:absolute; left:14px; width:16px; height:16px; color:#9ca3af; pointer-events:none; }
  .input-wrap input { width:100%; background:#fff; border:1.5px solid #e5e7eb; border-radius:8px; padding:13px 44px; font-size:15px; color:#111827; outline:none; transition:border-color 0.2s,box-shadow 0.2s; font-family:inherit; }
  .input-wrap input::placeholder { color:#9ca3af; }
  .input-wrap input:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,0.12); }
  .input-wrap input.error { border-color:#f87171; box-shadow:0 0 0 3px rgba(248,113,113,0.1); }
  .field-error { font-size:12px; color:#dc2626; margin-top:5px; }
  .btn-primary { width:100%; padding:14px; background:#4f46e5; border:none; border-radius:8px; font-size:15px; font-weight:700; color:#fff; cursor:pointer; transition:background 0.2s,box-shadow 0.2s; margin-bottom:20px; font-family:inherit; }
  .btn-primary:hover { background:#4338ca; box-shadow:0 4px 16px rgba(79,70,229,0.35); }
  .back-link { text-align:center; font-size:14px; color:#64748b; }
  .back-link a { color:#4f46e5; font-weight:600; text-decoration:none; }
  .back-link a:hover { text-decoration:underline; }
  .right-footer { padding:20px 52px; border-top:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; }
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
  <h2 class="hero-heading">Reset your password securely</h2>
  <p class="hero-sub">We'll send a 6-digit verification code to your email address. Use it to set a new password — no links, no hassle.</p>
</div>

<div class="right">
  <div class="right-inner">
    <h1 class="card-title">Forgot your password?</h1>
    <p class="card-sub">Enter your account email and we'll send you a one-time code.</p>

    @if(session('status'))
      <div class="alert-success">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        {{ session('status') }}
      </div>
    @endif

    @if($errors->any())
      <div class="alert-error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
      @csrf
      <div class="field">
        <label for="email">Email Address</label>
        <div class="input-wrap">
          <svg class="prefix" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 7 10-7"/></svg>
          <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="name@company.com" required autofocus class="{{ $errors->has('email') ? 'error' : '' }}"/>
        </div>
        @error('email')<p class="field-error">{{ $message }}</p>@enderror
      </div>

      <button type="submit" class="btn-primary">Send Verification Code</button>
    </form>

    <p class="back-link"><a href="{{ route('login') }}">← Back to sign in</a></p>

    @if(session('status'))
      <div style="margin-top:24px;padding:16px;background:#f0f0ff;border-radius:8px;text-align:center;">
        <p style="font-size:13px;color:#4f46e5;font-weight:600;">Code sent! Proceed to enter it:</p>
        <a href="{{ route('password.reset') }}?email={{ urlencode(old('email', '')) }}" style="display:inline-block;margin-top:10px;padding:10px 20px;background:#4f46e5;color:#fff;border-radius:6px;font-size:14px;font-weight:600;text-decoration:none;">Enter verification code →</a>
      </div>
    @endif
  </div>
  <div class="right-footer">
    <span class="copy">© {{ date('Y') }} ChatBot Nepal by iSoftro</span>
  </div>
</div>

</body>
</html>
