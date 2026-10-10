<?php
declare(strict_types=1);

$host = 'mysql-bomboclatsito.alwaysdata.net';
$db   = 'bomboclatsito_articulosgit';
$user = 'bomboclatsito';
$pass = 'ju1337an';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->exec("CREATE TABLE IF NOT EXISTS articulos (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(150) NOT NULL,
        marca VARCHAR(100) NOT NULL,
        unidad VARCHAR(60) NOT NULL,
        ubicacion VARCHAR(150) NOT NULL,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (PDOException $e) {
    http_response_code(500);
    exit('No fue posible conectar con la base de datos. Verifica las credenciales y que el usuario tenga permisos para crear tablas.');
}
