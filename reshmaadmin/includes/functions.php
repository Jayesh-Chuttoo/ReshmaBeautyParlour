<?php

/**
 * Database Queries & Helper Functions
 * Reshma Beauty Parlour
 */

// Ensure db.php is required before calling these functions

/**
 * ==========================================
 * PRODUCTS QUERIES
 * ==========================================
 */
 
function addProductWithImage($pdo, $name, $description, $price, $file)
{
    $targetDir = __DIR__ . '/../public/uploads/';

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $imagePath = null;
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid image format. Only JPG, PNG, and WebP are allowed.'];
        }

        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid('prod_', true) . '.' . $fileExtension;
        $targetFilePath = $targetDir . $uniqueName;

        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            $imagePath = 'uploads/' . $uniqueName;
        } else {
            return ['success' => false, 'message' => 'Failed to move uploaded file.'];
        }
    }

    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image) VALUES (:name, :description, :price, :image)");
    $success = $stmt->execute([
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'image' => $imagePath
    ]);

    return ['success' => $success, 'message' => $success ? 'Product added successfully!' : 'Database error.'];
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

function getAllBookings($pdo)
{
    $stmt = $pdo->query("SELECT * FROM bookings ORDER BY booking_date DESC");
    return $stmt->fetchAll();
}

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
 * CART QUERIES
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

/**
 * ==========================================
 * USERS / AUTHENTICATION QUERIES
 * ==========================================
 */

function getAllUsers($pdo)
{
    $stmt = $pdo->query("SELECT id, username FROM users ORDER BY id DESC");
    return $stmt->fetchAll();
}

function registerUser($pdo, $username, $password)
{
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

 
/**
 * Database Queries & Helper Functions
 * Reshma Beauty Parlour
 */

function getAllProducts($pdo)
{
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll();
    
    foreach ($products as &$product) {
        $product['sizes'] = !empty($product['sizes']) ? json_decode($product['sizes'], true) : [];
    }
    return $products;
}

function getProductById($pdo, $id)
{
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch();

    if ($product) {
        $product['sizes'] = !empty($product['sizes']) ? json_decode($product['sizes'], true) : [];
    }
    return $product;
}

function addProduct($pdo, $name, $description, $price, $stock, $sizes, $file)
{
    $targetDir = __DIR__ . '/../public/uploads/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $imagePath = null;
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid image format. Only JPG, PNG, and WebP are allowed.'];
        }

        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid('prod_', true) . '.' . $fileExtension;
        $targetFilePath = $targetDir . $uniqueName;

        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            $imagePath = 'uploads/' . $uniqueName;
        } else {
            return ['success' => false, 'message' => 'Failed to move uploaded file.'];
        }
    }

    $sizesJson = !empty($sizes) ? json_encode(array_values(array_filter($sizes))) : null;

    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, sizes, image) VALUES (:name, :description, :price, :stock, :sizes, :image)");
    $success = $stmt->execute([
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'stock' => $stock,
        'sizes' => $sizesJson,
        'image' => $imagePath
    ]);

    return ['success' => $success, 'message' => $success ? 'Product added successfully!' : 'Database error.'];
}

function updateProduct($pdo, $id, $name, $description, $price, $stock, $sizes, $file)
{
    $targetDir = __DIR__ . '/../public/uploads/';
    $existing = getProductById($pdo, $id);
    if (!$existing) {
        return ['success' => false, 'message' => 'Product not found.'];
    }

    $imagePath = $existing['image'];

    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid image format. Only JPG, PNG, and WebP are allowed.'];
        }

        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid('prod_', true) . '.' . $fileExtension;
        $targetFilePath = $targetDir . $uniqueName;

        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            // Remove old image if hosted locally
            if (!empty($existing['image']) && file_exists(__DIR__ . '/../public/' . $existing['image'])) {
                @unlink(__DIR__ . '/../public/' . $existing['image']);
            }
            $imagePath = 'uploads/' . $uniqueName;
        } else {
            return ['success' => false, 'message' => 'Failed to move uploaded file.'];
        }
    }

    $sizesJson = !empty($sizes) ? json_encode(array_values(array_filter($sizes))) : null;

    $stmt = $pdo->prepare("UPDATE products SET name = :name, description = :description, price = :price, stock = :stock, sizes = :sizes, image = :image WHERE id = :id");
    $success = $stmt->execute([
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'stock' => $stock,
        'sizes' => $sizesJson,
        'image' => $imagePath,
        'id' => $id
    ]);

    return ['success' => $success, 'message' => $success ? 'Product updated successfully!' : 'Database error.'];
}

// Keep your existing services, bookings, cart, and user functions here...
function addProductWithSizes($pdo, $name, $description, $price, $totalStock, $sizesJson, $status, $file)
{
    $imagePath = null;
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'prod_' . uniqid() . '.' . $ext;
        $uploadDir = __DIR__ . '/../public/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $imagePath = 'uploads/' . $filename;
        }
    }
    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, sizes, status, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $success = $stmt->execute([$name, $description, $price, $totalStock, $sizesJson, $status, $imagePath]);
    return ['success' => $success, 'message' => $success ? 'Product added successfully!' : 'Failed to add product.'];
}

function updateProductWithSizes($pdo, $id, $name, $description, $price, $totalStock, $sizesJson, $status, $file)
{
    $imagePath = null;
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'prod_' . uniqid() . '.' . $ext;
        $uploadDir = __DIR__ . '/../public/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $imagePath = 'uploads/' . $filename;
        }
    }

    if ($imagePath) {
        $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, sizes=?, status=?, image=? WHERE id=?");
        $success = $stmt->execute([$name, $description, $price, $totalStock, $sizesJson, $status, $imagePath, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, sizes=?, status=? WHERE id=?");
        $success = $stmt->execute([$name, $description, $price, $totalStock, $sizesJson, $status, $id]);
    }
    return ['success' => $success, 'message' => $success ? 'Product updated successfully!' : 'Failed to update product.'];
}