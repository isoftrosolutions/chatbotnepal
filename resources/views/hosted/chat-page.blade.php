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

    .footer { padding: 12px 18px; text-align: center; color: var(--muted); border-top: 1px solid var(--line); background: #fff; }

    @media (max-width: 1120px) {
      .content { grid-template-columns: 1fr; }
      .left, .right { border: none; border-top: 1px solid var(--line); }
      .messages { height: 390px; }
    }
  </style>
</head>
<body>
<div class="page">
  <div class="top">
    <h1>{{ $title }}</h1>
    <div class="url">https://{{ request()->getHost() }}/c/{{ $slug }}</div>
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
        <h3>Welcome ??</h3>
        <p>{{ $welcomeMessage }}</p>

        <div class="quick">Quick Actions</div>
        <div class="qa">
          <button data-q="Check room availability">Check Room Availability</button>
          <button data-q="Show room prices">View Room Prices</button>
          <button data-q="Share your location">Hotel Location</button>
        </div>
      </aside>

      <section class="center">
        <div id="messages" class="messages">
          <div class="msg bot">{{ $welcomeMessage }}</div>
          <div id="typing" class="typing">Assistant is typing...</div>
        </div>

        <div class="composer">
          <input id="message" placeholder="Type your message..." />
          <button id="send">?</button>
        </div>
      </section>

      <aside class="right">
        <div class="lead-card">
          <h4>Almost there! ??</h4>
          <p>Please share your details so we can assist you better.</p>
          <input id="leadName" placeholder="Your name" />
          <input id="leadPhone" placeholder="Phone number" />
          <input id="leadEmail" placeholder="Email (optional)" />
          <button id="leadSubmit">Submit</button>
          <div class="lead-note">You can continue chatting after this.</div>
        </div>
      </aside>
    </div>

    <div class="footer">We typically reply instantly ?</div>
  </div>
</div>

<script>
let sessionId = null;
const slug = @json($slug);
const messagesEl = document.getElementById('messages');
const typingEl = document.getElementById('typing');

function appendMessage(role, text) {
  const div = document.createElement('div');
  div.className = 'msg ' + role;
  div.textContent = text;
  messagesEl.insertBefore(div, typingEl);
  messagesEl.scrollTop = messagesEl.scrollHeight;
}

async function init() {
  const res = await fetch('/api/chat/init', {
    method: 'POST', headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ slug, source_url: window.location.href })
  });
  const data = await res.json();
  if (data.success) sessionId = data.session_id;
}

async function sendMessage(text) {
  if (!sessionId || !text) return;
  appendMessage('user', text);
  typingEl.style.display = 'block';
  const res = await fetch('/api/chat/message', {
    method: 'POST', headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ session_id: sessionId, message: text, source_url: window.location.href })
  });
  const data = await res.json();
  typingEl.style.display = 'none';
  appendMessage('bot', data.reply || 'Sorry, I could not process that.');
}

async function submitLead() {
  if (!sessionId) return;
  const name = document.getElementById('leadName').value.trim();
  const phone = document.getElementById('leadPhone').value.trim();
  const email = document.getElementById('leadEmail').value.trim();

  const res = await fetch('/api/chat/lead', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ session_id: sessionId, name, phone, email, trigger: 'sidebar_form' })
  });
  const data = await res.json();
  if (data.success) {
    document.getElementById('leadSubmit').textContent = 'Submitted';
    document.getElementById('leadSubmit').disabled = true;
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

document.querySelectorAll('.qa button').forEach((btn) => {
  btn.addEventListener('click', () => sendMessage(btn.dataset.q));
});

document.getElementById('leadSubmit').addEventListener('click', submitLead);

init();
</script>
</body>
</html>
