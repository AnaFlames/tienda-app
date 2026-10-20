<?php
// Tienda DAW · aplicación de ejemplo para Despliegue de Aplicaciones Web (2º DAW)
// PHP + MySQL. Nginx recibe las peticiones y se las pasa a PHP-FPM por FastCGI.
// Este archivo es el «controlador frontal»: todas las rutas llegan aquí (try_files … /index.php).
declare(strict_types=1);

// La configuración (con la contraseña) está FUERA de public/: Nginx nunca puede servirla.
$config = require __DIR__ . '/../config.php';

function bd(array $c): PDO
{
    $dsn = isset($c['db_socket'])
        ? "mysql:unix_socket={$c['db_socket']};dbname={$c['db_name']};charset=utf8mb4"
        : "mysql:host={$c['db_host']};dbname={$c['db_name']};charset=utf8mb4";
    return new PDO($dsn, $c['db_user'], $c['db_password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 3,
    ]);
}

function h(string $t): string
{
    return htmlspecialchars($t, ENT_QUOTES, 'UTF-8');
}

function json(int $codigo, $datos): void
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE), "\n";
}

// El «router»: según la ruta, una cosa u otra.
$ruta = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

try {
    switch ($ruta) {
        case '/salud':
            // ¿La aplicación llega a la base de datos?
            try {
                bd($config)->query('SELECT 1');
                json(200, ['estado' => 'ok', 'bd' => 'ok', 'pid' => getmypid()]);
            } catch (Throwable $e) {
                json(503, ['estado' => 'error', 'bd' => 'sin conexión', 'detalle' => $e->getCode()]);
            }
            break;

        case '/api/productos':
            json(200, bd($config)->query('SELECT id, nombre, precio FROM productos ORDER BY id')->fetchAll());
            break;

        case '/':
            $pdo = bd($config);
            $pdo->exec('UPDATE visitas SET total = total + 1 WHERE id = 1');
            $total = (int) $pdo->query('SELECT total FROM visitas WHERE id = 1')->fetchColumn();
            $productos = $pdo->query('SELECT nombre, precio FROM productos ORDER BY id')->fetchAll();
            $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            header('Content-Type: text/html; charset=utf-8');
            echo "<!doctype html>\n<html lang=\"es\"><head><meta charset=\"utf-8\"><title>Tienda DAW</title></head>\n<body>\n";
            echo "<h1>Tienda DAW</h1>\n<ul>\n";
            foreach ($productos as $p) {
                echo '<li>', h($p['nombre']), ' · ', number_format((float) $p['precio'], 2, '.', ''), " €</li>\n";
            }
            echo "</ul>\n";
            echo "<p>Visitas: {$total}</p>\n";
            echo '<p>Visitante: ', h($_SERVER['REMOTE_ADDR'] ?? '?'), ' · Protocolo: ', $https ? 'https' : 'http', "</p>\n";
            echo '<p>Atendido por PHP ', PHP_VERSION, ' · proceso ', getmypid(), "</p>\n";
            echo "</body></html>\n";
            break;

        default:
            http_response_code(404);
            echo "No existe esa página\n";
    }
} catch (Throwable $e) {
    // El motivo va al log (Nginx lo apunta en /var/log/nginx/error.log); al visitante, solo un 500.
    error_log("[tienda-app] {$_SERVER['REQUEST_METHOD']} {$ruta}: {$e->getMessage()}");
    http_response_code(500);
    echo "Error interno de la aplicación\n";
}
