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

function insertBooking($pdo, $serviceName, $clientName, $contact, $isWhatsapp, $peopleCount, $onsiteVisit, $bookingDate)
{
    $stmt = $pdo->prepare("INSERT INTO bookings (service_name, client_name, contact, is_whatsapp, people_count, onsite_visit, booking_date) VALUES (:service_name, :client_name, :contact, :is_whatsapp, :people_count, :onsite_visit, :booking_date)");

    return $stmt->execute([
        'service_name'  => $serviceName,
        'client_name'   => $clientName,
        'contact'       => $contact,
        'is_whatsapp'   => $isWhatsapp ? 1 : 0,
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
    // Clean up expired cart items (Lazy Expiry cleanup)
    $pdo->query("DELETE FROM cart WHERE expires_at <= NOW()");

    $stmt = $pdo->prepare("SELECT c.id as cart_id, c.product_id, c.quantity, c.size, p.* FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

function addToCartDb($pdo, $userId, $productId, $quantity = 1, $size = 'M')
{
    // Fallback safeguard if size is empty or invalid
    $size = !empty($size) ? strtoupper(trim($size)) : 'M';

    $stmt = $pdo->prepare("
        INSERT INTO cart (user_id, product_id, quantity, size, expires_at) 
        VALUES (:user_id, :product_id, :quantity, :size, DATE_ADD(NOW(), INTERVAL 24 HOUR))
        ON DUPLICATE KEY UPDATE 
            quantity = quantity + VALUES(quantity),
            expires_at = DATE_ADD(NOW(), INTERVAL 24 HOUR)
    ");

    return $stmt->execute([
        'user_id'    => $userId,
        'product_id' => $productId,
        'quantity'   => $quantity,
        'size'       => $size
    ]);
}

function updateCartItemQuantityDb($pdo, $cartId, $userId, $quantity)
{
    if ($quantity <= 0) {
        return removeFromCartDb($pdo, $cartId, $userId);
    }
    // Update quantity and refresh the 24-hour timer
    $stmt = $pdo->prepare("UPDATE cart SET quantity = :quantity, expires_at = DATE_ADD(NOW(), INTERVAL 24 HOUR) WHERE id = :id AND user_id = :user_id");
    return $stmt->execute(['quantity' => $quantity, 'id' => $cartId, 'user_id' => $userId]);
}

function removeFromCartDb($pdo, $cartId, $userId)
{
    // Deleting from cart automatically releases the stock reservation!
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = :id AND user_id = :user_id");
    return $stmt->execute(['id' => $cartId, 'user_id' => $userId]);
}

function clearCartDb($pdo, $userId)
{
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
    return $stmt->execute(['user_id' => $userId]);
}

// Helper to calculate true available stock based on active cart holds
function getEffectiveSizeStock($pdo, $product)
{
    // Clean up expired carts first (Lazy Expiry cleanup)
    $pdo->query("DELETE FROM cart WHERE expires_at <= NOW()");

    $baseSizes = $product['sizes'] ?? ['S' => 0, 'M' => 0, 'L' => 0];
    if (is_string($baseSizes)) {
        $baseSizes = json_decode($baseSizes, true) ?: ['S' => 0, 'M' => 0, 'L' => 0];
    }

    $productId = $product['id'];

    // Sum active quantities locked inside everyone's active carts for this product
    $stmt = $pdo->prepare("SELECT size, SUM(quantity) as held_qty FROM cart WHERE product_id = :product_id GROUP BY size");
    $stmt->execute(['product_id' => $productId]);
    $holds = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Returns e.g. ['M' => 5]

    $effectiveSizes = [];
    foreach ($baseSizes as $size => $totalStock) {
        $held = (int)($holds[$size] ?? 0);
        $effectiveSizes[$size] = max(0, (int)$totalStock - $held);
    }

    return $effectiveSizes;
}

/**
 * ==========================================
 * USERS / AUTHENTICATION QUERIES
 * ==========================================
 */
function updateUserPhone($pdo, $userId, $phone)
{
    $stmt = $pdo->prepare("UPDATE users SET phone = :phone WHERE id = :id");
    return $stmt->execute(['phone' => $phone, 'id' => $userId]);
}

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
function getFulfillmentMethods($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM fulfillment_methods WHERE is_active = 1 ORDER BY fee ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}
