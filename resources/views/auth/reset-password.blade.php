<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Reset Password — ChatBot Nepal</title>
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
  .alert-error { background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:12px 14px; font-size:13.5px; color:#dc2626; margin-bottom:24px; display:flex; align-items:flex-start; gap:8px; }
  .alert-error svg { width:16px; height:16px; flex-shrink:0; margin-top:1px; }
  .field { margin-bottom:20px; }
  .field label { display:block; font-size:11px; font-weight:700; letter-spacing:1.1px; text-transform:uppercase; color:#374151; margin-bottom:8px; }
  .input-wrap { position:relative; display:flex; align-items:center; }
  .input-wrap svg.prefix { position:absolute; left:14px; width:16px; height:16px; color:#9ca3af; pointer-events:none; }
  .input-wrap input { width:100%; background:#fff; border:1.5px solid #e5e7eb; border-radius:8px; padding:13px 44px; font-size:15px; color:#111827; outline:none; transition:border-color 0.2s,box-shadow 0.2s; font-family:inherit; }
  .input-wrap input.otp-input { font-size:24px; font-weight:700; letter-spacing:8px; text-align:center; font-family:'Courier New',monospace; padding:13px 16px; }
  .input-wrap input::placeholder { color:#9ca3af; letter-spacing:normal; }
  .input-wrap input:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,0.12); }
  .input-wrap input.error { border-color:#f87171; box-shadow:0 0 0 3px rgba(248,113,113,0.1); }
  .input-wrap .suffix { position:absolute; right:12px; background:none; border:none; cursor:pointer; padding:4px; display:flex; align-items:center; border-radius:4px; color:#9ca3af; transition:color 0.2s; }
  .input-wrap .suffix:hover,.input-wrap .suffix.active { color:#6366f1; }
  .input-wrap .suffix svg { width:18px; height:18px; }
  .field-error { font-size:12px; color:#dc2626; margin-top:5px; }
  .field-hint { font-size:12px; color:#6b7280; margin-top:5px; }
  .btn-primary { width:100%; padding:14px; background:#4f46e5; border:none; border-radius:8px; font-size:15px; font-weight:700; color:#fff; cursor:pointer; transition:background 0.2s,box-shadow 0.2s; margin-bottom:20px; font-family:inherit; }
  .btn-primary:hover { background:#4338ca; box-shadow:0 4px 16px rgba(79,70,229,0.35); }
  .back-link { text-align:center; font-size:14px; color:#64748b; }
  .back-link a { color:#4f46e5; font-weight:600; text-decoration:none; }
  .back-link a:hover { text-decoration:underline; }
  .right-footer { padding:20px 52px; border-top:1px solid #f1f5f9; }
  .right-footer .copy { font-size:11px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; }
  @media(max-width:900px) { body{flex-direction:column;} .left{min-height:auto;padding:40px 32px;} .hero-heading{font-size:28px;} .right{width:100%;min-height:auto;} .right-inner{padding:40px 32px;} }
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
  <h2 class="hero-heading">Enter your code &amp; new password</h2>
  <p class="hero-sub">Paste the 6-digit code from your email, then choose a strong new password.</p>
</div>

<div class="right">
  <div class="right-inner">
    <h1 class="card-title">Set new password</h1>
    <p class="card-sub">Enter the code we sent to your email, then your new password.</p>

    @if($errors->any())
      <div class="alert-error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>{{ $errors->first() }}</div>
      </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
      @csrf

      <div class="field">
        <label for="email">Email Address</label>
        <div class="input-wrap">
          <svg class="prefix" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 7 10-7"/></svg>
          <input type="email" id="email" name="email" value="{{ old('email', $email) }}" placeholder="name@company.com" required autocomplete="email" class="{{ $errors->has('email') ? 'error' : '' }}"/>
        </div>
        @error('email')<p class="field-error">{{ $message }}</p>@enderror
      </div>

      <div class="field">
        <label for="otp">Verification Code</label>
        <div class="input-wrap">
          <input type="text" id="otp" name="otp" placeholder="000000" inputmode="numeric" pattern="\d{6}" maxlength="6" required autocomplete="one-time-code" class="otp-input {{ $errors->has('otp') ? 'error' : '' }}"/>
        </div>
        @error('otp')<p class="field-error">{{ $message }}</p>@enderror
        <p class="field-hint">6-digit code — check your inbox (and spam folder)</p>
      </div>

      <div class="field">
        <label for="password">New Password</label>
        <div class="input-wrap">
          <svg class="prefix" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
          <input type="password" id="password" name="password" placeholder="Min 12 chars, mixed case, number, symbol" required autocomplete="new-password" class="{{ $errors->has('password') ? 'error' : '' }}"/>
          <button class="suffix" type="button" onclick="togglePwd('password','eye1')" aria-label="Toggle">
            <svg id="eye1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        @error('password')<p class="field-error">{{ $message }}</p>@enderror
        <p class="field-hint">Min 12 characters · uppercase &amp; lowercase · number · symbol</p>
      </div>

      <div class="field">
        <label for="password_confirmation">Confirm New Password</label>
        <div class="input-wrap">
          <svg class="prefix" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
          <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repeat new password" required autocomplete="new-password"/>
          <button class="suffix" type="button" onclick="togglePwd('password_confirmation','eye2')" aria-label="Toggle">
            <svg id="eye2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-primary">Reset Password</button>
    </form>

    <p class="back-link">
      Didn't get a code? <a href="{{ route('password.request') }}">Request a new one</a>
    </p>
  </div>
  <div class="right-footer">
    <span class="copy">© {{ date('Y') }} ChatBot Nepal by iSoftro</span>
  </div>
</div>

<script>
function togglePwd(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon  = document.getElementById(iconId);
  const btn   = icon.closest('button');
  const show  = input.type === 'password';
  input.type  = show ? 'text' : 'password';
  btn.classList.toggle('active', show);
  icon.innerHTML = show
    ? '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
    : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
}
</script>
</body>
</html>
