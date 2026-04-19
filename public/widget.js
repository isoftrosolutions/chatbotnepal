(function() {
    const SCRIPT_TAG = document.currentScript;
    const SITE_ID = SCRIPT_TAG.getAttribute('data-token') || SCRIPT_TAG.getAttribute('data-site-id');
    const BASE_URL = new URL(SCRIPT_TAG.src).origin;

    if (!SITE_ID) {
        console.error('ChatBot Nepal: Missing data-token or data-site-id attribute.');
        return;
    }

    let config = {
        business_name: 'ChatBot Nepal',
        welcome_message: 'Namaste! How can I help you today?',
        primary_color: '#10b981',
        bot_name: 'Assistant',
        bot_avatar_url: null,
        show_powered_by: true,
        prechat_enabled: false,
    };
    let conversationId = sessionStorage.getItem('cbn_conversation_id')
        ? parseInt(sessionStorage.getItem('cbn_conversation_id'), 10)
        : null;
    let visitorId = localStorage.getItem('cbn_visitor_id') || uuidv4();
    localStorage.setItem('cbn_visitor_id', visitorId);
    let visitorInfo = JSON.parse(localStorage.getItem('cbn_visitor_info') || 'null') || { name: '', email: '', phone: '' };
    let isWindowOpen = false;
    let sessionToken = null;
    let busy = false;
    let prechatShown = false;

    /* ─────────────────────────────────────────
       DESIGN TOKENS  (ChatBot Nepal Green)
    ───────────────────────────────────────── */
    const styles = `
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        #cn-widget * { box-sizing: border-box; margin: 0; padding: 0; }
        #cn-widget {
            position: fixed; bottom: 24px; right: 24px; z-index: 999999;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        }

        /* ── LAUNCHER BUTTON ── */
        #cn-launcher {
            width: 58px; height: 58px; border-radius: 50%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 8px 28px rgba(16,185,129,.50), 0 2px 8px rgba(0,0,0,.15);
            transition: transform .25s cubic-bezier(.34,1.56,.64,1), box-shadow .22s ease;
            outline: none; -webkit-tap-highlight-color: transparent;
            position: relative;
        }
        #cn-launcher:hover {
            transform: scale(1.10);
            box-shadow: 0 12px 36px rgba(16,185,129,.65), 0 2px 10px rgba(0,0,0,.18);
        }
        #cn-launcher:active { transform: scale(.95); }
        #cn-launcher::before {
            content: ''; position: absolute; inset: -6px; border-radius: 50%;
            border: 2.5px solid rgba(16,185,129,.4);
            animation: launcher-ring 2.5s ease-out infinite;
        }
        @keyframes launcher-ring {
            0% { opacity: .9; transform: scale(.88); }
            70% { opacity: 0; transform: scale(1.28); }
            100% { opacity: 0; transform: scale(1.28); }
        }
        .cn-l-icon {
            transition: transform .3s cubic-bezier(.4,0,.2,1), opacity .25s ease;
            position: absolute;
        }
        .cn-l-icon.hidden { transform: rotate(80deg) scale(.5); opacity: 0; pointer-events: none; }

        /* Badge */
        #cn-badge {
            position: absolute; top: 1px; right: 1px; min-width: 18px; height: 18px;
            background: #ef4444; border-radius: 999px;
            border: 2.5px solid #fff;
            font-size: 10px; font-weight: 700; color: #fff;
            display: flex; align-items: center; justify-content: center; padding: 0 3px;
            transition: transform .3s cubic-bezier(.34,1.56,.64,1), opacity .2s;
        }
        #cn-badge.gone { transform: scale(0); opacity: 0; }

        /* ── CHAT WINDOW ── */
        #cn-window {
            position: fixed; bottom: 96px; right: 24px;
            width: 370px; height: 570px; max-height: calc(100dvh - 110px);
            background: #fff; border-radius: 20px;
            box-shadow: 0 24px 64px rgba(0,0,0,.18), 0 4px 16px rgba(0,0,0,.08), 0 0 0 1px rgba(0,0,0,.06);
            display: flex; flex-direction: column; overflow: hidden;
            opacity: 0; transform: translateY(14px) scale(.97);
            pointer-events: none;
            transition: opacity .28s cubic-bezier(.4,0,.2,1), transform .32s cubic-bezier(.34,1.2,.64,1);
        }
        #cn-window.open {
            opacity: 1; transform: translateY(0) scale(1);
            pointer-events: all;
        }

        /* ── HEADER ── */
        #cn-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            padding: 16px 18px; display: flex; align-items: center; gap: 12px;
            flex-shrink: 0; position: relative; overflow: hidden;
        }
        #cn-header::before, #cn-header::after {
            content: ''; position: absolute; border-radius: 50%;
            background: rgba(255,255,255,.08);
        }
        #cn-header::before { width: 100px; height: 100px; top: -30px; right: 60px; }
        #cn-header::after { width: 60px; height: 60px; bottom: -20px; right: 20px; }

        .cn-hdr-avatar {
            width: 42px; height: 42px; border-radius: 12px;
            background: rgba(255,255,255,.22);
            border: 1.5px solid rgba(255,255,255,.35);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; position: relative; z-index: 1;
        }
        .cn-online-ring {
            position: absolute; bottom: -2px; right: -2px;
            width: 12px; height: 12px; border-radius: 50%;
            background: #34d399; border: 2px solid #10b981;
            box-shadow: 0 0 0 0 rgba(52,211,153,.7);
            animation: online-pulse 2.2s ease-in-out infinite;
        }
        @keyframes online-pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(52,211,153,.7); }
            60% { box-shadow: 0 0 0 5px rgba(52,211,153,0); }
        }

        .cn-hdr-info { flex: 1; min-width: 0; z-index: 1; }
        .cn-hdr-name {
            font-size: .95rem; font-weight: 700; color: #fff;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            letter-spacing: -.01em;
        }
        .cn-hdr-status {
            margin-top: 2px; font-size: .72rem; color: rgba(255,255,255,.82);
            display: flex; align-items: center; gap: 5px; font-weight: 500;
        }
        .cn-hdr-status-dot {
            width: 6px; height: 6px; border-radius: 50%; background: #a7f3d0;
        }

        .cn-hdr-btns { display: flex; gap: 3px; z-index: 1; }
        .cn-hdr-btn {
            width: 32px; height: 32px; border-radius: 9px; border: none;
            background: rgba(255,255,255,.12); color: rgba(255,255,255,.9);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: background .17s; outline: none;
        }
        .cn-hdr-btn:hover { background: rgba(255,255,255,.22); }
        .cn-hdr-btn:active { background: rgba(255,255,255,.30); }

        /* ── INTRO BANNER ── */
        #cn-intro {
            background: #f0fdf4; border-bottom: 1px solid #d1fae5;
            padding: 12px 18px; display: flex; align-items: center; gap: 10px;
            flex-shrink: 0;
        }
        .cn-intro-icon {
            width: 34px; height: 34px; border-radius: 9px;
            background: #d1fae5; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; color: #059669;
        }
        .cn-intro-text { font-size: .75rem; color: #374151; line-height: 1.5; }
        .cn-intro-text strong { color: #065f46; font-weight: 700; }

        /* ── MESSAGES AREA ── */
        #cn-messages {
            flex: 1; overflow-y: auto; padding: 16px 14px 8px;
            display: flex; flex-direction: column; gap: 10px;
            scroll-behavior: smooth; background: #fff;
        }
        #cn-messages::-webkit-scrollbar { width: 4px; }
        #cn-messages::-webkit-scrollbar-track { background: transparent; }
        #cn-messages::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }

        /* date pill */
        .cn-date-pill {
            align-self: center; background: #f3f4f6; border-radius: 999px;
            font-size: .65rem; font-weight: 600; color: #6b7280;
            padding: 3px 12px; letter-spacing: .05em; text-transform: uppercase;
        }

        /* message row */
        .cn-row {
            display: flex; align-items: flex-end; gap: 7px;
            animation: row-in .25s cubic-bezier(.4,0,.2,1) both;
        }
        @keyframes row-in {
            from { opacity: 0; transform: translateY(7px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .cn-row.user { flex-direction: row-reverse; }

        .cn-avatar {
            width: 28px; height: 28px; border-radius: 9px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            flex-shrink: 0; display: flex; align-items: center; justify-content: center;
            margin-bottom: 18px; box-shadow: 0 2px 8px rgba(16,185,129,.3);
        }
        .cn-row.user .cn-avatar { display: none; }

        .cn-col {
            display: flex; flex-direction: column; max-width: 80%;
        }
        .cn-row.user .cn-col { align-items: flex-end; }

        .cn-bubble {
            padding: 10px 13px; border-radius: 14px;
            font-size: .865rem; line-height: 1.58; word-break: break-word;
        }
        .cn-row.bot .cn-bubble {
            background: #f9fafb; border: 1px solid #e5e7eb; color: #111827;
            border-bottom-left-radius: 4px;
        }
        .cn-row.user .cn-bubble {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff; border-bottom-right-radius: 4px;
            box-shadow: 0 3px 12px rgba(16,185,129,.30);
        }
        .cn-bubble strong { font-weight: 700; }
        .cn-bubble ul { padding-left: 16px; margin-top: 4px; }
        .cn-bubble li { margin-bottom: 2px; }

        .cn-ts {
            font-size: .63rem; color: #9ca3af; margin-top: 4px;
            padding: 0 2px; font-weight: 500;
        }

        /* ── TYPING INDICATOR ── */
        #cn-typing {
            display: none; align-items: flex-end; gap: 7px;
            padding: 0 14px 6px; animation: row-in .22s ease both;
        }
        #cn-typing.show { display: flex; }
        .cn-typing-bub {
            background: #f9fafb; border: 1px solid #e5e7eb;
            border-radius: 14px; border-bottom-left-radius: 4px;
            padding: 12px 15px; display: flex; gap: 5px; align-items: center;
        }
        .cn-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: #9ca3af; animation: tdot 1.25s ease-in-out infinite;
        }
        .cn-dot:nth-child(2) { animation-delay: .16s; }
        .cn-dot:nth-child(3) { animation-delay: .32s; }
        @keyframes tdot {
            0%, 60%, 100% { transform: translateY(0); background: #9ca3af; }
            30% { transform: translateY(-5px); background: #10b981; }
        }

        /* ── INPUT AREA ── */
        #cn-input-area {
            padding: 10px 12px 12px; border-top: 1px solid #e5e7eb;
            background: #fff; display: flex; align-items: flex-end;
            gap: 7px; flex-shrink: 0;
        }
        #cn-input {
            flex: 1; background: #f9fafb; border: 1.5px solid #e5e7eb;
            border-radius: 12px; color: #111827;
            font-family: 'Plus Jakarta Sans', sans-serif; font-size: .875rem;
            font-weight: 400; padding: 10px 13px; outline: none;
            resize: none; height: 42px; max-height: 96px; line-height: 1.45;
            transition: border-color .18s, box-shadow .18s;
        }
        #cn-input::placeholder { color: #9ca3af; }
        #cn-input:focus {
            border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.15);
            background: #fff;
        }
        .cn-action-btns { display: flex; gap: 5px; align-items: center; }
        .cn-act-btn {
            width: 38px; height: 38px; border-radius: 10px;
            border: 1.5px solid #e5e7eb; background: #fff;
            color: #9ca3af; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: border-color .17s, color .17s, background .17s;
            outline: none; flex-shrink: 0;
        }
        .cn-act-btn:hover { border-color: #10b981; color: #10b981; background: #f0fdf4; }
        #cn-send {
            width: 38px; height: 38px; border-radius: 10px; border: none;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: transform .18s cubic-bezier(.34,1.56,.64,1), box-shadow .18s, opacity .18s;
            box-shadow: 0 3px 12px rgba(16,185,129,.4); outline: none; flex-shrink: 0;
        }
        #cn-send:hover { transform: scale(1.09); box-shadow: 0 5px 18px rgba(16,185,129,.55); }
        #cn-send:active { transform: scale(.93); }
        #cn-send:disabled { opacity: .38; cursor: not-allowed; transform: none; box-shadow: none; }

        /* ── FOOTER ── */
        .cn-footer {
            text-align: center; padding: 6px 0 10px;
            font-size: .63rem; color: #9ca3af; letter-spacing: .03em;
            background: #fff;
        }
        .cn-footer a { color: #059669; text-decoration: none; font-weight: 600; }
        .cn-footer a:hover { color: #10b981; }

        /* ── ERROR BUBBLE ── */
        .cn-bubble.error {
            background: #fef2f2; border-color: #fecaca; color: #b91c1c;
        }

        /* ── PRE-CHAT FORM ── */
        #cn-prechat {
            position: absolute; inset: 0; z-index: 10;
            background: rgba(255,255,255,.97); backdrop-filter: blur(6px);
            display: flex; flex-direction: column; justify-content: center;
            padding: 28px 24px; border-radius: 20px;
            animation: row-in .25s cubic-bezier(.4,0,.2,1) both;
        }
        #cn-prechat.gone { display: none; }
        .cn-pcf-title {
            font-size: 1.05rem; font-weight: 800; color: #1B1B38;
            margin-bottom: 4px; letter-spacing: -.02em;
        }
        .cn-pcf-sub {
            font-size: .78rem; color: #6b7280; margin-bottom: 20px; line-height: 1.5;
        }
        .cn-pcf-field { margin-bottom: 12px; }
        .cn-pcf-label {
            display: block; font-size: .72rem; font-weight: 600; color: #374151;
            margin-bottom: 5px; text-transform: uppercase; letter-spacing: .05em;
        }
        .cn-pcf-input {
            width: 100%; padding: 10px 13px; border: 1.5px solid #e5e7eb;
            border-radius: 11px; font-size: .875rem; font-family: 'Plus Jakarta Sans',sans-serif;
            color: #111827; outline: none; transition: border-color .18s, box-shadow .18s;
            background: #f9fafb;
        }
        .cn-pcf-input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.15); background: #fff; }
        .cn-pcf-btn {
            width: 100%; padding: 12px; border: none; border-radius: 12px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff; font-size: .9rem; font-weight: 700;
            font-family: 'Plus Jakarta Sans',sans-serif;
            cursor: pointer; margin-top: 4px;
            box-shadow: 0 4px 14px rgba(16,185,129,.4);
            transition: transform .18s, box-shadow .18s;
        }
        .cn-pcf-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(16,185,129,.5); }
        .cn-pcf-skip {
            display: block; text-align: center; margin-top: 12px;
            font-size: .75rem; color: #9ca3af; cursor: pointer;
            text-decoration: underline; text-underline-offset: 2px;
        }
        .cn-pcf-skip:hover { color: #6b7280; }

        /* ── MOBILE ── */
        @media (max-width: 480px) {
            #cn-widget { bottom: 18px; right: 18px; }
            #cn-window {
                position: fixed; bottom: 0; right: 0; left: 0;
                width: 100%; max-height: 100dvh; height: 100dvh;
                border-radius: 20px 20px 0 0;
            }
            #cn-launcher { bottom: 18px; right: 18px; }
        }
    `;

    /* ─────────────────────────────────────────
       INIT — build DOM, inject styles
    ───────────────────────────────────────── */
    function init() {
        const styleEl = document.createElement('style');
        styleEl.textContent = styles;
        document.head.appendChild(styleEl);

        const container = document.createElement('div');
        container.id = 'cn-widget';
        container.innerHTML = `
            <button id="cn-launcher" aria-label="Open chat">
                <svg class="cn-l-icon" id="li-chat" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path fill="#fff" fill-rule="evenodd" clip-rule="evenodd"
                        d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10a9.96 9.96 0 0 1-4.905-1.28L2 22l1.28-5.095A9.96 9.96 0 0 1 2 12Zm6.5-1.5a1 1 0 1 0 0 2 1 1 0 0 0 0-2Zm2.5 1a1 1 0 1 1 2 0 1 1 0 0 1-2 0Zm4.5-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z"/>
                </svg>
                <svg class="cn-l-icon hidden" id="li-close" width="20" height="20" fill="none" viewBox="0 0 24 24">
                    <path stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/>
                </svg>
                <span id="cn-badge">1</span>
            </button>

            <div id="cn-window" role="dialog" aria-label="Chat Assistant">
                <div id="cn-header">
                    <div class="cn-hdr-avatar">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24">
                            <rect x="3" y="8" width="18" height="13" rx="4" fill="rgba(255,255,255,.9)"/>
                            <circle cx="9" cy="14" r="1.5" fill="#10b981"/>
                            <circle cx="15" cy="14" r="1.5" fill="#10b981"/>
                            <rect x="10.5" y="4" width="3" height="4" rx="1.5" fill="rgba(255,255,255,.9)"/>
                            <circle cx="12" cy="4" r="1.5" fill="rgba(255,255,255,.9)"/>
                            <path stroke="rgba(255,255,255,.9)" stroke-width="1.5" stroke-linecap="round" d="M9 18h6"/>
                        </svg>
                        <span class="cn-online-ring"></span>
                    </div>
                    <div class="cn-hdr-info">
                        <div class="cn-hdr-name">${config.business_name}</div>
                        <div class="cn-hdr-status">
                            <span class="cn-hdr-status-dot"></span>
                            Online — Replies instantly
                        </div>
                    </div>
                    <div class="cn-hdr-btns">
                        <button class="cn-hdr-btn" id="cn-min" title="Minimize" aria-label="Minimize">
                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-width="2.2" stroke-linecap="round" d="M5 12h14"/>
                            </svg>
                        </button>
                        <button class="cn-hdr-btn" id="cn-close" title="Close" aria-label="Close">
                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-width="2.2" stroke-linecap="round" d="M18 6 6 18M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div id="cn-intro">
                    <div class="cn-intro-icon">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M12 2 3 6v6c0 5.25 3.75 10.15 9 11.35C17.25 22.15 21 17.25 21 12V6l-9-4Zm-1 13-3-3 1.41-1.41L11 12.17l4.59-4.58L17 9l-6 6Z"/>
                        </svg>
                    </div>
                    <div class="cn-intro-text">
                        <strong>AI Business Assistant</strong> — Serving Nepal<br/>
                        Replies in Nepali, Hindi &amp; English automatically
                    </div>
                </div>

                <div id="cn-prechat" class="gone">
                    <div class="cn-pcf-title">Before we start 👋</div>
                    <div class="cn-pcf-sub">Tell us a little about yourself — all fields are optional.</div>
                    <div class="cn-pcf-field">
                        <label class="cn-pcf-label">Your Name</label>
                        <input id="cn-pcf-name" class="cn-pcf-input" type="text" placeholder="e.g. Ram Prasad" autocomplete="name">
                    </div>
                    <div class="cn-pcf-field">
                        <label class="cn-pcf-label">Email Address</label>
                        <input id="cn-pcf-email" class="cn-pcf-input" type="email" placeholder="you@example.com" autocomplete="email">
                    </div>
                    <div class="cn-pcf-field">
                        <label class="cn-pcf-label">Phone Number</label>
                        <input id="cn-pcf-phone" class="cn-pcf-input" type="tel" placeholder="+977 98XXXXXXXX" autocomplete="tel">
                    </div>
                    <button class="cn-pcf-btn" id="cn-pcf-submit">Start Chat</button>
                    <span class="cn-pcf-skip" id="cn-pcf-skip">Skip for now</span>
                </div>

                <div id="cn-messages" role="log" aria-live="polite">
                    <div class="cn-date-pill">Today</div>
                </div>

                <div id="cn-typing" role="status" aria-label="Assistant is typing">
                    <div class="cn-avatar">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
                            <path fill="#fff" d="M12 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.38 5.07L2 22l4.93-1.38A9.94 9.94 0 0 0 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2Z"/>
                        </svg>
                    </div>
                    <div class="cn-typing-bub">
                        <span class="cn-dot"></span>
                        <span class="cn-dot"></span>
                        <span class="cn-dot"></span>
                    </div>
                </div>

                <div id="cn-input-area">
                    <textarea id="cn-input" placeholder="Type your message…" rows="1" aria-label="Message" autocomplete="off"></textarea>
                    <div class="cn-action-btns">
                        <button class="cn-act-btn" id="cn-mic" title="Voice input" aria-label="Voice input">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24">
                                <rect x="9" y="2" width="6" height="12" rx="3" stroke="currentColor" stroke-width="1.8"/>
                                <path stroke="currentColor" stroke-width="1.8" stroke-linecap="round" d="M5 10a7 7 0 0 0 14 0M12 19v3M9 22h6"/>
                            </svg>
                        </button>
                        <button id="cn-send" disabled aria-label="Send message">
                            <svg width="17" height="17" fill="none" viewBox="0 0 24 24">
                                <path fill="#fff" d="M2.01 21 23 12 2.01 3 2 10l15 2-15 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="cn-footer" id="cn-footer">
                    Powered by <a href="https://chatbotnepal.isoftroerp.com/" target="_blank">ChatBot Nepal</a>
                </div>
            </div>
        `;
        document.body.appendChild(container);

        initSession().then(() => setupEvents());
    }

    /* ─────────────────────────────────────────
       SESSION — fetch token + config from API
    ───────────────────────────────────────── */
    function initSession() {
        return fetch(`${BASE_URL}/api/widget/session`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ site_id: SITE_ID }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.session_token) {
                sessionToken = data.session_token;
                config = { ...config, ...data.config };
                applyConfig();
            }
        })
        .catch(() => {})
        .finally(() => {
            bootChat();
        });
    }

    /* ─────────────────────────────────────────
       BOOT — show welcome message from config
    ───────────────────────────────────────── */
    function bootChat() {
        const typingEl = document.getElementById('cn-typing');
        typingEl.classList.add('show');
        setTimeout(() => {
            typingEl.classList.remove('show');
            addMsg(config.welcome_message, 'bot');
        }, 900 + Math.random() * 400);
    }

    /* ─────────────────────────────────────────
       EVENTS
    ───────────────────────────────────────── */
    function setupEvents() {
        const launcher = document.getElementById('cn-launcher');
        const liChat   = document.getElementById('li-chat');
        const liClose  = document.getElementById('li-close');
        const badge    = document.getElementById('cn-badge');
        const win      = document.getElementById('cn-window');
        const closeBtn = document.getElementById('cn-close');
        const minBtn   = document.getElementById('cn-min');
        const input    = document.getElementById('cn-input');
        const sendBtn  = document.getElementById('cn-send');

        function openChat() {
            isWindowOpen = true;
            win.classList.add('open');
            liChat.classList.add('hidden');
            liClose.classList.remove('hidden');
            badge.classList.add('gone');

            // Show pre-chat form only if enabled by client and visitor hasn't identified yet
            const prechat = document.getElementById('cn-prechat');
            if (config.prechat_enabled && !prechatShown && !conversationId && !visitorInfo.name && !visitorInfo.email && !visitorInfo.phone) {
                prechatShown = true;
                prechat.classList.remove('gone');
            } else {
                input.focus();
            }
        }

        function closeChat() {
            isWindowOpen = false;
            win.classList.remove('open');
            liChat.classList.remove('hidden');
            liClose.classList.add('hidden');
        }

        launcher.addEventListener('click', () => isWindowOpen ? closeChat() : openChat());
        closeBtn.addEventListener('click', closeChat);
        minBtn.addEventListener('click', closeChat);

        function autoResize() {
            input.style.height = '42px';
            input.style.height = Math.min(input.scrollHeight, 96) + 'px';
        }

        input.addEventListener('input', () => {
            sendBtn.disabled = !input.value.trim();
            autoResize();
        });

        function handleSend() {
            const text = input.value.trim();
            if (!text || busy) return;
            addMsg(text, 'user');
            input.value = '';
            sendBtn.disabled = true;
            autoResize();
            sendMessage(text);
        }

        sendBtn.addEventListener('click', handleSend);
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); handleSend(); }
        });

        function dismissPrechat() {
            const prechat = document.getElementById('cn-prechat');
            prechat.classList.add('gone');
            input.focus();
        }

        document.getElementById('cn-pcf-submit').addEventListener('click', () => {
            const name  = document.getElementById('cn-pcf-name').value.trim();
            const email = document.getElementById('cn-pcf-email').value.trim();
            const phone = document.getElementById('cn-pcf-phone').value.trim();
            visitorInfo = { name, email, phone };
            localStorage.setItem('cbn_visitor_info', JSON.stringify(visitorInfo));
            dismissPrechat();
        });

        document.getElementById('cn-pcf-skip').addEventListener('click', () => {
            dismissPrechat();
        });

        // Allow Enter key in pre-chat fields to submit
        ['cn-pcf-name','cn-pcf-email','cn-pcf-phone'].forEach(id => {
            document.getElementById(id).addEventListener('keydown', e => {
                if (e.key === 'Enter') document.getElementById('cn-pcf-submit').click();
            });
        });

        document.getElementById('cn-mic').addEventListener('click', () => {
            const t = document.createElement('div');
            t.style.cssText = 'position:fixed;bottom:108px;right:28px;background:#065f46;color:#fff;font-family:Plus Jakarta Sans,sans-serif;font-size:.78rem;font-weight:600;padding:9px 16px;border-radius:10px;z-index:99999;pointer-events:none;animation:row-in .2s ease both;';
            t.textContent = 'Voice input coming soon!';
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 2000);
        });
    }

    /* ─────────────────────────────────────────
       SEND MESSAGE — POST to /api/chat
    ───────────────────────────────────────── */
    function sendMessage(text) {
        busy = true;
        const typingEl = document.getElementById('cn-typing');
        const sendBtn  = document.getElementById('cn-send');
        typingEl.classList.add('show');
        scrollToBottom();

        const headers = { 'Content-Type': 'application/json' };
        if (sessionToken) headers['X-Session-Token'] = sessionToken;

        fetch(`${BASE_URL}/api/chat`, {
            method: 'POST',
            headers,
            body: JSON.stringify({
                site_id:        SITE_ID,
                message:        text,
                visitor_id:     visitorId,
                conversation_id: conversationId || undefined,
                source_url:     window.location.href,
                visitor_name:   visitorInfo.name  || undefined,
                visitor_email:  visitorInfo.email || undefined,
                visitor_phone:  visitorInfo.phone || undefined,
            }),
        })
        .then(r => {
            if (!r.ok) return r.json().then(d => Promise.reject(d));
            return r.json();
        })
        .then(data => {
            if (data.reply) {
                if (data.conversation_id) {
                    conversationId = data.conversation_id;
                    sessionStorage.setItem('cbn_conversation_id', conversationId);
                }
                addMsg(data.reply, 'bot');
            } else {
                addMsg('Sorry, I could not process that. Please try again.', 'bot', true);
            }
        })
        .catch(err => {
            const msg = (err && err.error) ? err.error : 'Unable to connect. Please check your connection.';
            addMsg(msg, 'bot', true);
        })
        .finally(() => {
            typingEl.classList.remove('show');
            busy = false;
            if (sendBtn) sendBtn.disabled = false;
            scrollToBottom();
        });
    }

    /* ─────────────────────────────────────────
       DOM HELPERS
    ───────────────────────────────────────── */
    function addMsg(text, role, isError) {
        const container = document.getElementById('cn-messages');
        const row = document.createElement('div');
        row.className = `cn-row ${role}`;

        if (role === 'bot') {
            const av = document.createElement('div');
            av.className = 'cn-avatar';
            av.innerHTML = `<svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path fill="#fff" d="M12 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.38 5.07L2 22l4.93-1.38A9.94 9.94 0 0 0 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2Z"/></svg>`;
            row.appendChild(av);
        }

        const col = document.createElement('div');
        col.className = 'cn-col';

        const bubble = document.createElement('div');
        bubble.className = 'cn-bubble' + (isError ? ' error' : '');
        bubble.innerHTML = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');

        const time = document.createElement('div');
        time.className = 'cn-ts';
        time.textContent = formatTime(new Date());

        col.appendChild(bubble);
        col.appendChild(time);
        row.appendChild(col);
        container.appendChild(row);
        scrollToBottom();
        return row;
    }

    function scrollToBottom() {
        const container = document.getElementById('cn-messages');
        if (container) container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
    }

    function formatTime(date) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function applyConfig() {
        const nameEl = document.querySelector('.cn-hdr-name');
        if (nameEl) nameEl.textContent = config.business_name;

        const footer = document.getElementById('cn-footer');
        if (footer && config.show_powered_by === false) footer.style.display = 'none';

        if (config.bot_avatar_url) {
            const av = document.querySelector('.cn-hdr-avatar');
            if (av) av.innerHTML = `<img src="${config.bot_avatar_url}" alt="${config.bot_name}" style="width:100%;height:100%;border-radius:12px;object-fit:cover;">`;
        }
    }

    function uuidv4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    if (document.readyState === 'complete') init();
    else window.addEventListener('load', init);

})();
