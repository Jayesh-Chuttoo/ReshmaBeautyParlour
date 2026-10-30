<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$action = $_POST['action'] ?? '';
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = floatval($_POST['price'] ?? 0);
$stock = intval($_POST['stock'] ?? 0);
$sizes = $_POST['sizes'] ?? [];
$file = $_FILES['product_image'] ?? null;

if (empty($name) || $price <= 0) {
    $_SESSION['flash_message'] = 'Error: Product name and valid price are required.';
    $_SESSION['flash_type'] = 'error';
    header('Location: index.php');
    exit;
}

if ($action === 'add_product') {
    $result = addProduct($pdo, $name, $description, $price, $stock, $sizes, $file);
} elseif ($action === 'edit_product') {
    $productId = $_POST['product_id'] ?? null;
    $result = updateProduct($pdo, $productId, $name, $description, $price, $stock, $sizes, $file);
} else {
    $result = ['success' => false, 'message' => 'Invalid action.'];
}

$_SESSION['flash_message'] = $result['message'];
$_SESSION['flash_type'] = $result['success'] ? 'success' : 'error';
header('Location: index.php');
exit;
