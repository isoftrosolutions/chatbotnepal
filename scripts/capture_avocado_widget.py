from playwright.sync_api import sync_playwright
import time

url = "https://www.avocadoresort.com/"
screenshot_path = "C:/Apache24/htdocs/ai/screenshots/avocado_with_widget.png"

with sync_playwright() as p:
    browser = p.chromium.launch()
    page = browser.new_page(viewport={'width': 1920, 'height': 1080})
    page.goto(url, wait_until='networkidle', timeout=60000)

    # Wait additional time for widget JS to bootstrap
    page.wait_for_timeout(5000)

    # Check if chat launcher iframe/button is present
    widget_elements = page.query_selector_all('[id*="chatbot"], [class*="chatbot"], iframe[src*="chatbot"], #chatbot-nepal-launcher, [id*="chat-launcher"]')
    print(f"Widget-related elements found: {len(widget_elements)}")
    for el in widget_elements:
        print(f"  Tag: {el.evaluate('el => el.tagName')}, id={el.get_attribute('id')}, class={el.get_attribute('class')}")

    # Also check for any iframes
    iframes = page.query_selector_all('iframe')
    print(f"\nAll iframes on page: {len(iframes)}")
    for iframe in iframes:
        src = iframe.get_attribute('src') or ''
        print(f"  iframe src: {src}")

    # Scroll to bottom right to see floating button
    page.evaluate("window.scrollTo(0, 0)")
    page.wait_for_timeout(1000)

    page.screenshot(path=screenshot_path, full_page=False)
    print(f"\nScreenshot saved: {screenshot_path}")

    browser.close()
