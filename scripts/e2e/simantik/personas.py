"""Muat persona & resolve email dari config YAML + bootstrap + .env."""

from __future__ import annotations

import os
from dataclasses import dataclass
from pathlib import Path
from typing import Any

import yaml

# Kunci persona (personas.yaml `key`) → (env email, env password)
PERSONA_ENV_KEYS: dict[str, tuple[str, str]] = {
    "super_administrator": ("SIMANTIK_EMAIL_SUPER_ADMIN", "SIMANTIK_PASSWORD_SUPER_ADMIN"),
    "admin": ("SIMANTIK_EMAIL_ADMIN_IT", "SIMANTIK_PASSWORD_ADMIN_IT"),
    "admin_unit": ("SIMANTIK_EMAIL_PEMOHON", "SIMANTIK_PASSWORD_PEMOHON"),
    "kepala_unit": ("SIMANTIK_EMAIL_KEPALA_UNIT", "SIMANTIK_PASSWORD_KEPALA_UNIT"),
    "kasubbag_tu": ("SIMANTIK_EMAIL_KASUBBAG_TU", "SIMANTIK_PASSWORD_KASUBBAG_TU"),
    "kepala_pusat": ("SIMANTIK_EMAIL_KEPALA_PUSAT", "SIMANTIK_PASSWORD_KEPALA_PUSAT"),
    "admin_gudang_pusat": ("SIMANTIK_EMAIL_ADMIN_GUDANG", "SIMANTIK_PASSWORD_ADMIN_GUDANG"),
    "admin_gudang_persediaan": ("SIMANTIK_EMAIL_ADMIN_GUDANG_PERSEDIAAN", "SIMANTIK_PASSWORD_ADMIN_GUDANG_PERSEDIAAN"),
    "admin_gudang_aset": ("SIMANTIK_EMAIL_ADMIN_GUDANG_ASET", "SIMANTIK_PASSWORD_ADMIN_GUDANG_ASET"),
    "admin_gudang_farmasi": ("SIMANTIK_EMAIL_ADMIN_GUDANG_FARMASI", "SIMANTIK_PASSWORD_ADMIN_GUDANG_FARMASI"),
    "perencana": ("SIMANTIK_EMAIL_PERENCANA", "SIMANTIK_PASSWORD_PERENCANA"),
    "pengadaan": ("SIMANTIK_EMAIL_PENGADAAN", "SIMANTIK_PASSWORD_PENGADAAN"),
    "keuangan": ("SIMANTIK_EMAIL_KEUANGAN", "SIMANTIK_PASSWORD_KEUANGAN"),
}

FLOW_ENV_KEYS: dict[str, tuple[str, str]] = {
    "pemohon": ("SIMANTIK_EMAIL_PEMOHON", "SIMANTIK_PASSWORD_PEMOHON"),
    "kepala_unit": ("SIMANTIK_EMAIL_KEPALA_UNIT", "SIMANTIK_PASSWORD_KEPALA_UNIT"),
    "kasubbag_tu": ("SIMANTIK_EMAIL_KASUBBAG_TU", "SIMANTIK_PASSWORD_KASUBBAG_TU"),
    "admin_gudang": ("SIMANTIK_EMAIL_ADMIN_GUDANG", "SIMANTIK_PASSWORD_ADMIN_GUDANG"),
    "penerima": ("SIMANTIK_EMAIL_PENERIMA", "SIMANTIK_PASSWORD_PENERIMA"),
}


@dataclass
class Persona:
    key: str
    label: str
    email: str
    password: str
    allowed_get: list[str]
    forbidden_get: list[str]


def _substitute(value: str, defaults: dict[str, str]) -> str:
    out = value
    for k, v in defaults.items():
        out = out.replace("${" + k + "}", v)
    return out


def _env_or_none(name: str) -> str | None:
    val = os.getenv(name)
    if val is None:
        return None
    val = val.strip()
    return val or None


def _build_defaults(raw_defaults: dict[str, Any]) -> dict[str, str]:
    defaults = {k: str(v) for k, v in raw_defaults.items()}
    admin_pw = _env_or_none("SIMANTIK_PASSWORD_ADMIN")
    pegawai_pw = _env_or_none("SIMANTIK_PASSWORD_PEGAWAI")
    if admin_pw:
        defaults["admin_password"] = admin_pw
    if pegawai_pw:
        defaults["pegawai_password"] = pegawai_pw
    return defaults


def _credentials_from_env(key: str, mapping: dict[str, tuple[str, str]]) -> tuple[str | None, str | None]:
    pair = mapping.get(key)
    if not pair:
        return None, None
    return _env_or_none(pair[0]), _env_or_none(pair[1])


def _resolve_email(entry: dict[str, Any], bootstrap: dict[str, Any], defaults: dict[str, str]) -> str | None:
    env_email, _ = _credentials_from_env(str(entry.get("key", "")), PERSONA_ENV_KEYS)
    if env_email:
        return env_email

    if entry.get("email"):
        return _substitute(str(entry["email"]), defaults)

    jabatan_name = entry.get("jabatan_name")
    if jabatan_name:
        email = (bootstrap.get("emails_by_jabatan") or {}).get(jabatan_name)
        if email:
            return email

    pattern = entry.get("email_pattern")
    if pattern and jabatan_name:
        jabatan_id = (bootstrap.get("jabatan_ids") or {}).get(jabatan_name)
        if jabatan_id:
            return _substitute(str(pattern).replace("{jabatan_id}", str(jabatan_id)), defaults)

    return None


def _resolve_password(entry: dict[str, Any], defaults: dict[str, str]) -> str:
    _, env_password = _credentials_from_env(str(entry.get("key", "")), PERSONA_ENV_KEYS)
    if env_password:
        return env_password
    return _substitute(str(entry.get("password", defaults.get("pegawai_password", ""))), defaults)


def load_personas(config_path: Path, bootstrap: dict[str, Any]) -> list[Persona]:
    raw = yaml.safe_load(config_path.read_text(encoding="utf-8"))
    defaults = _build_defaults(raw.get("defaults") or {})
    personas: list[Persona] = []

    for entry in raw.get("personas") or []:
        email = _resolve_email(entry, bootstrap, defaults)
        if not email:
            continue
        password = _resolve_password(entry, defaults)
        if not password:
            continue
        personas.append(
            Persona(
                key=str(entry["key"]),
                label=str(entry.get("label", entry["key"])),
                email=email,
                password=password,
                allowed_get=list(entry.get("allowed_get") or []),
                forbidden_get=list(entry.get("forbidden_get") or []),
            )
        )
    return personas


def load_flow_config(config_path: Path, bootstrap: dict[str, Any]) -> dict[str, dict[str, str]]:
    raw = yaml.safe_load(config_path.read_text(encoding="utf-8"))
    defaults = _build_defaults(raw.get("defaults") or {})
    flow_raw = raw.get("transaction_flow") or {}
    flow: dict[str, dict[str, str]] = {}

    for role_key, spec in flow_raw.items():
        if not isinstance(spec, dict):
            continue

        env_email, env_password = _credentials_from_env(role_key, FLOW_ENV_KEYS)
        email = env_email
        if not email:
            email = spec.get("email")
        if not email and spec.get("jabatan_name"):
            email = (bootstrap.get("emails_by_jabatan") or {}).get(spec["jabatan_name"])
        if not email:
            continue

        password = env_password
        if not password:
            password = _substitute(str(spec.get("password", defaults.get("pegawai_password", ""))), defaults)
        if not password:
            continue

        if role_key == "penerima" and not env_email:
            pemohon_email, _ = _credentials_from_env("pemohon", FLOW_ENV_KEYS)
            if pemohon_email:
                email = pemohon_email

        flow[role_key] = {"email": email, "password": password, "label": spec.get("jabatan_name") or role_key}
    return flow
