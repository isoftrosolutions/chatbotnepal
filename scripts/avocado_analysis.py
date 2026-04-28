from playwright.sync_api import sync_playwright
import json

URL = "https://www.avocadoresort.com/"
SCREENSHOTS_DIR = "C:/Apache24/htdocs/ai/screenshots"

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page(viewport={'width': 1920, 'height': 1080})

        print("Navigating to:", URL)
        page.goto(URL, wait_until='networkidle', timeout=60000)
        page.wait_for_timeout(3000)

        # Screenshot 1: top of page (above the fold)
        page.screenshot(path=f"{SCREENSHOTS_DIR}/avocado_top.png", full_page=False)
        print("Saved: avocado_top.png")

        # Full-page screenshot
        page.screenshot(path=f"{SCREENSHOTS_DIR}/avocado_fullpage.png", full_page=True)
        print("Saved: avocado_fullpage.png")

        # Scroll to bottom
        page.evaluate("window.scrollTo(0, document.body.scrollHeight)")
        page.wait_for_timeout(2000)
        page.screenshot(path=f"{SCREENSHOTS_DIR}/avocado_bottom.png", full_page=False)
        print("Saved: avocado_bottom.png")

        # Scroll back to top for widget search
        page.evaluate("window.scrollTo(0, 0)")
        page.wait_for_timeout(1000)

        # Extract page source and look for widget script tags
        html = page.content()

        # Search for widget-related patterns
        import re
        patterns = [
            r'<script[^>]*widget\.js[^>]*>.*?</script>',
            r'<script[^>]*data-token[^>]*>.*?</script>',
            r'<script[^>]*data-site-id[^>]*>.*?</script>',
            r'<script[^>]*(chatbot|chat-widget|livechat|tawk|intercom|crisp|freshchat|drift|hubspot)[^>]*>',
        ]

        found_scripts = []
        for pat in patterns:
            matches = re.findall(pat, html, re.IGNORECASE | re.DOTALL)
            found_scripts.extend(matches)

        print("\n=== Widget Script Tags Found ===")
        if found_scripts:
            for s in found_scripts:
                print(s[:500])
        else:
            print("No widget script tags found with primary patterns.")

        # Broader search for common chat widget indicators
        broader = re.findall(r'<script[^>]*>(.*?)</script>', html, re.DOTALL | re.IGNORECASE)
        chat_keywords = ['widget', 'chatbot', 'livechat', 'tawk', 'intercom', 'crisp', 'freshchat', 'drift', 'hubspot', 'zendesk', 'chat']
        found_inline = []
        for script in broader:
            for kw in chat_keywords:
                if kw.lower() in script.lower():
                    found_inline.append(script[:300])
                    break

        print("\n=== Inline Scripts with Chat Keywords ===")
        if found_inline:
            for s in found_inline[:5]:
                print("---")
                print(s)
        else:
            print("None found.")

        # Look for chat-related script src attributes
        src_matches = re.findall(r'<script[^>]*src=["\']([^"\']*(?:chat|widget|bot|tawk|intercom|crisp|drift|hubspot|zendesk)[^"\']*)["\'][^>]*>', html, re.IGNORECASE)
        print("\n=== Script src with Chat Keywords ===")
        if src_matches:
            for s in src_matches:
                print(s)
        else:
            print("None found.")

        # Look for data-token or data-site-id anywhere in the HTML
        token_matches = re.findall(r'data-token=["\']([^"\']+)["\']', html, re.IGNORECASE)
        site_id_matches = re.findall(r'data-site-id=["\']([^"\']+)["\']', html, re.IGNORECASE)
        print("\n=== data-token values ===", token_matches if token_matches else "None")
        print("=== data-site-id values ===", site_id_matches if site_id_matches else "None")

        # Try to find chat widget elements in the DOM
        print("\n=== DOM Chat Widget Search ===")
        chat_selectors = [
            'iframe[src*="chat"]',
            'iframe[src*="widget"]',
            'iframe[src*="bot"]',
            '[id*="chat"]',
            '[id*="widget"]',
            '[id*="bot"]',
            '[class*="chat-bubble"]',
            '[class*="chat-widget"]',
            '[class*="chat-button"]',
            '[class*="chatbot"]',
            'button[class*="chat"]',
            'div[class*="launcher"]',
        ]

        for sel in chat_selectors:
            try:
                elements = page.query_selector_all(sel)
                if elements:
                    for el in elements[:3]:
                        outer = el.evaluate('el => el.outerHTML')
                        print(f"Selector: {sel}")
                        print(outer[:400])
                        print()
            except:
                pass

        # Try clicking common chat widget positions (bottom-right corner)
        print("\n=== Attempting to find and click chat widget ===")

        # First try specific selectors
        click_selectors = [
            '[id*="chat"]',
            '[id*="widget"]',
            '[class*="chat-bubble"]',
            '[class*="launcher"]',
            '[class*="chatbot"]',
            'button[class*="chat"]',
            'iframe[title*="chat"]',
        ]

        clicked = False
        for sel in click_selectors:
            try:
                el = page.query_selector(sel)
                if el and el.is_visible():
                    print(f"Found visible element: {sel}")
                    el.click()
                    page.wait_for_timeout(2000)
                    page.screenshot(path=f"{SCREENSHOTS_DIR}/avocado_widget_open.png", full_page=False)
                    print("Saved: avocado_widget_open.png (after clicking widget)")
                    clicked = True
                    break
            except Exception as e:
                pass

        if not clicked:
            print("Could not find a clickable chat widget via selectors.")
            # Try clicking in bottom-right area (common position for chat bubbles)
            try:
                page.mouse.click(1870, 1000)
                page.wait_for_timeout(2000)
                page.screenshot(path=f"{SCREENSHOTS_DIR}/avocado_bottomright_click.png", full_page=False)
                print("Saved: avocado_bottomright_click.png (clicked bottom-right corner)")
            except Exception as e:
                print(f"Bottom-right click failed: {e}")

        browser.close()
        print("\nDone.")

if __name__ == "__main__":
    run()
