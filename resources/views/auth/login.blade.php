<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Sign In — ChatBot Nepal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    min-height: 100vh;
    display: flex;
    background: #ffffff;
  }

  /* ── LEFT PANEL ── */
  .left {
    flex: 1;
    background: linear-gradient(160deg, #f0fdf7 0%, #ffffff 60%, #f0fdf7 100%);
    padding: 48px 56px;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
    min-height: 100vh;
  }

  .left::before {
    content: '';
    position: absolute;
    top: -120px; right: -120px;
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(24,226,153,0.12) 0%, transparent 70%);
    pointer-events: none;
  }

  /* LOGO */
  .logo-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 72px;
  }

  .logo-icon {
    width: 40px; height: 40px;
    background: #0d0d0d;
    border-radius: 9999px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }

  .logo-icon svg { width: 20px; height: 20px; }

  .logo-name {
    font-size: 18px;
    font-weight: 600;
    color: #0d0d0d;
    letter-spacing: -0.3px;
  }

  /* HERO TEXT */
  .hero-heading {
    font-size: 40px;
    font-weight: 600;
    line-height: 1.10;
    color: #0d0d0d;
    letter-spacing: -0.8px;
    margin-bottom: 16px;
  }

  .hero-sub {
    font-size: 18px;
    color: #666666;
    line-height: 1.50;
    max-width: 400px;
    margin-bottom: 56px;
  }

  /* FEATURES */
  .features {
    display: flex;
    flex-direction: column;
    gap: 24px;
    flex: 1;
  }

  .feature {
    display: flex;
    align-items: flex-start;
    gap: 14px;
  }

  .feature-icon {
    width: 40px; height: 40px;
    background: #fafafa;
    border: 1px solid rgba(0,0,0,0.05);
    border-radius: 12px;
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
  }

  .feature-icon svg { width: 18px; height: 18px; stroke: #0d0d0d; }

  .feature-text strong {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #0d0d0d;
    margin-bottom: 2px;
  }

  .feature-text span {
    font-size: 13px;
    color: #888888;
    line-height: 1.5;
  }

  /* TRUSTED BY */
  .trusted {
    margin-top: auto;
    padding-top: 40px;
    border-top: 1px solid rgba(0,0,0,0.05);
  }

  .trusted-inner {
    display: flex;
    align-items: center;
    gap: 14px;
  }

  .avatars {
    display: flex;
  }

  .avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    border: 2px solid #ffffff;
    background: #18E299;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px;
    font-weight: 600;
    color: #0d0d0d;
    margin-left: -6px;
    flex-shrink: 0;
  }

  .avatar:first-child { margin-left: 0; }
  .avatar:nth-child(2) { background: #d4fae8; }
  .avatar:nth-child(3) { background: #0fa76e; color: #fff; }

  .trusted-text {
    font-size: 13px;
    color: #888888;
  }

  .trusted-text strong {
    color: #0d0d0d;
    font-weight: 600;
  }

  /* ── RIGHT PANEL ── */
  .right {
    width: 480px;
    flex-shrink: 0;
    background: #ffffff;
    border-left: 1px solid rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

  .right-inner {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 56px 48px;
  }

  .card-title {
    font-size: 28px;
    font-weight: 600;
    color: #0d0d0d;
    letter-spacing: -0.56px;
    margin-bottom: 6px;
  }

  .card-sub {
    font-size: 15px;
    color: #666666;
    margin-bottom: 32px;
    line-height: 1.5;
  }

  /* ERROR ALERT */
  .alert-error {
    background: #fef2f2;
    border: 1px solid rgba(212,92,86,0.15);
    border-radius: 9999px;
    padding: 10px 16px;
    font-size: 14px;
    color: #d45656;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .alert-error svg { width: 16px; height: 16px; flex-shrink: 0; }

  /* FORM FIELDS */
  .field { margin-bottom: 18px; }

  .field label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #333333;
    margin-bottom: 6px;
  }

  .input-wrap {
    position: relative;
    display: flex;
    align-items: center;
  }

  .input-wrap svg.prefix {
    position: absolute; left: 14px;
    width: 16px; height: 16px;
    color: #888888;
    pointer-events: none;
    flex-shrink: 0;
  }

  .input-wrap input {
    width: 100%;
    background: #fafafa;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 9999px;
    padding: 12px 44px;
    font-size: 15px;
    color: #0d0d0d;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    font-family: inherit;
  }

  .input-wrap input::placeholder { color: #888888; }

  .input-wrap input:focus {
    border-color: #18E299;
    box-shadow: 0 0 0 1px #18E299;
  }

  .input-wrap input.error {
    border-color: #d45656;
    box-shadow: 0 0 0 1px rgba(212,92,86,0.3);
  }

  .input-wrap .suffix {
    position: absolute; right: 12px;
    background: none; border: none;
    cursor: pointer; padding: 4px;
    display: flex; align-items: center;
    border-radius: 4px;
    color: #888888;
    transition: color 0.2s;
  }

  .input-wrap .suffix:hover { color: #0d0d0d; }
  .input-wrap .suffix.active { color: #18E299; }
  .input-wrap .suffix svg { width: 18px; height: 18px; }

  /* REMEMBER + FORGOT */
  .row-mid {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
  }

  .remember {
    display: flex; align-items: center; gap: 8px;
    cursor: pointer;
  }

  .remember input[type="checkbox"] {
    width: 16px; height: 16px;
    accent-color: #18E299;
    cursor: pointer;
    border-radius: 4px;
  }

  .remember span {
    font-size: 14px;
    color: #333333;
    user-select: none;
  }

  .forgot {
    font-size: 14px;
    font-weight: 500;
    color: #18E299;
    text-decoration: none;
    transition: color 0.2s;
  }
  .forgot:hover { color: #0fa76e; }

  /* SIGN IN BUTTON */
  .btn-signin {
    width: 100%;
    padding: 13px 24px;
    background: #0d0d0d;
    border: none;
    border-radius: 9999px;
    font-size: 15px;
    font-weight: 500;
    color: #ffffff;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.1s;
    margin-bottom: 24px;
    font-family: inherit;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: rgba(0,0,0,0.06) 0px 1px 2px;
  }

  .btn-signin:hover {
    opacity: 0.9;
  }

  .btn-signin:active { transform: scale(0.99); }

  .btn-signin svg { width: 18px; height: 18px; }

  /* DEMO LINK */
  .demo-row {
    text-align: center;
    font-size: 14px;
    color: #666666;
  }

  .demo-row a {
    color: #18E299;
    font-weight: 500;
    text-decoration: none;
  }

  .demo-row a:hover { color: #0fa76e; text-decoration: underline; }

  /* RIGHT FOOTER */
  .right-footer {
    padding: 16px 48px;
    border-top: 1px solid rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
  }

  .right-footer .copy {
    font-size: 12px;
    color: #888888;
  }

  .right-footer .footer-links {
    display: flex;
    gap: 16px;
  }

  .right-footer .footer-links a {
    font-size: 12px;
    font-weight: 500;
    color: #888888;
    text-decoration: none;
    transition: color 0.2s;
  }

  .right-footer .footer-links a:hover { color: #18E299; }

  /* MOBILE */
  @media (max-width: 900px) {
    body { flex-direction: column; }
    .left { min-height: auto; padding: 40px 32px; }
    .hero-heading { font-size: 32px; }
    .trusted { display: none; }
    .right { width: 100%; min-height: auto; border-left: none; border-top: 1px solid rgba(0,0,0,0.05); }
    .right-inner { padding: 40px 32px; }
    .right-footer { padding: 16px 32px; }
  }

  @media (max-width: 480px) {
    .left { padding: 32px 24px; }
    .logo-row { margin-bottom: 40px; }
    .hero-heading { font-size: 28px; }
    .hero-sub { margin-bottom: 36px; }
    .right-inner { padding: 32px 24px; }
    .right-footer { padding: 16px 24px; }
  }
</style>
</head>
<body>

<!-- ── LEFT PANEL ── -->
<div class="left">

  <div class="logo-row">
    <div class="logo-icon">
      <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M20 2H4C2.9 2 2 2.9 2 4v12c0 1.1.9 2 2 2h4l4 4 4-4h4c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z" fill="rgba(255,255,255,0.15)"/>
        <path d="M8 10h8M8 14h5" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
      </svg>
    </div>
    <span class="logo-name">ChatBot Nepal</span>
  </div>

  <h2 class="hero-heading">AI Chatbots for Nepali Businesses</h2>
  <p class="hero-sub">Empower your enterprise with conversational AI tailored for Nepal's unique market.</p>

  <div class="features">
    <div class="feature">
      <div class="feature-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <path d="M12 6v6l4 2"/>
        </svg>
      </div>
      <div class="feature-text">
        <strong>24/7 AI Customer Support</strong>
        <span>Automate responses and resolve queries instantly, any time of day.</span>
      </div>
    </div>

    <div class="feature">
      <div class="feature-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <path d="M2 12h20M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/>
        </svg>
      </div>
      <div class="feature-text">
        <strong>Works on Any Website</strong>
        <span>Seamless integration via a single line of code on your existing platform.</span>
      </div>
    </div>

    <div class="feature">
      <div class="feature-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
          <circle cx="12" cy="9" r="2.5"/>
        </svg>
      </div>
      <div class="feature-text">
        <strong>Built for Nepal</strong>
        <span>Optimized for local linguistic nuances and business cultural context.</span>
      </div>
    </div>
  </div>

  <div class="trusted">
    <div class="trusted-inner">
      <div class="avatars">
        <div class="avatar">S</div>
        <div class="avatar">R</div>
        <div class="avatar">A</div>
      </div>
      <p class="trusted-text">Serving businesses across <strong>Nepal</strong></p>
    </div>
  </div>

</div>

<!-- ── RIGHT PANEL ── -->
<div class="right">
  <div class="right-inner">

    <h1 class="card-title">Sign in to your account</h1>
    <p class="card-sub">Enter your credentials to access your dashboard.</p>

    @if($errors->any())
      <div class="alert-error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        {{ $errors->first() }}
      </div>
    @endif

    @if(session('error'))
      <div class="alert-error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        {{ session('error') }}
      </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
      @csrf

      <div class="field">
        <label for="email">Email Address</label>
        <div class="input-wrap">
          <svg class="prefix" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
            <rect x="2" y="4" width="20" height="16" rx="2"/>
            <path d="M2 7l10 7 10-7"/>
          </svg>
          <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email') }}"
            placeholder="name@company.com"
            autocomplete="email"
            required
            autofocus
            class="{{ $errors->has('email') ? 'error' : '' }}"
          />
        </div>
      </div>

      <div class="field">
        <label for="pwd">Password</label>
        <div class="input-wrap">
          <svg class="prefix" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
            <rect x="5" y="11" width="14" height="10" rx="2"/>
            <path d="M8 11V7a4 4 0 018 0v4"/>
          </svg>
          <input
            type="password"
            id="pwd"
            name="password"
            placeholder="Enter your password"
            autocomplete="current-password"
            required
            class="{{ $errors->has('password') ? 'error' : '' }}"
          />
          <button class="suffix" type="button" onclick="togglePwd()" aria-label="Toggle password visibility">
            <svg id="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>
      </div>

      <div class="row-mid">
        <label class="remember">
          <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} />
          <span>Remember me</span>
        </label>
        <a href="{{ route('password.request') }}" class="forgot">Forgot password?</a>
      </div>

      <button type="submit" class="btn-signin">
        Sign In
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
      </button>

    </form>

    <div class="demo-row">
      Don't have an account? <a href="https://wa.me/9779811144402" target="_blank">Request a demo</a>
    </div>

  </div>

  <div class="right-footer">
    <span class="copy">&copy; 2026 ChatBot Nepal by iSoftro</span>
    <div class="footer-links">
      <a href="{{ route('privacy-policy') }}">Privacy</a>
      <a href="{{ route('terms') }}">Terms</a>
      <a href="https://wa.me/9779811144402" target="_blank">Support</a>
    </div>
  </div>
</div>

<script>
  function togglePwd() {
    const input = document.getElementById('pwd');
    const icon  = document.getElementById('eye-icon');
    const btn   = icon.closest('button');
    const showing = input.type === 'text';
    input.type = showing ? 'password' : 'text';
    btn.classList.toggle('active', !showing);
    icon.innerHTML = showing
      ? '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'
      : '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
  }
</script>

</body>
</html>
