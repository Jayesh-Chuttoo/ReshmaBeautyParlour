<?php

/**
 * Database Queries & Helper Functions
 * Reshma Beauty Parlour
 */

require_once __DIR__ . '/db.php'; // Requires your PDO connection from db.php

/**
 * ==========================================
 * PRODUCTS QUERIES
 * ==========================================
 */

function getAllProducts($pdo)
{
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    return $stmt->fetchAll();
}

function getProductById($pdo, $id)
{
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

/**
 * ==========================================
 * SERVICES QUERIES
 * ==========================================
 */

function getAllServices($pdo)
{
    $stmt = $pdo->query("SELECT * FROM services ORDER BY id ASC");
    return $stmt->fetchAll();
}

function getServiceById($pdo, $id)
{
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

/**
 * ==========================================
 * BOOKINGS QUERIES
 * ==========================================
 */

function insertBooking($pdo, $serviceName, $clientName, $contact, $peopleCount, $onsiteVisit, $bookingDate)
{
    $stmt = $pdo->prepare("INSERT INTO bookings (service_name, client_name, contact, people_count, onsite_visit, booking_date) VALUES (:service_name, :client_name, :contact, :people_count, :onsite_visit, :booking_date)");

    return $stmt->execute([
        'service_name'  => $serviceName,
        'client_name'   => $clientName,
        'contact'       => $contact,
        'people_count'  => $peopleCount,
        'onsite_visit'  => $onsiteVisit ? 1 : 0,
        'booking_date'  => $bookingDate
    ]);
}

/**
 * ==========================================
 * CART QUERIES (User Database-Driven Cart)
 * ==========================================
 */

function getCartItems($pdo, $userId)
{
    $stmt = $pdo->prepare("SELECT c.id as cart_id, c.quantity, p.* FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

function addToCartDb($pdo, $userId, $productId, $quantity = 1)
{
    // Check if item already exists in cart for this user
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id");
    $stmt->execute(['user_id' => $userId, 'product_id' => $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $newQty = $existing['quantity'] + $quantity;
        $update = $pdo->prepare("UPDATE cart SET quantity = :quantity WHERE id = :id");
        return $update->execute(['quantity' => $newQty, 'id' => $existing['id']]);
    } else {
        $insert = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
        return $insert->execute(['user_id' => $userId, 'product_id' => $productId, 'quantity' => $quantity]);
    }
}

function updateCartItemQuantityDb($pdo, $cartId, $userId, $quantity)
{
    if ($quantity <= 0) {
        return removeFromCartDb($pdo, $cartId, $userId);
    }
    $stmt = $pdo->prepare("UPDATE cart SET quantity = :quantity WHERE id = :id AND user_id = :user_id");
    return $stmt->execute(['quantity' => $quantity, 'id' => $cartId, 'user_id' => $userId]);
}

function removeFromCartDb($pdo, $cartId, $userId)
{
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = :id AND user_id = :user_id");
    return $stmt->execute(['id' => $cartId, 'user_id' => $userId]);
}

function clearCartDb($pdo, $userId)
{
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
    return $stmt->execute(['user_id' => $userId]);
}

/**
 * ==========================================
 * USERS / AUTHENTICATION QUERIES
 * ==========================================
 */

function registerUser($pdo, $username, $password)
{
    // Hash the password securely using Bcrypt
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
    return $stmt->execute([
        'username' => $username,
        'password' => $hashedPassword
    ]);
}

function getUserByUsername($pdo, $username)
{
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    return $stmt->fetch();
}