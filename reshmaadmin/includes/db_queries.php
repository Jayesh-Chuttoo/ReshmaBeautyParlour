<?php
require_once __DIR__ . '/config.php';

function getAllProducts($pdo)
{
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    return $stmt->fetchAll();
}

function getAllServices($pdo)
{
    $stmt = $pdo->query("SELECT * FROM services ORDER BY id DESC");
    return $stmt->fetchAll();
}

function getAllBookings($pdo)
{
    $stmt = $pdo->query("SELECT * FROM bookings ORDER BY booking_date DESC");
    return $stmt->fetchAll();
}

function getAllUsers($pdo)
{
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
    return $stmt->fetchAll();
}

function getAdminUsers($pdo)
{
    $stmt = $pdo->query("SELECT id, username, role, created_at FROM users WHERE role IN ('admin', 'manager') ORDER BY id DESC");
    return $stmt->fetchAll();
}
