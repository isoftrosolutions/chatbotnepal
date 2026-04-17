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
        primary_color: '#DAFF01',
        bot_name: 'Assistant',
        bot_avatar_url: null
    };
    let conversationId = sessionStorage.getItem('cbn_conversation_id');
    let visitorId = localStorage.getItem('cbn_visitor_id') || uuidv4();
    localStorage.setItem('cbn_visitor_id', visitorId);
    let isWindowOpen = false;
    let sessionToken = null;

    /* ─────────────────────────────────────────
       DESIGN TOKENS  (Kinetic High-Performance)
    ───────────────────────────────────────── */
    const T = {
        lime:       '#DAFF01',
        limeText:   '#181E00',
        limeDim:    'rgba(218,255,1,0.12)',
        limeShadow: 'rgba(218,255,1,0.18)',
        purple:     '#5602C9',
        purpleDim:  'rgba(86,2,201,0.18)',
        s0:         '#0D0D0D',   /* deepest bg         */
        s1:         '#131313',   /* window bg          */
        s2:         '#1C1B1B',   /* secondary surface  */
        s3:         '#252525',   /* card / input       */
        s4:         '#303030',   /* hover / active     */
        textHi:     '#F0F0F0',
        textMid:    '#888888',
        textLo:     '#444444',
    };

    const styles = `
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;1,400&display=swap');

        #cbn-widget * { box-sizing: border-box; margin: 0; padding: 0; }
        #cbn-widget {
            position: fixed; bottom: 28px; right: 28px; z-index: 999999;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        }

        /* ── FAB ── */
        #cbn-button {
            width: 60px; height: 60px; border-radius: 50%;
            background: ${T.lime};
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 8px 32px ${T.limeShadow}, 0 2px 8px rgba(0,0,0,0.4);
            transition: transform 0.28s cubic-bezier(0.34,1.56,0.64,1),
                        box-shadow 0.28s ease;
            border: none; position: relative; overflow: visible;
        }
        #cbn-button::before {
            content: ''; position: absolute; inset: -6px; border-radius: 50%;
            background: ${T.limeDim};
            animation: cbn-pulse-ring 2.8s ease-in-out infinite;
            pointer-events: none;
        }
        #cbn-button.open::before { display: none; }
        #cbn-button:hover {
            transform: scale(1.08);
            box-shadow: 0 12px 40px ${T.limeShadow}, 0 4px 12px rgba(0,0,0,0.5);
        }
        #cbn-button svg { width: 26px; height: 26px; }
        #cbn-button .cbn-close-icon { display: none; }
        #cbn-button.open .cbn-chat-icon { display: none; }
        #cbn-button.open .cbn-close-icon { display: block; }
        @keyframes cbn-pulse-ring {
            0%,100% { transform: scale(1); opacity: 0.6; }
            50%      { transform: scale(1.28); opacity: 0; }
        }

        /* ── WINDOW ── */
        #cbn-window {
            position: absolute; bottom: 76px; right: 0;
            width: 384px; height: 588px; max-height: calc(100vh - 116px);
            background: ${T.s1}; border-radius: 20px;
            box-shadow:
                0 40px 80px rgba(0,0,0,0.55),
                0 16px 40px ${T.purpleDim},
                0 0 0 0.5px rgba(255,255,255,0.06);
            display: none; flex-direction: column; overflow: hidden;
            transform-origin: bottom right;
        }
        #cbn-window.open {
            display: flex;
            animation: cbn-open 0.36s cubic-bezier(0.34,1.42,0.64,1) forwards;
        }
        @keyframes cbn-open {
            from { opacity: 0; transform: scale(0.78) translateY(20px); }
            to   { opacity: 1; transform: scale(1)    translateY(0);    }
        }

        /* ── HEADER ── */
        #cbn-header {
            padding: 16px 18px 14px;
            background: rgba(19,19,19,0.88);
            backdrop-filter: blur(24px) saturate(160%);
            -webkit-backdrop-filter: blur(24px) saturate(160%);
            color: ${T.textHi};
            display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0; position: relative;
        }
        /* lime accent bar at very top of header */
        #cbn-header::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0;
            height: 2px; background: ${T.lime};
            border-radius: 20px 20px 0 0;
        }
        #cbn-header-info { display: flex; align-items: center; gap: 13px; }

        #cbn-avatar {
            width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
            background: ${T.s3};
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; position: relative;
        }
        #cbn-avatar::after {
            content: ''; position: absolute; inset: 0; border-radius: 12px;
            box-shadow: inset 0 0 0 0.5px rgba(255,255,255,0.08);
            pointer-events: none;
        }
        #cbn-avatar img  { width: 100%; height: 100%; object-fit: cover; }
        #cbn-avatar svg  { width: 22px; height: 22px; }

        #cbn-header-text { display: flex; flex-direction: column; gap: 2px; }
        .cbn-business-name {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700; font-size: 15px;
            letter-spacing: -0.03em; color: ${T.textHi};
            line-height: 1.2;
        }
        .cbn-bot-name {
            font-size: 11.5px; color: ${T.textMid};
            font-weight: 500; line-height: 1;
        }
        .cbn-status {
            display: flex; align-items: center; gap: 5px;
            font-size: 11px; color: ${T.textMid}; margin-top: 2px;
        }
        #cbn-status-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: ${T.lime};
            box-shadow: 0 0 0 0 ${T.limeDim};
            animation: cbn-dot-pulse 2.4s ease-in-out infinite;
            flex-shrink: 0;
        }
        @keyframes cbn-dot-pulse {
            0%,100% { box-shadow: 0 0 0 0   ${T.limeDim}; }
            50%      { box-shadow: 0 0 0 5px rgba(218,255,1,0); }
        }

        #cbn-close-btn {
            width: 32px; height: 32px; border-radius: 8px;
            background: ${T.s3}; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.18s; flex-shrink: 0;
        }
        #cbn-close-btn:hover { background: ${T.s4}; }
        #cbn-close-btn svg {
            width: 14px; height: 14px;
            stroke: ${T.textMid}; stroke-width: 2.2; fill: none;
        }

        /* ── MESSAGES ── */
        #cbn-messages {
            flex: 1; padding: 18px 16px; overflow-y: auto;
            background: ${T.s0};
            display: flex; flex-direction: column; gap: 10px;
            scroll-behavior: smooth;
        }
        #cbn-messages::-webkit-scrollbar { width: 3px; }
        #cbn-messages::-webkit-scrollbar-track { background: transparent; }
        #cbn-messages::-webkit-scrollbar-thumb {
            background: ${T.s4}; border-radius: 2px;
        }

        /* date chip */
        .cbn-date-chip {
            align-self: center;
            font-size: 10px; font-weight: 600; letter-spacing: 0.06em;
            text-transform: uppercase; color: ${T.textLo};
            padding: 3px 10px; background: ${T.s2}; border-radius: 20px;
            margin: 4px 0;
        }

        .cbn-msg {
            display: flex; gap: 9px; max-width: 88%;
            animation: cbn-msg-in 0.24s cubic-bezier(0.22,1,0.36,1) forwards;
            opacity: 0;
        }
        @keyframes cbn-msg-in {
            from { opacity: 0; transform: translateY(10px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0)    scale(1);    }
        }
        .cbn-msg.bot     { align-self: flex-start; }
        .cbn-msg.visitor { align-self: flex-end; flex-direction: row-reverse; }

        .cbn-msg-avatar {
            width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
            background: ${T.s3};
            display: flex; align-items: center; justify-content: center;
            align-self: flex-end; margin-bottom: 20px;
        }
        .cbn-msg-avatar svg { width: 15px; height: 15px; }
        .cbn-msg-avatar img { width: 100%; height: 100%; border-radius: 8px; object-fit: cover; }
        .cbn-msg.visitor .cbn-msg-avatar { background: ${T.s4}; }

        .cbn-msg-content  { display: flex; flex-direction: column; gap: 4px; min-width: 0; }

        /* Bot bubble — tonal layer */
        .cbn-msg.bot .cbn-msg-bubble {
            background: ${T.s2}; color: ${T.textHi};
            border-radius: 4px 16px 16px 16px;
            padding: 11px 15px;
            font-size: 14px; line-height: 1.6;
            word-wrap: break-word; white-space: pre-wrap;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        /* Visitor bubble — lime accent */
        .cbn-msg.visitor .cbn-msg-bubble {
            background: ${T.lime}; color: ${T.limeText};
            border-radius: 16px 4px 16px 16px;
            padding: 11px 15px;
            font-size: 14px; line-height: 1.6;
            word-wrap: break-word; white-space: pre-wrap;
            font-weight: 500;
            box-shadow: 0 4px 24px ${T.limeShadow};
        }
        .cbn-msg-time {
            font-size: 10px; color: ${T.textLo};
            font-weight: 500; padding: 0 4px;
        }
        .cbn-msg.visitor .cbn-msg-time { text-align: right; }

        /* ── THINKING DOTS ── */
        .cbn-thinking {
            display: flex; gap: 5px; padding: 4px 2px; align-items: center;
        }
        .cbn-thinking-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: ${T.s4};
            animation: cbn-think 1.2s infinite ease-in-out;
        }
        .cbn-thinking-dot:nth-child(1) { animation-delay: 0s;    }
        .cbn-thinking-dot:nth-child(2) { animation-delay: 0.18s; }
        .cbn-thinking-dot:nth-child(3) { animation-delay: 0.36s; }
        @keyframes cbn-think {
            0%,60%,100% { transform: translateY(0);   background: ${T.s4}; }
            30%          { transform: translateY(-6px); background: ${T.lime}; }
        }

        /* ── STREAMING CURSOR ── */
        .cbn-streaming .cbn-msg-bubble::after {
            content: ''; display: inline-block;
            width: 2px; height: 13px; vertical-align: middle;
            background: ${T.lime}; margin-left: 3px; border-radius: 1px;
            animation: cbn-blink 0.8s step-end infinite;
        }
        @keyframes cbn-blink { 0%,49% { opacity: 1; } 50%,100% { opacity: 0; } }

        /* ── INPUT AREA ── */
        #cbn-input-area {
            padding: 12px 14px 14px;
            background: ${T.s2};
            flex-shrink: 0;
        }
        #cbn-input-wrapper {
            display: flex; gap: 10px; align-items: flex-end;
            background: ${T.s3};
            border-radius: 14px; padding: 8px 8px 8px 16px;
            position: relative; overflow: hidden;
            transition: box-shadow 0.2s;
        }
        #cbn-input-wrapper::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0;
            width: 3px; background: ${T.s3};
            transition: background 0.2s, box-shadow 0.2s;
            border-radius: 14px 0 0 14px;
        }
        #cbn-input-wrapper:focus-within::before {
            background: ${T.lime};
            box-shadow: 2px 0 12px ${T.limeShadow};
        }
        #cbn-input-wrapper:focus-within {
            box-shadow: 0 0 0 1.5px rgba(218,255,1,0.15);
        }
        #cbn-input {
            flex: 1; border: none; background: transparent;
            font-size: 14px; font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            outline: none; resize: none; line-height: 1.5;
            max-height: 100px; min-height: 22px; padding: 2px 0;
            color: ${T.textHi};
        }
        #cbn-input::placeholder { color: ${T.textLo}; }

        #cbn-send {
            width: 36px; height: 36px; border-radius: 50%; border: none;
            background: ${T.lime}; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: transform 0.22s cubic-bezier(0.34,1.56,0.64,1),
                        box-shadow 0.22s ease;
            flex-shrink: 0;
        }
        #cbn-send:hover:not(:disabled) {
            transform: scale(1.1);
            box-shadow: 0 4px 16px ${T.limeShadow};
        }
        #cbn-send:active:not(:disabled) { transform: scale(0.96); }
        #cbn-send:disabled { opacity: 0.35; cursor: not-allowed; }
        #cbn-send svg { width: 16px; height: 16px; fill: ${T.limeText}; }

        /* ── BRANDING ── */
        #cbn-branding {
            padding: 7px 0 9px; text-align: center;
            font-size: 10.5px; font-weight: 500; letter-spacing: 0.01em;
            color: ${T.textLo}; background: ${T.s2};
        }
        #cbn-branding a {
            color: ${T.lime}; font-weight: 600; text-decoration: none;
            transition: opacity 0.15s;
        }
        #cbn-branding a:hover { opacity: 0.75; }

        /* ── MOBILE ── */
        @media (max-width: 480px) {
            #cbn-widget { bottom: 18px; right: 18px; left: 18px; }
            #cbn-window {
                position: fixed; bottom: 0; right: 0; left: 0; top: 0;
                width: 100%; max-width: none; height: 100%; max-height: 100%;
                border-radius: 0;
            }
            #cbn-button { width: 56px; height: 56px; }
            .cbn-msg { max-width: 92%; }
        }
    `;

    /* ─────────────────────────────────────
       INIT — build DOM, inject styles
    ───────────────────────────────────── */
    function init() {
        const styleEl = document.createElement('style');
        styleEl.textContent = styles;
        document.head.appendChild(styleEl);

        const container = document.createElement('div');
        container.id = 'cbn-widget';
        container.innerHTML = `
            <div id="cbn-window">
                <div id="cbn-header">
                    <div id="cbn-header-info">
                        <div id="cbn-avatar">
                            <svg viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="8" r="4" fill="#DAFF01"/>
                                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#DAFF01" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div id="cbn-header-text">
                            <div class="cbn-business-name">${config.business_name}</div>
                            <div class="cbn-bot-name">${config.bot_name}</div>
                            <div class="cbn-status">
                                <span id="cbn-status-dot"></span>
                                Online now
                            </div>
                        </div>
                    </div>
                    <button id="cbn-close-btn" title="Close">
                        <svg viewBox="0 0 24 24">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                <div id="cbn-messages">
                    <div class="cbn-date-chip">Today</div>
                </div>

                <div id="cbn-input-area">
                    <div id="cbn-input-wrapper">
                        <textarea id="cbn-input" placeholder="Ask me anything…" rows="1"></textarea>
                        <button id="cbn-send" title="Send">
                            <svg viewBox="0 0 24 24">
                                <path d="M22 2L11 13M22 2L15 22l-4-9-9-4 20-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div id="cbn-branding">
                    Powered by <a href="https://chatbotnepal.isoftroerp.com/" target="_blank">ChatBot Nepal</a>
                </div>
            </div>

            <button id="cbn-button" title="Chat">
                <svg class="cbn-chat-icon" viewBox="0 0 24 24" fill="none">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"
                          fill="#181E00"/>
                </svg>
                <svg class="cbn-close-icon" viewBox="0 0 24 24" fill="none">
                    <line x1="18" y1="6" x2="6" y2="18" stroke="#181E00" stroke-width="2.5" stroke-linecap="round"/>
                    <line x1="6" y1="6" x2="18" y2="18" stroke="#181E00" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </button>
        `;
        document.body.appendChild(container);

        function initSession() {
            return fetch(`${BASE_URL}/api/widget/session`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ site_id: SITE_ID })
            })
            .then(r => r.json())
            .then(data => {
                if (data.session_token) {
                    sessionToken = data.session_token;
                    config = { ...config, ...data.config };
                    updateTheme();
                    addMessage('bot', config.welcome_message);
                }
            });
        }

        initSession().then(() => setupEvents());
    }

    /* ─────────────────────────────────────
       EVENTS
    ───────────────────────────────────── */
    function setupEvents() {
        const btn     = document.getElementById('cbn-button');
        const win     = document.getElementById('cbn-window');
        const closeBtn = document.getElementById('cbn-close-btn');
        const input   = document.getElementById('cbn-input');
        const sendBtn = document.getElementById('cbn-send');

        function toggleWindow() {
            isWindowOpen = !isWindowOpen;
            win.classList.toggle('open', isWindowOpen);
            btn.classList.toggle('open', isWindowOpen);
            if (isWindowOpen) { input.focus(); scrollToBottom(); }
        }

        btn.onclick = toggleWindow;
        closeBtn.onclick = toggleWindow;

        function autoResize() {
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 120) + 'px';
        }
        input.addEventListener('input', autoResize);

        function sendMessage() {
            const msg = input.value.trim();
            if (!msg) return;
            if (!sessionToken) {
                addMessage('bot', 'Chat not ready. Please refresh the page.');
                return;
            }

            addMessage('visitor', msg);
            input.value = '';
            autoResize();

            const { msgDiv: botMsgDiv, bubble: botBubble } = showStreamingIndicator();
            sendBtn.disabled = true;

            let conversationIdValue = null;

            fetch(`${BASE_URL}/api/chat/stream`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Session-Token': sessionToken,
                    'Accept': 'text/event-stream',
                },
                body: JSON.stringify({
                    site_id: SITE_ID,
                    message: msg,
                    visitor_id: visitorId,
                    conversation_id: conversationId,
                    source_url: window.location.href
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('Stream failed');
                const reader  = response.body.getReader();
                const decoder = new TextDecoder();

                function read() {
                    reader.read().then(({ done, value }) => {
                        if (done) { sendBtn.disabled = false; return; }

                        const chunk = decoder.decode(value);
                        const lines = chunk.split('\n');

                        for (const line of lines) {
                            if (line.startsWith('data: ')) {
                                try {
                                    const data = JSON.parse(line.slice(6));
                                    if (data.type === 'chunk') {
                                        appendToBubble(botBubble, data.content);
                                    } else if (data.type === 'done') {
                                        conversationIdValue = data.conversation_id;
                                        removeStreamingIndicator();
                                        addMessageTime(botMsgDiv);
                                    } else if (data.type === 'error') {
                                        removeStreamingIndicator();
                                        botBubble.innerText = data.message || "I'm sorry, I'm having trouble connecting right now.";
                                    }
                                } catch (e) { /* skip invalid JSON */ }
                            }
                        }

                        if (conversationIdValue) {
                            conversationId = conversationIdValue;
                            sessionStorage.setItem('cbn_conversation_id', conversationId);
                            sendBtn.disabled = false;
                        } else {
                            read();
                        }
                    });
                }

                read();
            })
            .catch(error => {
                console.error('Stream error:', error);
                removeStreamingIndicator();
                botBubble.innerText = "I'm sorry, I'm having trouble connecting right now. Please try again later.";
                sendBtn.disabled = false;
            });
        }

        sendBtn.onclick = sendMessage;
        input.onkeydown = (e) => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
        };
    }

    /* ─────────────────────────────────────
       DOM HELPERS
    ───────────────────────────────────── */
    function createAvatarElement(role) {
        const avatar = document.createElement('div');
        avatar.className = 'cbn-msg-avatar';
        if (role === 'bot') {
            if (config.bot_avatar_url) {
                avatar.innerHTML = `<img src="${config.bot_avatar_url}" alt="Bot">`;
            } else {
                avatar.innerHTML = `<svg viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="8" r="4" fill="#DAFF01"/>
                    <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#DAFF01" stroke-width="1.8" stroke-linecap="round"/>
                </svg>`;
            }
        } else {
            avatar.innerHTML = `<svg viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="7" r="4" fill="#888"/>
                <path d="M20 21a8 8 0 1 0-16 0" stroke="#888" stroke-width="1.8" stroke-linecap="round"/>
            </svg>`;
        }
        return avatar;
    }

    function addMessage(role, text) {
        const container = document.getElementById('cbn-messages');
        const msgDiv    = document.createElement('div');
        msgDiv.className = `cbn-msg ${role}`;

        msgDiv.appendChild(createAvatarElement(role));

        const content = document.createElement('div');
        content.className = 'cbn-msg-content';

        const bubble = document.createElement('div');
        bubble.className = 'cbn-msg-bubble';
        bubble.innerText = text;

        const time = document.createElement('div');
        time.className = 'cbn-msg-time';
        time.innerText = formatTime(new Date());

        content.appendChild(bubble);
        content.appendChild(time);
        msgDiv.appendChild(content);
        container.appendChild(msgDiv);
        scrollToBottom();

        return msgDiv;
    }

    function showStreamingIndicator() {
        const container = document.getElementById('cbn-messages');
        const msgDiv    = document.createElement('div');
        msgDiv.className = 'cbn-msg bot cbn-streaming';

        msgDiv.appendChild(createAvatarElement('bot'));

        const content = document.createElement('div');
        content.className = 'cbn-msg-content';

        const bubble = document.createElement('div');
        bubble.className = 'cbn-msg-bubble';
        bubble.innerHTML = '<div class="cbn-thinking"><div class="cbn-thinking-dot"></div><div class="cbn-thinking-dot"></div><div class="cbn-thinking-dot"></div></div>';

        const time = document.createElement('div');
        time.className = 'cbn-msg-time';

        content.appendChild(bubble);
        content.appendChild(time);
        msgDiv.appendChild(content);
        container.appendChild(msgDiv);
        scrollToBottom();

        return { msgDiv, bubble };
    }

    function appendToBubble(bubble, text) {
        if (bubble.querySelector('.cbn-thinking')) {
            bubble.innerHTML = '';
        }
        const existingHtml = bubble.innerHTML.replace('<span class="cbn-cursor"></span>', '');
        bubble.innerHTML = existingHtml + text + '<span class="cbn-cursor"></span>';
        scrollToBottom();
    }

    function removeStreamingIndicator() {
        const streaming = document.querySelector('.cbn-streaming');
        if (streaming) {
            streaming.classList.remove('cbn-streaming');
            const bubble = streaming.querySelector('.cbn-msg-bubble');
            if (bubble) {
                bubble.innerHTML = bubble.innerHTML.replace('<span class="cbn-cursor"></span>', '');
            }
        }
    }

    function addMessageTime(msgDiv) {
        const time = msgDiv.querySelector('.cbn-msg-time');
        if (time) time.innerText = formatTime(new Date());
    }

    function scrollToBottom() {
        const container = document.getElementById('cbn-messages');
        container.scrollTop = container.scrollHeight;
    }

    function formatTime(date) {
        return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    }

    function updateTheme() {
        const nameEl = document.querySelector('.cbn-business-name');
        const botEl  = document.querySelector('.cbn-bot-name');
        if (nameEl) nameEl.textContent = config.business_name;
        if (botEl)  botEl.textContent  = config.bot_name;

        if (config.bot_avatar_url) {
            const avatar = document.getElementById('cbn-avatar');
            if (avatar) avatar.innerHTML = `<img src="${config.bot_avatar_url}" alt="${config.bot_name}">`;
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
