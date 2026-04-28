from playwright.sync_api import sync_playwright

url = "https://www.avocadoresort.com/"
screenshot_path = "C:/Apache24/htdocs/ai/screenshots/avocado_final.png"

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    context = browser.new_context(
        viewport={'width': 1920, 'height': 1080},
        user_agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    )
    page = context.new_page()

    # Capture console logs and network requests related to widget
    widget_requests = []
    console_msgs = []

    page.on("console", lambda msg: console_msgs.append(f"[{msg.type}] {msg.text}"))
    page.on("request", lambda req: widget_requests.append(req.url) if ('chatbot' in req.url or 'widget' in req.url) else None)

    page.goto(url, wait_until='networkidle', timeout=60000)
    page.wait_for_timeout(6000)

    # Check all elements in document including shadow DOM
    all_ids = page.evaluate("""
        () => {
            const all = document.querySelectorAll('*');
            const ids = [];
            all.forEach(el => {
                if (el.id && (el.id.toLowerCase().includes('chat') || el.id.toLowerCase().includes('bot'))) {
                    ids.push({tag: el.tagName, id: el.id, classes: el.className});
                }
            });
            return ids;
        }
    """)
    print("Chat-related elements by ID:")
    for el in all_ids:
        print(f"  {el}")

    # Check for shadow hosts
    shadow_hosts = page.evaluate("""
        () => {
            const all = document.querySelectorAll('*');
            const hosts = [];
            all.forEach(el => {
                if (el.shadowRoot) {
                    hosts.push({tag: el.tagName, id: el.id || '', class: el.className || ''});
                }
            });
            return hosts;
        }
    """)
    print(f"\nShadow DOM hosts: {len(shadow_hosts)}")
    for h in shadow_hosts:
        print(f"  {h}")

    print(f"\nWidget-related network requests ({len(widget_requests)}):")
    for r in widget_requests:
        print(f"  {r}")

    print(f"\nConsole messages ({len(console_msgs)}):")
    for m in console_msgs[:20]:
        print(f"  {m}")

    page.screenshot(path=screenshot_path, full_page=False)
    print(f"\nScreenshot saved: {screenshot_path}")

    browser.close()
