<?php

/**
 * Database Connection Setup via PDO
 * Reshma Beauty Parlour
 */

$host     = 'localhost';
$db       = 'reshma_beauty_db';
$user     = 'root';
$password = ''; // Update with your actual database password
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (\PDOException $e) {
    // In a production environment, log this error securely rather than displaying it
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
