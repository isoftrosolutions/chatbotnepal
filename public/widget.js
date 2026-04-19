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
       DESIGN TOKENS — WhatsApp-style Chat
    ───────────────────────────────────────── */
    const styles = `
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        #cn-widget * { box-sizing: border-box; margin: 0; padding: 0; }
        #cn-widget {
            position: fixed; bottom: 24px; right: 24px; z-index: 999999;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        /* ── LAUNCHER BUTTON ── */
        #cn-launcher {
            width: 58px; height: 58px; border-radius: 50%;
            background: #25d366;
            border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 6px 24px rgba(37,211,102,.50), 0 2px 8px rgba(0,0,0,.12);
            transition: transform .25s cubic-bezier(.34,1.56,.64,1), box-shadow .22s ease;
            outline: none; -webkit-tap-highlight-color: transparent;
            position: relative;
        }
        #cn-launcher:hover {
            transform: scale(1.10);
            box-shadow: 0 10px 32px rgba(37,211,102,.60), 0 2px 10px rgba(0,0,0,.15);
        }
        #cn-launcher:active { transform: scale(.95); }
        #cn-launcher::before {
            content: ''; position: absolute; inset: -6px; border-radius: 50%;
            border: 2.5px solid rgba(37,211,102,.35);
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
            background: #fff; border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,.22), 0 2px 12px rgba(0,0,0,.08);
            display: flex; flex-direction: column; overflow: hidden;
            opacity: 0; transform: translateY(14px) scale(.97);
            pointer-events: none;
            transition: opacity .28s cubic-bezier(.4,0,.2,1), transform .32s cubic-bezier(.34,1.2,.64,1);
        }
        #cn-window.open {
            opacity: 1; transform: translateY(0) scale(1);
            pointer-events: all;
        }

        /* ── HEADER — WhatsApp teal ── */
        #cn-header {
            background: #075e54;
            padding: 10px 14px; display: flex; align-items: center; gap: 10px;
            flex-shrink: 0; position: relative;
        }

        .cn-hdr-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: rgba(255,255,255,.15);
            border: none;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; position: relative; z-index: 1;
            overflow: hidden;
        }
        .cn-online-ring {
            position: absolute; bottom: 0px; right: 0px;
            width: 12px; height: 12px; border-radius: 50%;
            background: #25d366; border: 2px solid #075e54;
        }

        .cn-hdr-info { flex: 1; min-width: 0; z-index: 1; }
        .cn-hdr-name {
            font-size: .92rem; font-weight: 600; color: #fff;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .cn-hdr-status {
            margin-top: 1px; font-size: .7rem; color: rgba(255,255,255,.72);
            display: flex; align-items: center; gap: 0; font-weight: 400;
        }

        .cn-hdr-btns { display: flex; gap: 2px; z-index: 1; }
        .cn-hdr-btn {
            width: 34px; height: 34px; border-radius: 50%; border: none;
            background: transparent; color: rgba(255,255,255,.85);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: background .17s; outline: none;
        }
        .cn-hdr-btn:hover { background: rgba(255,255,255,.12); }
        .cn-hdr-btn:active { background: rgba(255,255,255,.2); }

        /* ── MESSAGES AREA — WhatsApp wallpaper ── */
        #cn-messages {
            flex: 1; overflow-y: auto; padding: 8px 12px 8px;
            display: flex; flex-direction: column; gap: 3px;
            scroll-behavior: smooth;
            background-color: #efeae2;
            background-image: url("data:image/svg+xml,%3Csvg width='200' height='200' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3Cpattern id='p' width='40' height='40' patternUnits='userSpaceOnUse'%3E%3Cpath d='M20 2a2 2 0 110 4 2 2 0 010-4z' fill='%23d6cfc5' opacity='.35'/%3E%3Cpath d='M8 15l4-3 4 3' stroke='%23d6cfc5' fill='none' stroke-width='.8' opacity='.3'/%3E%3Ccircle cx='32' cy='28' r='1.5' fill='%23d6cfc5' opacity='.25'/%3E%3Cpath d='M2 32l3 3h-6z' fill='%23d6cfc5' opacity='.2'/%3E%3Crect x='28' y='8' width='4' height='3' rx='1' fill='%23d6cfc5' opacity='.2'/%3E%3Cpath d='M16 34a3 3 0 016 0' stroke='%23d6cfc5' fill='none' stroke-width='.7' opacity='.25'/%3E%3Cpath d='M35 18l2 4h-4z' fill='%23d6cfc5' opacity='.18'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='200' height='200' fill='url(%23p)'/%3E%3C/svg%3E");
        }
        #cn-messages::-webkit-scrollbar { width: 5px; }
        #cn-messages::-webkit-scrollbar-track { background: transparent; }
        #cn-messages::-webkit-scrollbar-thumb { background: rgba(0,0,0,.15); border-radius: 4px; }

        /* date pill */
        .cn-date-pill {
            align-self: center;
            background: rgba(225,218,208,.9);
            border-radius: 8px;
            font-size: .68rem; font-weight: 500; color: #54656f;
            padding: 5px 12px;
            margin: 6px 0;
            box-shadow: 0 1px 1px rgba(0,0,0,.06);
        }

        /* ── MESSAGE BUBBLES — WhatsApp style ── */
        .cn-row {
            display: flex; 
            flex-direction: column;
            animation: row-in .2s ease both;
            padding: 1px 0;
        }
        @keyframes row-in {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .cn-row.user { align-items: flex-end; }
        .cn-row.bot { align-items: flex-start; }

        .cn-col {
            max-width: 78%;
            position: relative;
        }

        .cn-bubble {
            padding: 6px 8px 8px 9px;
            font-size: .855rem; line-height: 1.5; word-break: break-word;
            position: relative;
            min-width: 80px;
        }
        .cn-bubble strong { font-weight: 700; }
        .cn-bubble ul { padding-left: 16px; margin-top: 4px; }
        .cn-bubble li { margin-bottom: 2px; }

        /* Bot (incoming) bubble — white with left tail */
        .cn-row.bot .cn-bubble {
            background: #fff;
            color: #111b21;
            border-radius: 0 8px 8px 8px;
            box-shadow: 0 1px 1px rgba(0,0,0,.06);
        }
        .cn-row.bot .cn-col::before {
            content: '';
            position: absolute;
            top: 0; left: -8px;
            width: 0; height: 0;
            border-top: 0px solid transparent;
            border-bottom: 10px solid transparent;
            border-right: 8px solid #fff;
        }

        /* User (outgoing) bubble — WhatsApp green with right tail */
        .cn-row.user .cn-bubble {
            background: #d9fdd3;
            color: #111b21;
            border-radius: 8px 0 8px 8px;
            box-shadow: 0 1px 1px rgba(0,0,0,.06);
        }
        .cn-row.user .cn-col::before {
            content: '';
            position: absolute;
            top: 0; right: -8px;
            width: 0; height: 0;
            border-top: 0px solid transparent;
            border-bottom: 10px solid transparent;
            border-left: 8px solid #d9fdd3;
        }

        /* Timestamp row inside bubble */
        .cn-ts-row {
            display: flex; align-items: center; justify-content: flex-end;
            gap: 3px; margin-top: 2px;
            float: right;
            margin-left: 10px;
            position: relative;
            top: 4px;
        }
        .cn-ts {
            font-size: .625rem; color: #667781; font-weight: 400;
            white-space: nowrap;
        }
        /* Double check marks (read receipts) */
        .cn-check {
            display: inline-flex; color: #53bdeb;
        }
        .cn-check.cn-check-pending { color: #667781; }
        .cn-row.bot .cn-check { display: none; }

        /* ── TYPING INDICATOR ── */
        #cn-typing {
            display: none; 
            flex-direction: column;
            align-items: flex-start;
            padding: 1px 0;
            animation: row-in .2s ease both;
        }
        #cn-typing.show { display: flex; }
        .cn-typing-wrap {
            position: relative;
            max-width: 78%;
        }
        .cn-typing-wrap::before {
            content: '';
            position: absolute;
            top: 0; left: -8px;
            width: 0; height: 0;
            border-top: 0px solid transparent;
            border-bottom: 10px solid transparent;
            border-right: 8px solid #fff;
        }
        .cn-typing-bub {
            background: #fff;
            border-radius: 0 8px 8px 8px;
            padding: 12px 15px; display: flex; gap: 5px; align-items: center;
            box-shadow: 0 1px 1px rgba(0,0,0,.06);
        }
        .cn-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: #9ca3af; animation: tdot 1.25s ease-in-out infinite;
        }
        .cn-dot:nth-child(2) { animation-delay: .16s; }
        .cn-dot:nth-child(3) { animation-delay: .32s; }
        @keyframes tdot {
            0%, 60%, 100% { transform: translateY(0); background: #9ca3af; }
            30% { transform: translateY(-5px); background: #25d366; }
        }

        /* ── INPUT AREA — WhatsApp style ── */
        #cn-input-area {
            padding: 6px 8px 8px;
            background: #efeae2;
            display: flex; align-items: flex-end;
            gap: 6px; flex-shrink: 0;
        }
        .cn-input-wrap {
            flex: 1;
            background: #fff;
            border-radius: 24px;
            display: flex; align-items: flex-end;
            padding: 4px 6px 4px 12px;
            box-shadow: 0 1px 2px rgba(0,0,0,.08);
        }
        #cn-input {
            flex: 1; background: transparent; border: none;
            color: #111b21;
            font-family: 'Plus Jakarta Sans', sans-serif; font-size: .875rem;
            font-weight: 400; padding: 7px 4px; outline: none;
            resize: none; height: 38px; max-height: 96px; line-height: 1.4;
        }
        #cn-input::placeholder { color: #8696a0; }

        .cn-input-actions {
            display: flex; align-items: center; gap: 0; padding-right: 4px;
        }
        .cn-in-btn {
            width: 34px; height: 34px; border-radius: 50%;
            border: none; background: transparent;
            color: #8696a0; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: color .15s; outline: none; flex-shrink: 0;
        }
        .cn-in-btn:hover { color: #54656f; }

        /* Send button — green circle */
        #cn-send {
            width: 42px; height: 42px; border-radius: 50%; border: none;
            background: #00a884;
            color: #fff; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: transform .15s ease, background .15s;
            box-shadow: 0 2px 8px rgba(0,168,132,.35); outline: none; flex-shrink: 0;
        }
        #cn-send:hover { background: #008f72; transform: scale(1.06); }
        #cn-send:active { transform: scale(.94); }
        #cn-send:disabled { background: #a0aeb6; cursor: not-allowed; transform: none; box-shadow: none; }

        /* Character counter */
        #cn-char-count {
            position: absolute; bottom: 2px; right: 60px;
            font-size: .65rem; color: #8696a0;
        }
        #cn-char-count.warning { color: #f59e0b; }
        #cn-char-count.error { color: #ef4444; font-weight: 600; }

        /* ── FOOTER ── */
        .cn-footer {
            text-align: center; padding: 5px 0 7px;
            font-size: .6rem; color: #8696a0; letter-spacing: .02em;
            background: #efeae2;
        }
        .cn-footer a { color: #027d5a; text-decoration: none; font-weight: 600; }
        .cn-footer a:hover { color: #00a884; }

        /* ── ERROR BUBBLE ── */
        .cn-bubble.error {
            background: #fff; border-left: 3px solid #ef4444;
        }

        /* ── SCROLL TO BOTTOM BUTTON ── */
        #cn-scroll-btn {
            position: absolute; bottom: 8px; right: 12px;
            background: #075e54; color: #fff;
            border: none; border-radius: 20px;
            padding: 8px 14px; font-size: .75rem; font-weight: 600;
            cursor: pointer; display: none;
            box-shadow: 0 2px 8px rgba(0,0,0,.2);
            z-index: 5;
        }
        #cn-scroll-btn.show { display: flex; align-items: center; gap: 4px; }
        #cn-scroll-btn:hover { background: #0a6e68; }

        /* ── PRE-CHAT FORM ── */
        #cn-prechat {
            position: absolute; inset: 0; z-index: 10;
            background: rgba(255,255,255,.97); backdrop-filter: blur(6px);
            display: flex; flex-direction: column; justify-content: center;
            padding: 28px 24px; border-radius: 12px;
            animation: row-in .25s cubic-bezier(.4,0,.2,1) both;
        }
        #cn-prechat.gone { display: none; }
        .cn-pcf-title {
            font-size: 1.05rem; font-weight: 800; color: #111b21;
            margin-bottom: 4px; letter-spacing: -.02em;
        }
        .cn-pcf-sub {
            font-size: .78rem; color: #667781; margin-bottom: 20px; line-height: 1.5;
        }
        .cn-pcf-field { margin-bottom: 12px; }
        .cn-pcf-label {
            display: block; font-size: .72rem; font-weight: 600; color: #374151;
            margin-bottom: 5px; text-transform: uppercase; letter-spacing: .05em;
        }
        .cn-pcf-input {
            width: 100%; padding: 10px 13px; border: 1.5px solid #e5e7eb;
            border-radius: 11px; font-size: .875rem; font-family: 'Plus Jakarta Sans',sans-serif;
            color: #111b21; outline: none; transition: border-color .18s, box-shadow .18s;
            background: #f9fafb;
        }
        .cn-pcf-input:focus { border-color: #00a884; box-shadow: 0 0 0 3px rgba(0,168,132,.15); background: #fff; }
        .cn-pcf-input.invalid { border-color: #ef4444; background: #fef2f2; }
        .cn-pcf-btn {
            width: 100%; padding: 12px; border: none; border-radius: 12px;
            background: #00a884;
            color: #fff; font-size: .9rem; font-weight: 700;
            font-family: 'Plus Jakarta Sans',sans-serif;
            cursor: pointer; margin-top: 4px;
            box-shadow: 0 4px 14px rgba(0,168,132,.35);
            transition: transform .18s, box-shadow .18s;
        }
        .cn-pcf-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0,168,132,.45); }
        .cn-pcf-skip {
            display: block; text-align: center; margin-top: 12px;
            font-size: .75rem; color: #8696a0; cursor: pointer;
            text-decoration: underline; text-underline-offset: 2px;
        }
        .cn-pcf-skip:hover { color: #54656f; }

        /* ── ENCRYPTION NOTICE ── */
        .cn-encrypt-notice {
            align-self: center;
            background: rgba(255,234,170,.6);
            border-radius: 7px;
            font-size: .65rem; font-weight: 400; color: #54656f;
            padding: 5px 12px;
            margin: 4px 0 6px;
            display: flex; align-items: center; gap: 5px;
            text-align: center;
            line-height: 1.4;
        }

        /* ── MOBILE ── */
        @media (max-width: 480px) {
            #cn-widget { bottom: 18px; right: 18px; }
            #cn-window {
                position: fixed; bottom: 0; right: 0; left: 0;
                width: 100%; max-height: 100dvh; height: 100dvh;
                border-radius: 0;
            }
            #cn-launcher { bottom: 18px; right: 18px; }
            .cn-drag-handle {
                display: flex; width: 100%; justify-content: center; padding: 8px 0 6px;
            }
            .cn-drag-pill {
                width: 36px; height: 4px; background: #d1d5db; border-radius: 2px;
            }
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
                <svg class="cn-l-icon" id="li-chat" width="26" height="26" fill="none" viewBox="0 0 24 24">
                    <path fill="#fff" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347ZM12.05 21.785h-.013A9.872 9.872 0 0 1 6.7 20.15l-.383-.228-3.971 1.042 1.06-3.873-.25-.397A9.86 9.86 0 0 1 1.63 12.05C1.63 6.315 6.315 1.63 12.05 1.63c2.772 0 5.378 1.08 7.336 3.04a10.3 10.3 0 0 1 3.034 7.348c-.003 5.736-4.688 10.42-10.37 10.42v-.653Z"/>
                </svg>
                <svg class="cn-l-icon hidden" id="li-close" width="20" height="20" fill="none" viewBox="0 0 24 24">
                    <path stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/>
                </svg>
                <span id="cn-badge">1</span>
            </button>

            <div id="cn-window" role="dialog" aria-label="Chat Assistant">
                <div class="cn-drag-handle" style="display:none"><div class="cn-drag-pill"></div></div>
                <div id="cn-header">
                    <div class="cn-hdr-avatar">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <rect x="3" y="8" width="18" height="13" rx="4" fill="rgba(255,255,255,.85)"/>
                            <circle cx="9" cy="14" r="1.5" fill="#075e54"/>
                            <circle cx="15" cy="14" r="1.5" fill="#075e54"/>
                            <rect x="10.5" y="4" width="3" height="4" rx="1.5" fill="rgba(255,255,255,.85)"/>
                            <circle cx="12" cy="4" r="1.5" fill="rgba(255,255,255,.85)"/>
                            <path stroke="rgba(255,255,255,.85)" stroke-width="1.5" stroke-linecap="round" d="M9 18h6"/>
                        </svg>
                        <span class="cn-online-ring"></span>
                    </div>
                    <div class="cn-hdr-info">
                        <div class="cn-hdr-name">${config.business_name}</div>
                        <div class="cn-hdr-status">online</div>
                    </div>
                    <div class="cn-hdr-btns">
                        <button class="cn-hdr-btn" id="cn-min" title="Minimize" aria-label="Minimize">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M5 12h14"/>
                            </svg>
                        </button>
                        <button class="cn-hdr-btn" id="cn-close" title="Close" aria-label="Close">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M18 6 6 18M6 6l12 12"/>
                            </svg>
                        </button>
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
                    <div class="cn-encrypt-notice">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><path fill="#8696a0" d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2Zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2ZM9 8V6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9Z"/></svg>
                        Messages are AI-generated. Powered by ChatBot Nepal.
                    </div>
                    <div class="cn-date-pill">Today</div>
                    <button id="cn-scroll-btn" type="button">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M12 5v14M5 12l7 7 7-7"/></svg>
                        Down
                    </button>
                </div>

                <div id="cn-typing" role="status" aria-label="Assistant is typing">
                    <div class="cn-typing-wrap">
                        <div class="cn-typing-bub">
                            <span class="cn-dot"></span>
                            <span class="cn-dot"></span>
                            <span class="cn-dot"></span>
                        </div>
                    </div>
                </div>

                <div id="cn-input-area">
                    <div class="cn-input-wrap">
                        <span id="cn-char-count"></span>
                        <textarea id="cn-input" placeholder="Type a message" rows="1" aria-label="Message" autocomplete="off"></textarea>
                        <div class="cn-input-actions">
                            <button class="cn-in-btn" id="cn-mic" title="Voice input" aria-label="Voice input">
                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24">
                                    <rect x="9" y="2" width="6" height="12" rx="3" stroke="currentColor" stroke-width="1.6"/>
                                    <path stroke="currentColor" stroke-width="1.6" stroke-linecap="round" d="M5 10a7 7 0 0 0 14 0M12 19v3"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <button id="cn-send" disabled aria-label="Send message">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24">
                            <path fill="#fff" d="M2.01 21 23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
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
            if (window.innerWidth <= 480) {
                document.querySelector('.cn-drag-handle').style.display = 'flex';
            }

            const prechat = document.getElementById('cn-prechat');
            const needsPrechat = config.prechat_enabled && (!visitorInfo.name || !visitorInfo.email || !visitorInfo.phone);
            if (needsPrechat) {
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
            badge.classList.remove('gone');
            badge.textContent = '1';
            document.querySelector('.cn-drag-handle').style.display = 'none';
        }

        launcher.addEventListener('click', () => isWindowOpen ? closeChat() : openChat());
        closeBtn.addEventListener('click', closeChat);
        minBtn.addEventListener('click', closeChat);

        function autoResize() {
            input.style.height = '38px';
            input.style.height = Math.min(input.scrollHeight, 96) + 'px';
        }

        input.addEventListener('input', () => {
            const len = input.value.length;
            const charCount = document.getElementById('cn-char-count');
            const remaining = 1000 - len;
            if (remaining <= 100 && remaining > 0) {
                charCount.textContent = remaining + ' / 1000';
                charCount.classList.add('warning');
            } else if (remaining <= 0) {
                charCount.textContent = '0 / 1000';
                charCount.classList.remove('warning');
                charCount.classList.add('error');
                sendBtn.disabled = true;
            } else {
                charCount.textContent = '';
                charCount.classList.remove('warning', 'error');
            }
            sendBtn.disabled = !input.value.trim() || remaining <= 0;
            autoResize();
        });

        function handleSend() {
            const text = input.value.trim();
            if (!text || busy) return;
            window.lastUserMsg = text;
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
            let email = document.getElementById('cn-pcf-email').value.trim();
            const phone = document.getElementById('cn-pcf-phone').value.trim();
            const emailInput = document.getElementById('cn-pcf-email');
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                emailInput.style.borderColor = '#ef4444';
                emailInput.focus();
                return;
            }
            visitorInfo = { name, email, phone };
            localStorage.setItem('cbn_visitor_info', JSON.stringify(visitorInfo));
            dismissPrechat();
        });

        document.getElementById('cn-pcf-email').addEventListener('blur', function() {
            const val = this.value.trim();
            if (val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                this.classList.add('invalid');
            } else {
                this.classList.remove('invalid');
            }
        });

        document.getElementById('cn-pcf-skip').addEventListener('click', () => {
            dismissPrechat();
        });

        ['cn-pcf-name','cn-pcf-email','cn-pcf-phone'].forEach(id => {
            document.getElementById(id).addEventListener('keydown', e => {
                if (e.key === 'Enter') document.getElementById('cn-pcf-submit').click();
            });
        });

        document.getElementById('cn-mic').addEventListener('click', () => {
            const t = document.createElement('div');
            t.style.cssText = 'position:fixed;bottom:108px;right:28px;background:#075e54;color:#fff;font-family:Plus Jakarta Sans,sans-serif;font-size:.78rem;font-weight:600;padding:9px 16px;border-radius:10px;z-index:99999;pointer-events:none;animation:row-in .2s ease both;';
            t.textContent = 'Voice input coming soon!';
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 2000);
        });

        const messages = document.getElementById('cn-messages');
        const scrollBtn = document.getElementById('cn-scroll-btn');
        messages.addEventListener('scroll', () => {
            const atBottom = messages.scrollHeight - messages.scrollTop <= messages.clientHeight + 50;
            scrollBtn.classList.toggle('show', !atBottom);
        });
        scrollBtn.addEventListener('click', scrollToBottom);

        messages.addEventListener('click', e => {
            if (e.target.classList.contains('cn-retry')) {
                e.preventDefault();
                if (window.lastUserMsg) {
                    addMsg(window.lastUserMsg, 'user');
                    sendMessage(window.lastUserMsg);
                }
            }
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
                updateChecksToRead();
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

        const col = document.createElement('div');
        col.className = 'cn-col';

        const bubble = document.createElement('div');
        bubble.className = 'cn-bubble' + (isError ? ' error' : '');

        const content = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');

        const timeStr = formatTime(new Date());
        const checkSvg = role === 'user'
            ? `<span class="cn-check cn-check-pending"><svg width="16" height="11" viewBox="0 0 16 11" fill="none"><path d="M11.07.66 4.88 6.85 2.41 4.38.94 5.85l3.94 3.94 7.66-7.66L11.07.66Z" fill="#667781"/><path d="M14.07.66 7.88 6.85l-.53-.53-1.47 1.47 2 2 7.66-7.66L14.07.66Z" fill="#667781"/></svg></span>`
            : '';

        let footer = `<span class="cn-ts-row"><span class="cn-ts">${timeStr}</span>${checkSvg}</span>`;
        if (isError) {
            footer += `<a href="#" class="cn-retry" style="margin-left:8px;color:#4318FF;font-size:.65rem;font-weight:600;text-decoration:underline;">Try again</a>`;
        }
        bubble.innerHTML = `${content}${footer}`;

        col.appendChild(bubble);
        row.appendChild(col);
        container.appendChild(row);
        scrollToBottom();
        return row;
    }

    function scrollToBottom() {
        const container = document.getElementById('cn-messages');
        if (container) container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
    }

    function updateChecksToRead() {
        document.querySelectorAll('.cn-check-pending').forEach(el => {
            el.classList.remove('cn-check-pending');
            const paths = el.querySelectorAll('path');
            paths[0].setAttribute('fill', '#53bdeb');
            paths[1].setAttribute('fill', '#53bdeb');
        });
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
            if (av) av.innerHTML = `<img src="${config.bot_avatar_url}" alt="${config.bot_name}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;"><span class="cn-online-ring"></span>`;
        }

        const prechat = document.getElementById('cn-prechat');
        if (prechat && !config.prechat_enabled) {
            prechat.style.display = 'none';
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
