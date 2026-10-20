<?php
// Copia este archivo como config.php (en esta misma carpeta, FUERA de public/) y pon la contraseña.
// config.php NUNCA se sube al repositorio: está en .gitignore.
return [
    'db_socket'   => '/run/mysqld/mysqld.sock',   // MySQL en la misma máquina, sin pasar por la red
    'db_name'     => 'tienda',
    'db_user'     => 'tienda',
    'db_password' => 'cambia_esta_clave',
];
