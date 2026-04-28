from playwright.sync_api import sync_playwright
import re

url = "https://www.avocadoresort.com/"
screenshot_path = "C:/Apache24/htdocs/ai/screenshots/avocado_desktop.png"

with sync_playwright() as p:
    browser = p.chromium.launch()
    page = browser.new_page(viewport={'width': 1920, 'height': 1080})
    page.goto(url, wait_until='networkidle', timeout=60000)

    # Get full page source
    content = page.content()

    # Save source for inspection
    with open("C:/Apache24/htdocs/ai/screenshots/avocado_source.html", "w", encoding="utf-8") as f:
        f.write(content)

    # Count occurrences
    widget_js_count = content.lower().count('widget.js')
    chatbotnepal_count = content.lower().count('chatbotnepal')

    print(f"widget.js occurrences: {widget_js_count}")
    print(f"chatbotnepal occurrences: {chatbotnepal_count}")

    # Find all script tags containing widget.js or chatbotnepal
    script_pattern = re.compile(
        r'<script[^>]*(?:widget\.js|chatbotnepal)[^>]*>.*?</script>|<script[^>]*(?:widget\.js|chatbotnepal)[^>]*/?>',
        re.IGNORECASE | re.DOTALL
    )
    matches = script_pattern.findall(content)

    print(f"\nTotal matching script tags found: {len(matches)}")
    for i, m in enumerate(matches, 1):
        print(f"\n--- Occurrence {i} ---")
        print(m)

    # Also search line by line for any line containing widget.js or chatbotnepal
    print("\n\n=== All lines containing widget.js or chatbotnepal ===")
    for lineno, line in enumerate(content.splitlines(), 1):
        if 'widget.js' in line.lower() or 'chatbotnepal' in line.lower():
            print(f"Line {lineno}: {line.strip()}")

    # Take screenshot
    page.screenshot(path=screenshot_path, full_page=False)
    print(f"\nScreenshot saved to: {screenshot_path}")

    browser.close()
