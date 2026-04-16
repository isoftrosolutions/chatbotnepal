(function() {
    // 1. Configuration & Constants
    const SCRIPT_TAG = document.currentScript;
    const SITE_ID = SCRIPT_TAG.getAttribute('data-site-id');
    const BASE_URL = new URL(SCRIPT_TAG.src).origin;

    if (!SITE_ID) {
        console.error('ChatBot Nepal: Missing data-site-id attribute.');
        return;
    }

    // 2. State Management
    let config = {
        business_name: 'ChatBot Nepal',
        welcome_message: 'Namaste! How can I help you today?',
        primary_color: '#4318FF',
        bot_name: 'Assistant'
    };
    let conversationId = sessionStorage.getItem('cbn_conversation_id');
    let visitorId = localStorage.getItem('cbn_visitor_id') || uuidv4();
    localStorage.setItem('cbn_visitor_id', visitorId);

    // 3. UI Styles
    const styles = `
        #cbn-widget { position: fixed; bottom: 30px; right: 30px; z-index: 999999; font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        #cbn-button { width: 60px; height: 60px; border-radius: 20px; background: ${config.primary_color}; cursor: pointer; display: flex; align-items: center; justify-content: center; shadow: 0 10px 25px -5px rgba(67, 24, 255, 0.4); transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        #cbn-button:hover { transform: scale(1.1) translateY(-5px); }
        #cbn-button svg { width: 28px; height: 28px; fill: white; }

        #cbn-window { position: absolute; bottom: 80px; right: 0; width: 380px; height: 600px; max-height: calc(100vh - 120px); background: white; border-radius: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.15); display: none; flex-direction: column; overflow: hidden; animation: cbn-slide 0.4s ease; }
        @keyframes cbn-slide { from { opacity: 0; transform: translateY(20px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }

        #cbn-header { padding: 25px; background: ${config.primary_color}; color: white; display: flex; align-items: center; justify-content: space-between; }
        #cbn-header .title { font-weight: 700; font-size: 16px; }
        #cbn-header .status { font-size: 11px; opacity: 0.8; display: flex; items-center gap: 5px; }
        #cbn-header .close { cursor: pointer; opacity: 0.7; transition: opacity 0.2s; }
        #cbn-header .close:hover { opacity: 1; }

        #cbn-messages { flex: 1; padding: 20px; overflow-y: auto; background: #F4F7FE; display: flex; flex-direction: column; gap: 15px; }
        .cbn-msg { max-width: 80%; padding: 12px 16px; border-radius: 18px; font-size: 14px; line-height: 1.5; position: relative; }
        .cbn-msg.bot { align-self: flex-start; background: white; color: #1B1B38; border-bottom-left-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .cbn-msg.visitor { align-self: flex-end; background: ${config.primary_color}; color: white; border-bottom-right-radius: 4px; }

        #cbn-input-area { padding: 20px; background: white; border-top: 1px solid #F0F0F0; display: flex; gap: 10px; }
        #cbn-input { flex: 1; border: none; background: #F4F7FE; padding: 12px 15px; rounded: 12px; font-size: 14px; outline: none; }
        #cbn-send { background: ${config.primary_color}; border: none; width: 40px; height: 40px; border-radius: 10px; cursor: pointer; display: flex; items-center justify-center; }
        #cbn-send svg { width: 18px; height: 18px; fill: white; }

        .cbn-typing { display: flex; gap: 4px; padding: 5px; }
        .cbn-dot { width: 6px; height: 6px; background: #A3AED0; rounded-full; animation: cbn-bounce 1.4s infinite ease-in-out; }
        .cbn-dot:nth-child(2) { animation-delay: 0.2s; }
        .cbn-dot:nth-child(3) { animation-delay: 0.4s; }
        @keyframes cbn-bounce { 0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1.0); } }

        #cbn-branding { padding: 8px; text-align: center; font-size: 10px; color: #A3AED0; background: #F4F7FE; }
        #cbn-branding a { color: ${config.primary_color}; font-weight: bold; text-decoration: none; }
    `;

    // 4. Initialization
    function init() {
        // Inject Styles
        const styleSheet = document.createElement("style");
        styleSheet.innerText = styles;
        document.head.appendChild(styleSheet);

        // Inject HTML
        const container = document.createElement('div');
        container.id = 'cbn-widget';
        container.innerHTML = `
            <div id="cbn-window">
                <div id="cbn-header">
                    <div>
                        <div class="title">${config.business_name}</div>
                        <div class="status"><span style="width: 6px; height: 6px; background: #05CD99; border-radius: 50%; display: inline-block;"></span> Online</div>
                    </div>
                    <div class="close" id="cbn-close">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </div>
                </div>
                <div id="cbn-messages"></div>
                <div id="cbn-input-area">
                    <input type="text" id="cbn-input" placeholder="Type your message...">
                    <button id="cbn-send">
                        <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path></svg>
                    </button>
                </div>
                <div id="cbn-branding">Powered by <a href="https://chatbotnepal.isoftroerp.com/" target="_blank">ChatBot Nepal</a></div>
            </div>
            <div id="cbn-button">
                <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"></path></svg>
            </div>
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

    // 5. Helper Functions
    function setupEvents() {
        const btn = document.getElementById('cbn-button');
        const win = document.getElementById('cbn-window');
        const close = document.getElementById('cbn-close');
        const input = document.getElementById('cbn-input');
        const send = document.getElementById('cbn-send');

        btn.onclick = () => win.style.display = win.style.display === 'flex' ? 'none' : 'flex';
        close.onclick = () => win.style.display = 'none';
        
        const sendMessage = () => {
            const msg = input.value.trim();
            if (!msg) return;
            if (!sessionToken) {
                addMessage('bot', "Chat not ready. Please refresh the page.");
                return;
            }

            addMessage('visitor', msg);
            input.value = '';
            showTyping();

            fetch(`${BASE_URL}/api/chat`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Session-Token': sessionToken
                },
                body: JSON.stringify({
                    site_id: SITE_ID,
                    message: msg,
                    visitor_id: visitorId,
                    conversation_id: conversationId,
                    source_url: window.location.href
                })
            })
            .then(r => r.json())
            .then(data => {
                hideTyping();
                if (data.reply) {
                    addMessage('bot', data.reply);
                    if (data.conversation_id) {
                        conversationId = data.conversation_id;
                        sessionStorage.setItem('cbn_conversation_id', conversationId);
                    }
                }
            })
            .catch(() => {
                hideTyping();
                addMessage('bot', "I'm sorry, I'm having trouble connecting right now. Please try again later.");
            });
        };

        send.onclick = sendMessage;
        input.onkeypress = (e) => { if (e.key === 'Enter') sendMessage(); };
    }

    function addMessage(role, text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `cbn-msg ${role}`;
        msgDiv.innerText = text;
        const container = document.getElementById('cbn-messages');
        container.appendChild(msgDiv);
        container.scrollTop = container.scrollHeight;
    }

    function showTyping() {
        const typingDiv = document.createElement('div');
        typingDiv.id = 'cbn-typing-indicator';
        typingDiv.className = 'cbn-msg bot';
        typingDiv.innerHTML = `<div class="cbn-typing"><div class="cbn-dot"></div><div class="cbn-dot"></div><div class="cbn-dot"></div></div>`;
        const container = document.getElementById('cbn-messages');
        container.appendChild(typingDiv);
        container.scrollTop = container.scrollHeight;
    }

    function hideTyping() {
        const el = document.getElementById('cbn-typing-indicator');
        if (el) el.remove();
    }

    function updateTheme() {
        document.getElementById('cbn-header').style.background = config.primary_color;
        document.getElementById('cbn-button').style.background = config.primary_color;
        document.getElementById('cbn-send').style.background = config.primary_color;
        document.querySelectorAll('.cbn-msg.visitor').forEach(el => el.style.background = config.primary_color);
        document.querySelector('.title').innerText = config.business_name;
    }

    function uuidv4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    // 6. Start
    if (document.readyState === 'complete') init();
    else window.addEventListener('load', init);

})();
