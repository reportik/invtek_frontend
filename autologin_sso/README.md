# Módulo Odoo: Auto Login SSO

Permite que un usuario que ya inició sesión en tu sitio (Laravel) entre a Odoo sin volver a escribir usuario/contraseña, mediante un enlace con token JWT.

## Instalación en odoo.sh

1. Copia la carpeta `autologin_sso` al repositorio de tu proyecto Odoo (junto al resto de addons).
2. Asegúrate de que el entorno Odoo tenga la dependencia Python `PyJWT`. En odoo.sh suele bastar con añadir en el `requirements.txt` del proyecto (si existe): `PyJWT>=2.0.0`.
3. Sube el commit a la rama que usa odoo.sh.
4. En Odoo: **Apps** → buscar "Auto Login SSO" → **Instalar**.

## Configuración en Odoo

En **Ajustes > Técnico > Parámetros > Parámetros del sistema** crea:

| Clave | Descripción |
|-------|-------------|
| `autologin_sso.secret` | **Obligatorio.** Misma clave que `AUTOLOGIN_SECRET` en tu FastAPI. Ejemplo: una cadena larga y aleatoria. |
| `autologin_sso.algorithm` | Opcional. Por defecto `HS256`. |

**No necesitas cambiar las contraseñas de los usuarios.** El módulo establece la sesión directamente sin requerir contraseña.

## Uso

- La URL que genera tu backend es: `https://tu-odoo.com/autologin?token=JWT`
- Opcional: `...&redirect=/my/orders` para ir a una ruta concreta (por defecto `/my/orders`).
- El token lo genera FastAPI (válido 2 minutos); Odoo solo lo valida y crea la sesión.

## Seguridad

- El JWT solo contiene el login del usuario (email), no la contraseña.
- El token expira en 2 minutos.
- Usa HTTPS en producción.
- Mantén el mismo `AUTOLOGIN_SECRET` en FastAPI y en Odoo (`autologin_sso.secret`).
