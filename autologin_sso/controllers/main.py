# -*- coding: utf-8 -*-
import jwt
from datetime import datetime, timedelta
from odoo import http
from odoo.http import request


class AutoLoginController(http.Controller):

    def _get_secret(self):
        return (
            request.env["ir.config_parameter"]
            .sudo()
            .get_param("autologin_sso.secret", "")
        )

    def _get_algorithm(self):
        return (
            request.env["ir.config_parameter"]
            .sudo()
            .get_param("autologin_sso.algorithm", "HS256")
        )

    # ──────────────────────────────────────────────
    # Laravel → Odoo  (recibe token, crea sesión)
    # ──────────────────────────────────────────────
    @http.route("/autologin", type="http", auth="public", website=False)
    def autologin(self, token=None, redirect=None, **kw):
        if not token:
            return request.redirect("/web/login")

        secret = self._get_secret()
        algorithm = self._get_algorithm()
        if not secret:
            return request.redirect("/web/login")

        try:
            payload = jwt.decode(token, secret, algorithms=[algorithm])
            login = payload.get("login")
            if not login:
                return request.redirect("/web/login")

            user = (
                request.env["res.users"]
                .sudo()
                .search([("login", "=", login)], limit=1)
            )
            if not user:
                return request.redirect("/web/login")

            request.session.uid = user.id
            request.session.login = user.login
            request.session.session_token = user._compute_session_token(request.session.sid)
            request.env["res.users"].browse(user.id)._update_last_login()

            target = (redirect or "/my/home").strip()
            if not target.startswith("/"):
                target = "/my/home"
            return request.redirect(target)

        except jwt.ExpiredSignatureError:
            return request.redirect("/web/login")
        except jwt.InvalidTokenError:
            return request.redirect("/web/login")

    # ──────────────────────────────────────────────
    # Odoo → Laravel  (genera token, redirige)
    # ──────────────────────────────────────────────
    @http.route("/goto-laravel", type="http", auth="user", website=False)
    def goto_laravel(self, redirect=None, **kw):
        """
        El usuario ya está logueado en Odoo (auth='user').
        Genera un JWT con su login y redirige a Laravel.
        Configurar en Odoo: autologin_sso.laravel_url = http://itekniaapp.serveftp.com:8080
        """
        secret = self._get_secret()
        algorithm = self._get_algorithm()

        laravel_url = (
            request.env["ir.config_parameter"]
            .sudo()
            .get_param("autologin_sso.laravel_url", "")
        ).rstrip("/")

        if not secret or not laravel_url:
            return request.redirect("/web")

        user = request.env.user
        payload = {
            "login": user.login,
            "exp": datetime.utcnow() + timedelta(minutes=2),
        }
        token = jwt.encode(payload, secret, algorithm=algorithm)

        target = (redirect or "/").strip()
        url = f"{laravel_url}/autologin-from-odoo?token={token}&redirect={target}"
        return request.redirect(url)
