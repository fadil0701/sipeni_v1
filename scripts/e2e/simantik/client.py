"""HTTP client for Laravel session + CSRF authentication."""

from __future__ import annotations

import re
from dataclasses import dataclass
from typing import Any
from urllib.parse import urljoin, urlparse

import requests
from bs4 import BeautifulSoup


@dataclass
class HttpResult:
    method: str
    url: str
    status_code: int
    ok: bool
    message: str
    final_url: str = ""

    @property
    def is_success(self) -> bool:
        return self.ok


class SimantikClient:
    CSRF_META_RE = re.compile(r'name="csrf-token"\s+content="([^"]+)"')
    CSRF_INPUT_RE = re.compile(r'name="_token"\s+value="([^"]+)"')

    def __init__(
        self,
        base_url: str,
        timeout: int = 30,
        verify_ssl: bool = True,
    ) -> None:
        self.base_url = base_url.rstrip("/")
        self.timeout = timeout
        self.session = requests.Session()
        self.session.verify = verify_ssl
        self._csrf: str | None = None
        self._logged_in_email: str | None = None

    def absolute(self, path: str) -> str:
        if path.startswith("http://") or path.startswith("https://"):
            return path
        if not path.startswith("/"):
            path = "/" + path
        return self.base_url + path

    def extract_csrf(self, html: str) -> str | None:
        if not html:
            return None
        match = self.CSRF_META_RE.search(html)
        if match:
            return match.group(1)
        match = self.CSRF_INPUT_RE.search(html)
        if match:
            return match.group(1)
        soup = BeautifulSoup(html, "html.parser")
        meta = soup.find("meta", attrs={"name": "csrf-token"})
        if meta and meta.get("content"):
            return str(meta["content"]).strip()
        inp = soup.find("input", attrs={"name": "_token"})
        if inp and inp.get("value"):
            return str(inp["value"]).strip()
        return None

    def refresh_csrf(self, html: str | None = None) -> str:
        if html:
            token = self.extract_csrf(html)
            if token:
                self._csrf = token
                return token

        for path in ("/", "/login"):
            try:
                response = self.session.get(self.absolute(path), timeout=self.timeout)
                token = self.extract_csrf(response.text)
                if token:
                    self._csrf = token
                    return token
            except requests.RequestException:
                continue

        if self._csrf:
            return self._csrf

        raise RuntimeError("CSRF token tidak ditemukan di halaman.")

    @property
    def csrf(self) -> str:
        if not self._csrf:
            return self.refresh_csrf()
        return self._csrf

    def get(self, path: str, *, allow_redirects: bool = True) -> requests.Response:
        return self.session.get(
            self.absolute(path),
            timeout=self.timeout,
            allow_redirects=allow_redirects,
        )

    def post(
        self,
        path: str,
        data: dict[str, Any] | list[tuple[str, Any]] | None = None,
        *,
        html_for_csrf: str | None = None,
    ) -> requests.Response:
        token = self.refresh_csrf(html_for_csrf)
        if isinstance(data, list):
            payload: list[tuple[str, Any]] = [("_token", token), *data]
        else:
            payload_dict = dict(data or {})
            payload_dict.setdefault("_token", token)
            payload = payload_dict  # type: ignore[assignment]
        return self.session.post(
            self.absolute(path),
            data=payload,
            timeout=self.timeout,
            allow_redirects=True,
        )

    def login(self, email: str, password: str) -> HttpResult:
        login_page = self.get("/login")
        csrf = self.refresh_csrf(login_page.text)
        response = self.post(
            "/login",
            {"email": email, "password": password, "remember": "1"},
            html_for_csrf=login_page.text,
        )

        if "/two-factor-challenge" in response.url:
            return HttpResult(
                method="POST",
                url=self.absolute("/login"),
                status_code=response.status_code,
                ok=False,
                message="Login memerlukan 2FA. Set TWO_FACTOR_ENABLED=false di .env target.",
                final_url=response.url,
            )

        if response.status_code == 429:
            return HttpResult(
                method="POST",
                url=self.absolute("/login"),
                status_code=429,
                ok=False,
                message="Rate limit login (throttle:5,1). Tunggu 1 menit.",
                final_url=response.url,
            )

        if response.status_code >= 500:
            return HttpResult(
                method="POST",
                url=self.absolute("/login"),
                status_code=response.status_code,
                ok=False,
                message=f"Server error saat login ({response.status_code})",
                final_url=response.url,
            )

        # Sukses: redirect ke dashboard, bukan kembali ke /login
        parsed = urlparse(response.url)
        on_login = parsed.path.rstrip("/") == "/login"
        has_login_error = "These credentials do not match" in response.text or "credentials" in response.text.lower()

        if on_login or has_login_error:
            return HttpResult(
                method="POST",
                url=self.absolute("/login"),
                status_code=response.status_code,
                ok=False,
                message=f"Login gagal untuk {email}",
                final_url=response.url,
            )

        self._logged_in_email = email
        token = self.extract_csrf(response.text)
        if token:
            self._csrf = token
        return HttpResult(
            method="POST",
            url=self.absolute("/login"),
            status_code=response.status_code,
            ok=True,
            message=f"Login berhasil sebagai {email}",
            final_url=response.url,
        )

    def logout(self) -> None:
        try:
            login_page = self.session.get(self.absolute("/login"), timeout=self.timeout)
            token = self.extract_csrf(login_page.text)
            if token:
                self.session.post(
                    self.absolute("/logout"),
                    data={"_token": token},
                    timeout=self.timeout,
                    allow_redirects=True,
                )
        except Exception:
            pass
        self.session.cookies.clear()
        self._csrf = None
        self._logged_in_email = None

    def check_get(
        self,
        path: str,
        *,
        expect_status: int | tuple[int, ...] = 200,
        label: str | None = None,
    ) -> HttpResult:
        response = self.get(path)
        expected = (expect_status,) if isinstance(expect_status, int) else expect_status
        ok = response.status_code in expected
        name = label or path
        if ok:
            msg = f"GET {name} -> {response.status_code}"
        else:
            snippet = response.text[:200].replace("\n", " ")
            msg = f"GET {name} -> {response.status_code} (diharapkan {expected}) | {snippet}"
        return HttpResult(
            method="GET",
            url=self.absolute(path),
            status_code=response.status_code,
            ok=ok,
            message=msg,
            final_url=response.url,
        )

    def parse_select_options(self, html: str, select_name: str) -> list[tuple[str, str]]:
        soup = BeautifulSoup(html, "html.parser")
        select = soup.find("select", attrs={"name": select_name})
        if not select:
            select = soup.find("select", attrs={"id": select_name})
        if not select:
            return []
        options: list[tuple[str, str]] = []
        for opt in select.find_all("option"):
            value = (opt.get("value") or "").strip()
            if value:
                options.append((value, opt.get_text(strip=True)))
        return options

    def find_first_href(self, html: str, path_fragment: str) -> str | None:
        soup = BeautifulSoup(html, "html.parser")
        for a in soup.find_all("a", href=True):
            href = a["href"]
            if path_fragment in href:
                return href
        return None

    def find_form_action_ids(self, html: str, action_fragment: str) -> list[str]:
        """Ambil ID numerik dari action form POST (mis. approval/123/mengetahui)."""
        soup = BeautifulSoup(html, "html.parser")
        ids: list[str] = []
        for form in soup.find_all("form"):
            action = form.get("action") or ""
            if action_fragment not in action:
                continue
            parts = action.rstrip("/").split("/")
            for part in reversed(parts):
                if part.isdigit():
                    ids.append(part)
                    break
        return ids

    def extract_errors(self, html: str) -> list[str]:
        soup = BeautifulSoup(html, "html.parser")
        errors: list[str] = []
        for li in soup.select(".bg-red-50 li, .border-red-400 li, ul.list-disc li.text-red-600"):
            text = li.get_text(strip=True)
            if text and len(text) < 500:
                errors.append(text)
        for p in soup.select("p.text-red-600, p.text-red-800"):
            text = p.get_text(strip=True)
            if text:
                errors.append(text)
        return errors[:5]

    def page_contains(self, html: str, *needles: str) -> bool:
        lowered = html.lower()
        return all(n.lower() in lowered for n in needles)
