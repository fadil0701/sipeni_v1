"""Wrapper Playwright — login, navigasi, overlay langkah di browser."""

from __future__ import annotations

import os
import re
import time
from contextlib import contextmanager
from typing import Any, Generator
from urllib.parse import urljoin

from playwright.sync_api import Browser, BrowserContext, Page, Playwright, sync_playwright


class SimantikBrowser:
    OVERLAY_ID = "simantik-e2e-overlay"

    def __init__(
        self,
        base_url: str,
        *,
        headless: bool = False,
        slow_mo: int = 400,
        viewport_width: int = 1400,
        viewport_height: int = 900,
        step_pause: float = 1.2,
        default_timeout: int = 30000,
    ) -> None:
        self.base_url = base_url.rstrip("/")
        self.headless = headless
        self.slow_mo = slow_mo
        self.viewport_width = viewport_width
        self.viewport_height = viewport_height
        self.step_pause = step_pause
        self.default_timeout = default_timeout
        self._playwright: Playwright | None = None
        self._browser: Browser | None = None
        self._context: BrowserContext | None = None
        self.page: Page | None = None

    @contextmanager
    def session(self) -> Generator[SimantikBrowser, None, None]:
        self._playwright = sync_playwright().start()
        self._browser = self._playwright.chromium.launch(
            headless=self.headless,
            slow_mo=self.slow_mo,
        )
        self._context = self._browser.new_context(
            viewport={"width": self.viewport_width, "height": self.viewport_height},
            locale="id-ID",
        )
        self._context.set_default_timeout(self.default_timeout)
        self.page = self._context.new_page()
        self.page.on("dialog", lambda dialog: dialog.accept())
        try:
            yield self
        finally:
            if self._context:
                self._context.close()
            if self._browser:
                self._browser.close()
            if self._playwright:
                self._playwright.stop()
            self.page = None

    def url(self, path: str) -> str:
        if path.startswith("http"):
            return path
        if not path.startswith("/"):
            path = "/" + path
        return self.base_url + path

    def pause(self, seconds: float | None = None) -> None:
        time.sleep(seconds if seconds is not None else self.step_pause)

    def show_step(self, persona: str, message: str) -> None:
        assert self.page is not None
        safe_persona = persona.replace("'", "\\'")
        safe_msg = message.replace("'", "\\'")
        self.page.evaluate(
            f"""() => {{
            let el = document.getElementById('{self.OVERLAY_ID}');
            if (!el) {{
                el = document.createElement('div');
                el.id = '{self.OVERLAY_ID}';
                el.style.cssText = 'position:fixed;z-index:99999;top:12px;left:50%;transform:translateX(-50%);'
                    + 'background:#1e3a8a;color:#fff;padding:12px 20px;border-radius:10px;'
                    + 'font:600 14px/1.4 system-ui,sans-serif;box-shadow:0 8px 24px rgba(0,0,0,.25);'
                    + 'max-width:90vw;text-align:center;pointer-events:none;';
                document.body.appendChild(el);
            }}
            el.innerHTML = '<div style="opacity:.85;font-size:11px;text-transform:uppercase;letter-spacing:.05em">{safe_persona}</div>'
                + '<div style="margin-top:4px">{safe_msg}</div>';
        }}"""
        )
        print(f"  >> [{persona}] {message}")

    def goto(self, path: str, *, wait: str = "domcontentloaded") -> None:
        assert self.page is not None
        self.page.goto(self.url(path), wait_until=wait)

    def login(self, email: str, password: str, *, persona: str = "User") -> None:
        assert self.page is not None
        self.show_step(persona, "Membuka halaman login...")
        self.goto("/login")
        self.page.wait_for_selector("#email", state="visible", timeout=self.default_timeout)
        self.page.fill("#email", email)
        self.page.fill("#password", password)
        self.pause(0.5)
        self.show_step(persona, "Mengisi kredensial & klik Login")
        self.page.click("button.login-submit")
        self.page.wait_for_url(re.compile(r".*(?!/login).*$"), timeout=self.default_timeout)
        self.pause()

    def logout(self, *, persona: str = "User") -> None:
        assert self.page is not None
        if "/login" in self.page.url:
            return
        self.show_step(persona, "Logout...")
        try:
            self.page.click("#user-menu-button", timeout=5000)
            self.page.click('form[action*="logout"] button[type="submit"]', timeout=5000)
            self.page.wait_for_url(re.compile(r".*/login.*"), timeout=self.default_timeout)
        except Exception:
            self.goto("/login")
        self.page.wait_for_selector("#email", state="visible", timeout=self.default_timeout)
        self.pause(0.8)

    def select_native(self, selector: str, value: str) -> None:
        assert self.page is not None
        self.page.wait_for_selector(selector, state="attached")
        self.page.select_option(selector, value)

    def set_select_value(self, selector: str, value: str) -> None:
        """Set nilai select + trigger change (Select2 / Choices / native)."""
        assert self.page is not None
        self.page.wait_for_selector(selector, state="attached")
        self.page.evaluate(
            """([selector, value]) => {
                const el = document.querySelector(selector);
                if (!el) return;
                el.value = value;
                if (window.jQuery) {
                    const $el = window.jQuery(el);
                    if ($el.hasClass('select2-hidden-accessible')) {
                        $el.val(value).trigger('change');
                        return;
                    }
                }
                el.dispatchEvent(new Event('change', { bubbles: true }));
            }""",
            [selector, value],
        )
        self.pause(0.8)

    def click_submit_and_wait(self, form_selector: str, *, url_pattern: str | None = None) -> None:
        assert self.page is not None
        btn = f"{form_selector} button[type='submit']"
        self.page.wait_for_selector(btn, state="visible")
        if url_pattern:
            with self.page.expect_navigation(url=re.compile(url_pattern), timeout=self.default_timeout):
                self.page.click(btn)
        else:
            with self.page.expect_navigation(timeout=self.default_timeout):
                self.page.click(btn)
        self.page.wait_for_load_state("domcontentloaded")

    def visible_errors(self) -> list[str]:
        assert self.page is not None
        return self.page.evaluate(
            """() => {
                const out = [];
                document.querySelectorAll('.bg-red-50 li, .text-red-600, .login-alert li').forEach(el => {
                    const t = (el.textContent || '').trim();
                    if (t && t.length < 400) out.push(t);
                });
                return out;
            }"""
        )

    def wait_for_item_row(self) -> None:
        assert self.page is not None
        self.page.wait_for_selector("#detailContainer .item-row", timeout=self.default_timeout)

    def wait_for_select_option(self, selector: str, value: str, *, timeout_ms: int | None = None) -> None:
        assert self.page is not None
        timeout = timeout_ms or self.default_timeout
        self.page.wait_for_function(
            """([selector, value]) => {
                const el = document.querySelector(selector);
                return el && Array.from(el.options).some(o => o.value === value);
            }""",
            arg=[selector, value],
            timeout=timeout,
        )

    def confirm_modal_if_open(self) -> None:
        """Klik 'Ya, Lanjutkan' pada modal konfirmasi global (data-confirm)."""
        assert self.page is not None
        ok_btn = self.page.locator("#global-confirm-modal [data-confirm-ok]")
        if ok_btn.count() == 0:
            return
        try:
            ok_btn.first.click(timeout=5000)
            self.page.wait_for_selector(
                "#global-confirm-modal:not(.is-open)",
                timeout=self.default_timeout,
            )
        except Exception:
            pass
        self.pause(0.4)

    def click_confirm_submit(self, selector: str, *, wait_navigation: bool = False) -> None:
        """Klik tombol submit yang memicu modal data-confirm, lalu konfirmasi."""
        assert self.page is not None
        btn = self.page.locator(selector).first
        btn.wait_for(state="visible", timeout=self.default_timeout)
        btn.click()
        modal_ok = self.page.locator("#global-confirm-modal.is-open [data-confirm-ok]")
        try:
            modal_ok.wait_for(state="visible", timeout=5000)
        except Exception:
            if wait_navigation:
                self.page.wait_for_load_state("domcontentloaded")
            return
        if wait_navigation:
            with self.page.expect_navigation(timeout=self.default_timeout):
                modal_ok.click()
        else:
            modal_ok.click()
            self.page.wait_for_load_state("domcontentloaded")
        self.pause(0.4)

    def click_first_matching(self, selector: str) -> bool:
        assert self.page is not None
        loc = self.page.locator(selector).first
        if loc.count() == 0:
            return False
        loc.click()
        return True

    def extract_id_from_url(self, pattern: str) -> str | None:
        assert self.page is not None
        match = re.search(pattern, self.page.url)
        return match.group(1) if match else None

    def screenshot(self, path: str) -> None:
        assert self.page is not None
        self.page.screenshot(path=path, full_page=False)
