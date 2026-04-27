(function() {
    const SCRIPT_TAG = document.currentScript;
    const SITE_ID = SCRIPT_TAG.getAttribute('data-token') || SCRIPT_TAG.getAttribute('data-site-id');
    const BASE_URL = new URL(SCRIPT_TAG.src).origin;

    if (!SITE_ID) {
        console.error('ChatBot Nepal: Missing data-token or data-site-id attribute.');
        return;
    }

    let config = {
        business_name:        'ChatBot Nepal',
        welcome_message:      'Namaste! How can I help you today?',
        primary_color:        '#006d77',
        bot_name:             'Assistant',
        bot_avatar_url:       null,
        tagline:              null,
        privacy_policy_url:   null,
        support_email:        null,
        message_meta_enabled: false,
        show_powered_by:      true,
        prechat_enabled:      false,
        company_logo_url:     null,
        watermark_enabled:    false,
        watermark_opacity:    0.1,
        watermark_position:   'center',
        suggested_questions:  [],
    };

    /* ─── Conversation persistence — localStorage with 24-hour TTL ─── */
    function saveConversationId(id) {
        try {
            localStorage.setItem('cbn_conversation', JSON.stringify({ id, ts: Date.now() }));
        } catch (_) {}
    }
    function loadConversationId() {
        try {
            const raw = localStorage.getItem('cbn_conversation');
            if (!raw) {
                /* migrate from old sessionStorage key */
                const legacyId = sessionStorage.getItem('cbn_conversation_id');
                if (legacyId) {
                    saveConversationId(parseInt(legacyId, 10));
                    sessionStorage.removeItem('cbn_conversation_id');
                    return parseInt(legacyId, 10);
                }
                return null;
            }
            const data = JSON.parse(raw);
            if (Date.now() - data.ts > 24 * 60 * 60 * 1000) {
                localStorage.removeItem('cbn_conversation');
                return null;
            }
            return data.id;
        } catch (_) {
            return null;
        }
    }

    let conversationId = loadConversationId();
    let visitorId      = localStorage.getItem('cbn_visitor_id') || uuidv4();
    localStorage.setItem('cbn_visitor_id', visitorId);
    let visitorInfo    = JSON.parse(localStorage.getItem('cbn_visitor_info') || 'null') || { name: '', email: '', phone: '' };
    let isWindowOpen   = false;
    let sessionToken   = null;
    let busy           = false;
    let firstMsgAdded  = false;
    let lastMsgDateStr = null;
    let focusTrapHandler = null;

    /* ─── ESCALATION — phrases that signal a user wants a human ─── */
    const ESCALATION_RE = /\b(human|agent|real person|live (chat|agent|support)|speak to|talk to|contact (someone|support|a person)|someone real|customer service)\b/i;

    function getLauncherPosition() {
        const pos = config.position || 'bottom-right';
        if (pos === 'bottom-left') return { bottom: '24px', left: '24px', right: 'auto' };
        return { bottom: '24px', right: '24px', left: 'auto' };
    }

    function normalizeAssetUrl(maybeUrl) {
        if (!maybeUrl || typeof maybeUrl !== 'string') return null;
        try { return new URL(maybeUrl, BASE_URL).href; } catch { return maybeUrl; }
    }

    function normalizeHexColor(maybeHex) {
        if (!maybeHex || typeof maybeHex !== 'string') return null;
        const hex = maybeHex.trim();
        if (/^#[0-9a-fA-F]{6}$/.test(hex)) return hex.toLowerCase();
        if (/^#[0-9a-fA-F]{3}$/.test(hex)) {
            const r = hex[1], g = hex[2], b = hex[3];
            return (`#${r}${r}${g}${g}${b}${b}`).toLowerCase();
        }
        return null;
    }

    function darkenHex(hex, amount01) {
        const normalized = normalizeHexColor(hex);
        if (!normalized) return null;
        const amt = Math.max(0, Math.min(1, amount01 || 0));
        const r = parseInt(normalized.slice(1, 3), 16);
        const g = parseInt(normalized.slice(3, 5), 16);
        const b = parseInt(normalized.slice(5, 7), 16);
        const toHex = (n) => n.toString(16).padStart(2, '0');
        return `#${toHex(Math.round(r*(1-amt)))}${toHex(Math.round(g*(1-amt)))}${toHex(Math.round(b*(1-amt)))}`;
    }

    /* ─────────────────────────────────────────
       DESIGN TOKENS
    ───────────────────────────────────────── */
    const styles = `
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        #cn-widget * { box-sizing: border-box; margin: 0; padding: 0; }
        #cn-widget {
            position: fixed; bottom: 24px; right: 24px; z-index: 999999;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --cn-primary: #006d77;
            --cn-primary-dark: #00535b;
            --cn-surface: #ffffff;
            --cn-text: #191c1d;
            --cn-muted: #3e494a;
            --cn-bot-bubble: #e7e8e9;
            --cn-user-bubble: var(--cn-primary);
            --cn-input-bg: #f3f4f5;
        }

        /* ── LAUNCHER — animated pill ── */
        #cn-launcher {
            border-radius: 999px;
            background: var(--cn-primary);
            border: none; cursor: pointer;
            display: flex; align-items: center;
            padding: 0 0 0 18px;
            gap: 0;
            box-shadow: 0 10px 32px rgba(0,109,119,.20), 0 2px 10px rgba(0,109,119,.12);
            outline: none; -webkit-tap-highlight-color: transparent;
            position: relative; overflow: hidden;
            will-change: transform;
            height: 58px;
        }

        /* Text section (left) */
        #cn-l-text-wrap {
            display: flex; flex-direction: column; justify-content: center;
            gap: 1px; padding-right: 14px;
            overflow: hidden;
        }
        #cn-l-greeting {
            font-size: .72rem; font-weight: 700; color: rgba(255,255,255,.80);
            letter-spacing: .02em; line-height: 1; white-space: nowrap;
        }
        #cn-l-title {
            font-size: .86rem; font-weight: 700; color: #fff;
            line-height: 1.2; white-space: nowrap;
        }

        /* Bot avatar section (right) */
        #cn-l-bot-wrap {
            width: 58px; height: 58px;
            border-radius: 50%;
            background: rgba(0,0,0,.15);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; position: relative;
            will-change: transform;
        }
        #cn-l-bot-wrap::before {
            content: ''; position: absolute; inset: -5px; border-radius: 50%;
            border: 2px solid rgba(255,255,255,.4);
        }
        @media (prefers-reduced-motion: no-preference) {
            #cn-l-bot-wrap::before { animation: cn-pill-ring 2.8s ease-out infinite; }
        }
        @keyframes cn-pill-ring {
            0%   { transform: scale(.9); opacity: .8; }
            70%  { transform: scale(1.3); opacity: 0; }
            100% { transform: scale(1.3); opacity: 0; }
        }

        /* Close X (shown only when window is open) */
        #cn-l-close-icon {
            display: none;
            align-items: center; justify-content: center;
            width: 58px; height: 58px;
            border-radius: 50%;
            background: rgba(0,0,0,.18);
            flex-shrink: 0;
        }

        /* Badge */
        #cn-badge {
            position: absolute; top: 4px; right: 4px; min-width: 18px; height: 18px;
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
            background: var(--cn-surface); border-radius: 22px;
            box-shadow: 0 32px 64px rgba(0,109,119,.12), 0 6px 18px rgba(0,109,119,.08);
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
            background: var(--cn-primary);
            padding: 12px 14px; display: flex; align-items: center; gap: 12px;
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
            background: #22c55e; border: 2px solid var(--cn-primary);
        }

        .cn-hdr-info { flex: 1; min-width: 0; z-index: 1; }
        .cn-hdr-name {
            font-size: .92rem; font-weight: 600; color: #fff;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .cn-hdr-status {
            margin-top: 2px; font-size: .72rem; color: rgba(255,255,255,.82);
            display: flex; align-items: center; gap: 5px; font-weight: 500;
            line-height: 1;
        }

        /* Header action buttons — min 40×40 touch target */
        .cn-hdr-btns { display: flex; gap: 2px; z-index: 1; }
        .cn-hdr-btn {
            width: 40px; height: 40px; border-radius: 50%; border: none;
            background: transparent; color: rgba(255,255,255,.85);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: background .17s; outline: none;
        }
        .cn-hdr-btn:hover { background: rgba(255,255,255,.12); }
        .cn-hdr-btn:active { background: rgba(255,255,255,.2); }
        .cn-hdr-btn:focus-visible { box-shadow: 0 0 0 2px rgba(255,255,255,.6); }

        /* ── MESSAGES AREA ── */
        #cn-messages {
            flex: 1; overflow-y: auto; padding: 14px 16px 12px;
            display: flex; flex-direction: column; gap: 0;
            scroll-behavior: smooth;
            background: var(--cn-surface);
            position: relative;
        }

        /* Watermark overlay */
        #cn-messages::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            pointer-events: none; z-index: 1;
            opacity: 0; transition: opacity 0.3s ease;
            background-image: var(--watermark-image, none);
            background-position: var(--watermark-position, center);
            background-repeat: no-repeat;
            background-size: 180px 180px;
        }
        #cn-messages.has-watermark::before {
            opacity: var(--watermark-opacity, 0.1);
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

        /* ── MESSAGE BUBBLES ── */
        .cn-row {
            display: flex; align-items: flex-end;
            gap: 10px;
            padding: 7px 0;
        }
        @media (prefers-reduced-motion: no-preference) {
            .cn-row { animation: row-in .2s ease both; }
        }
        @keyframes row-in {
            from { opacity: 0; transform: translateY(5px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .cn-row.user { justify-content: flex-end; }
        .cn-row.bot  { justify-content: flex-start; }

        .cn-col { max-width: 76%; position: relative; }

        .cn-bubble {
            padding: 12px 14px;
            font-size: .92rem; line-height: 1.55; word-break: break-word;
            position: relative; min-width: 72px;
        }
        .cn-bubble strong { font-weight: 700; }
        .cn-bubble em { font-style: italic; }
        .cn-bubble ul { padding-left: 18px; margin: 6px 0; }
        .cn-bubble ol { padding-left: 18px; margin: 6px 0; }
        .cn-bubble li { margin-bottom: 3px; }
        .cn-bubble pre {
            background: rgba(0,0,0,.06); border-radius: 6px;
            padding: 8px 10px; margin: 6px 0;
            overflow-x: auto; font-size: .8rem; font-family: monospace;
            white-space: pre-wrap; word-break: break-all;
        }
        .cn-bubble code {
            background: rgba(0,0,0,.06); border-radius: 3px;
            padding: 1px 5px; font-family: monospace; font-size: .85em;
        }
        .cn-bubble pre code { background: none; padding: 0; }

        /* Bot bubble */
        .cn-row.bot .cn-bubble {
            background: var(--cn-bot-bubble); color: var(--cn-text);
            border-radius: 1rem 1rem 1rem 0.25rem; box-shadow: none;
        }
        /* User bubble */
        .cn-row.user .cn-bubble {
            background: var(--cn-user-bubble); color: #fff;
            border-radius: 1rem 1rem 0.25rem 1rem; box-shadow: none;
        }

        /* Bot message avatar */
        .cn-msg-avatar {
            width: 34px; height: 34px; border-radius: 999px;
            background: var(--cn-primary); color: #fff;
            display: flex; align-items: center; justify-content: center;
            flex: 0 0 auto;
            box-shadow: 0 2px 10px rgba(0,109,119,.10);
            overflow: hidden;
        }
        .cn-msg-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 999px; }

        /* Timestamp */
        .cn-ts-row {
            display: flex; align-items: center; justify-content: flex-end;
            gap: 3px; margin-top: 2px;
            float: right; margin-left: 10px;
            position: relative; top: 4px;
        }
        .cn-ts { font-size: .7rem; color: #667781; font-weight: 400; white-space: nowrap; }
        /* Single-tick sent indicator — no read-receipt implication */
        .cn-check { display: inline-flex; color: rgba(255,255,255,.6); }
        .cn-check.cn-check-pending { color: rgba(255,255,255,.4); }
        .cn-check.cn-check-replied { color: rgba(255,255,255,.9); }
        .cn-row.bot .cn-check { display: none; }

        /* ── TYPING INDICATOR ── */
        #cn-typing {
            display: none; flex-direction: column;
            align-items: flex-start; padding: 1px 0;
        }
        @media (prefers-reduced-motion: no-preference) {
            #cn-typing { animation: row-in .2s ease both; }
        }
        #cn-typing.show { display: flex; }
        .cn-typing-bub {
            background: var(--cn-bot-bubble);
            border-radius: 1rem 1rem 1rem 0.25rem;
            padding: 12px 15px; display: flex; gap: 5px; align-items: center;
        }
        .cn-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: #bec8ca;
        }
        @media (prefers-reduced-motion: no-preference) {
            .cn-dot { animation: tdot 1.25s ease-in-out infinite; }
            .cn-dot:nth-child(2) { animation-delay: .16s; }
            .cn-dot:nth-child(3) { animation-delay: .32s; }
        }
        @keyframes tdot {
            0%, 60%, 100% { transform: translateY(0); background: #bec8ca; }
            30% { transform: translateY(-5px); background: var(--cn-primary); }
        }

        /* ── INPUT AREA ── */
        #cn-input-area {
            padding: 12px 12px 14px;
            background: var(--cn-surface);
            display: flex; align-items: flex-end;
            gap: 10px; flex-shrink: 0;
        }
        .cn-input-wrap {
            flex: 1; background: var(--cn-input-bg);
            border-radius: 18px;
            display: flex; align-items: flex-end;
            position: relative; padding: 8px 14px;
            border: 1px solid rgba(190,200,202,.4);
            box-shadow: 0 2px 10px rgba(0,109,119,.06);
        }
        #cn-input {
            flex: 1; background: transparent; border: none;
            color: var(--cn-text);
            font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1rem;
            font-weight: 500; padding: 8px 6px; outline: none;
            resize: none; height: 38px; max-height: 96px; line-height: 1.4;
        }
        #cn-input::placeholder { color: #9ca3af; font-weight: 500; }

        /* Character counter */
        #cn-char-count {
            position: absolute; bottom: 6px; right: 12px;
            font-size: .65rem; color: #8696a0; pointer-events: none;
        }
        #cn-char-count.warning { color: #f59e0b; }
        #cn-char-count.error   { color: #ef4444; font-weight: 600; }

        /* Send button — fully circular, 44×44 touch target */
        #cn-send {
            width: 44px; height: 44px; border-radius: 50%; border: none;
            background: var(--cn-primary);
            color: #fff; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: transform .15s ease, background .15s;
            box-shadow: 0 8px 18px rgba(0,109,119,.12); outline: none; flex-shrink: 0;
        }
        #cn-send:hover   { background: var(--cn-primary-dark); transform: translateY(-1px); }
        #cn-send:active  { transform: scale(.94); }
        #cn-send:disabled { background: #a0aeb6; cursor: not-allowed; transform: none; box-shadow: none; }
        #cn-send:focus-visible { box-shadow: 0 0 0 3px rgba(0,109,119,.4); }

        /* ── FOOTER ── */
        .cn-footer {
            text-align: center; padding: 5px 0 7px;
            font-size: .6rem; color: var(--cn-muted); letter-spacing: .02em;
            background: var(--cn-surface);
        }
        .cn-footer a { color: var(--cn-primary); text-decoration: none; font-weight: 600; }

        /* ── ERROR BUBBLE ── */
        .cn-bubble.error {
            background: #fef2f2; border-left: 3px solid #ef4444; color: #111;
        }
        .cn-row.user .cn-bubble.error { background: #fef2f2; color: #111; }

        /* ── QUICK-REPLY CHIPS ── */
        .cn-chips {
            display: flex; flex-wrap: wrap; gap: 8px;
            padding: 4px 0 6px;
        }
        .cn-chip {
            display: inline-flex; align-items: center;
            padding: 7px 14px; border-radius: 999px;
            border: 1.5px solid var(--cn-primary);
            background: rgba(0,109,119,.06);
            color: var(--cn-primary); font-size: .82rem; font-weight: 600;
            cursor: pointer; transition: background .15s, color .15s;
            font-family: 'Plus Jakarta Sans', sans-serif;
            white-space: normal; text-align: left; line-height: 1.3;
        }
        .cn-chip:hover  { background: var(--cn-primary); color: #fff; }
        .cn-chip:active { transform: scale(.97); }
        .cn-chip:focus-visible { outline: 2px solid var(--cn-primary); outline-offset: 2px; }

        /* ── ESCALATION CARD ── */
        .cn-escalation {
            margin-top: 10px; padding-top: 10px;
            border-top: 1px solid rgba(0,0,0,.1);
            font-size: .78rem; color: #54656f;
        }
        .cn-escalation p { margin-bottom: 8px; }
        .cn-esc-btn {
            display: inline-block; padding: 6px 14px;
            background: var(--cn-primary); color: #fff;
            border-radius: 8px; font-size: .75rem; font-weight: 600;
            text-decoration: none; transition: background .15s;
        }
        .cn-esc-btn:hover { background: var(--cn-primary-dark); color: #fff; }

        /* ── SCROLL TO BOTTOM BUTTON ── */
        #cn-scroll-btn {
            position: absolute; bottom: 8px; right: 12px;
            background: var(--cn-primary); color: #fff;
            border: none; border-radius: 50%;
            width: 40px; height: 40px;
            cursor: pointer; display: none;
            align-items: center; justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,.2); z-index: 5;
        }
        #cn-scroll-btn.show { display: flex; }
        #cn-scroll-btn:hover { background: var(--cn-primary-dark); }

        /* ── PRE-CHAT FORM ── */
        #cn-prechat {
            position: absolute; left: 0; right: 0; bottom: 0;
            top: 61px; z-index: 10;
            display: flex; flex-direction: column;
            background: #ffffff;
            overflow-y: auto;
        }
        @media (prefers-reduced-motion: no-preference) {
            #cn-prechat { animation: row-in .25s cubic-bezier(.4,0,.2,1) both; }
        }
        #cn-prechat.gone { display: none; }

        /* Pre-chat branding header */
        .cn-pcf-header {
            background: linear-gradient(135deg, var(--cn-primary) 0%, var(--cn-primary-dark) 100%);
            padding: 20px 16px 16px;
            color: white; text-align: center;
            position: relative; overflow: hidden;
        }
        .cn-pcf-company-logo {
            width: 48px; height: 48px; border-radius: 50%;
            background: rgba(255,255,255,0.2); border: 3px solid rgba(255,255,255,0.3);
            margin: 0 auto 12px; display: flex; align-items: center; justify-content: center;
            backdrop-filter: blur(10px);
        }
        .cn-pcf-company-name { font-size: 1.1rem; font-weight: 700; margin-bottom: 4px; }
        .cn-pcf-company-tagline { font-size: 0.8rem; opacity: 0.9; font-weight: 400; }

        /* Form scroll area */
        .cn-pcf-scroll { flex: 1; display: flex; flex-direction: column; padding: 0; gap: 0; }

        .cn-pcf-step { display: none; flex-direction: column; min-height: 100%; }
        @media (prefers-reduced-motion: no-preference) { .cn-pcf-step { animation: step-in 0.3s ease-out both; } }
        .cn-pcf-step.active { display: flex; }
        @keyframes step-in {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .cn-pcf-step-content { padding: 20px 16px; flex: 1; }

        /* Floating-label fields */
        .cn-pcf-field { margin-bottom: 20px; position: relative; }
        .cn-pcf-input-wrapper { position: relative; }
        .cn-pcf-input {
            width: 100%; padding: 16px 40px 8px 16px; border: 2px solid rgba(0,109,119,.2);
            border-radius: 12px; font-size: 1rem; font-family: 'Plus Jakarta Sans',sans-serif;
            color: #111b21; outline: none; transition: all 0.3s ease;
            background: rgba(255,255,255,.95); box-sizing: border-box;
        }
        .cn-pcf-input:focus  { border-color: var(--cn-primary); box-shadow: 0 0 0 4px rgba(0,109,119,.1); background: #fff; }
        .cn-pcf-input.invalid { border-color: #ef4444; box-shadow: 0 0 0 4px rgba(239,68,68,.1); background: #fef2f2; }
        .cn-pcf-input.filled { border-color: var(--cn-primary); }
        .cn-pcf-input::placeholder { color: transparent; }

        .cn-pcf-label {
            position: absolute; top: 16px; left: 16px;
            font-size: 1rem; color: #8696a0; pointer-events: none;
            transition: all 0.3s ease;
        }
        .cn-pcf-input:focus + .cn-pcf-label,
        .cn-pcf-input.filled + .cn-pcf-label { top: 8px; font-size: 0.72rem; color: var(--cn-primary); font-weight: 600; }
        .cn-pcf-input.invalid + .cn-pcf-label { color: #ef4444; }

        .cn-pcf-field-icon {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            font-size: 1.1rem; color: #8696a0; pointer-events: none;
        }
        .cn-pcf-field-error { font-size: 0.72rem; color: #ef4444; margin-top: 4px; padding-left: 4px; display: none; }
        .cn-pcf-input.invalid ~ .cn-pcf-field-error { display: block; }

        /* CTA buttons */
        .cn-pcf-actions {
            padding: 16px; background: rgba(255,255,255,.9);
            backdrop-filter: blur(10px); border-top: 1px solid rgba(0,0,0,.05);
        }
        .cn-pcf-btn-row { display: flex; flex-direction: column; gap: 12px; }
        .cn-pcf-btn.primary {
            width: 100%; padding: 14px 20px; border: none; border-radius: 12px;
            background: linear-gradient(135deg, var(--cn-primary) 0%, var(--cn-primary-dark) 100%);
            color: #fff; font-size: 1rem; font-weight: 700;
            font-family: 'Plus Jakarta Sans',sans-serif;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(0,109,119,.3);
            position: relative; overflow: hidden;
        }
        .cn-pcf-btn.primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,109,119,.4); }
        .cn-pcf-btn.primary:active { transform: translateY(0); }
        .cn-pcf-btn.primary:disabled { background: #a0aeb6; cursor: not-allowed; transform: none; box-shadow: none; }
        .cn-pcf-btn.secondary {
            width: 100%; padding: 12px 20px; border: 2px solid rgba(0,109,119,.3);
            border-radius: 12px; background: rgba(255,255,255,.8);
            color: var(--cn-primary); font-size: 0.95rem; font-weight: 600;
            font-family: 'Plus Jakarta Sans',sans-serif;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;
            transition: all 0.3s ease;
        }
        .cn-pcf-btn.secondary:hover { background: var(--cn-primary); color: white; border-color: var(--cn-primary); }

        /* Consent checkbox */
        .cn-pcf-consent {
            display: flex; align-items: flex-start; gap: 10px;
            font-size: 0.75rem; color: #54656f; line-height: 1.4;
            padding: 12px 0 4px;
        }
        .cn-pcf-consent input[type="checkbox"] {
            width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px;
            accent-color: var(--cn-primary); cursor: pointer;
        }
        .cn-pcf-consent a { color: var(--cn-primary); text-decoration: underline; }

        /* Trust indicator */
        .cn-pcf-trust { display: flex; flex-direction: column; gap: 8px; margin-top: 16px; }
        .cn-pcf-trust-item { display: flex; align-items: center; gap: 8px; font-size: 0.75rem; color: #54656f; }
        .cn-pcf-trust-item svg { width: 14px; height: 14px; flex-shrink: 0; }

        /* Encryption notice */
        .cn-encrypt-notice {
            align-self: center;
            background: rgba(0,109,119,.05); border-radius: 12px;
            font-size: .72rem; font-weight: 500; color: var(--cn-muted);
            padding: 7px 12px; margin: 2px 0 8px;
            display: flex; align-items: center; gap: 5px;
            text-align: center; line-height: 1.4;
        }

        /* ── ANIMATIONS ── */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80%       { transform: translateX(5px); }
        }

        /* ── MOBILE ── */
        @media (max-width: 480px) {
            #cn-widget { bottom: 18px; right: 18px; }
            #cn-window {
                position: fixed; bottom: 0; right: 0; left: 0;
                width: 100%; max-height: 100dvh; height: 100dvh;
                border-radius: 0;
            }
            #cn-input-area {
                padding-bottom: calc(14px + env(safe-area-inset-bottom, 0px));
            }
            #cn-launcher { bottom: 18px; right: 18px; }
            .cn-drag-handle {
                display: flex; width: 100%; justify-content: center; padding: 8px 0 6px;
            }
            .cn-drag-pill { width: 36px; height: 4px; background: #d1d5db; border-radius: 2px; }
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
                <div id="cn-l-text-wrap">
                    <span id="cn-l-greeting">Hi there!</span>
                    <span id="cn-l-title">Virtual Assistant</span>
                </div>
                <div id="cn-l-bot-wrap">
                    <svg id="cn-bot-svg" width="36" height="36" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="overflow:visible">
                        <rect x="20.5" y="1" width="3" height="8" rx="1.5" fill="rgba(255,255,255,0.85)" id="cn-ant-stem"/>
                        <circle id="cn-ant-tip" cx="22" cy="1" r="4" fill="white"/>
                        <rect x="5" y="9" width="34" height="26" rx="9" fill="rgba(255,255,255,0.95)"/>
                        <rect x="1" y="16" width="5" height="10" rx="2.5" fill="rgba(255,255,255,0.75)"/>
                        <rect x="38" y="16" width="5" height="10" rx="2.5" fill="rgba(255,255,255,0.75)"/>
                        <ellipse id="cn-eye-l" cx="14" cy="21" rx="4.5" ry="4.5"/>
                        <circle cx="15.8" cy="19.2" r="1.6" fill="white"/>
                        <ellipse id="cn-eye-r" cx="30" cy="21" rx="4.5" ry="4.5"/>
                        <circle cx="31.8" cy="19.2" r="1.6" fill="white"/>
                        <path id="cn-smile" d="M14 31 Q22 37 30 31" stroke="rgba(255,255,255,0.9)" stroke-width="2.5" stroke-linecap="round" fill="none"/>
                    </svg>
                </div>
                <div id="cn-l-close-icon" aria-hidden="true">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24">
                        <path stroke="#fff" stroke-width="2.5" stroke-linecap="round" d="M18 6 6 18M6 6l12 12"/>
                    </svg>
                </div>
                <span id="cn-badge" aria-hidden="true">1</span>
            </button>

            <div id="cn-window" role="dialog" aria-modal="true" aria-labelledby="cn-hdr-name">
                <div class="cn-drag-handle" style="display:none"><div class="cn-drag-pill"></div></div>
                <div id="cn-header">
                    <div class="cn-hdr-avatar">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <rect x="3" y="8" width="18" height="13" rx="4" fill="rgba(255,255,255,.85)"/>
                            <circle cx="9" cy="14" r="1.5" fill="#006d77"/>
                            <circle cx="15" cy="14" r="1.5" fill="#006d77"/>
                            <rect x="10.5" y="4" width="3" height="4" rx="1.5" fill="rgba(255,255,255,.85)"/>
                            <circle cx="12" cy="4" r="1.5" fill="rgba(255,255,255,.85)"/>
                            <path stroke="rgba(255,255,255,.85)" stroke-width="1.5" stroke-linecap="round" d="M9 18h6"/>
                        </svg>
                        <span class="cn-online-ring" aria-hidden="true"></span>
                    </div>
                    <div class="cn-hdr-info">
                        <div class="cn-hdr-name" id="cn-hdr-name">Assistant</div>
                        <div class="cn-hdr-status">AI-powered &middot; Replies instantly</div>
                    </div>
                    <div class="cn-hdr-btns">
                        <button class="cn-hdr-btn" id="cn-close" title="Close" aria-label="Close chat">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M18 6 6 18M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div id="cn-prechat" class="gone">
                    <div class="cn-pcf-scroll">
                        <div class="cn-pcf-header">
                            <div class="cn-pcf-company-logo">
                                <svg width="28" height="28" fill="none" viewBox="0 0 24 24">
                                    <rect x="3" y="8" width="18" height="13" rx="4" fill="rgba(255,255,255,.9)"/>
                                    <circle cx="9" cy="14" r="1.5" fill="#006d77"/>
                                    <circle cx="15" cy="14" r="1.5" fill="#006d77"/>
                                    <rect x="10.5" y="4" width="3" height="4" rx="1.5" fill="rgba(255,255,255,.9)"/>
                                    <circle cx="12" cy="4" r="1.5" fill="rgba(255,255,255,.9)"/>
                                    <path stroke="rgba(255,255,255,.9)" stroke-width="1.5" stroke-linecap="round" d="M9 18h6"/>
                                </svg>
                            </div>
                            <div class="cn-pcf-company-name" id="cn-pcf-biz-name">ChatBot Nepal</div>
                            <div class="cn-pcf-company-tagline" id="cn-pcf-tagline">We're here to help you!</div>
                        </div>

                        <div class="cn-pcf-step active">
                            <div class="cn-pcf-step-content">
                                <div style="text-align:center;margin-bottom:24px;">
                                    <h3 style="font-size:1.1rem;font-weight:700;color:#111b21;margin-bottom:8px;">Tell us about yourself</h3>
                                    <p style="font-size:0.88rem;color:#54656f;line-height:1.4;">This helps us provide personalized assistance</p>
                                </div>

                                <div class="cn-pcf-field">
                                    <div class="cn-pcf-input-wrapper">
                                        <input id="cn-pcf-name" class="cn-pcf-input" type="text" autocomplete="name">
                                        <label class="cn-pcf-label" for="cn-pcf-name">Full Name</label>
                                        <span class="cn-pcf-field-icon" aria-hidden="true">&#128100;</span>
                                    </div>
                                    <div class="cn-pcf-field-error" role="alert">Please enter a valid name</div>
                                </div>

                                <div class="cn-pcf-field">
                                    <div class="cn-pcf-input-wrapper">
                                        <input id="cn-pcf-email" class="cn-pcf-input" type="email" autocomplete="email">
                                        <label class="cn-pcf-label" for="cn-pcf-email">Email Address</label>
                                        <span class="cn-pcf-field-icon" aria-hidden="true">&#128231;</span>
                                    </div>
                                    <div class="cn-pcf-field-error" role="alert">Please enter a valid email address</div>
                                </div>

                                <div class="cn-pcf-field">
                                    <div class="cn-pcf-input-wrapper">
                                        <input id="cn-pcf-phone" class="cn-pcf-input" type="tel" autocomplete="tel">
                                        <label class="cn-pcf-label" for="cn-pcf-phone">Phone Number (Optional)</label>
                                        <span class="cn-pcf-field-icon" aria-hidden="true">&#128241;</span>
                                    </div>
                                    <div class="cn-pcf-field-error" role="alert">Please enter a valid phone number</div>
                                </div>

                                <div class="cn-pcf-trust">
                                    <div class="cn-pcf-trust-item">
                                        <svg viewBox="0 0 24 24" fill="none">
                                            <path fill="#006d77" d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2Zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2ZM9 8V6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9Z"/>
                                        </svg>
                                        <span>Your information is secure and private</span>
                                    </div>
                                </div>

                                <div class="cn-pcf-consent" id="cn-pcf-consent-wrap">
                                    <input type="checkbox" id="cn-pcf-consent" aria-required="true">
                                    <label for="cn-pcf-consent">
                                        I agree to the
                                        <a id="cn-pcf-privacy-link" href="#" target="_blank" rel="noopener noreferrer">Privacy Policy</a>
                                        and consent to this data being used to assist me.
                                    </label>
                                </div>
                            </div>

                            <div class="cn-pcf-actions">
                                <div class="cn-pcf-btn-row">
                                    <button class="cn-pcf-btn primary" id="cn-pcf-submit">
                                        <span>Start My Conversation</span>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                    <button class="cn-pcf-btn secondary" id="cn-pcf-skip" type="button">
                                        <span>Continue as Guest</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="cn-messages" role="log" aria-live="polite" aria-label="Chat messages">
                    <div class="cn-encrypt-notice" aria-hidden="true">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><path fill="#8696a0" d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2Zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2ZM9 8V6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9Z"/></svg>
                        AI-generated responses. Powered by ChatBot Nepal.
                    </div>
                    <div class="cn-date-pill" id="cn-date-pill" aria-hidden="true">Today</div>
                    <button id="cn-scroll-btn" type="button" aria-label="Scroll to latest message">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                </div>

                <div id="cn-typing" role="status" aria-label="Assistant is typing">
                    <div class="cn-typing-bub">
                        <span class="cn-dot"></span>
                        <span class="cn-dot"></span>
                        <span class="cn-dot"></span>
                    </div>
                </div>

                <div id="cn-input-area">
                    <div class="cn-input-wrap">
                        <span id="cn-char-count" aria-hidden="true"></span>
                        <textarea id="cn-input" placeholder="Type your message..." rows="1"
                            aria-label="Message input" autocomplete="off" maxlength="1000"></textarea>
                    </div>
                    <button id="cn-send" disabled aria-label="Send message">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24">
                            <path fill="#fff" d="M2.01 21 23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </div>

                <div class="cn-footer" id="cn-footer">
                    Powered by <a href="https://chatbotnepal.isoftroerp.com/" target="_blank" rel="noopener noreferrer">ChatBot Nepal</a>
                </div>
            </div>
        `;
        document.body.appendChild(container);

        applyConfig();
        initSession().then(() => setupEvents());
    }

    /* ─────────────────────────────────────────
       MARKDOWN RENDERER
    ───────────────────────────────────────── */
    function renderMarkdown(text) {
        /* 1 — HTML-encode to prevent XSS (backticks / asterisks not affected) */
        let html = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        /* 2 — Fenced code blocks (``` ... ```) */
        html = html.replace(/```([\s\S]*?)```/g, (_, code) =>
            `<pre><code>${code.trim()}</code></pre>`
        );

        /* 3 — Inline code */
        html = html.replace(/`([^`\n]+)`/g, '<code>$1</code>');

        /* 4 — Bold (** or __) */
        html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/__(.+?)__/g, '<strong>$1</strong>');

        /* 5 — Italic (* or _), guarded against double-asterisks already replaced */
        html = html.replace(/\*([^*\n]+)\*/g, '<em>$1</em>');
        html = html.replace(/_([^_\n]+)_/g, '<em>$1</em>');

        /* 6 — Unordered lists */
        html = html.replace(/((?:^[ \t]*[-*+] .+(?:\n|$))+)/gm, (m) => {
            const items = m.trim().split('\n')
                .map(l => `<li>${l.replace(/^[ \t]*[-*+] /, '').trim()}</li>`)
                .join('');
            return `<ul>${items}</ul>`;
        });

        /* 7 — Ordered lists */
        html = html.replace(/((?:^[ \t]*\d+\. .+(?:\n|$))+)/gm, (m) => {
            const items = m.trim().split('\n')
                .map(l => `<li>${l.replace(/^[ \t]*\d+\. /, '').trim()}</li>`)
                .join('');
            return `<ol>${items}</ol>`;
        });

        /* 8 — Headings (h1–h3 → bold block) */
        html = html.replace(/^#{1,3} (.+)$/gm,
            '<strong style="display:block;font-size:1rem;margin:8px 0 4px">$1</strong>');

        /* 9 — Newlines → <br> (skip inside <ul>/<ol>/<pre> by replacing only bare \n) */
        html = html.replace(/\n/g, '<br>');

        return html;
    }

    /* ─────────────────────────────────────────
       CONVERSATION HISTORY LOADER
    ───────────────────────────────────────── */
    async function loadHistory(savedConvId) {
        try {
            const headers = { 'Content-Type': 'application/json' };
            if (sessionToken) headers['X-Session-Token'] = sessionToken;

            const r = await fetch(`${BASE_URL}/api/chat/history`, {
                method: 'POST',
                headers,
                body: JSON.stringify({
                    site_id:         SITE_ID,
                    conversation_id: savedConvId,
                    visitor_id:      visitorId,
                }),
            });

            if (!r.ok) { localStorage.removeItem('cbn_conversation'); return false; }

            const data = await r.json();
            if (!data.messages || !data.messages.length) return false;

            /* Clear just the message rows (keep encrypt notice + date pill) */
            document.querySelectorAll('#cn-messages .cn-row').forEach(el => el.remove());

            data.messages.forEach(msg => {
                addMsg(msg.message, msg.role === 'visitor' ? 'user' : 'bot', false, msg.time);
            });
            conversationId = savedConvId;
            return true;
        } catch (_) {
            localStorage.removeItem('cbn_conversation');
            return false;
        }
    }

    /* ─────────────────────────────────────────
       SESSION — fetch token + config
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
                const incoming = { ...(data.config || {}) };
                if (incoming.company_logo_url) incoming.company_logo_url = normalizeAssetUrl(incoming.company_logo_url);
                if (incoming.bot_avatar_url)   incoming.bot_avatar_url   = normalizeAssetUrl(incoming.bot_avatar_url);
                if (incoming.primary_color)    incoming.primary_color    = normalizeHexColor(incoming.primary_color) || incoming.primary_color;
                config = { ...config, ...incoming };
                applyConfig();
            }
        })
        .catch(() => {})
        .finally(() => { bootChat(); });
    }

    /* ─────────────────────────────────────────
       BOOT — load history or show welcome
    ───────────────────────────────────────── */
    async function bootChat() {
        const savedConvId = loadConversationId();
        if (savedConvId) {
            const restored = await loadHistory(savedConvId);
            if (restored) return;
        }

        const typingEl = document.getElementById('cn-typing');
        typingEl.classList.add('show');
        setTimeout(() => {
            typingEl.classList.remove('show');
            let welcome = config.welcome_message;
            if (visitorInfo.name) welcome = `Hi, ${visitorInfo.name}! ` + welcome;
            const welcomeRow = addMsg(welcome, 'bot');
            /* Render quick-reply chips below the welcome bubble */
            const questions = Array.isArray(config.suggested_questions) ? config.suggested_questions.filter(Boolean) : [];
            if (questions.length) {
                const col = welcomeRow && welcomeRow.querySelector('.cn-col');
                if (col) {
                    const chips = document.createElement('div');
                    chips.className = 'cn-chips';
                    chips.setAttribute('role', 'group');
                    chips.setAttribute('aria-label', 'Suggested questions');
                    questions.slice(0, 4).forEach(q => {
                        const btn = document.createElement('button');
                        btn.className = 'cn-chip';
                        btn.type = 'button';
                        btn.textContent = q;
                        btn.addEventListener('click', () => {
                            chips.remove();
                            const input = document.getElementById('cn-input');
                            if (input) { input.value = q; input.dispatchEvent(new Event('input')); }
                            addMsg(q, 'user');
                            sendMessage(q);
                        });
                        chips.appendChild(btn);
                    });
                    col.appendChild(chips);
                    scrollToBottom();
                }
            }
        }, 900 + Math.random() * 400);
    }

    /* ─────────────────────────────────────────
       FOCUS TRAP
    ───────────────────────────────────────── */
    function buildFocusTrap(container) {
        const sel = 'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
        return function handleTab(e) {
            if (e.key !== 'Tab' && e.key !== 'Escape') return;
            if (e.key === 'Escape') { closeChat(); return; }
            const focusable = Array.from(container.querySelectorAll(sel)).filter(el => {
                const s = window.getComputedStyle(el);
                return s.display !== 'none' && s.visibility !== 'hidden';
            });
            if (!focusable.length) return;
            const first = focusable[0], last = focusable[focusable.length - 1];
            if (e.shiftKey) {
                if (document.activeElement === first) { e.preventDefault(); last.focus(); }
            } else {
                if (document.activeElement === last)  { e.preventDefault(); first.focus(); }
            }
        };
    }

    /* ─────────────────────────────────────────
       EVENTS
    ───────────────────────────────────────── */
    function setupEvents() {
        const launcher  = document.getElementById('cn-launcher');
        const badge     = document.getElementById('cn-badge');
        const win       = document.getElementById('cn-window');
        const closeBtn  = document.getElementById('cn-close');
        const input     = document.getElementById('cn-input');
        const sendBtn   = document.getElementById('cn-send');

        const textWrap  = document.getElementById('cn-l-text-wrap');
        const botWrap   = document.getElementById('cn-l-bot-wrap');
        const closeIcon = document.getElementById('cn-l-close-icon');

        function launcherToClose() {
            if (window.gsap) {
                gsap.to(textWrap, { opacity: 0, x: -12, duration: 0.22, ease: 'power2.in',
                    onComplete: () => { textWrap.style.display = 'none'; }
                });
                gsap.to(botWrap, { opacity: 0, scale: 0.6, duration: 0.18, ease: 'power2.in',
                    onComplete: () => {
                        botWrap.style.display = 'none';
                        closeIcon.style.display = 'flex';
                        gsap.from(closeIcon, { scale: 0, rotation: -90, duration: 0.35, ease: 'back.out(2)' });
                    }
                });
            } else {
                textWrap.style.display = 'none';
                botWrap.style.display  = 'none';
                closeIcon.style.display = 'flex';
            }
        }

        function launcherToBot() {
            if (window.gsap) {
                gsap.to(closeIcon, { scale: 0, rotation: 90, duration: 0.2, ease: 'power2.in',
                    onComplete: () => {
                        closeIcon.style.display = 'none';
                        textWrap.style.display = 'flex';
                        botWrap.style.display  = 'flex';
                        gsap.fromTo(textWrap, { opacity: 0, x: -12 }, { opacity: 1, x: 0, duration: 0.3, ease: 'power2.out' });
                        gsap.fromTo(botWrap, { opacity: 0, scale: 0.6 }, { opacity: 1, scale: 1, duration: 0.35, ease: 'back.out(1.7)' });
                    }
                });
            } else {
                closeIcon.style.display = 'none';
                textWrap.style.display = 'flex';
                botWrap.style.display  = 'flex';
            }
        }

        /* ── DRAG-TO-DISMISS (mobile sheet) ── */
        let dragStartY = 0, dragCurrentY = 0, dragging = false;
        const dragHandle = document.querySelector('.cn-drag-handle');

        function onDragStart(e) {
            const messages = document.getElementById('cn-messages');
            if (messages.scrollTop > 0) return; /* only dismiss when scrolled to top */
            dragging    = true;
            dragStartY  = (e.touches ? e.touches[0].clientY : e.clientY);
            dragCurrentY = dragStartY;
            win.style.transition = 'none';
        }
        function onDragMove(e) {
            if (!dragging) return;
            dragCurrentY = (e.touches ? e.touches[0].clientY : e.clientY);
            const delta = Math.max(0, dragCurrentY - dragStartY);
            win.style.transform = `translateY(${delta}px)`;
        }
        function onDragEnd() {
            if (!dragging) return;
            dragging = false;
            win.style.transition = '';
            const delta = dragCurrentY - dragStartY;
            if (delta > 120) {
                win.style.transform = '';
                closeChat();
            } else {
                win.style.transform = '';
            }
        }
        dragHandle.addEventListener('touchstart', onDragStart, { passive: true });
        dragHandle.addEventListener('touchmove',  onDragMove,  { passive: true });
        dragHandle.addEventListener('touchend',   onDragEnd);

        function openChat() {
            isWindowOpen = true;
            win.classList.add('open');
            badge.classList.add('gone');
            launcherToClose();

            if (window.innerWidth <= 480) {
                dragHandle.style.display = 'flex';
            }

            /* Install focus trap */
            focusTrapHandler = buildFocusTrap(win);
            document.addEventListener('keydown', focusTrapHandler);

            const prechat = document.getElementById('cn-prechat');
            const skipPrechat = sessionStorage.getItem('cbn_prechat_skipped') === '1';
            const needsPrechat = config.prechat_enabled && !skipPrechat && (!visitorInfo.name || !visitorInfo.email);
            if (needsPrechat) {
                prechat.classList.remove('gone');
                setTimeout(() => {
                    const first = document.getElementById('cn-pcf-name');
                    if (first) first.focus();
                }, 200);
            } else {
                input.focus();
            }
        }

        function closeChat() {
            isWindowOpen = false;
            win.classList.remove('open');
            /* Note: badge stays gone after first open — no artificial re-appearance */
            launcherToBot();
            dragHandle.style.display = 'none';
            if (focusTrapHandler) {
                document.removeEventListener('keydown', focusTrapHandler);
                focusTrapHandler = null;
            }
            launcher.focus();
        }

        launcher.addEventListener('click', () => isWindowOpen ? closeChat() : openChat());
        closeBtn.addEventListener('click', closeChat);

        /* Auto-grow textarea */
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
                charCount.className = 'warning';
            } else if (remaining <= 0) {
                charCount.textContent = '0 / 1000';
                charCount.className = 'error';
            } else {
                charCount.textContent = '';
                charCount.className = '';
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
            document.getElementById('cn-char-count').textContent = '';
            autoResize();
            /* Check for escalation intent before sending */
            if (ESCALATION_RE.test(text)) {
                window._pendingEscalation = true;
            }
            sendMessage(text);
        }

        sendBtn.addEventListener('click', handleSend);
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); handleSend(); }
        });

        /* ── PRE-CHAT FORM ── */
        function dismissPrechat() {
            const prechat = document.getElementById('cn-prechat');
            prechat.classList.add('gone');
            const submitBtn = document.getElementById('cn-pcf-submit');
            if (submitBtn._originalHtml) {
                submitBtn.innerHTML = submitBtn._originalHtml;
                submitBtn.disabled = false;
                delete submitBtn._originalHtml;
            }
            input.focus();
            /* Personalize welcome if visible */
            if (visitorInfo.name) {
                const welcomeRows = document.querySelectorAll('#cn-messages .cn-row.bot');
                if (welcomeRows.length === 1) {
                    const firstBubble = welcomeRows[0].querySelector('.cn-bubble');
                    if (firstBubble && !firstBubble.dataset.personalized) {
                        firstBubble.innerHTML = renderMarkdown(`Hi, ${visitorInfo.name}! ` + config.welcome_message);
                        firstBubble.dataset.personalized = '1';
                    }
                }
            }
        }

        /* Field helpers */
        function validateField(field) {
            const value = field.value.trim();
            const isOptional = field.id === 'cn-pcf-phone';

            field.classList.remove('invalid');

            if (!isOptional && !value) {
                showError(field, 'This field is required'); return false;
            }
            if (value) {
                if (field.id === 'cn-pcf-name' && value.length < 2) {
                    showError(field, 'Name must be at least 2 characters'); return false;
                }
                if (field.id === 'cn-pcf-email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    showError(field, 'Please enter a valid email address'); return false;
                }
                if (field.id === 'cn-pcf-phone' && !/^[\+]?[1-9][\d]{0,15}$/.test(value.replace(/[\s\-\(\)]/g, ''))) {
                    showError(field, 'Please enter a valid phone number'); return false;
                }
            }
            hideError(field);
            return true;
        }

        function showError(field, msg) {
            field.classList.add('invalid');
            const err = field.parentElement.querySelector('.cn-pcf-field-error');
            if (err) err.textContent = msg;
        }
        function hideError(field) {
            field.classList.remove('invalid');
            const err = field.parentElement.querySelector('.cn-pcf-field-error');
            if (err) err.textContent = '';
        }

        ['cn-pcf-name','cn-pcf-email','cn-pcf-phone'].forEach((id, idx, arr) => {
            const field = document.getElementById(id);
            field.addEventListener('input', function() {
                if (this.value.trim()) this.classList.add('filled');
                else this.classList.remove('filled');
                validateField(this);
            });
            field.addEventListener('blur', function() { validateField(this); });
            field.addEventListener('keydown', e => {
                if (e.key !== 'Enter') return;
                e.preventDefault();
                const next = document.getElementById(arr[idx + 1]);
                if (next) next.focus();
                else document.getElementById('cn-pcf-submit').click();
            });
        });

        document.getElementById('cn-pcf-submit').addEventListener('click', () => {
            const fields = ['cn-pcf-name','cn-pcf-email','cn-pcf-phone'];
            let valid = true, firstInvalid = null;
            fields.forEach(id => {
                if (!validateField(document.getElementById(id))) {
                    valid = false; if (!firstInvalid) firstInvalid = document.getElementById(id);
                }
            });

            /* Consent checkbox */
            const consentEl = document.getElementById('cn-pcf-consent');
            if (consentEl && !consentEl.checked) {
                valid = false;
                consentEl.closest('.cn-pcf-consent').style.outline = '2px solid #ef4444';
                consentEl.closest('.cn-pcf-consent').style.borderRadius = '6px';
                if (!firstInvalid) firstInvalid = consentEl;
            } else if (consentEl) {
                consentEl.closest('.cn-pcf-consent').style.outline = '';
            }

            if (!valid) {
                if (firstInvalid) firstInvalid.focus();
                const content = document.querySelector('.cn-pcf-step-content');
                if (content) { content.style.animation = 'none'; requestAnimationFrame(() => { content.style.animation = 'shake 0.5s ease-in-out'; }); }
                return;
            }

            visitorInfo = {
                name:  document.getElementById('cn-pcf-name').value.trim(),
                email: document.getElementById('cn-pcf-email').value.trim(),
                phone: document.getElementById('cn-pcf-phone').value.trim(),
            };
            localStorage.setItem('cbn_visitor_info', JSON.stringify(visitorInfo));

            const submitBtn = document.getElementById('cn-pcf-submit');
            if (!submitBtn._originalHtml) submitBtn._originalHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span>Starting Chat...</span>';
            submitBtn.disabled = true;

            setTimeout(dismissPrechat, 500);
        });

        document.getElementById('cn-pcf-skip').addEventListener('click', () => {
            sessionStorage.setItem('cbn_prechat_skipped', '1');
            dismissPrechat();
        });

        /* Scroll to bottom button */
        const messages  = document.getElementById('cn-messages');
        const scrollBtn = document.getElementById('cn-scroll-btn');
        messages.addEventListener('scroll', () => {
            const atBottom = messages.scrollHeight - messages.scrollTop <= messages.clientHeight + 50;
            scrollBtn.classList.toggle('show', !atBottom);
        });
        scrollBtn.addEventListener('click', scrollToBottom);

        /* Retry on error bubbles */
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
       SEND MESSAGE — SSE streaming
    ───────────────────────────────────────── */
    function sendMessage(text) {
        busy = true;
        const typingEl = document.getElementById('cn-typing');
        const sendBtn  = document.getElementById('cn-send');
        typingEl.classList.add('show');
        sendBtn.disabled = true;
        scrollToBottom();

        const headers = { 'Content-Type': 'application/json' };
        if (sessionToken) headers['X-Session-Token'] = sessionToken;

        const body = JSON.stringify({
            site_id:         SITE_ID,
            message:         text,
            visitor_id:      visitorId,
            conversation_id: conversationId || undefined,
            source_url:      window.location.href,
            visitor_name:    visitorInfo.name  || undefined,
            visitor_email:   visitorInfo.email || undefined,
            visitor_phone:   visitorInfo.phone || undefined,
        });

        let streamRow     = null;
        let streamBubble  = null;
        let streamContent = '';
        let finished      = false;

        function finalize() {
            if (finished) return;
            finished = true;
            typingEl.classList.remove('show');
            busy = false;
            sendBtn.disabled = false;
            scrollToBottom();
            if (streamContent && window._pendingEscalation) {
                window._pendingEscalation = false;
                maybeShowEscalation(streamBubble);
            }
        }

        fetch(`${BASE_URL}/api/chat/stream`, { method: 'POST', headers, body })
        .then(r => {
            if (!r.ok) return r.json().then(d => Promise.reject(d));

            typingEl.classList.remove('show');
            streamRow    = addMsg('', 'bot');
            streamBubble = streamRow.querySelector('.cn-bubble');

            const reader  = r.body.getReader();
            const decoder = new TextDecoder();
            let buffer    = '';

            function pump() {
                return reader.read().then(({ done, value }) => {
                    if (done) { finalize(); return; }

                    buffer += decoder.decode(value, { stream: true });
                    const parts = buffer.split('\n\n');
                    buffer = parts.pop();

                    for (const part of parts) {
                        const line = part.trim();
                        if (!line.startsWith('data: ')) continue;
                        try {
                            const ev = JSON.parse(line.slice(6));
                            if (ev.type === 'chunk') {
                                streamContent += ev.content;
                                streamBubble.innerHTML = renderMarkdown(streamContent);
                                scrollToBottom();
                            } else if (ev.type === 'done') {
                                if (ev.conversation_id) {
                                    conversationId = ev.conversation_id;
                                    saveConversationId(conversationId);
                                }
                                updateChecksToRead();
                            } else if (ev.type === 'error') {
                                streamContent = ev.message || 'Something went wrong.';
                                streamBubble.innerHTML = renderMarkdown(streamContent);
                                streamBubble.classList.add('error');
                                addRetryLink(streamBubble);
                            }
                        } catch (_) {}
                    }
                    return pump();
                });
            }
            return pump();
        })
        .catch(err => {
            if (!streamRow) {
                /* Stream not started — fall back to blocking API */
                useFallbackChat(text);
            } else {
                /* Partial stream failed */
                if (!streamContent) {
                    streamBubble.innerHTML = 'Unable to complete response.';
                    streamBubble.classList.add('error');
                    addRetryLink(streamBubble);
                }
                finalize();
            }
        })
        .finally(finalize);
    }

    /* Fallback to blocking /api/chat (HTTP environments or stream error) */
    function useFallbackChat(text) {
        busy = true;
        const typingEl = document.getElementById('cn-typing');
        const sendBtn  = document.getElementById('cn-send');
        typingEl.classList.add('show');
        sendBtn.disabled = true;

        const headers = { 'Content-Type': 'application/json' };
        if (sessionToken) headers['X-Session-Token'] = sessionToken;

        fetch(`${BASE_URL}/api/chat`, {
            method: 'POST', headers,
            body: JSON.stringify({
                site_id:         SITE_ID,
                message:         text,
                visitor_id:      visitorId,
                conversation_id: conversationId || undefined,
                source_url:      window.location.href,
                visitor_name:    visitorInfo.name  || undefined,
                visitor_email:   visitorInfo.email || undefined,
                visitor_phone:   visitorInfo.phone || undefined,
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
                    saveConversationId(conversationId);
                }
                addMsg(data.reply, 'bot');
                updateChecksToRead();
            } else {
                addMsg('Sorry, I could not process that. Please try again.', 'bot', true);
            }
        })
        .catch(err => {
            const msg = (err && err.error) ? err.error : 'Unable to connect. Please check your connection and try again.';
            addMsg(msg, 'bot', true);
        })
        .finally(() => {
            typingEl.classList.remove('show');
            busy = false;
            sendBtn.disabled = false;
            scrollToBottom();
        });
    }

    /* ─────────────────────────────────────────
       ESCALATION CTA
    ───────────────────────────────────────── */
    function maybeShowEscalation(bubble) {
        if (!bubble || bubble.querySelector('.cn-escalation')) return;
        const email = config.support_email;
        if (!email) return;

        const card = document.createElement('div');
        card.className = 'cn-escalation';
        card.innerHTML = `
            <p>Want to speak with our team directly?</p>
            <a href="mailto:${escapeAttr(email)}" class="cn-esc-btn">Contact Support &rarr;</a>
        `;
        bubble.appendChild(card);
        scrollToBottom();
    }

    /* ─────────────────────────────────────────
       DOM HELPERS
    ───────────────────────────────────────── */
    function formatDateLabel(date) {
        const today     = new Date();
        const yesterday = new Date(today); yesterday.setDate(today.getDate() - 1);
        const ymd = (d) => d.toISOString().slice(0, 10);
        if (ymd(date) === ymd(today))     return 'Today';
        if (ymd(date) === ymd(yesterday)) return 'Yesterday';
        return date.toLocaleDateString([], { weekday: 'long', month: 'short', day: 'numeric' });
    }

    function addMsg(text, role, isError, timeOverride) {
        const msgDate    = timeOverride ? new Date(timeOverride) : new Date();
        const msgDateStr = msgDate.toISOString().slice(0, 10);
        const container  = document.getElementById('cn-messages');

        /* Insert date pill on first message or when date changes */
        if (!firstMsgAdded || msgDateStr !== lastMsgDateStr) {
            if (!firstMsgAdded) {
                /* Replace the static placeholder pill on very first message */
                const staticPill = document.getElementById('cn-date-pill');
                if (staticPill) staticPill.remove();
            }
            firstMsgAdded  = true;
            lastMsgDateStr = msgDateStr;
            const pill = document.createElement('div');
            pill.className = 'cn-date-pill';
            pill.setAttribute('aria-hidden', 'true');
            pill.textContent = formatDateLabel(msgDate);
            container.appendChild(pill);
        }

        const row = document.createElement('div');
        row.className = `cn-row ${role}`;

        if (role === 'bot') {
            const avatar = document.createElement('div');
            avatar.className = 'cn-msg-avatar';
            avatar.setAttribute('aria-hidden', 'true');
            if (config.company_logo_url) {
                avatar.innerHTML = `<img src="${escapeAttr(config.company_logo_url)}" alt="">`;
            } else {
                avatar.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path fill="currentColor" d="M12 2a7 7 0 0 0-7 7v3a6 6 0 0 0 6 6h2a6 6 0 0 0 6-6V9a7 7 0 0 0-7-7Zm-4 9a2 2 0 1 1 0-4 2 2 0 0 1 0 4Zm8 0a2 2 0 1 1 0-4 2 2 0 0 1 0 4Zm-8.5 9.5A2.5 2.5 0 0 1 10 18h4a2.5 2.5 0 0 1 2.5 2.5c0 .83-.67 1.5-1.5 1.5H9c-.83 0-1.5-.67-1.5-1.5Z"/></svg>`;
            }
            row.appendChild(avatar);
        }

        const col    = document.createElement('div');
        col.className = 'cn-col';

        const bubble = document.createElement('div');
        bubble.className = 'cn-bubble' + (isError ? ' error' : '');

        const content = text ? renderMarkdown(text) : '';

        let footer = '';
        if (config.message_meta_enabled) {
            const timeStr = timeOverride || formatTime(new Date());
            const checkSvg = role === 'user'
                ? `<span class="cn-check cn-check-pending" aria-label="Sent" aria-hidden="true"><svg width="12" height="9" viewBox="0 0 12 9" fill="none"><path d="M1 4.5 4.5 8 11 1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>`
                : '';
            footer = `<span class="cn-ts-row"><span class="cn-ts">${timeStr}</span>${checkSvg}</span>`;
        }
        if (isError) {
            footer += `<a href="#" class="cn-retry" style="margin-left:8px;color:var(--cn-primary);font-size:.75rem;font-weight:600;text-decoration:underline;">Try again</a>`;
        }

        bubble.innerHTML = content + footer;
        col.appendChild(bubble);
        row.appendChild(col);
        container.appendChild(row);
        scrollToBottom();
        return row;
    }

    function addRetryLink(bubble) {
        if (!bubble || bubble.querySelector('.cn-retry')) return;
        const a = document.createElement('a');
        a.href = '#'; a.className = 'cn-retry';
        a.style.cssText = 'display:block;margin-top:6px;color:var(--cn-primary);font-size:.75rem;font-weight:600;text-decoration:underline;';
        a.textContent = 'Try again';
        bubble.appendChild(a);
    }

    function scrollToBottom() {
        const container = document.getElementById('cn-messages');
        if (container) container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
    }

    function updateChecksToRead() {
        document.querySelectorAll('.cn-check-pending').forEach(el => {
            el.classList.remove('cn-check-pending');
            el.classList.add('cn-check-replied');
        });
    }

    function formatTime(date) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function escapeAttr(str) {
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    /* ─────────────────────────────────────────
       APPLY CONFIG
    ───────────────────────────────────────── */
    function applyConfig() {
        const widgetRoot = document.getElementById('cn-widget');
        if (widgetRoot) {
            const primary     = normalizeHexColor(config.primary_color) || '#006d77';
            const primaryDark = darkenHex(primary, 0.24) || primary;
            widgetRoot.style.setProperty('--cn-primary',      primary);
            widgetRoot.style.setProperty('--cn-primary-dark', primaryDark);
            widgetRoot.style.setProperty('--cn-user-bubble',  primary);

            const pos = getLauncherPosition();
            widgetRoot.style.bottom = pos.bottom;
            widgetRoot.style.right  = pos.right;
            widgetRoot.style.left   = pos.left;

            /* Also reposition the chat window to match launcher side */
            const winEl = document.getElementById('cn-window');
            if (winEl) {
                if ((config.position || 'bottom-right') === 'bottom-left') {
                    winEl.style.right = 'auto';
                    winEl.style.left  = '24px';
                } else {
                    winEl.style.left  = 'auto';
                    winEl.style.right = '24px';
                }
            }
        }

        /* Header: show bot_name, fall back to business_name */
        const nameEl = document.getElementById('cn-hdr-name');
        if (nameEl) nameEl.textContent = config.bot_name || config.business_name;

        /* Launcher label */
        const launcherTitle = document.getElementById('cn-l-title');
        if (launcherTitle) launcherTitle.textContent = (config.bot_name || config.business_name || 'Virtual') + ' Assistant';

        /* Pre-chat company name + tagline */
        const pcfBiz = document.getElementById('cn-pcf-biz-name');
        if (pcfBiz) pcfBiz.textContent = config.business_name;

        const pcfTagline = document.getElementById('cn-pcf-tagline');
        if (pcfTagline) pcfTagline.textContent = config.tagline || "We're here to help you!";

        /* Privacy policy link in pre-chat form */
        const privacyLink = document.getElementById('cn-pcf-privacy-link');
        if (privacyLink) {
            if (config.privacy_policy_url) {
                privacyLink.href = config.privacy_policy_url;
            } else {
                /* No URL configured — hide the consent checkbox entirely to avoid dead link */
                const consentWrap = document.getElementById('cn-pcf-consent-wrap');
                if (consentWrap) consentWrap.style.display = 'none';
            }
        }

        /* Sync bot eye fill and smile to primary */
        const primary = normalizeHexColor(config.primary_color) || '#006d77';
        ['cn-eye-l','cn-eye-r'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.setAttribute('fill', primary);
        });
        const smile = document.getElementById('cn-smile');
        if (smile) smile.setAttribute('stroke', primary);

        /* Powered-by footer */
        const footer = document.getElementById('cn-footer');
        if (footer && config.show_powered_by === false) footer.style.display = 'none';

        /* Header avatar — company logo or bot avatar */
        const headerAvatar = document.querySelector('.cn-hdr-avatar');
        if (headerAvatar) {
            const logoSrc = config.company_logo_url || config.bot_avatar_url;
            if (logoSrc) {
                headerAvatar.innerHTML = `<img src="${escapeAttr(logoSrc)}" alt="${escapeAttr(config.bot_name || config.business_name)}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;"><span class="cn-online-ring" aria-hidden="true"></span>`;
            }
        }

        /* Pre-chat logo */
        const prechatLogo = document.querySelector('.cn-pcf-company-logo');
        if (prechatLogo && config.company_logo_url) {
            prechatLogo.innerHTML = `<img src="${escapeAttr(config.company_logo_url)}" alt="${escapeAttr(config.business_name)}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">`;
        }

        /* Pre-chat visibility */
        const prechat = document.getElementById('cn-prechat');
        if (prechat && !config.prechat_enabled) prechat.style.display = 'none';

        /* Watermark */
        const messagesEl = document.getElementById('cn-messages');
        if (messagesEl) {
            if (config.watermark_enabled && config.company_logo_url) {
                messagesEl.classList.add('has-watermark');
                messagesEl.style.setProperty('--watermark-opacity', config.watermark_opacity || 0.1);
                const posMap = { 'top-left': 'left top', 'top-right': 'right top', 'bottom-left': 'left bottom', 'bottom-right': 'right bottom' };
                messagesEl.style.setProperty('--watermark-position', posMap[config.watermark_position] || 'center');
                messagesEl.style.setProperty('--watermark-image', `url("${config.company_logo_url}")`);
            } else {
                messagesEl.classList.remove('has-watermark');
                messagesEl.style.removeProperty('--watermark-image');
            }
        }
    }

    /* ─────────────────────────────────────────
       UTILITIES
    ───────────────────────────────────────── */
    function uuidv4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    /* ─────────────────────────────────────────
       GSAP — dynamic load with SRI
    ───────────────────────────────────────── */
    function loadGSAP(callback) {
        if (window.gsap) { callback(); return; }
        const s = document.createElement('script');
        s.src         = 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js';
        s.crossOrigin = 'anonymous';
        s.onload  = callback;
        s.onerror = callback; // graceful: CSS-only animations
        document.head.appendChild(s);
    }

    /* ─────────────────────────────────────────
       LAUNCHER ANIMATIONS
    ───────────────────────────────────────── */
    function initLauncherAnimations() {
        if (!window.gsap) return;

        const launcher = document.getElementById('cn-launcher');
        const botWrap  = document.getElementById('cn-l-bot-wrap');
        const botSvg   = document.getElementById('cn-bot-svg');
        const antTip   = document.getElementById('cn-ant-tip');
        const eyeL     = document.getElementById('cn-eye-l');
        const eyeR     = document.getElementById('cn-eye-r');
        const textWrap = document.getElementById('cn-l-text-wrap');
        if (!launcher) return;

        /* Entrance */
        const entry = gsap.timeline({ delay: 0.4 });
        entry
            .from(launcher, { y: 50, opacity: 0, scale: 0.7, duration: 0.85, ease: 'back.out(2)' })
            .from(textWrap, { opacity: 0, x: -16, duration: 0.45, ease: 'power2.out' }, '-=0.4')
            .from(botSvg,   { scale: 0, rotation: -25, opacity: 0, duration: 0.55, ease: 'back.out(2.5)', transformOrigin: 'center center' }, '-=0.35');

        /* Idle float */
        gsap.to(launcher, { y: -6, duration: 2.4, ease: 'sine.inOut', repeat: -1, yoyo: true });

        /* Bot head bob */
        gsap.to(botWrap, { rotation: 4, duration: 1.9, ease: 'sine.inOut', repeat: -1, yoyo: true, transformOrigin: 'center bottom' });

        /* Antenna glow */
        if (antTip) {
            gsap.to(antTip, { scale: 1.5, opacity: 0.5, duration: 0.85, ease: 'sine.inOut', repeat: -1, yoyo: true, transformOrigin: 'center center' });
        }

        /* Eye blink */
        function scheduleBlink() {
            if (!document.getElementById('cn-launcher')) return;
            setTimeout(() => {
                if (!eyeL || !eyeR) return;
                gsap.timeline({ onComplete: scheduleBlink })
                    .to([eyeL, eyeR], { scaleY: 0.06, duration: 0.07, ease: 'power3.in', transformOrigin: 'center center' })
                    .to([eyeL, eyeR], { scaleY: 1,    duration: 0.11, ease: 'power2.out', transformOrigin: 'center center' });
            }, 1800 + Math.random() * 2800);
        }
        scheduleBlink();

        /* Greeting shimmer */
        const greeting = document.getElementById('cn-l-greeting');
        if (greeting) gsap.to(greeting, { opacity: 0.65, duration: 1.8, ease: 'sine.inOut', repeat: -1, yoyo: true });

        /* Hover spring */
        launcher.addEventListener('mouseenter', () => {
            if (isWindowOpen) return;
            gsap.to(launcher, { scale: 1.06, duration: 0.35, ease: 'back.out(2)', overwrite: 'auto' });
            gsap.to(botSvg,   { rotation: 8, duration: 0.3, ease: 'back.out(2)', transformOrigin: 'center center', overwrite: 'auto' });
        });
        launcher.addEventListener('mouseleave', () => {
            if (isWindowOpen) return;
            gsap.to(launcher, { scale: 1, duration: 0.4, ease: 'elastic.out(1, 0.5)', overwrite: 'auto' });
            gsap.to(botSvg,   { rotation: 0, duration: 0.4, ease: 'elastic.out(1, 0.5)', transformOrigin: 'center center', overwrite: 'auto' });
        });

        /* Click press */
        launcher.addEventListener('mousedown', () => gsap.to(launcher, { scale: 0.94, duration: 0.12, ease: 'power3.in', overwrite: 'auto' }));
        launcher.addEventListener('mouseup',   () => gsap.to(launcher, { scale: 1,    duration: 0.3,  ease: 'back.out(2)',  overwrite: 'auto' }));
    }

    /* ─────────────────────────────────────────
       START
    ───────────────────────────────────────── */
    function startWidget() {
        init();
        loadGSAP(initLauncherAnimations);
    }

    if (document.readyState === 'complete') startWidget();
    else window.addEventListener('load', startWidget);

})();
