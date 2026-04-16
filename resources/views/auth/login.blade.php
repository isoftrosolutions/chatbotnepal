<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Sign In — ChatBot Nepal</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
    min-height: 100vh;
    display: flex;
    background: #0f0e2a;
  }

  /* ── LEFT PANEL ── */
  .left {
    flex: 1;
    background: linear-gradient(160deg, #1e1b4b 0%, #1a1a35 40%, #0f0e2a 100%);
    padding: 48px 56px;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
    min-height: 100vh;
  }

  /* subtle radial glow top-right */
  .left::before {
    content: '';
    position: absolute;
    top: -80px; right: -80px;
    width: 360px; height: 360px;
    background: radial-gradient(circle, rgba(99,102,241,0.18) 0%, transparent 70%);
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
    width: 48px; height: 48px;
    background: #4f46e5;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }

  .logo-icon svg { width: 26px; height: 26px; }

  .logo-name {
    font-size: 20px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: -0.3px;
  }

  /* HERO TEXT */
  .hero-heading {
    font-size: 46px;
    font-weight: 800;
    line-height: 1.12;
    color: #ffffff;
    margin-bottom: 20px;
    letter-spacing: -0.5px;
  }

  .hero-sub {
    font-size: 15px;
    color: rgba(255,255,255,0.5);
    line-height: 1.65;
    max-width: 400px;
    margin-bottom: 56px;
  }

  /* FEATURES */
  .features {
    display: flex;
    flex-direction: column;
    gap: 28px;
    flex: 1;
  }

  .feature {
    display: flex;
    align-items: flex-start;
    gap: 16px;
  }

  .feature-icon {
    width: 42px; height: 42px;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px;
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
  }

  .feature-icon svg { width: 19px; height: 19px; stroke: rgba(255,255,255,0.75); }

  .feature-text strong {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 3px;
  }

  .feature-text span {
    font-size: 13px;
    color: rgba(255,255,255,0.42);
    line-height: 1.5;
  }

  /* TRUSTED BY */
  .trusted {
    margin-top: auto;
    padding-top: 40px;
    border-top: 1px solid rgba(255,255,255,0.08);
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
    width: 36px; height: 36px;
    border-radius: 50%;
    border: 2px solid #1e1b4b;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    display: flex; align-items: center; justify-content: center;
    font-size: 12px;
    font-weight: 700;
    color: #fff;
    margin-left: -8px;
    flex-shrink: 0;
  }

  .avatar:first-child { margin-left: 0; background: linear-gradient(135deg, #ec4899, #8b5cf6); }
  .avatar:nth-child(2) { background: linear-gradient(135deg, #3b82f6, #6366f1); }
  .avatar:nth-child(3) { background: linear-gradient(135deg, #10b981, #3b82f6); }

  .trusted-text {
    font-size: 13.5px;
    color: rgba(255,255,255,0.5);
  }

  .trusted-text strong {
    color: #ffffff;
    font-weight: 700;
  }

  /* ── RIGHT PANEL ── */
  .right {
    width: 500px;
    flex-shrink: 0;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

  .right-inner {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 56px 52px;
  }

  .card-title {
    font-size: 28px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.4px;
    margin-bottom: 6px;
  }

  .card-sub {
    font-size: 14px;
    color: #64748b;
    margin-bottom: 36px;
    line-height: 1.5;
  }

  /* ERROR ALERT */
  .alert-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    padding: 12px 14px;
    font-size: 13.5px;
    color: #dc2626;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .alert-error svg { width: 16px; height: 16px; flex-shrink: 0; }

  /* FORM FIELDS */
  .field { margin-bottom: 20px; }

  .field label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.1px;
    text-transform: uppercase;
    color: #374151;
    margin-bottom: 8px;
  }

  .input-wrap {
    position: relative;
    display: flex;
    align-items: center;
  }

  .input-wrap svg.prefix {
    position: absolute; left: 14px;
    width: 16px; height: 16px;
    color: #9ca3af;
    pointer-events: none;
    flex-shrink: 0;
  }

  .input-wrap input {
    width: 100%;
    background: #ffffff;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    padding: 13px 44px;
    font-size: 15px;
    color: #111827;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    font-family: inherit;
  }

  .input-wrap input::placeholder { color: #9ca3af; }

  .input-wrap input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
  }

  .input-wrap input.error {
    border-color: #f87171;
    box-shadow: 0 0 0 3px rgba(248,113,113,0.1);
  }

  .input-wrap .suffix {
    position: absolute; right: 12px;
    background: none; border: none;
    cursor: pointer; padding: 4px;
    display: flex; align-items: center;
    border-radius: 4px;
    color: #9ca3af;
    transition: color 0.2s;
  }

  .input-wrap .suffix:hover { color: #6366f1; }
  .input-wrap .suffix.active { color: #6366f1; }
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
    accent-color: #4f46e5;
    cursor: pointer;
    border-radius: 4px;
  }

  .remember span {
    font-size: 14px;
    color: #374151;
    user-select: none;
  }

  .forgot {
    font-size: 14px;
    font-weight: 600;
    color: #4f46e5;
    text-decoration: none;
    transition: color 0.2s;
  }
  .forgot:hover { color: #4338ca; text-decoration: underline; }

  /* SIGN IN BUTTON */
  .btn-signin {
    width: 100%;
    padding: 14px;
    background: #4f46e5;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 700;
    color: #ffffff;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
    margin-bottom: 28px;
    font-family: inherit;
    letter-spacing: 0.1px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  .btn-signin:hover {
    background: #4338ca;
    box-shadow: 0 4px 16px rgba(79,70,229,0.35);
  }

  .btn-signin:active { transform: scale(0.99); }

  .btn-signin svg { width: 18px; height: 18px; }

  /* OR DIVIDER */
  .divider {
    display: flex; align-items: center; gap: 14px;
    margin-bottom: 20px;
  }

  .divider hr { flex: 1; border: none; border-top: 1.5px solid #f1f5f9; }
  .divider span {
    font-size: 11px;
    letter-spacing: 1.2px;
    color: #94a3b8;
    text-transform: uppercase;
    white-space: nowrap;
    font-weight: 600;
  }

  /* SOCIAL BUTTONS */
  .social-row {
    display: flex; gap: 12px;
    margin-bottom: 28px;
  }

  .btn-social {
    flex: 1;
    display: flex; align-items: center; justify-content: center;
    gap: 8px;
    padding: 11px;
    background: #ffffff;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    cursor: not-allowed;
    opacity: 0.7;
    font-family: inherit;
    transition: border-color 0.2s;
  }

  .btn-social .social-icon { width: 18px; height: 18px; }

  /* DEMO LINK */
  .demo-row {
    text-align: center;
    font-size: 14px;
    color: #64748b;
  }

  .demo-row a {
    color: #4f46e5;
    font-weight: 600;
    text-decoration: none;
  }

  .demo-row a:hover { text-decoration: underline; }

  /* RIGHT FOOTER */
  .right-footer {
    padding: 20px 52px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
  }

  .right-footer .copy {
    font-size: 11px;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .right-footer .footer-links {
    display: flex;
    gap: 16px;
  }

  .right-footer .footer-links a {
    font-size: 11px;
    font-weight: 600;
    color: #94a3b8;
    text-decoration: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: color 0.2s;
  }

  .right-footer .footer-links a:hover { color: #4f46e5; }

  /* MOBILE */
  @media (max-width: 900px) {
    body { flex-direction: column; }
    .left { min-height: auto; padding: 40px 32px; }
    .hero-heading { font-size: 34px; }
    .trusted { display: none; }
    .right { width: 100%; min-height: auto; }
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

  <h2 class="hero-heading">AI-Powered Chatbots for Nepali Businesses</h2>
  <p class="hero-sub">Empower your enterprise with sophisticated conversational AI tailored specifically for the unique market dynamics of Nepal.</p>

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
      <p class="trusted-text">Trusted by <strong>50+ businesses</strong> across Nepal</p>
    </div>
  </div>

</div>

<!-- ── RIGHT PANEL ── -->
<div class="right">
  <div class="right-inner">

    <h1 class="card-title">Sign in to your account</h1>
    <p class="card-sub">Enter your credentials to access the manager.</p>

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
            placeholder="••••••••"
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

    <div class="divider">
      <hr/><span>Or continue with</span><hr/>
    </div>

    <div class="social-row">
      <button class="btn-social" disabled title="Coming soon">
        <svg class="social-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
          <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
          <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
          <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
        </svg>
        Google
      </button>
      <button class="btn-social" disabled title="Coming soon">
        <svg class="social-icon" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="1.8" stroke-linecap="round">
          <circle cx="12" cy="12" r="10"/>
          <path d="M12 8v4M12 16h.01"/>
        </svg>
        SSO
      </button>
    </div>

    <div class="demo-row">
      Don't have an account? <a href="https://wa.me/9779811144402" target="_blank">Request a demo</a>
    </div>

  </div>

  <div class="right-footer">
    <span class="copy">© 2026 ChatBot Nepal by iSoftro</span>
    <div class="footer-links">
      <a href="#">Privacy</a>
      <a href="#">Terms</a>
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
