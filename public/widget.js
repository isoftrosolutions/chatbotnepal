(function() {
    const SCRIPT_TAG = document.currentScript;
    const SITE_ID = SCRIPT_TAG.getAttribute('data-site-id');
    const BASE_URL = new URL(SCRIPT_TAG.src).origin;

    if (!SITE_ID) {
        console.error('ChatBot Nepal: Missing data-site-id attribute.');
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

    const styles = `
        #cbn-widget * { box-sizing: border-box; margin: 0; padding: 0; }
        #cbn-widget { position: fixed; bottom: 20px; right: 20px; z-index: 999999; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }

        #cbn-button {
            width: 64px; height: 64px; border-radius: 50%;
            background: linear-gradient(135deg, ${config.primary_color} 0%, #6B5CE7 100%);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 8px 30px rgba(67, 24, 255, 0.4);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 3px solid rgba(255,255,255,0.2);
            position: relative; overflow: hidden;
        }
        #cbn-button::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, transparent 100%);
        }
        #cbn-button:hover { transform: scale(1.1); box-shadow: 0 12px 40px rgba(67, 24, 255, 0.5); }
        #cbn-button svg { width: 28px; height: 28px; fill: white; position: relative; z-index: 1; }
        #cbn-button .cbn-close-icon { display: none; }
        #cbn-button.open .cbn-chat-icon { display: none; }
        #cbn-button.open .cbn-close-icon { display: block; }

        #cbn-window {
            position: absolute; bottom: 80px; right: 0;
            width: 100%; max-width: 420px; height: 600px; max-height: calc(100vh - 120px);
            background: #fff; border-radius: 24px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.15), 0 10px 30px rgba(0,0,0,0.1);
            display: none; flex-direction: column; overflow: hidden;
            animation: cbn-slide-up 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 1px solid rgba(0,0,0,0.05);
        }
        #cbn-window.open { display: flex; }
        @keyframes cbn-slide-up { from { opacity: 0; transform: translateY(30px) scale(0.9); } to { opacity: 1; transform: translateY(0) scale(1); } }

        #cbn-header {
            padding: 20px 24px; background: linear-gradient(135deg, ${config.primary_color} 0%, #6B5CE7 100%);
            color: white; display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0;
        }
        #cbn-header-info { display: flex; align-items: center; gap: 14px; }
        #cbn-avatar {
            width: 48px; height: 48px; border-radius: 50%;
            background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;
            border: 2px solid rgba(255,255,255,0.3); overflow: hidden;
        }
        #cbn-avatar img { width: 100%; height: 100%; object-fit: cover; }
        #cbn-avatar svg { width: 28px; height: 28px; fill: white; }
        #cbn-header-text .cbn-business-name { font-weight: 700; font-size: 16px; line-height: 1.2; }
        #cbn-header-text .cbn-bot-name { font-size: 13px; opacity: 0.9; font-weight: 400; }
        #cbn-header-text .cbn-status { font-size: 11px; opacity: 0.8; display: flex; align-items: center; gap: 6px; margin-top: 4px; }
        #cbn-status-dot { width: 8px; height: 8px; background: #05CD99; border-radius: 50%; animation: cbn-pulse 2s infinite; }
        @keyframes cbn-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }

        #cbn-close-btn {
            width: 36px; height: 36px; border-radius: 50%;
            background: rgba(255,255,255,0.15); border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s; color: white;
        }
        #cbn-close-btn:hover { background: rgba(255,255,255,0.25); transform: scale(1.1); }
        #cbn-close-btn svg { width: 20px; height: 20px; stroke: currentColor; stroke-width: 2; fill: none; }

        #cbn-messages {
            flex: 1; padding: 20px 16px; overflow-y: auto;
            background: linear-gradient(180deg, #F8FAFC 0%, #F1F5F9 100%);
            display: flex; flex-direction: column; gap: 12px;
            scroll-behavior: smooth;
        }
        #cbn-messages::-webkit-scrollbar { width: 6px; }
        #cbn-messages::-webkit-scrollbar-track { background: transparent; }
        #cbn-messages::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 3px; }

        .cbn-msg { display: flex; gap: 10px; max-width: 85%; animation: cbn-msg-in 0.3s ease; }
        @keyframes cbn-msg-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .cbn-msg.bot { align-self: flex-start; }
        .cbn-msg.visitor { align-self: flex-end; flex-direction: row-reverse; }

        .cbn-msg-avatar {
            width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
            background: linear-gradient(135deg, ${config.primary_color} 0%, #6B5CE7 100%);
            display: flex; align-items: center; justify-content: center;
            align-self: flex-end;
        }
        .cbn-msg-avatar svg { width: 18px; height: 18px; fill: white; }
        .cbn-msg-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
        .cbn-msg.visitor .cbn-msg-avatar { background: #64748B; }

        .cbn-msg-content { display: flex; flex-direction: column; gap: 4px; }
        .cbn-msg-bubble {
            padding: 14px 18px; border-radius: 20px; font-size: 14px; line-height: 1.6;
            position: relative; word-wrap: break-word; white-space: pre-wrap;
        }
        .cbn-msg.bot .cbn-msg-bubble {
            background: white; color: #1E293B;
            border-bottom-left-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .cbn-msg.visitor .cbn-msg-bubble {
            background: linear-gradient(135deg, ${config.primary_color} 0%, #6B5CE7 100%);
            color: white; border-bottom-right-radius: 6px;
            box-shadow: 0 2px 8px rgba(67, 24, 255, 0.25);
        }

        .cbn-msg-time { font-size: 10px; color: #94A3B8; padding: 0 4px; }
        .cbn-msg.visitor .cbn-msg-time { text-align: right; }

        /* ChatGPT-style typing indicator */
        .cbn-typing-container {
            display: flex; align-items: center; gap: 4px;
        }
        .cbn-cursor {
            display: inline-block; width: 2px; height: 18px;
            background: ${config.primary_color};
            margin-left: 2px;
            animation: cbn-blink 0.8s infinite;
            vertical-align: text-bottom;
        }
        @keyframes cbn-blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }

        /* Thinking dots animation */
        .cbn-thinking {
            display: flex; gap: 4px; padding: 4px 0;
        }
        .cbn-thinking-dot {
            width: 6px; height: 6px; background: #94A3B8; border-radius: 50%;
            animation: cbn-think 1.4s infinite ease-in-out;
        }
        .cbn-thinking-dot:nth-child(2) { animation-delay: 0.2s; }
        .cbn-thinking-dot:nth-child(3) { animation-delay: 0.4s; }
        @keyframes cbn-think { 0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; } 40% { transform: scale(1); opacity: 1; } }

        /* Streaming text effect */
        .cbn-streaming .cbn-msg-bubble::after {
            content: '';
            display: inline-block; width: 2px; height: 16px;
            background: ${config.primary_color};
            margin-left: 2px; animation: cbn-blink 0.8s infinite;
            vertical-align: text-bottom;
        }

        .cbn-date-separator {
            text-align: center; padding: 12px 0; position: relative;
        }
        .cbn-date-separator span {
            background: #E2E8F0; padding: 6px 16px; border-radius: 20px;
            font-size: 11px; color: #64748B; font-weight: 500;
        }

        #cbn-input-area {
            padding: 16px; background: white; border-top: 1px solid #F1F5F9;
            flex-shrink: 0;
        }
        #cbn-input-wrapper {
            display: flex; gap: 10px; align-items: flex-end;
            background: #F8FAFC; border: 2px solid #E2E8F0;
            border-radius: 24px; padding: 6px 6px 6px 20px;
            transition: all 0.2s;
        }
        #cbn-input-wrapper:focus-within {
            border-color: ${config.primary_color};
            background: white;
            box-shadow: 0 0 0 4px rgba(67, 24, 255, 0.1);
        }
        #cbn-input {
            flex: 1; border: none; background: transparent; font-size: 14px;
            outline: none; resize: none; line-height: 1.5; max-height: 120px;
            min-height: 24px; padding: 4px 0;
        }
        #cbn-input::placeholder { color: #94A3B8; }
        #cbn-send {
            width: 44px; height: 44px; border-radius: 50%; border: none;
            background: linear-gradient(135deg, ${config.primary_color} 0%, #6B5CE7 100%);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: all 0.2s; flex-shrink: 0;
        }
        #cbn-send:hover { transform: scale(1.05); box-shadow: 0 4px 15px rgba(67, 24, 255, 0.4); }
        #cbn-send:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        #cbn-send svg { width: 20px; height: 20px; fill: white; }

        #cbn-branding {
            padding: 10px; text-align: center; font-size: 11px;
            color: #94A3B8; background: #F8FAFC; border-top: 1px solid #F1F5F9;
        }
        #cbn-branding a { color: ${config.primary_color}; font-weight: 600; text-decoration: none; }
        #cbn-branding a:hover { text-decoration: underline; }

        @media (max-width: 480px) {
            #cbn-widget { bottom: 16px; right: 16px; left: 16px; }
            #cbn-window { position: fixed; bottom: 0; right: 0; left: 0; top: 0; width: 100%; max-width: none; height: 100%; max-height: 100%; border-radius: 0; }
            #cbn-button { width: 56px; height: 56px; }
            .cbn-msg { max-width: 90%; }
            .cbn-msg-avatar { width: 28px; height: 28px; }
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
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
                        </div>
                        <div id="cbn-header-text">
                            <div class="cbn-business-name">${config.business_name}</div>
                            <div class="cbn-bot-name">${config.bot_name}</div>
                            <div class="cbn-status"><span id="cbn-status-dot"></span> Online</div>
                        </div>
                    </div>
                    <button id="cbn-close-btn">
                        <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div id="cbn-messages"></div>
                <div id="cbn-input-area">
                    <div id="cbn-input-wrapper">
                        <textarea id="cbn-input" placeholder="Type your message..." rows="1"></textarea>
                        <button id="cbn-send">
                            <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                        </button>
                    </div>
                </div>
                <div id="cbn-branding">Powered by <a href="https://chatbotnepal.isoftroerp.com/" target="_blank">ChatBot Nepal</a></div>
            </div>
            <button id="cbn-button">
                <svg class="cbn-chat-icon" viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                <svg class="cbn-close-icon" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18" stroke="white" stroke-width="2"/><line x1="6" y1="6" x2="18" y2="18" stroke="white" stroke-width="2"/></svg>
            </button>
        `;
        document.body.appendChild(container);

        let sessionToken = null;

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

            const eventSource = new EventSource(`${BASE_URL}/api/chat/stream`, {
                withCredentials: false
            });

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
                        if (done) return;

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
                            eventSource.close();
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
                eventSource.close();
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

        content.appendChild(bubble);
        msgDiv.appendChild(content);

        container.appendChild(msgDiv);
        scrollToBottom();

        return { msgDiv, bubble };
    }

    function appendToBubble(bubble, text) {
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
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g', function(c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    if (document.readyState === 'complete') init();
    else window.addEventListener('load', init);

})();
