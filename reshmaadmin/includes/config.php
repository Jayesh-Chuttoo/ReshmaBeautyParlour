<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-error.log');

$host     = 'localhost';
$db       = 'reshma_beauty_db';
$user     = 'root';
$pass = ''; // Update with your actual database password
$charset  = 'utf8mb4';
/*
$host     = '127.0.0.1';
$db       = 'infinity_reshma_beauty_db';
$user     = 'infinity_user';
$pass = 'XBYkznp)Tr#wpR56'; // Update with your actual database password
$charset  = 'utf8mb4';
*/

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
