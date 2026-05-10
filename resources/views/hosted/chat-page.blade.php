<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $title }}</title>
  <meta property="og:title" content="{{ $title }}" />
  <meta property="og:description" content="{{ $ogDescription }}" />
  <style>
    :root {
      --brand-primary: {{ $brandPrimary }};
      --brand-bg: {{ $brandBg }};
      --brand-font: {{ $brandFont }};
      --text: #141426;
      --muted: #6b7085;
      --line: #e6e8ef;
      --white: #ffffff;
      --shadow: 0 14px 40px rgba(17, 24, 39, 0.12);
    }

    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: var(--brand-font);
      background: linear-gradient(180deg, #f7f8ff 0%, var(--brand-bg) 100%);
      color: var(--text);
    }

    .page { max-width: 1280px; margin: 24px auto; padding: 0 16px; }
    .top { text-align: center; margin-bottom: 14px; }
    .top h1 { margin: 0 0 12px; font-size: 38px; }
    .url {
      display: inline-block;
      border: 1px solid #ccc3ff;
      border-radius: 14px;
      padding: 10px 18px;
      color: #4427c9;
      font-weight: 600;
      background: #fdfcff;
    }
    .share-row { margin-top: 10px; display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; transition: opacity .25s ease; }
    .share-row.hidden { opacity: 0; pointer-events: none; }
    .share-btn {
      border: 1px solid #dddff2;
      background: #fff;
      color: #2b2f48;
      border-radius: 10px;
      padding: 8px 12px;
      font-size: 13px;
      font-weight: 600;
      text-decoration: none;
    }
    .share-btn svg { width: 14px; height: 14px; vertical-align: -2px; margin-right: 6px; }

    .shell {
      background: var(--white);
      border: 1px solid var(--line);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: var(--shadow);
    }

    .bar {
      background: linear-gradient(120deg, #351469 0%, #231048 100%);
      color: #fff;
      padding: 16px 22px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .brand { display: flex; gap: 12px; align-items: center; }
    .brand img { width: 42px; height: 42px; border-radius: 10px; object-fit: cover; }
    .online { font-size: 14px; display: flex; align-items: center; gap: 8px; }
    .dot { width: 9px; height: 9px; border-radius: 50%; background: #3ee36c; }

    .content { display: grid; grid-template-columns: 310px 1fr 290px; min-height: 640px; }
    .left, .center, .right { padding: 18px; }
    .left { background: #f9f8ff; border-right: 1px solid var(--line); }
    .right { background: #fbfbff; border-left: 1px solid var(--line); }

    .welcome-image { width: 100%; border-radius: 12px; height: 170px; object-fit: cover; border: 1px solid #d7d9ea; }
    .left h3 { margin: 16px 0 8px; font-size: 30px; }
    .left p { margin: 0 0 12px; color: var(--muted); line-height: 1.45; font-size: 16px; }

    .quick { margin-top: 16px; font-weight: 700; color: #3d2ea3; font-size: 18px; }
    .qa { margin-top: 10px; display: grid; gap: 10px; }
    .qa button {
      width: 100%; text-align: left; padding: 12px 14px; border: 1px solid #e0e1f3;
      background: #fff; border-radius: 11px; cursor: pointer; font-size: 16px;
    }

    .messages {
      height: 484px;
      overflow: auto;
      padding: 6px 2px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .msg {
      max-width: 86%;
      padding: 12px 14px;
      border-radius: 14px;
      white-space: pre-wrap;
      line-height: 1.4;
      font-size: 15px;
    }
    .bot { background: #fff; border: 1px solid var(--line); }
    .user {
      background: linear-gradient(135deg, #7d58f8 0%, var(--brand-primary) 100%);
      color: #fff;
      margin-left: auto;
    }
    .typing { color: var(--muted); font-size: 14px; display: none; padding: 4px 2px; }
    .btn-row { display: flex; flex-wrap: wrap; gap: 8px; margin: 6px 0 10px; }
    .chat-btn {
      border: 1px solid #ccd0ea;
      background: #fff;
      border-radius: 10px;
      padding: 7px 10px;
      font-size: 13px;
      cursor: pointer;
      color: #2c2f42;
      text-decoration: none;
      display: inline-block;
    }

    .composer {
      margin-top: 12px;
      display: flex;
      border: 1px solid #d8d9ea;
      border-radius: 12px;
      overflow: hidden;
      background: #fff;
    }
    .composer input {
      flex: 1;
      border: none;
      outline: none;
      padding: 13px;
      font-size: 15px;
    }
    .composer button {
      width: 54px;
      border: none;
      background: var(--brand-primary);
      color: #fff;
      font-size: 20px;
      cursor: pointer;
    }

    .lead-card {
      border: 1px solid #e4e5f4;
      border-radius: 14px;
      background: #fff;
      padding: 16px;
    }
    .lead-card.floating {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: min(92vw, 360px);
      z-index: 1200;
      box-shadow: 0 20px 56px rgba(14, 17, 32, 0.28);
    }
    .lead-card.hidden { display: none; }
    .lead-card h4 { margin: 0 0 6px; font-size: 24px; }
    .lead-card p { margin: 0 0 14px; color: var(--muted); font-size: 14px; }
    .lead-card input {
      width: 100%;
      margin-bottom: 10px;
      border: 1px solid #d8daeb;
      border-radius: 10px;
      padding: 11px;
      font-size: 14px;
    }
    .lead-card button {
      width: 100%;
      background: var(--brand-primary);
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 11px;
      font-weight: 700;
      cursor: pointer;
    }
    .lead-note { margin-top: 8px; text-align: center; color: var(--muted); font-size: 12px; }
    .lead-skip {
      margin-top: 8px;
      width: 100%;
      background: #fff;
      border: 1px solid #d8daeb;
      border-radius: 10px;
      padding: 10px;
      cursor: pointer;
      font-weight: 600;
      color: #2c2f42;
    }

    .footer { padding: 12px 18px; text-align: center; color: var(--muted); border-top: 1px solid var(--line); background: #fff; }

    @media (max-width: 1120px) {
      .content { grid-template-columns: 1fr; }
      .left, .right { border: none; border-top: 1px solid var(--line); }
      .messages { height: 390px; }
    }
    @media (max-width: 768px) {
      .page { margin: 10px auto; padding: 0 8px; }
      .top h1 { font-size: 24px; }
      .share-row { display: none !important; }
      .bar { padding: 12px; }
      .content { min-height: auto; }
      .left, .center, .right { padding: 12px; }
      .left h3 { font-size: 24px; }
      .messages { height: 300px; }
      .composer input { font-size: 14px; padding: 11px; }
      .composer button { width: 46px; font-size: 16px; }
      .url { font-size: 12px; padding: 8px 10px; word-break: break-all; }
      .right { order: 3; }
      .center { order: 2; }
      .left { order: 1; }
      .msg { max-width: 94%; font-size: 14px; }
    }
  </style>
</head>
<body>
<div class="page">
  <div class="top">
    <h1>{{ $title }}</h1>
    <div class="share-row" id="shareRow">
      <a class="share-btn" id="shareFacebook" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 22v-8h2.7l.4-3h-3.1V9.1c0-.9.3-1.5 1.6-1.5h1.7V4.9c-.3 0-1.3-.1-2.4-.1-2.4 0-4 1.5-4 4.2V11H8v3h2.4v8h3.1z"/></svg>Facebook</a>
      <a class="share-btn" id="shareTwitter" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor"><path d="m18.9 2 2.6 3.7-5.7 6.5L22 22h-4.7l-4.6-6.1L7.4 22H2.7l6.1-7L2 2h4.8l4.2 5.7L15.9 2h3z"/></svg>X</a>
      <a class="share-btn" id="shareWhatsapp" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.7 14.9L2 22l5.3-1.4A10 10 0 1 0 12 2Zm5.8 14.1c-.2.6-1.3 1.2-1.8 1.2-.5 0-1.2.2-3.8-1-3.2-1.5-5.2-4.9-5.4-5.1-.2-.2-1.3-1.7-1.3-3.2s.8-2.3 1.1-2.6c.3-.3.6-.4.8-.4h.6c.2 0 .4 0 .6.5.2.6.8 2 .8 2.1.1.2.1.4 0 .6 0 .2-.2.4-.3.5-.2.2-.3.4-.5.6-.1.1-.3.3-.1.6.2.4 1 1.6 2.1 2.5 1.4 1.2 2.5 1.6 2.9 1.8.4.2.6.1.8-.1.2-.3 1-1.1 1.3-1.5.2-.3.5-.3.8-.2.3.1 2 .9 2.3 1 .3.2.5.2.6.4.1.2.1 1-.1 1.6Z"/></svg>WhatsApp</a>
    </div>
  </div>

  <div class="shell">
    <div class="bar">
      <div class="brand">
        @if($logoUrl)
          <img src="{{ $logoUrl }}" alt="logo" />
        @endif
        <div>
          <div style="font-size:24px;font-weight:700">{{ $title }}</div>
          <div style="opacity:.84;font-size:14px">Luxury AI assistant experience</div>
        </div>
      </div>
      <div class="online"><span class="dot"></span>AI Assistant Online</div>
    </div>

    <div class="content">
      <aside class="left">
        @if($logoUrl)
          <img class="welcome-image" src="{{ $logoUrl }}" alt="welcome" />
        @endif
        <h3>Welcome</h3>
        <p>{{ $welcomeMessage }}</p>

        <div class="quick">Quick Actions</div>
        <div class="qa">
          <button class="quick-action-btn" data-prompt="Check room availability">Check Room Availability</button>
          <button class="quick-action-btn" data-prompt="Show room prices">View Room Prices</button>
          <button class="quick-action-btn" data-prompt="Share your location">Hotel Location</button>
        </div>
      </aside>

      <section class="center">
        <div id="messages" class="messages">
          <div class="msg bot">{{ $welcomeMessage }}</div>
          <div id="typing" class="typing">Assistant is typing...</div>
        </div>

        <div class="composer">
          <input id="message" placeholder="Type your message..." />
          <button id="send">></button>
        </div>
        <div id="chatStatus" style="margin-top:8px;color:#6b7085;font-size:12px;">Connecting...</div>
      </section>

      <aside class="right" id="leadAside">
        <div class="lead-card hidden" id="leadCard">
          <h4>Almost there!</h4>
          <p>Please share your details so we can assist you better.</p>
          <input id="leadName" placeholder="Your name" />
          <input id="leadPhone" placeholder="Phone number" />
          <input id="leadEmail" placeholder="Email (optional)" />
          <button id="leadSubmit">Submit</button>
          <button id="leadSkip" class="lead-skip" type="button">Skip</button>
          <div class="lead-note">You can continue chatting after this.</div>
        </div>
      </aside>
    </div>

    <div class="footer">We typically reply instantly</div>
  </div>
</div>

<script>
let sessionId = null;
let sessionToken = null;
let messageCount = 0;
let leadCaptureShown = false;
const slug = @json($slug);
const shareUrl = window.location.href;
const messagesEl = document.getElementById('messages');
const typingEl = document.getElementById('typing');
const statusEl = document.getElementById('chatStatus');
const leadCardEl = document.getElementById('leadCard');
const leadAsideEl = document.getElementById('leadAside');
const shareRowEl = document.getElementById('shareRow');
const isMobile = window.matchMedia('(max-width: 768px)').matches;

function getFingerprint() {
  const key = 'hosted_chat_fingerprint_v1';
  let fp = localStorage.getItem(key);
  if (!fp) {
    fp = 'fp_' + Math.random().toString(36).slice(2) + Date.now().toString(36);
    localStorage.setItem(key, fp);
  }
  return fp;
}

function appendMessage(role, text) {
  const div = document.createElement('div');
  div.className = 'msg ' + role;
  div.textContent = text;
  messagesEl.insertBefore(div, typingEl);
  messagesEl.scrollTop = messagesEl.scrollHeight;
}

function appendButtons(buttons) {
  if (!Array.isArray(buttons) || buttons.length === 0) return;
  const row = document.createElement('div');
  row.className = 'btn-row';

  buttons.slice(0, 4).forEach((btn) => {
    if (btn.type === 'reply' && btn.value) {
      const button = document.createElement('button');
      button.className = 'chat-btn';
      button.type = 'button';
      button.textContent = btn.label || 'Option';
      button.addEventListener('click', () => sendMessage(btn.value));
      row.appendChild(button);
      return;
    }

    if (btn.type === 'link' && btn.url) {
      const link = document.createElement('a');
      link.className = 'chat-btn';
      link.href = btn.url;
      link.target = '_blank';
      link.rel = 'noopener';
      link.textContent = btn.label || 'Open';
      row.appendChild(link);
    }
  });

  messagesEl.insertBefore(row, typingEl);
  messagesEl.scrollTop = messagesEl.scrollHeight;
}

async function init() {
  const res = await fetch('/api/chat/init', {
    method: 'POST', headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ slug, visitor_fingerprint: getFingerprint(), source_url: window.location.href })
  });
  const data = await res.json();
  if (data.success) {
    sessionId = data.session_id;
    sessionToken = data.session_token;
    statusEl.textContent = 'Connected';
  } else {
    statusEl.textContent = data.error || 'Connection failed. Refresh to retry.';
  }
}

async function sendMessage(text) {
  if (!sessionId || !sessionToken || !text) {
    statusEl.textContent = 'Not connected yet.';
    return;
  }
  appendMessage('user', text);
  typingEl.style.display = 'block';
  const res = await fetch('/api/chat/message', {
    method: 'POST', headers: {'Content-Type':'application/json'},
    body: JSON.stringify({
      session_id: sessionId,
      session_token: sessionToken,
      visitor_fingerprint: getFingerprint(),
      message: text,
      source_url: window.location.href
    })
  });
  const data = await res.json();
  typingEl.style.display = 'none';
  if (!res.ok || !data.success) {
    statusEl.textContent = data.error || 'Message failed. Please retry.';
    appendMessage('bot', 'Sorry, your message could not be processed. Please try again.');
    return;
  }
  statusEl.textContent = 'Connected';
  appendMessage('bot', data.reply || 'Sorry, I could not process that.');
  appendButtons(data.buttons || []);
  messageCount += 1;
  maybeShowLeadCapture();
}

function maybeShowLeadCapture() {
  if (leadCaptureShown) return;
  if (messageCount >= 1) {
    if (!isMobile) {
      leadCardEl.classList.remove('hidden');
      leadAsideEl.style.display = '';
    }
    leadCaptureShown = true;
  }
}

function showFirstVisitLeadPopup() {
  if (isMobile) return;
  const key = 'hosted_lead_popup_seen_v1';
  if (localStorage.getItem(key) === '1') return;
  localStorage.setItem(key, '1');

  leadAsideEl.style.display = 'none';
  leadCardEl.classList.remove('hidden');
  leadCardEl.classList.add('floating');

  setTimeout(() => {
    if (leadCardEl.classList.contains('floating')) {
      leadCardEl.classList.add('hidden');
      leadCardEl.classList.remove('floating');
    }
  }, 5000);
}

function skipLeadCard() {
  if (leadCardEl.classList.contains('floating')) {
    leadCardEl.classList.remove('floating');
    leadAsideEl.style.display = '';
    leadCardEl.classList.remove('hidden');
    return;
  }
  leadCardEl.classList.add('hidden');
}

async function submitLead() {
  if (!sessionId || !sessionToken) return;
  const name = document.getElementById('leadName').value.trim();
  const phone = document.getElementById('leadPhone').value.trim();
  const email = document.getElementById('leadEmail').value.trim();

  const res = await fetch('/api/chat/lead', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({
      session_id: sessionId,
      session_token: sessionToken,
      visitor_fingerprint: getFingerprint(),
      name,
      phone,
      email,
      trigger: 'sidebar_form'
    })
  });
  const data = await res.json();
  if (data.success) {
    document.getElementById('leadSubmit').textContent = 'Submitted';
    document.getElementById('leadSubmit').disabled = true;
  } else {
    statusEl.textContent = data.error || 'Lead submit failed.';
  }
}

document.getElementById('send').addEventListener('click', () => {
  const input = document.getElementById('message');
  const value = input.value.trim();
  input.value = '';
  sendMessage(value);
});

document.getElementById('message').addEventListener('keydown', (e) => {
  if (e.key === 'Enter') document.getElementById('send').click();
});

document.querySelectorAll('.quick-action-btn').forEach((btn) => {
  btn.addEventListener('click', () => sendMessage(btn.dataset.prompt));
});

document.getElementById('leadSubmit').addEventListener('click', submitLead);
document.getElementById('leadSkip').addEventListener('click', skipLeadCard);
document.getElementById('shareFacebook').href = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(shareUrl);
document.getElementById('shareTwitter').href = 'https://twitter.com/intent/tweet?url=' + encodeURIComponent(shareUrl) + '&text=' + encodeURIComponent('Chat with us live');
document.getElementById('shareWhatsapp').href = 'https://wa.me/?text=' + encodeURIComponent('Chat with us: ' + shareUrl);
setTimeout(() => {
  if (!isMobile) {
    shareRowEl.classList.add('hidden');
  }
}, 2000);

showFirstVisitLeadPopup();
init();
</script>
</body>
</html>
