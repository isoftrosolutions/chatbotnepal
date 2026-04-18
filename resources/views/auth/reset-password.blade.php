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
  .alert-success { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:12px 14px; font-size:13.5px; color:#15803d; margin-bottom:24px; display:flex; align-items:center; gap:8px; }
  .alert-success svg, .alert-error svg { width:16px; height:16px; flex-shrink:0; }
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
  .password-strength { margin-top:8px; display:none; }
  .password-strength.visible { display:block; }
  .strength-bar { height:4px; border-radius:2px; background:#e5e7eb; overflow:hidden; margin-bottom:6px; }
  .strength-fill { height:100%; width:0%; transition:width 0.3s,background 0.3s; border-radius:2px; }
  .strength-fill.weak { width:25%; background:#ef4444; }
  .strength-fill.fair { width:50%; background:#f59e0b; }
  .strength-fill.good { width:75%; background:#10b981; }
  .strength-fill.strong { width:100%; background:#059669; }
  .strength-text { font-size:11px; font-weight:600; }
  .strength-text.weak { color:#ef4444; }
  .strength-text.fair { color:#f59e0b; }
  .strength-text.good { color:#10b981; }
  .strength-text.strong { color:#059669; }
  .requirements-list { font-size:11px; color:#6b7280; margin-top:8px; }
  .requirements-list li { margin-bottom:3px; display:flex; align-items:center; gap:6px; }
  .requirements-list .check { width:14px; height:14px; border-radius:50%; background:#e5e7eb; display:flex; align-items:center; justify-content:center; font-size:9px; color:#fff; transition:background 0.2s; }
  .requirements-list .check.met { background:#10b981; }
  .btn-primary { width:100%; padding:14px; background:#4f46e5; border:none; border-radius:8px; font-size:15px; font-weight:700; color:#fff; cursor:pointer; transition:background 0.2s,box-shadow 0.2s; margin-bottom:20px; font-family:inherit; }
  .btn-primary:hover { background:#4338ca; box-shadow:0 4px 16px rgba(79,70,229,0.35); }
  .back-link { text-align:center; font-size:14px; color:#64748b; }
  .back-link a { color:#4f46e5; font-weight:600; text-decoration:none; }
  .back-link a:hover { text-decoration:underline; }
  .resend-timer { font-size:13px; color:#6b7280; margin-top:8px; }
  .resend-timer span { font-weight:600; color:#4f46e5; }
  .resend-link { display:inline-block; }
  .resend-link.disabled { color:#9ca3af; pointer-events:none; }
  .otp-fields { display:flex; gap:8px; justify-content:center; margin-bottom:4px; }
  .otp-fields input { width:48px; height:52px; text-align:center; font-size:22px; font-weight:700; font-family:'Courier New',monospace; border:1.5px solid #e5e7eb; border-radius:8px; outline:none; transition:border-color 0.2s,box-shadow 0.2s; }
  .otp-fields input:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,0.12); }
  .otp-fields input.error { border-color:#f87171; box-shadow:0 0 0 3px rgba(248,113,113,0.1); }
  .otp-fields input.filled { border-color:#10b981; background:#f0fdf4; }
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
    @if(session('status'))
      <div class="alert-success" style="margin-bottom:20px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        {{ session('status') }}
      </div>
    @endif

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
          <input type="email" id="email" name="email" value="{{ old('email', session('email', $email)) }}" placeholder="name@company.com" required autocomplete="email" class="{{ $errors->has('email') ? 'error' : '' }}"/>
        </div>
        @error('email')<p class="field-error">{{ $message }}</p>@enderror
      </div>

      <div class="field">
        <label for="otp">Verification Code</label>
        <div class="otp-fields">
          <input type="text" id="otp1" maxlength="1" inputmode="numeric" pattern="\d" oninput="handleOtpInput(this, 'otp2')" onkeydown="handleOtpKeydown(event, this, null)"/>
          <input type="text" id="otp2" maxlength="1" inputmode="numeric" pattern="\d" oninput="handleOtpInput(this, 'otp3')" onkeydown="handleOtpKeydown(event, this, 'otp1')"/>
          <input type="text" id="otp3" maxlength="1" inputmode="numeric" pattern="\d" oninput="handleOtpInput(this, 'otp4')" onkeydown="handleOtpKeydown(event, this, 'otp2')"/>
          <input type="text" id="otp4" maxlength="1" inputmode="numeric" pattern="\d" oninput="handleOtpInput(this, 'otp5')" onkeydown="handleOtpKeydown(event, this, 'otp3')"/>
          <input type="text" id="otp5" maxlength="1" inputmode="numeric" pattern="\d" oninput="handleOtpInput(this, 'otp6')" onkeydown="handleOtpKeydown(event, this, 'otp4')"/>
          <input type="text" id="otp6" maxlength="1" inputmode="numeric" pattern="\d" oninput="handleOtpInput(this, null)" onkeydown="handleOtpKeydown(event, this, 'otp5')"/>
        </div>
        <input type="hidden" name="otp" id="otp-hidden"/>
        @error('otp')<p class="field-error">{{ $message }}</p>@enderror
        <p class="field-hint">6-digit code — check your inbox (and spam folder)</p>
      </div>

      <div class="field">
        <label for="password">New Password</label>
        <div class="input-wrap">
          <svg class="prefix" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
          <input type="password" id="password" name="password" placeholder="Min 12 chars, mixed case, number, symbol" required autocomplete="new-password" class="{{ $errors->has('password') ? 'error' : '' }}" oninput="checkPasswordStrength()"/>
          <button class="suffix" type="button" onclick="togglePwd('password','eye1')" aria-label="Toggle">
            <svg id="eye1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        @error('password')<p class="field-error">{{ $message }}</p>@enderror
        <div class="password-strength" id="strength-meter">
          <div class="strength-bar"><div class="strength-fill" id="strength-fill"></div></div>
          <span class="strength-text" id="strength-text"></span>
        </div>
        <ul class="requirements-list">
          <li><span class="check" id="req-len">✓</span> At least 12 characters</li>
          <li><span class="check" id="req-upper">✓</span> Uppercase letter (A-Z)</li>
          <li><span class="check" id="req-lower">✓</span> Lowercase letter (a-z)</li>
          <li><span class="check" id="req-num">✓</span> Number (0-9)</li>
          <li><span class="check" id="req-sym">✓</span> Symbol (!@#$%^&*)</li>
        </ul>
      </div>

      <div class="field">
        <label for="password_confirmation">Confirm New Password</label>
        <div class="input-wrap">
          <svg class="prefix" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
          <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repeat new password" required autocomplete="new-password" oninput="checkPasswordMatch()"/>
          <button class="suffix" type="button" onclick="togglePwd('password_confirmation','eye2')" aria-label="Toggle">
            <svg id="eye2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <p class="field-hint" id="match-hint"></p>
      </div>

      <button type="submit" class="btn-primary">Reset Password</button>
    </form>

    <p class="back-link">
      Didn't get a code? <a href="{{ route('password.request') }}" id="resend-link" class="resend-link">Request a new one</a>
    </p>
    <p class="resend-timer" id="resend-timer"></p>
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

function checkPasswordStrength() {
  const pwd = document.getElementById('password').value;
  const meter = document.getElementById('strength-meter');
  const fill = document.getElementById('strength-fill');
  const text = document.getElementById('strength-text');
  
  const hasLen = pwd.length >= 12;
  const hasUpper = /[A-Z]/.test(pwd);
  const hasLower = /[a-z]/.test(pwd);
  const hasNum = /[0-9]/.test(pwd);
  const hasSym = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(pwd);
  
  document.getElementById('req-len').classList.toggle('met', hasLen);
  document.getElementById('req-upper').classList.toggle('met', hasUpper);
  document.getElementById('req-lower').classList.toggle('met', hasLower);
  document.getElementById('req-num').classList.toggle('met', hasNum);
  document.getElementById('req-sym').classList.toggle('met', hasSym);
  
  const score = [hasLen, hasUpper, hasLower, hasNum, hasSym].filter(Boolean).length;
  
  if (pwd.length === 0) {
    meter.classList.remove('visible');
    fill.className = 'strength-fill';
    return;
  }
  
  meter.classList.add('visible');
  
  if (score <= 2) {
    fill.className = 'strength-fill weak';
    text.className = 'strength-text weak';
    text.textContent = 'Weak';
  } else if (score === 3) {
    fill.className = 'strength-fill fair';
    text.className = 'strength-text fair';
    text.textContent = 'Fair';
  } else if (score === 4) {
    fill.className = 'strength-fill good';
    text.className = 'strength-text good';
    text.textContent = 'Good';
  } else {
    fill.className = 'strength-fill strong';
    text.className = 'strength-text strong';
    text.textContent = 'Strong';
  }
}

function checkPasswordMatch() {
  const pwd = document.getElementById('password').value;
  const confirm = document.getElementById('password_confirmation').value;
  const hint = document.getElementById('match-hint');
  
  if (confirm.length === 0) {
    hint.textContent = '';
    return;
  }
  
  if (pwd === confirm) {
    hint.innerHTML = '<span style="color:#10b981;">✓ Passwords match</span>';
  } else {
    hint.innerHTML = '<span style="color:#ef4444;">✗ Passwords do not match</span>';
  }
}

function handleOtpInput(current, nextId) {
  const val = current.value;
  if (!/^\d*$/.test(val)) {
    current.value = '';
    return;
  }
  current.classList.toggle('filled', val.length === 1);
  if (val && nextId) {
    document.getElementById(nextId).focus();
  }
  updateHiddenOtp();
}

function handleOtpKeydown(e, current, prevId) {
  if (e.key === 'Backspace' && !current.value && prevId) {
    document.getElementById(prevId).focus();
  }
  updateHiddenOtp();
}

function updateHiddenOtp() {
  let otp = '';
  for (let i = 1; i <= 6; i++) {
    otp += document.getElementById('otp' + i).value;
  }
  document.getElementById('otp-hidden').value = otp;
}

function startResendTimer() {
  const link = document.getElementById('resend-link');
  const timer = document.getElementById('resend-timer');
  let seconds = 60;
  link.classList.add('disabled');
  timer.innerHTML = 'Resend code in <span id="timer-seconds">60</span> seconds';
  
  const interval = setInterval(() => {
    seconds--;
    const span = document.getElementById('timer-seconds');
    if (span) span.textContent = seconds;
    if (seconds <= 0) {
      clearInterval(interval);
      link.classList.remove('disabled');
      timer.innerHTML = '';
    }
  }, 1000);
}

document.addEventListener('DOMContentLoaded', function() {
  // Pre-fill email from URL if not already set
  const emailInput = document.getElementById('email');
  const urlParams = new URLSearchParams(window.location.search);
  const emailFromUrl = urlParams.get('email');
  if (emailFromUrl && !emailInput.value) {
    emailInput.value = emailFromUrl;
  }
  
  startResendTimer();
  
  // Focus first OTP field if there's an email
  if (emailInput.value) {
    document.getElementById('otp1').focus();
  }
});
</script>
</body>
</html>
