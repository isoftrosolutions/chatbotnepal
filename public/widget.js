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
        primary_color: '#4318FF',
        bot_name: 'Assistant',
        bot_avatar_url: null
    };
    let conversationId = sessionStorage.getItem('cbn_conversation_id');
    let visitorId = localStorage.getItem('cbn_visitor_id') || uuidv4();
    localStorage.setItem('cbn_visitor_id', visitorId);
    let isWindowOpen = false;
    let sessionToken = null;

    const styles = `
        #cbn-widget * { box-sizing: border-box; margin: 0; padding: 0; }
        #cbn-widget { position: fixed; bottom: 24px; right: 24px; z-index: 999999; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }

        /* ===== TOGGLE BUTTON ===== */
        #cbn-button {
            width: 58px; height: 58px; border-radius: 50%;
            background: linear-gradient(135deg, ${config.primary_color} 0%, #6B5CE7 100%);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 20px rgba(67, 24, 255, 0.4), 0 2px 8px rgba(0,0,0,0.12);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none; position: relative;
        }
        #cbn-button::after {
            content: ''; position: absolute; inset: -5px; border-radius: 50%;
            background: linear-gradient(135deg, ${config.primary_color}35, #6B5CE735);
            animation: cbn-ripple 2.5s ease-in-out infinite; z-index: -1;
        }
        @keyframes cbn-ripple { 0%,100% { transform: scale(1); opacity: 0.7; } 50% { transform: scale(1.2); opacity: 0; } }
        #cbn-button.open::after { display: none; }
        #cbn-button:hover { transform: scale(1.08); box-shadow: 0 8px 28px rgba(67, 24, 255, 0.5); }
        #cbn-button svg { width: 26px; height: 26px; fill: white; }
        #cbn-button .cbn-close-icon { display: none; }
        #cbn-button.open .cbn-chat-icon { display: none; }
        #cbn-button.open .cbn-close-icon { display: block; }

        /* ===== CHAT WINDOW ===== */
        #cbn-window {
            position: absolute; bottom: 74px; right: 0;
            width: 375px; height: 570px; max-height: calc(100vh - 110px);
            background: #fff; border-radius: 20px;
            box-shadow: 0 24px 64px rgba(0,0,0,0.13), 0 8px 24px rgba(0,0,0,0.07), 0 0 0 1px rgba(0,0,0,0.04);
            display: none; flex-direction: column; overflow: hidden;
            transform-origin: bottom right;
        }
        #cbn-window.open {
            display: flex;
            animation: cbn-open 0.32s cubic-bezier(0.34, 1.5, 0.64, 1) forwards;
        }
        @keyframes cbn-open { from { opacity: 0; transform: scale(0.82) translateY(16px); } to { opacity: 1; transform: scale(1) translateY(0); } }

        /* ===== HEADER ===== */
        #cbn-header {
            padding: 15px 18px;
            background: linear-gradient(135deg, ${config.primary_color} 0%, #6B5CE7 100%);
            color: white; display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0; position: relative; overflow: hidden;
        }
        #cbn-header::before {
            content: ''; position: absolute; top: -40px; right: -30px;
            width: 140px; height: 140px;
            background: rgba(255,255,255,0.07); border-radius: 50%; pointer-events: none;
        }
        #cbn-header::after {
            content: ''; position: absolute; bottom: -50px; right: 60px;
            width: 100px; height: 100px;
            background: rgba(255,255,255,0.04); border-radius: 50%; pointer-events: none;
        }
        #cbn-header-info { display: flex; align-items: center; gap: 12px; position: relative; }
        #cbn-avatar {
            width: 42px; height: 42px; border-radius: 50%;
            background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center;
            border: 2px solid rgba(255,255,255,0.32); overflow: hidden; flex-shrink: 0;
        }
        #cbn-avatar img { width: 100%; height: 100%; object-fit: cover; }
        #cbn-avatar svg { width: 22px; height: 22px; fill: white; }
        .cbn-business-name { font-weight: 700; font-size: 15px; line-height: 1.3; letter-spacing: -0.2px; }
        .cbn-bot-name { font-size: 12px; opacity: 0.82; margin-top: 1px; font-weight: 400; }
        .cbn-status { font-size: 11px; opacity: 0.78; display: flex; align-items: center; gap: 5px; margin-top: 3px; }
        #cbn-status-dot {
            width: 7px; height: 7px; background: #4ADE80; border-radius: 50%;
            box-shadow: 0 0 0 2px rgba(74, 222, 128, 0.25);
            animation: cbn-pulse 2.5s infinite;
        }
        @keyframes cbn-pulse { 0%,100% { box-shadow: 0 0 0 2px rgba(74,222,128,0.3); } 50% { box-shadow: 0 0 0 5px rgba(74,222,128,0); } }
        #cbn-close-btn {
            width: 30px; height: 30px; border-radius: 50%;
            background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s; color: white; flex-shrink: 0; position: relative;
        }
        #cbn-close-btn:hover { background: rgba(255,255,255,0.26); }
        #cbn-close-btn svg { width: 15px; height: 15px; stroke: currentColor; stroke-width: 2.5; fill: none; }

        /* ===== MESSAGES AREA ===== */
        #cbn-messages {
            flex: 1; padding: 14px 14px; overflow-y: auto;
            background: #F7F9FC;
            display: flex; flex-direction: column; gap: 6px;
            scroll-behavior: smooth;
        }
        #cbn-messages::-webkit-scrollbar { width: 4px; }
        #cbn-messages::-webkit-scrollbar-track { background: transparent; }
        #cbn-messages::-webkit-scrollbar-thumb { background: #D1D9E6; border-radius: 2px; }

        .cbn-msg {
            display: flex; gap: 8px; max-width: 86%;
            animation: cbn-msg-in 0.22s ease forwards; opacity: 0;
        }
        @keyframes cbn-msg-in { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
        .cbn-msg.bot { align-self: flex-start; }
        .cbn-msg.visitor { align-self: flex-end; flex-direction: row-reverse; }

        .cbn-msg-avatar {
            width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
            background: linear-gradient(135deg, ${config.primary_color} 0%, #6B5CE7 100%);
            display: flex; align-items: center; justify-content: center;
            align-self: flex-end; margin-bottom: 18px;
        }
        .cbn-msg-avatar svg { width: 15px; height: 15px; fill: white; }
        .cbn-msg-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
        .cbn-msg.visitor .cbn-msg-avatar { background: #94A3B8; }

        .cbn-msg-content { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
        .cbn-msg-bubble {
            padding: 10px 14px; font-size: 14px; line-height: 1.55;
            word-wrap: break-word; white-space: pre-wrap;
        }
        .cbn-msg.bot .cbn-msg-bubble {
            background: white; color: #1E293B;
            border-radius: 16px 16px 16px 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 0 0 1px rgba(0,0,0,0.04);
        }
        .cbn-msg.visitor .cbn-msg-bubble {
            background: linear-gradient(135deg, ${config.primary_color} 0%, #6B5CE7 100%);
            color: white; border-radius: 16px 16px 4px 16px;
            box-shadow: 0 2px 8px rgba(67, 24, 255, 0.22);
        }
        .cbn-msg-time { font-size: 10px; color: #9CA3AF; padding: 0 3px; }
        .cbn-msg.visitor .cbn-msg-time { text-align: right; }

        /* Thinking dots */
        .cbn-thinking { display: flex; gap: 4px; padding: 3px 2px; align-items: center; }
        .cbn-thinking-dot {
            width: 7px; height: 7px; background: #BDC3CE; border-radius: 50%;
            animation: cbn-think 1.1s infinite ease-in-out;
        }
        .cbn-thinking-dot:nth-child(2) { animation-delay: 0.15s; }
        .cbn-thinking-dot:nth-child(3) { animation-delay: 0.3s; }
        @keyframes cbn-think {
            0%, 60%, 100% { transform: translateY(0); background: #BDC3CE; }
            30% { transform: translateY(-5px); background: ${config.primary_color}; }
        }

        /* Streaming cursor */
        .cbn-streaming .cbn-msg-bubble::after {
            content: ''; display: inline-block; width: 2px; height: 14px;
            background: ${config.primary_color}; margin-left: 2px;
            animation: cbn-blink 0.75s infinite; vertical-align: middle; border-radius: 1px;
        }
        @keyframes cbn-blink { 0%, 49% { opacity: 1; } 50%, 100% { opacity: 0; } }

        /* ===== INPUT AREA ===== */
        #cbn-input-area {
            padding: 10px 12px 13px; background: white; border-top: 1px solid #EEF1F6;
            flex-shrink: 0;
        }
        #cbn-input-wrapper {
            display: flex; gap: 8px; align-items: flex-end;
            background: #F2F5FA; border: 1.5px solid #E4E9F2;
            border-radius: 14px; padding: 7px 7px 7px 14px;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        #cbn-input-wrapper:focus-within {
            border-color: ${config.primary_color};
            background: white;
            box-shadow: 0 0 0 3px ${config.primary_color}1A;
        }
        #cbn-input {
            flex: 1; border: none; background: transparent; font-size: 14px;
            outline: none; resize: none; line-height: 1.5; max-height: 96px;
            min-height: 22px; padding: 2px 0; color: #1E293B; font-family: inherit;
        }
        #cbn-input::placeholder { color: #A8B3C4; }
        #cbn-send {
            width: 34px; height: 34px; border-radius: 10px; border: none;
            background: linear-gradient(135deg, ${config.primary_color} 0%, #6B5CE7 100%);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: all 0.2s; flex-shrink: 0;
        }
        #cbn-send:hover:not(:disabled) { transform: scale(1.06); box-shadow: 0 3px 12px rgba(67, 24, 255, 0.38); }
        #cbn-send:disabled { opacity: 0.42; cursor: not-allowed; }
        #cbn-send svg { width: 17px; height: 17px; fill: white; }

        /* ===== BRANDING ===== */
        #cbn-branding {
            padding: 7px; text-align: center; font-size: 11px;
            color: #A8B3C4; background: white; border-top: 1px solid #F2F5FA;
        }
        #cbn-branding a { color: ${config.primary_color}; font-weight: 600; text-decoration: none; }

        /* ===== MOBILE ===== */
        @media (max-width: 480px) {
            #cbn-widget { bottom: 16px; right: 16px; left: 16px; }
            #cbn-window { position: fixed; bottom: 0; right: 0; left: 0; top: 0; width: 100%; max-width: none; height: 100%; max-height: 100%; border-radius: 0; }
            #cbn-button { width: 54px; height: 54px; }
            .cbn-msg { max-width: 90%; }
            .cbn-msg-avatar { width: 26px; height: 26px; }
        }
    `;

    function init() {
        const styleSheet = document.createElement("style");
        styleSheet.innerText = styles;
        document.head.appendChild(styleSheet);

        const container = document.createElement('div');
        container.id = 'cbn-widget';
        container.innerHTML = `
            <div id="cbn-window">
                <div id="cbn-header">
                    <div id="cbn-header-info">
                        <div id="cbn-avatar">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                        </div>
                        <div id="cbn-header-text">
                            <div class="cbn-business-name">${config.business_name}</div>
                            <div class="cbn-bot-name">${config.bot_name}</div>
                            <div class="cbn-status"><span id="cbn-status-dot"></span> Online now</div>
                        </div>
                    </div>
                    <button id="cbn-close-btn" title="Close">
                        <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div id="cbn-messages"></div>
                <div id="cbn-input-area">
                    <div id="cbn-input-wrapper">
                        <textarea id="cbn-input" placeholder="Write a message..." rows="1"></textarea>
                        <button id="cbn-send" title="Send">
                            <svg viewBox="0 0 24 24"><path d="M22 2L11 13M22 2L15 22l-4-9-9-4 20-7z"/></svg>
                        </button>
                    </div>
                </div>
                <div id="cbn-branding">Powered by <a href="https://chatbotnepal.isoftroerp.com/" target="_blank">ChatBot Nepal</a></div>
            </div>
            <button id="cbn-button">
                <svg class="cbn-chat-icon" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <svg class="cbn-close-icon" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18" stroke="white" stroke-width="2.5"/><line x1="6" y1="6" x2="18" y2="18" stroke="white" stroke-width="2.5"/></svg>
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

    function setupEvents() {
        const btn = document.getElementById('cbn-button');
        const win = document.getElementById('cbn-window');
        const closeBtn = document.getElementById('cbn-close-btn');
        const input = document.getElementById('cbn-input');
        const sendBtn = document.getElementById('cbn-send');

        function toggleWindow() {
            isWindowOpen = !isWindowOpen;
            win.classList.toggle('open', isWindowOpen);
            btn.classList.toggle('open', isWindowOpen);
            if (isWindowOpen) {
                input.focus();
                scrollToBottom();
            }
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
                addMessage('bot', "Chat not ready. Please refresh the page.");
                return;
            }

            const visitorMsgDiv = addMessage('visitor', msg);
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
                if (!response.ok) {
                    throw new Error('Stream failed');
                }
                const reader = response.body.getReader();
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
                                } catch (e) {
                                    // Skip invalid JSON
                                }
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
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        };
    }

    function createAvatarElement(role) {
        const avatar = document.createElement('div');
        avatar.className = 'cbn-msg-avatar';
        if (role === 'bot') {
            if (config.bot_avatar_url) {
                avatar.innerHTML = `<img src="${config.bot_avatar_url}" alt="Bot">`;
            } else {
                avatar.innerHTML = `<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>`;
            }
        } else {
            avatar.innerHTML = `<svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`;
        }
        return avatar;
    }

    function addMessage(role, text) {
        const container = document.getElementById('cbn-messages');
        const msgDiv = document.createElement('div');
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
        const msgDiv = document.createElement('div');
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
        if (time) {
            time.innerText = formatTime(new Date());
        }
    }

    function scrollToBottom() {
        const container = document.getElementById('cbn-messages');
        container.scrollTop = container.scrollHeight;
    }

    function formatTime(date) {
        return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    }

    function updateTheme() {
        document.querySelector('.cbn-business-name').textContent = config.business_name;
        document.querySelector('.cbn-bot-name').textContent = config.bot_name;

        if (config.bot_avatar_url) {
            const avatar = document.getElementById('cbn-avatar');
            avatar.innerHTML = `<img src="${config.bot_avatar_url}" alt="${config.bot_name}">`;
        }
    }

    function uuidv4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    if (document.readyState === 'complete') init();
    else window.addEventListener('load', init);

})();
