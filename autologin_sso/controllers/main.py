# -*- coding: utf-8 -*-
import jwt
from odoo import http
from odoo.http import request


class AutoLoginController(http.Controller):
    """
    Recibe un token JWT generado por tu backend (FastAPI), lo valida con la misma
    clave secreta, identifica al usuario por login y crea la sesión en Odoo
    SIN necesitar la contraseña del usuario.
    """

    @http.route("/autologin", type="http", auth="public", website=False)
    def autologin(self, token=None, redirect=None, **kw):
        if not token:
            return request.redirect("/web/login")

        # Clave compartida con FastAPI (configurar en Odoo: Ajustes > Técnico > Parámetros > autologin_sso.secret)
        secret = (
            request.env["ir.config_parameter"]
            .sudo()
            .get_param("autologin_sso.secret", "")
        )
        algorithm = (
            request.env["ir.config_parameter"]
            .sudo()
            .get_param("autologin_sso.algorithm", "HS256")
        )

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

            # Establecer la sesión directamente SIN necesitar contraseña
            # Esto es lo que hacen los módulos SSO de Odoo
            request.session.uid = user.id
            request.session.login = user.login
            request.session.session_token = user._compute_session_token(request.session.sid)
            request.env['res.users'].browse(user.id)._update_last_login()

            target = (redirect or "/my/orders").strip()
            if not target.startswith("/"):
                target = "/my/orders"
            return request.redirect(target)

        except jwt.ExpiredSignatureError:
            return request.redirect("/web/login")
        except jwt.InvalidTokenError:
            return request.redirect("/web/login")
