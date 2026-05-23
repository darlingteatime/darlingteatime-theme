from playwright.sync_api import sync_playwright

def test_frontend():
    print("Starting Playwright E2E frontend verification on http://localhost:8888...")
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()
        try:
            page.goto("http://localhost:8888", timeout=10000)
            title = page.title()
            print(f"Page loaded successfully! Title: '{title}'")
            
            # Validate basic page structure and theme loading
            assert len(title) > 0, "Page title should not be empty."
            
            # Check for header elements or body classes
            body_class = page.eval_on_selector("body", "el => el.className")
            print(f"Body classes: {body_class}")
            
            print("Successfully verified local WordPress frontend!")
        except Exception as e:
            print(f"Error occurred during verification: {e}")
            raise e
        finally:
            browser.close()

if __name__ == "__main__":
    test_frontend()
