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
        primary_color: '#006d77',
        bot_name: 'Assistant',
        bot_avatar_url: null,
        show_powered_by: true,
        prechat_enabled: false,
        company_logo_url: null,
        watermark_enabled: false,
        watermark_opacity: 0.1,
        watermark_position: 'center',
        message_meta_enabled: false,
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

    function getLauncherPosition() {
        const pos = config.position || 'bottom-right';
        switch (pos) {
            case 'bottom-left': return { bottom: '24px', left: '24px', right: 'auto' };
            case 'bottom-right': default: return { bottom: '24px', right: '24px', left: 'auto' };
        }
    }

    function normalizeAssetUrl(maybeUrl) {
        if (!maybeUrl || typeof maybeUrl !== 'string') return null;
        try { return new URL(maybeUrl, BASE_URL).href; } catch { return maybeUrl; }
    }

    function normalizeHexColor(maybeHex) {
        if (!maybeHex || typeof maybeHex !== 'string') return null;
        const hex = maybeHex.trim();
        if (!hex) return null;
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
        const dr = Math.round(r * (1 - amt));
        const dg = Math.round(g * (1 - amt));
        const db = Math.round(b * (1 - amt));
        const toHex = (n) => n.toString(16).padStart(2, '0');
        return `#${toHex(dr)}${toHex(dg)}${toHex(db)}`;
    }

    /* ─────────────────────────────────────────
       DESIGN TOKENS — WhatsApp-style Chat
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
        /* pulse ring behind bot wrap */
        #cn-l-bot-wrap::before {
            content: ''; position: absolute; inset: -5px; border-radius: 50%;
            border: 2px solid rgba(255,255,255,.4);
            animation: cn-pill-ring 2.8s ease-out infinite;
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

        /* ── HEADER — WhatsApp teal ── */
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
            display: flex; align-items: center; gap: 7px; font-weight: 500;
            line-height: 1;
        }
        .cn-status-dot {
            width: 8px; height: 8px; border-radius: 999px;
            background: #22c55e;
            box-shadow: 0 0 0 3px rgba(34,197,94,.18);
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
            pointer-events: none;
            z-index: 1;
            opacity: 0;
            transition: opacity 0.3s ease;
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
            display: none;
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
            align-items: flex-end;
            gap: 10px;
            animation: row-in .2s ease both;
            padding: 7px 0;
        }
        @keyframes row-in {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .cn-row.user { justify-content: flex-end; }
        .cn-row.bot { justify-content: flex-start; }

        .cn-col {
            max-width: 76%;
            position: relative;
        }

        .cn-bubble {
            padding: 12px 14px;
            font-size: .92rem; line-height: 1.55; word-break: break-word;
            position: relative;
            min-width: 72px;
        }
        .cn-bubble strong { font-weight: 700; }
        .cn-bubble ul { padding-left: 16px; margin-top: 4px; }
        .cn-bubble li { margin-bottom: 2px; }

        /* Bot (incoming) bubble — landing-page style */
        .cn-row.bot .cn-bubble {
            background: var(--cn-bot-bubble);
            color: var(--cn-text);
            border-radius: 1rem 1rem 1rem 0.25rem;
            box-shadow: none;
        }
        .cn-row.bot .cn-col::before {
            display: none;
            content: none;
        }

        /* User (outgoing) bubble — landing-page style */
        .cn-row.user .cn-bubble {
            background: var(--cn-user-bubble);
            color: #fff;
            border-radius: 1rem 1rem 0.25rem 1rem;
            box-shadow: none;
        }
        .cn-row.user .cn-col::before {
            display: none;
            content: none;
        }

        /* Bot message avatar badge */
        .cn-msg-avatar {
            width: 34px; height: 34px;
            border-radius: 999px;
            background: var(--cn-primary);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            flex: 0 0 auto;
            box-shadow: 0 2px 10px rgba(0,109,119,.10);
            overflow: hidden;
        }
        .cn-msg-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 999px; }

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
            display: none;
            content: none;
        }
        .cn-typing-bub {
            background: var(--cn-bot-bubble);
            border-radius: 1rem 1rem 1rem 0.25rem;
            padding: 12px 15px; display: flex; gap: 5px; align-items: center;
            box-shadow: none;
        }
        .cn-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: #bec8ca; animation: tdot 1.25s ease-in-out infinite;
        }
        .cn-dot:nth-child(2) { animation-delay: .16s; }
        .cn-dot:nth-child(3) { animation-delay: .32s; }
        @keyframes tdot {
            0%, 60%, 100% { transform: translateY(0); background: #bec8ca; }
            30% { transform: translateY(-5px); background: var(--cn-primary); }
        }

        /* ── INPUT AREA — WhatsApp style ── */
        #cn-input-area {
            padding: 12px 12px 14px;
            background: var(--cn-surface);
            display: flex; align-items: flex-end;
            gap: 10px; flex-shrink: 0;
        }
        .cn-input-wrap {
            flex: 1;
            background: var(--cn-input-bg);
            border-radius: 18px;
            display: flex; align-items: flex-end;
            position: relative;
            padding: 8px 10px 8px 14px;
            border: 1px solid rgba(190,200,202,.4);
            box-shadow: 0 2px 10px rgba(0,109,119,.06);
        }
        #cn-input {
            flex: 1; background: transparent; border: none;
            color: var(--cn-text);
            font-family: 'Plus Jakarta Sans', sans-serif; font-size: .92rem;
            font-weight: 500; padding: 8px 6px; outline: none;
            resize: none; height: 38px; max-height: 96px; line-height: 1.4;
        }
        #cn-input::placeholder { color: #9ca3af; font-weight: 500; }

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
            width: 44px; height: 44px; border-radius: 12px; border: none;
            background: var(--cn-primary);
            color: #fff; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: transform .15s ease, background .15s;
            box-shadow: 0 8px 18px rgba(0,109,119,.12); outline: none; flex-shrink: 0;
        }
        #cn-send:hover { background: var(--cn-primary-dark); transform: translateY(-1px); }
        #cn-send:active { transform: scale(.94); }
        #cn-send:disabled { background: #a0aeb6; cursor: not-allowed; transform: none; box-shadow: none; }

        /* Character counter */
        #cn-char-count {
            position: absolute; bottom: 6px; right: 12px;
            font-size: .65rem; color: #8696a0;
        }
        #cn-char-count.warning { color: #f59e0b; }
        #cn-char-count.error { color: #ef4444; font-weight: 600; }

        /* ── FOOTER ── */
        .cn-footer {
            text-align: center; padding: 5px 0 7px;
            font-size: .6rem; color: var(--cn-muted); letter-spacing: .02em;
            background: var(--cn-surface);
        }
        .cn-footer a { color: var(--cn-primary); text-decoration: none; font-weight: 600; }
        .cn-footer a:hover { color: var(--cn-primary); }

        /* ── ERROR BUBBLE ── */
        .cn-bubble.error {
            background: #fff; border-left: 3px solid #ef4444;
        }

        /* ── SCROLL TO BOTTOM BUTTON ── */
        #cn-scroll-btn {
            position: absolute; bottom: 8px; right: 12px;
            background: var(--cn-primary); color: #fff;
            border: none; border-radius: 20px;
            padding: 8px 14px; font-size: .75rem; font-weight: 600;
            cursor: pointer; display: none;
            box-shadow: 0 2px 8px rgba(0,0,0,.2);
            z-index: 5;
        }
        #cn-scroll-btn.show { display: flex; align-items: center; gap: 4px; }
        #cn-scroll-btn:hover { background: var(--cn-primary-dark); }

        /* ── PRE-CHAT FORM — Enhanced UX ── */
        #cn-prechat {
            position: absolute; left: 0; right: 0; bottom: 0;
            top: 61px; /* sit below the header */
            z-index: 10;
            display: flex; flex-direction: column;
            background-color: #efeae2;
            background-image: url("data:image/svg+xml,%3Csvg width='200' height='200' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3Cpattern id='p' width='40' height='40' patternUnits='userSpaceOnUse'%3E%3Cpath d='M20 2a2 2 0 110 4 2 2 0 010-4z' fill='%23d6cfc5' opacity='.35'/%3E%3Cpath d='M8 15l4-3 4 3' stroke='%23d6cfc5' fill='none' stroke-width='.8' opacity='.3'/%3E%3Ccircle cx='32' cy='28' r='1.5' fill='%23d6cfc5' opacity='.25'/%3E%3Cpath d='M2 32l3 3h-6z' fill='%23d6cfc5' opacity='.2'/%3E%3Crect x='28' y='8' width='4' height='3' rx='1' fill='%23d6cfc5' opacity='.2'/%3E%3Cpath d='M16 34a3 3 0 016 0' stroke='%23d6cfc5' fill='none' stroke-width='.7' opacity='.25'/%3E%3Cpath d='M35 18l2 4h-4z' fill='%23d6cfc5' opacity='.18'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='200' height='200' fill='url(%23p)'/%3E%3C/svg%3E");
            overflow-y: auto;
            animation: row-in .25s cubic-bezier(.4,0,.2,1) both;
        }
        #cn-prechat.gone { display: none; }

        /* Company branding header */
        .cn-pcf-header {
            background: linear-gradient(135deg, #006d77 0%, #00535b 100%);
            padding: 20px 16px 16px;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .cn-pcf-header::before {
            content: '';
            position: absolute; top: -50%; left: -50%; right: -50%; bottom: -50%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 3s ease-in-out infinite;
        }
        .cn-pcf-company-logo {
            width: 48px; height: 48px; border-radius: 50%;
            background: rgba(255,255,255,0.2); border: 3px solid rgba(255,255,255,0.3);
            margin: 0 auto 12px; display: flex; align-items: center; justify-content: center;
            backdrop-filter: blur(10px);
        }
        .cn-pcf-company-name {
            font-size: 1.1rem; font-weight: 700; margin-bottom: 4px;
        }
        .cn-pcf-company-tagline {
            font-size: 0.8rem; opacity: 0.9; font-weight: 400;
        }

        /* Progress indicator */
        .cn-pcf-progress {
            display: flex; justify-content: center; gap: 8px; margin: 16px 0 8px;
        }
        .cn-pcf-progress-step {
            width: 8px; height: 8px; border-radius: 50%;
            background: rgba(255,255,255,0.3); transition: all 0.3s ease;
        }
        .cn-pcf-progress-step.active {
            background: #006d77; transform: scale(1.2);
        }
        .cn-pcf-progress-step.completed {
            background: #006d77;
        }

        /* Scrollable inner content */
        .cn-pcf-scroll {
            flex: 1; display: flex; flex-direction: column; padding: 0; gap: 0;
        }

        /* Step-based form */
        .cn-pcf-step {
            display: none; flex-direction: column; min-height: 100%;
            animation: step-in 0.3s ease-out both;
        }
        .cn-pcf-step.active { display: flex; }
        @keyframes step-in {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .cn-pcf-step-content {
            padding: 20px 16px; flex: 1;
        }

        /* Enhanced form fields with floating labels */
        .cn-pcf-field {
            margin-bottom: 20px; position: relative;
        }
        .cn-pcf-input-wrapper {
            position: relative;
        }
        .cn-pcf-input {
            width: 100%; padding: 16px 16px 8px; border: 2px solid rgba(0,109,119,.2);
            border-radius: 12px; font-size: 1rem; font-family: 'Plus Jakarta Sans',sans-serif;
            color: #111b21; outline: none; transition: all 0.3s ease;
            background: rgba(255,255,255,.95); backdrop-filter: blur(10px);
            box-sizing: border-box;
        }
        .cn-pcf-input:focus {
            border-color: #006d77; box-shadow: 0 0 0 4px rgba(0,109,119,.1);
            background: #fff;
        }
        .cn-pcf-input.invalid {
            border-color: #ef4444; box-shadow: 0 0 0 4px rgba(239,68,68,.1);
            background: #fef2f2;
        }
        .cn-pcf-input.filled { border-color: #006d77; }
        .cn-pcf-input::placeholder { color: transparent; }

        /* Floating labels */
        .cn-pcf-label {
            position: absolute; top: 16px; left: 16px;
            font-size: 1rem; color: #8696a0; pointer-events: none;
            transition: all 0.3s ease; background: transparent;
            padding: 0 4px;
        }
        .cn-pcf-input:focus + .cn-pcf-label,
        .cn-pcf-input.filled + .cn-pcf-label {
            top: 8px; left: 12px; font-size: 0.75rem; color: #006d77;
            font-weight: 600; background: rgba(255,255,255,.95);
        }
        .cn-pcf-input.invalid + .cn-pcf-label {
            color: #ef4444;
        }

        /* Field icons */
        .cn-pcf-field-icon {
            position: absolute; right: 16px; top: 50%; transform: translateY(-50%);
            font-size: 1.2rem; color: #8696a0; pointer-events: none;
        }
        .cn-pcf-input:focus + .cn-pcf-label + .cn-pcf-field-icon {
            color: #006d77;
        }

        /* Field hints and errors */
        .cn-pcf-field-hint {
            font-size: 0.75rem; color: #8696a0; margin-top: 4px;
            padding-left: 4px; opacity: 0; transition: opacity 0.3s ease;
        }
        .cn-pcf-input:focus ~ .cn-pcf-field-hint { opacity: 1; }
        .cn-pcf-field-error {
            font-size: 0.75rem; color: #ef4444; margin-top: 4px;
            padding-left: 4px; display: none;
        }
        .cn-pcf-input.invalid ~ .cn-pcf-field-error { display: block; }

        /* Enhanced CTA buttons */
        .cn-pcf-actions {
            padding: 16px; background: rgba(255,255,255,.9);
            backdrop-filter: blur(10px); border-top: 1px solid rgba(0,0,0,.05);
        }
        .cn-pcf-btn-row {
            display: flex; flex-direction: column; gap: 12px;
        }
        .cn-pcf-btn.primary {
            width: 100%; padding: 14px 20px; border: none; border-radius: 12px;
            background: linear-gradient(135deg, #006d77 0%, #00535b 100%);
            color: #fff; font-size: 1rem; font-weight: 700;
            font-family: 'Plus Jakarta Sans',sans-serif;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(0,109,119,.3);
            position: relative; overflow: hidden;
        }
        .cn-pcf-btn.primary::before {
            content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        .cn-pcf-btn.primary:hover::before { left: 100%; }
        .cn-pcf-btn.primary:hover {
            transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,109,119,.4);
        }
        .cn-pcf-btn.primary:active { transform: translateY(0); }
        .cn-pcf-btn.primary:disabled {
            background: #a0aeb6; cursor: not-allowed; transform: none; box-shadow: none;
        }

        .cn-pcf-btn.secondary {
            width: 100%; padding: 12px 20px; border: 2px solid rgba(0,109,119,.3);
            border-radius: 12px; background: rgba(255,255,255,.8);
            color: #006d77; font-size: 0.95rem; font-weight: 600;
            font-family: 'Plus Jakarta Sans',sans-serif;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;
            transition: all 0.3s ease;
        }
        .cn-pcf-btn.secondary:hover {
            background: #006d77; color: white; border-color: #006d77;
            transform: translateY(-1px);
        }

        /* Trust indicators */
        .cn-pcf-trust {
            display: flex; flex-direction: column; gap: 8px; margin-top: 16px;
        }
        .cn-pcf-trust-item {
            display: flex; align-items: center; gap: 8px;
            font-size: 0.75rem; color: #54656f;
        }
        .cn-pcf-trust-item svg {
            width: 14px; height: 14px; flex-shrink: 0;
        }

        /* Privacy note */
        .cn-pcf-privacy {
            font-size: 0.7rem; color: #8696a0; margin-top: 12px; line-height: 1.4;
            display: flex; align-items: flex-start; gap: 6px; text-align: center;
            padding: 8px; background: rgba(0,109,119,.05); border-radius: 8px;
        }

        /* Animations */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.8; }
            50% { opacity: 1; }
        }

        /* ── ENCRYPTION NOTICE ── */
        .cn-encrypt-notice {
            align-self: center;
            background: rgba(0,109,119,.05);
            border-radius: 12px;
            font-size: .72rem; font-weight: 500; color: var(--cn-muted);
            padding: 7px 12px;
            margin: 2px 0 8px;
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
                <!-- Left: text -->
                <div id="cn-l-text-wrap">
                    <span id="cn-l-greeting">💬 Hi there!</span>
                    <span id="cn-l-title">Virtual Assistant</span>
                </div>

                <!-- Right: animated bot avatar -->
                <div id="cn-l-bot-wrap">
                    <svg id="cn-bot-svg" width="36" height="36" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="overflow:visible">
                        <!-- Antenna stem -->
                        <rect x="20.5" y="1" width="3" height="8" rx="1.5" fill="rgba(255,255,255,0.85)" id="cn-ant-stem"/>
                        <!-- Antenna tip (glows) -->
                        <circle id="cn-ant-tip" cx="22" cy="1" r="4" fill="white"/>
                        <!-- Head -->
                        <rect x="5" y="9" width="34" height="26" rx="9" fill="rgba(255,255,255,0.95)"/>
                        <!-- Left ear -->
                        <rect x="1" y="16" width="5" height="10" rx="2.5" fill="rgba(255,255,255,0.75)"/>
                        <!-- Right ear -->
                        <rect x="38" y="16" width="5" height="10" rx="2.5" fill="rgba(255,255,255,0.75)"/>
                        <!-- Left eye (blinks via GSAP) -->
                        <ellipse id="cn-eye-l" cx="14" cy="21" rx="4.5" ry="4.5"/>
                        <!-- Left eye shine -->
                        <circle cx="15.8" cy="19.2" r="1.6" fill="white"/>
                        <!-- Right eye (blinks via GSAP) -->
                        <ellipse id="cn-eye-r" cx="30" cy="21" rx="4.5" ry="4.5"/>
                        <!-- Right eye shine -->
                        <circle cx="31.8" cy="19.2" r="1.6" fill="white"/>
                        <!-- Smile -->
                        <path id="cn-smile" d="M14 31 Q22 37 30 31" stroke="rgba(255,255,255,0.9)" stroke-width="2.5" stroke-linecap="round" fill="none"/>
                    </svg>
                </div>

                <!-- Close icon (visible only when window is open) -->
                <div id="cn-l-close-icon" aria-hidden="true">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24">
                        <path stroke="#fff" stroke-width="2.5" stroke-linecap="round" d="M18 6 6 18M6 6l12 12"/>
                    </svg>
                </div>

                <span id="cn-badge">1</span>
            </button>

            <div id="cn-window" role="dialog" aria-label="Chat Assistant">
                <div class="cn-drag-handle" style="display:none"><div class="cn-drag-pill"></div></div>
                <div id="cn-header">
                    <div class="cn-hdr-avatar">
                        ${config.company_logo_url ?
                            `<img src="${config.company_logo_url}" alt="${config.business_name}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` :
                            `<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <rect x="3" y="8" width="18" height="13" rx="4" fill="rgba(255,255,255,.85)"/>
                                <circle cx="9" cy="14" r="1.5" fill="#006d77"/>
                                <circle cx="15" cy="14" r="1.5" fill="#006d77"/>
                                <rect x="10.5" y="4" width="3" height="4" rx="1.5" fill="rgba(255,255,255,.85)"/>
                                <circle cx="12" cy="4" r="1.5" fill="rgba(255,255,255,.85)"/>
                                <path stroke="rgba(255,255,255,.85)" stroke-width="1.5" stroke-linecap="round" d="M9 18h6"/>
                            </svg>`
                        }
                        <span class="cn-online-ring"></span>
                    </div>
                    <div class="cn-hdr-info">
                        <div class="cn-hdr-name">${config.business_name}</div>
                        <div class="cn-hdr-status"><span class="cn-status-dot"></span><span>Online now</span></div>
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
                    <div class="cn-pcf-scroll">
                        <!-- Company branding header -->
                        <div class="cn-pcf-header">
                            <div class="cn-pcf-company-logo">
                                ${config.company_logo_url ?
                                    `<img src="${config.company_logo_url}" alt="${config.business_name}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` :
                                    `<svg width="28" height="28" fill="none" viewBox="0 0 24 24">
                                        <rect x="3" y="8" width="18" height="13" rx="4" fill="rgba(255,255,255,.9)"/>
                                        <circle cx="9" cy="14" r="1.5" fill="#006d77"/>
                                        <circle cx="15" cy="14" r="1.5" fill="#006d77"/>
                                        <rect x="10.5" y="4" width="3" height="4" rx="1.5" fill="rgba(255,255,255,.9)"/>
                                        <circle cx="12" cy="4" r="1.5" fill="rgba(255,255,255,.9)"/>
                                        <path stroke="rgba(255,255,255,.9)" stroke-width="1.5" stroke-linecap="round" d="M9 18h6"/>
                                    </svg>`
                                }
                            </div>
                            <div class="cn-pcf-company-name">${config.business_name}</div>
                            <div class="cn-pcf-company-tagline">We're here to help you!</div>
                        </div>

                        <!-- Enhanced form -->
                        <div class="cn-pcf-step active">
                            <div class="cn-pcf-step-content">
                                <div style="text-align: center; margin-bottom: 24px;">
                                    <h3 style="font-size: 1.2rem; font-weight: 700; color: #111b21; margin-bottom: 8px;">Tell us about yourself</h3>
                                    <p style="font-size: 0.9rem; color: #54656f; line-height: 1.4;">This helps us provide personalized assistance</p>
                                </div>

                                <div class="cn-pcf-field">
                                    <div class="cn-pcf-input-wrapper">
                                        <input id="cn-pcf-name" class="cn-pcf-input" type="text" autocomplete="name">
                                        <label class="cn-pcf-label">Full Name</label>
                                        <div class="cn-pcf-field-icon">👤</div>
                                    </div>
                                    <div class="cn-pcf-field-hint">Enter your full name</div>
                                    <div class="cn-pcf-field-error">Please enter a valid name</div>
                                </div>

                                <div class="cn-pcf-field">
                                    <div class="cn-pcf-input-wrapper">
                                        <input id="cn-pcf-email" class="cn-pcf-input" type="email" autocomplete="email">
                                        <label class="cn-pcf-label">Email Address</label>
                                        <div class="cn-pcf-field-icon">📧</div>
                                    </div>
                                    <div class="cn-pcf-field-hint">We'll use this to follow up if needed</div>
                                    <div class="cn-pcf-field-error">Please enter a valid email address</div>
                                </div>

                                <div class="cn-pcf-field">
                                    <div class="cn-pcf-input-wrapper">
                                        <input id="cn-pcf-phone" class="cn-pcf-input" type="tel" autocomplete="tel">
                                        <label class="cn-pcf-label">Phone Number (Optional)</label>
                                        <div class="cn-pcf-field-icon">📱</div>
                                    </div>
                                    <div class="cn-pcf-field-hint">For urgent support or callbacks</div>
                                    <div class="cn-pcf-field-error">Please enter a valid phone number</div>
                                </div>

                                <!-- Trust indicators -->
                                <div class="cn-pcf-trust">
                                    <div class="cn-pcf-trust-item">
                                        <svg viewBox="0 0 24 24" fill="none">
                                            <path fill="#006d77" d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2Zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2ZM9 8V6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9Z"/>
                                        </svg>
                                        <span>Your information is secure & encrypted</span>
                                    </div>
                                    <div class="cn-pcf-trust-item">
                                        <svg viewBox="0 0 24 24" fill="none">
                                            <path fill="#006d77" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                        <span>Trusted by 1,200+ customers</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Enhanced action buttons -->
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

                                <div class="cn-pcf-privacy">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;margin-top:1px">
                                        <path fill="#8696a0" d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2Zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2ZM9 8V6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9Z"/>
                                    </svg>
                                    <span>Your data is private and used only to improve your support experience.</span>
                                </div>
                            </div>
                        </div>
                    </div>
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
                        <textarea id="cn-input" placeholder="Type your message..." rows="1" aria-label="Message" autocomplete="off"></textarea>
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

        applyConfig();
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
                const incomingConfig = { ...(data.config || {}) };
                if (incomingConfig.company_logo_url) incomingConfig.company_logo_url = normalizeAssetUrl(incomingConfig.company_logo_url);
                if (incomingConfig.bot_avatar_url) incomingConfig.bot_avatar_url = normalizeAssetUrl(incomingConfig.bot_avatar_url);
                if (incomingConfig.primary_color) incomingConfig.primary_color = normalizeHexColor(incomingConfig.primary_color) || incomingConfig.primary_color;
                config = { ...config, ...incomingConfig };
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
        const badge    = document.getElementById('cn-badge');
        const win      = document.getElementById('cn-window');
        const closeBtn = document.getElementById('cn-close');
        const minBtn   = document.getElementById('cn-min');
        const input    = document.getElementById('cn-input');
        const sendBtn  = document.getElementById('cn-send');

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
                botWrap.style.display = 'none';
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
                        gsap.fromTo(textWrap,
                            { opacity: 0, x: -12 },
                            { opacity: 1, x: 0, duration: 0.3, ease: 'power2.out' }
                        );
                        gsap.fromTo(botWrap,
                            { opacity: 0, scale: 0.6 },
                            { opacity: 1, scale: 1, duration: 0.35, ease: 'back.out(1.7)' }
                        );
                    }
                });
            } else {
                closeIcon.style.display = 'none';
                textWrap.style.display = 'flex';
                botWrap.style.display  = 'flex';
            }
        }

        function openChat() {
            isWindowOpen = true;
            win.classList.add('open');
            badge.classList.add('gone');
            launcherToClose();
            if (window.innerWidth <= 480) {
                document.querySelector('.cn-drag-handle').style.display = 'flex';
            }

            const prechat = document.getElementById('cn-prechat');
            const skipPrechat = sessionStorage.getItem('cbn_prechat_skipped') === '1';
            const needsPrechat = config.prechat_enabled && !skipPrechat && (!visitorInfo.name || !visitorInfo.email);
            if (needsPrechat) {
                prechat.classList.remove('gone');
                const tsEl = document.getElementById('cn-pcf-time');
                if (tsEl) tsEl.textContent = formatTime(new Date());
                setTimeout(() => {
                    const firstInput = document.getElementById('cn-pcf-name');
                    if (firstInput) firstInput.focus();
                }, 200);
            } else {
                input.focus();
            }
        }

        function closeChat() {
            isWindowOpen = false;
            win.classList.remove('open');
            badge.classList.remove('gone');
            badge.textContent = '1';
            launcherToBot();
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
            const submitBtn = document.getElementById('cn-pcf-submit');
            if (submitBtn && submitBtn.dataset && submitBtn.dataset.originalHtml) {
                submitBtn.innerHTML = submitBtn.dataset.originalHtml;
                submitBtn.disabled = false;
                delete submitBtn.dataset.originalHtml;
            }
            input.focus();
        }

        document.getElementById('cn-pcf-submit').addEventListener('click', () => {
            // Validate all fields
            const fields = ['cn-pcf-name', 'cn-pcf-email', 'cn-pcf-phone'];
            let isValid = true;
            let firstInvalidField = null;

            fields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!validateField(field)) {
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = field;
                }
            });

            if (!isValid) {
                firstInvalidField.focus();
                // Add shake animation to the form
                const form = document.querySelector('.cn-pcf-step-content');
                form.style.animation = 'shake 0.5s ease-in-out';
                setTimeout(() => form.style.animation = '', 500);
                return;
            }

            const name  = document.getElementById('cn-pcf-name').value.trim();
            const email = document.getElementById('cn-pcf-email').value.trim();
            const phone = document.getElementById('cn-pcf-phone').value.trim();

            visitorInfo = { name, email, phone };
            localStorage.setItem('cbn_visitor_info', JSON.stringify(visitorInfo));

            // Show loading state
            const submitBtn = document.getElementById('cn-pcf-submit');
            if (!submitBtn.dataset.originalHtml) submitBtn.dataset.originalHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span>Starting Chat...</span>';
            submitBtn.disabled = true;

            setTimeout(() => {
                dismissPrechat();
            }, 500);
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
            sessionStorage.setItem('cbn_prechat_skipped', '1');
            dismissPrechat();
        });

        // Enhanced form interactions
        ['cn-pcf-name','cn-pcf-email','cn-pcf-phone'].forEach(id => {
            const input = document.getElementById(id);
            const wrapper = input.parentElement;

            // Floating label and validation
            input.addEventListener('input', function() {
                const label = wrapper.querySelector('.cn-pcf-label');
                const icon = wrapper.querySelector('.cn-pcf-field-icon');

                // Update filled state
                if (this.value.trim()) {
                    this.classList.add('filled');
                } else {
                    this.classList.remove('filled');
                }

                // Real-time validation
                validateField(this);
            });

            input.addEventListener('focus', function() {
                wrapper.classList.add('focused');
            });

            input.addEventListener('blur', function() {
                wrapper.classList.remove('focused');
                validateField(this);
            });

            input.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const nextField = getNextField(input);
                    if (nextField) {
                        nextField.focus();
                    } else {
                        document.getElementById('cn-pcf-submit').click();
                    }
                }
            });
        });

        function getNextField(currentInput) {
            const fields = ['cn-pcf-name', 'cn-pcf-email', 'cn-pcf-phone'];
            const currentIndex = fields.indexOf(currentInput.id);
            if (currentIndex < fields.length - 1) {
                return document.getElementById(fields[currentIndex + 1]);
            }
            return null;
        }

        function validateField(input) {
            const value = input.value.trim();
            const errorDiv = input.parentElement.querySelector('.cn-pcf-field-error');
            const isRequired = input.id !== 'cn-pcf-phone'; // Phone is optional

            input.classList.remove('invalid');

            if (isRequired && !value) {
                showFieldError(input, 'This field is required');
                return false;
            }

            if (value) {
                switch(input.id) {
                    case 'cn-pcf-name':
                        if (value.length < 2) {
                            showFieldError(input, 'Name must be at least 2 characters');
                            return false;
                        }
                        break;
                    case 'cn-pcf-email':
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(value)) {
                            showFieldError(input, 'Please enter a valid email address');
                            return false;
                        }
                        break;
                    case 'cn-pcf-phone':
                        if (value && !/^[\+]?[1-9][\d]{0,15}$/.test(value.replace(/[\s\-\(\)]/g, ''))) {
                            showFieldError(input, 'Please enter a valid phone number');
                            return false;
                        }
                        break;
                }
            }

            hideFieldError(input);
            return true;
        }

        function showFieldError(input, message) {
            input.classList.add('invalid');
            const errorDiv = input.parentElement.querySelector('.cn-pcf-field-error');
            if (errorDiv) {
                errorDiv.textContent = message;
            }
        }

        function hideFieldError(input) {
            input.classList.remove('invalid');
            const errorDiv = input.parentElement.querySelector('.cn-pcf-field-error');
            if (errorDiv) {
                errorDiv.textContent = '';
            }
        }

        document.getElementById('cn-mic').addEventListener('click', () => {
            const t = document.createElement('div');
            t.style.cssText = 'position:fixed;bottom:108px;right:28px;background:#006d77;color:#fff;font-family:Plus Jakarta Sans,sans-serif;font-size:.78rem;font-weight:600;padding:9px 16px;border-radius:10px;z-index:99999;pointer-events:none;animation:row-in .2s ease both;';
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

        if (role === 'bot') {
            const avatar = document.createElement('div');
            avatar.className = 'cn-msg-avatar';
            if (config.company_logo_url) {
                avatar.innerHTML = `<img src="${config.company_logo_url}" alt="${config.business_name}">`;
            } else {
                avatar.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path fill="currentColor" d="M12 2a7 7 0 0 0-7 7v3a6 6 0 0 0 6 6h2a6 6 0 0 0 6-6V9a7 7 0 0 0-7-7Zm-4 9a2 2 0 1 1 0-4 2 2 0 0 1 0 4Zm8 0a2 2 0 1 1 0-4 2 2 0 0 1 0 4Zm-8.5 9.5A2.5 2.5 0 0 1 10 18h4a2.5 2.5 0 0 1 2.5 2.5c0 .83-.67 1.5-1.5 1.5H9c-.83 0-1.5-.67-1.5-1.5Z"/></svg>`;
            }
            row.appendChild(avatar);
        }

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

        let footer = '';
        if (config.message_meta_enabled) {
            const timeStr = formatTime(new Date());
            const checkSvg = role === 'user'
                ? `<span class="cn-check cn-check-pending"><svg width="16" height="11" viewBox="0 0 16 11" fill="none"><path d="M11.07.66 4.88 6.85 2.41 4.38.94 5.85l3.94 3.94 7.66-7.66L11.07.66Z" fill="#667781"/><path d="M14.07.66 7.88 6.85l-.53-.53-1.47 1.47 2 2 7.66-7.66L14.07.66Z" fill="#667781"/></svg></span>`
                : '';
            footer = `<span class="cn-ts-row"><span class="cn-ts">${timeStr}</span>${checkSvg}</span>`;
        }
        if (isError) {
            footer += `<a href="#" class="cn-retry" style="margin-left:8px;color:#4318FF;font-size:.75rem;font-weight:600;text-decoration:underline;">Try again</a>`;
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
        const widgetRoot = document.getElementById('cn-widget');
        if (widgetRoot) {
            const primary = normalizeHexColor(config.primary_color) || '#006d77';
            const primaryDark = darkenHex(primary, 0.24) || primary;
            widgetRoot.style.setProperty('--cn-primary', primary);
            widgetRoot.style.setProperty('--cn-primary-dark', primaryDark);
            widgetRoot.style.setProperty('--cn-user-bubble', primary);

            const launcherPos = getLauncherPosition();
            widgetRoot.style.bottom = launcherPos.bottom;
            widgetRoot.style.right = launcherPos.right;
            widgetRoot.style.left = launcherPos.left;
        }

        const nameEl = document.querySelector('.cn-hdr-name');
        if (nameEl) nameEl.textContent = config.business_name;

        const launcherTitle = document.getElementById('cn-l-title');
        if (launcherTitle) launcherTitle.textContent = (config.business_name || 'Virtual') + ' Virtual Assistant';

        // Sync bot eye fill to primary color
        const primary = normalizeHexColor(config.primary_color) || '#006d77';
        ['cn-eye-l','cn-eye-r'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.setAttribute('fill', primary);
        });
        const smile = document.getElementById('cn-smile');
        if (smile) smile.setAttribute('stroke', primary);

        const footer = document.getElementById('cn-footer');
        if (footer && config.show_powered_by === false) footer.style.display = 'none';

        const resolvedCompanyLogoUrl = normalizeAssetUrl(config.company_logo_url);
        if (resolvedCompanyLogoUrl) config.company_logo_url = resolvedCompanyLogoUrl;
        const resolvedBotAvatarUrl = normalizeAssetUrl(config.bot_avatar_url);
        if (resolvedBotAvatarUrl) config.bot_avatar_url = resolvedBotAvatarUrl;

        const headerAvatar = document.querySelector('.cn-hdr-avatar');
        if (headerAvatar) {
            if (config.company_logo_url) {
                headerAvatar.innerHTML = `<img src="${config.company_logo_url}" alt="${config.business_name}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;"><span class="cn-online-ring"></span>`;
            } else if (config.bot_avatar_url) {
                headerAvatar.innerHTML = `<img src="${config.bot_avatar_url}" alt="${config.bot_name}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;"><span class="cn-online-ring"></span>`;
            }
        }

        const prechatLogo = document.querySelector('.cn-pcf-company-logo');
        if (prechatLogo) {
            if (config.company_logo_url) {
                prechatLogo.innerHTML = `<img src="${config.company_logo_url}" alt="${config.business_name}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">`;
            }
        }

        const prechat = document.getElementById('cn-prechat');
        if (prechat && !config.prechat_enabled) {
            prechat.style.display = 'none';
        }

        // Apply watermark settings
        const messagesEl = document.getElementById('cn-messages');
        if (messagesEl) {
            if (config.watermark_enabled && config.company_logo_url) {
                messagesEl.classList.add('has-watermark');
                messagesEl.style.setProperty('--watermark-opacity', config.watermark_opacity || 0.1);

                // Set watermark position
                let position = 'center';
                switch (config.watermark_position) {
                    case 'top-left': position = 'left top'; break;
                    case 'top-right': position = 'right top'; break;
                    case 'bottom-left': position = 'left bottom'; break;
                    case 'bottom-right': position = 'right bottom'; break;
                    default: position = 'center';
                }
                messagesEl.style.setProperty('--watermark-position', position);

                messagesEl.style.setProperty('--watermark-image', `url("${config.company_logo_url}")`);
            } else {
                messagesEl.classList.remove('has-watermark');
                messagesEl.style.removeProperty('--watermark-image');
            }
        }
    }

    function uuidv4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    /* ─────────────────────────────────────────
       GSAP LOADER — dynamic CDN inject
    ───────────────────────────────────────── */
    function loadGSAP(callback) {
        if (window.gsap) { callback(); return; }
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js';
        s.onload  = callback;
        s.onerror = callback; // graceful: fall back to CSS-only
        document.head.appendChild(s);
    }

    /* ─────────────────────────────────────────
       LAUNCHER ANIMATIONS — "alive" bot
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

        // ── 1. ENTRANCE ──────────────────────────────────────────
        const entry = gsap.timeline({ delay: 0.4 });
        entry
            .from(launcher, {
                y: 50, opacity: 0, scale: 0.7,
                duration: 0.85, ease: 'back.out(2)',
            })
            .from(textWrap, {
                opacity: 0, x: -16,
                duration: 0.45, ease: 'power2.out',
            }, '-=0.4')
            .from(botSvg, {
                scale: 0, rotation: -25, opacity: 0,
                duration: 0.55, ease: 'back.out(2.5)',
                transformOrigin: 'center center',
            }, '-=0.35');

        // ── 2. IDLE FLOAT ────────────────────────────────────────
        gsap.to(launcher, {
            y: -6, duration: 2.4,
            ease: 'sine.inOut', repeat: -1, yoyo: true,
        });

        // ── 3. BOT HEAD GENTLE BOB ───────────────────────────────
        gsap.to(botWrap, {
            rotation: 4, duration: 1.9,
            ease: 'sine.inOut', repeat: -1, yoyo: true,
            transformOrigin: 'center bottom',
        });

        // ── 4. ANTENNA TIP GLOW PULSE ────────────────────────────
        if (antTip) {
            gsap.to(antTip, {
                scale: 1.5, opacity: 0.5, duration: 0.85,
                ease: 'sine.inOut', repeat: -1, yoyo: true,
                transformOrigin: 'center center',
            });
        }

        // ── 5. EYE BLINK — random schedule ───────────────────────
        function scheduleBlink() {
            if (!document.getElementById('cn-launcher')) return; // widget removed
            const pause = 1800 + Math.random() * 2800;
            setTimeout(() => {
                if (!eyeL || !eyeR) return;
                const tl = gsap.timeline({ onComplete: scheduleBlink });
                tl.to([eyeL, eyeR], {
                    scaleY: 0.06, duration: 0.07, ease: 'power3.in',
                    transformOrigin: 'center center',
                })
                .to([eyeL, eyeR], {
                    scaleY: 1, duration: 0.11, ease: 'power2.out',
                    transformOrigin: 'center center',
                });
            }, pause);
        }
        scheduleBlink();

        // ── 6. GREETING TEXT SUBTLE SHIMMER ─────────────────────
        const greeting = document.getElementById('cn-l-greeting');
        if (greeting) {
            gsap.to(greeting, {
                opacity: 0.65, duration: 1.8,
                ease: 'sine.inOut', repeat: -1, yoyo: true,
            });
        }

        // ── 7. HOVER — springy scale ─────────────────────────────
        launcher.addEventListener('mouseenter', () => {
            if (isWindowOpen) return;
            gsap.to(launcher, {
                scale: 1.06, duration: 0.35, ease: 'back.out(2)',
                overwrite: 'auto',
            });
            gsap.to(botSvg, {
                rotation: 8, duration: 0.3, ease: 'back.out(2)',
                transformOrigin: 'center center', overwrite: 'auto',
            });
        });
        launcher.addEventListener('mouseleave', () => {
            if (isWindowOpen) return;
            gsap.to(launcher, {
                scale: 1, duration: 0.4, ease: 'elastic.out(1, 0.5)',
                overwrite: 'auto',
            });
            gsap.to(botSvg, {
                rotation: 0, duration: 0.4, ease: 'elastic.out(1, 0.5)',
                transformOrigin: 'center center', overwrite: 'auto',
            });
        });

        // ── 8. CLICK PRESS FEEL ──────────────────────────────────
        launcher.addEventListener('mousedown', () => {
            gsap.to(launcher, { scale: 0.94, duration: 0.12, ease: 'power3.in', overwrite: 'auto' });
        });
        launcher.addEventListener('mouseup', () => {
            gsap.to(launcher, { scale: 1, duration: 0.3, ease: 'back.out(2)', overwrite: 'auto' });
        });
    }

    /* ─────────────────────────────────────────
       INIT — wired to load GSAP then start
    ───────────────────────────────────────── */
    function startWidget() {
        init();
        loadGSAP(initLauncherAnimations);
    }

    if (document.readyState === 'complete') startWidget();
    else window.addEventListener('load', startWidget);

})();
