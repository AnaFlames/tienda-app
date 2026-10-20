# Tienda DAW (PHP + MySQL)

Aplicación de ejemplo para practicar el despliegue de una aplicación PHP detrás de Nginx con PHP-FPM
en el módulo **Despliegue de Aplicaciones Web** (2º DAW).

## Qué hace

| Ruta | Respuesta |
|------|-----------|
| `/` | Página con los productos y un contador de visitas (lee y escribe en la base de datos) |
| `/api/productos` | Los productos en JSON |
| `/salud` | `{"estado":"ok"}` si llega a la base de datos; **503** si no |

## Estructura

```
public/index.php       ← lo único que ve Nginx (raíz web)
config.ejemplo.php     ← plantilla de configuración (sí va en el repositorio)
config.php             ← la configuración real con la contraseña (NO va en el repositorio)
bd/esquema.sql         ← tablas y datos de ejemplo
despliegue/app.nginx   ← sitio de Nginx con fastcgi_pass a PHP-FPM
```

## Despliegue (resumen)

```bash
sudo apt install -y nginx php-fpm php-mysql mysql-server git
sudo git clone <URL-de-este-repositorio> /var/www/tienda-app   # o clonar como tu usuario
cp config.ejemplo.php config.php   # y pon la contraseña de la base de datos
sudo mysql tienda < bd/esquema.sql
# sitio de Nginx: despliegue/app.nginx
```

No necesita librerías externas: usa PDO, que viene con la extensión php-mysql.
