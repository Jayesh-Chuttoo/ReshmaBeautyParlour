<?php

/**
 * Database Queries & Repository Modules
 * Reshma Beauty Parlour
 */

require_once __DIR__ . '/db.php'; // Requires your PDO connection from db.php

/**
 * ==========================================
 * MODULE 1: PRODUCTS
 * ==========================================
 */
class ProductRepository
{
    public function __construct(private PDO $pdo) {}

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM products ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getAvailableForShop(CartRepository $cartRepo): array
    {
        $allProducts = $this->getAll();
        $filteredProducts = [];

        foreach ($allProducts as $product) {
            if (isset($product['sizes']) && is_string($product['sizes'])) {
                $product['sizes'] = json_decode($product['sizes'], true) ?: [];
            }

            $effectiveSizes = $cartRepo->getEffectiveSizeStock($product);
            $product['effective_sizes'] = $effectiveSizes;

            $availableEffectiveSizes = array_filter($effectiveSizes, function ($qty) {
                return (int)$qty > 0;
            });

            if (count($availableEffectiveSizes) > 0 && (int)($product['stock'] ?? 0) > 0) {
                $filteredProducts[] = $product;
            }
        }

        return $filteredProducts;
    }
}

/**
 * ==========================================
 * MODULE 2: SERVICES
 * ==========================================
 */
class ServiceRepository
{
    public function __construct(private PDO $pdo) {}

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM services ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM services WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}

/**
 * ==========================================
 * MODULE 3: BOOKINGS
 * ==========================================
 */
class BookingRepository
{
    public function __construct(private PDO $pdo) {}

    public function insert(
        string $serviceName,
        string $clientName,
        string $contact,
        bool|int $isWhatsapp,
        int $peopleCount,
        bool|int $onsiteVisit,
        string $bookingDate
    ): bool {
        $stmt = $this->pdo->prepare(
            "INSERT INTO bookings (service_name, client_name, contact, is_whatsapp, people_count, onsite_visit, booking_date)
             VALUES (:service_name, :client_name, :contact, :is_whatsapp, :people_count, :onsite_visit, :booking_date)"
        );

        return $stmt->execute([
            'service_name' => $serviceName,
            'client_name'  => $clientName,
            'contact'      => $contact,
            'is_whatsapp'  => $isWhatsapp ? 1 : 0,
            'people_count' => $peopleCount,
            'onsite_visit' => $onsiteVisit ? 1 : 0,
            'booking_date' => $bookingDate,
        ]);
    }
}

/**
 * ==========================================
 * MODULE 4: CART & STOCK RESERVATIONS
 * ==========================================
 */
class CartRepository
{
    public function __construct(private PDO $pdo, private ProductRepository $products) {}

    public function purgeExpired(): void
    {
        $this->pdo->query("DELETE FROM cart WHERE expires_at <= NOW()");
    }

    public function getItems(int $userId): array
    {
        $this->purgeExpired();

        $stmt = $this->pdo->prepare(
            "SELECT c.id as cart_id, c.product_id, c.quantity, c.size, p.*
             FROM cart c
             JOIN products p ON c.product_id = p.id
             WHERE c.user_id = :user_id"
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getItemByIdForUser(int $cartId, int $userId): array|false
    {
        $stmt = $this->pdo->prepare("SELECT id, product_id, size, quantity FROM cart WHERE id = :id AND user_id = :user_id LIMIT 1");
        $stmt->execute(['id' => $cartId, 'user_id' => $userId]);
        return $stmt->fetch();
    }

    public function getQtyForProductSize(int $userId, int $productId, string $size): int
    {
        $size = strtoupper(trim($size));
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(quantity), 0)
             FROM cart
             WHERE user_id = :user_id AND product_id = :product_id AND UPPER(TRIM(size)) = :size"
        );
        $stmt->execute([
            'user_id'    => $userId,
            'product_id' => $productId,
            'size'       => $size,
        ]);
        return max(0, (int)$stmt->fetchColumn());
    }

    public function addItem(int $userId, int $productId, int $quantity = 1, string $size = 'M'): bool
    {
        $size     = !empty($size) ? strtoupper(trim($size)) : 'M';
        $quantity = max(1, $quantity);

        $this->purgeExpired();

        $product = $this->products->getById($productId);
        if (!$product) {
            return false;
        }

        $effectiveSizes   = $this->getEffectiveSizeStock($product);
        $remainingForSize = max(0, (int)($effectiveSizes[$size] ?? 0));

        $totalStmt = $this->pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE product_id = :product_id");
        $totalStmt->execute(['product_id' => $productId]);
        $totalHeldProduct = (int)$totalStmt->fetchColumn();
        $remainingTotal   = max(0, (int)($product['stock'] ?? 0) - $totalHeldProduct);

        $allowedToAdd = min($quantity, $remainingForSize, $remainingTotal);
        if ($allowedToAdd <= 0) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO cart (user_id, product_id, quantity, size, expires_at)
             VALUES (:user_id, :product_id, :quantity, :size, DATE_ADD(NOW(), INTERVAL 24 HOUR))
             ON DUPLICATE KEY UPDATE
                 quantity = quantity + VALUES(quantity),
                 expires_at = DATE_ADD(NOW(), INTERVAL 24 HOUR)"
        );

        return $stmt->execute([
            'user_id'    => $userId,
            'product_id' => $productId,
            'quantity'   => $allowedToAdd,
            'size'       => $size,
        ]);
    }

    public function updateQuantity(int $cartId, int $userId, int $quantity): bool
    {
        if ($quantity <= 0) {
            return $this->removeItem($cartId, $userId);
        }

        $this->purgeExpired();

        $cartRow = $this->getItemByIdForUser($cartId, $userId);
        if (!$cartRow) {
            return false;
        }

        $product = $this->products->getById((int)$cartRow['product_id']);
        if (!$product) {
            return false;
        }

        $size           = strtoupper(trim((string)($cartRow['size'] ?? 'M')));
        $currentUserQty = (int)$cartRow['quantity'];
        $effectiveSizes = $this->getEffectiveSizeStock($product);

        $maxForSize = $currentUserQty + max(0, (int)($effectiveSizes[$size] ?? 0));

        $otherStmt = $this->pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE product_id = :product_id AND id != :id");
        $otherStmt->execute([
            'product_id' => $cartRow['product_id'],
            'id'         => $cartId,
        ]);
        $otherHeld = (int)$otherStmt->fetchColumn();
        $maxTotal  = max(0, (int)($product['stock'] ?? 0) - $otherHeld);

        $cappedQty = min($quantity, $maxForSize, $maxTotal);
        if ($cappedQty <= 0) {
            return $this->removeItem($cartId, $userId);
        }

        $stmt = $this->pdo->prepare(
            "UPDATE cart
             SET quantity = :quantity, expires_at = DATE_ADD(NOW(), INTERVAL 24 HOUR)
             WHERE id = :id AND user_id = :user_id"
        );
        return $stmt->execute([
            'quantity' => $cappedQty,
            'id'       => $cartId,
            'user_id'  => $userId,
        ]);
    }

    public function removeItem(int $cartId, int $userId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM cart WHERE id = :id AND user_id = :user_id");
        return $stmt->execute(['id' => $cartId, 'user_id' => $userId]);
    }

    public function clearForUser(int $userId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
        return $stmt->execute(['user_id' => $userId]);
    }

    public function getEffectiveSizeStock(array $product): array
    {
        $this->purgeExpired();

        $baseSizes = $product['sizes'] ?? ['S' => 0, 'M' => 0, 'L' => 0];
        if (is_string($baseSizes)) {
            $baseSizes = json_decode($baseSizes, true) ?: ['S' => 0, 'M' => 0, 'L' => 0];
        }

        $productId = $product['id'];

        $stmt = $this->pdo->prepare(
            "SELECT UPPER(TRIM(size)) AS size, SUM(quantity) as held_qty
             FROM cart
             WHERE product_id = :product_id
             GROUP BY UPPER(TRIM(size))"
        );
        $stmt->execute(['product_id' => $productId]);
        $holds = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $effectiveSizes = [];
        foreach ($baseSizes as $size => $totalStock) {
            $normalizedSize = strtoupper(trim((string)$size));
            $held = (int)($holds[$normalizedSize] ?? 0);
            $effectiveSizes[$size] = max(0, (int)$totalStock - $held);
        }

        return $effectiveSizes;
    }
}

/**
 * ==========================================
 * MODULE 5: USERS & AUTHENTICATION
 * ==========================================
 */
class UserRepository
{
    public function __construct(private PDO $pdo) {}

    public function getProfileById(int $userId): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch();
    }

    public function updatePhone(int $userId, string $phone): bool
    {
        $stmt = $this->pdo->prepare("UPDATE users SET phone = :phone WHERE id = :id");
        return $stmt->execute(['phone' => $phone, 'id' => $userId]);
    }

    public function register(string $username, string $password): bool
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        return $stmt->execute([
            'username' => $username,
            'password' => $hashedPassword,
        ]);
    }

    public function getByUsername(string $username): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }
}

/**
 * ==========================================
 * MODULE 6: FULFILLMENT, CHECKOUT & ORDERS
 * ==========================================
 */
class OrderRepository
{
    public function __construct(
        private PDO $pdo,
        private ProductRepository $products,
        private CartRepository $cart,
        private UserRepository $users
    ) {}

    public function getFulfillmentMethods(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM fulfillment_methods WHERE is_active = 1 ORDER BY fee ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function validateCartStock(array $cartItems): ?string
    {
        foreach ($cartItems as $item) {
            $pid     = (int)($item['product_id'] ?? 0);
            $reqSize = strtoupper(trim((string)($item['size'] ?? '')));
            $reqQty  = max(1, (int)($item['quantity'] ?? 1));

            $prodRow = $this->products->getById($pid);
            if (!$prodRow) {
                return "One of the items in your cart is no longer available.";
            }

            $availTotal = max(0, (int)($prodRow['stock'] ?? 0));
            if ($reqQty > $availTotal) {
                return "Only {$availTotal} left in stock for {$prodRow['name']}. Please update your cart.";
            }

            if (!empty($prodRow['sizes']) && $reqSize !== '') {
                $sizesArr = is_string($prodRow['sizes'])
                    ? (json_decode($prodRow['sizes'], true) ?: [])
                    : (is_array($prodRow['sizes']) ? $prodRow['sizes'] : []);

                foreach ($sizesArr as $sKey => $sVal) {
                    if (strtoupper(trim((string)$sKey)) === $reqSize) {
                        $availSizeQty = max(0, (int)$sVal);
                        if ($reqQty > $availSizeQty) {
                            return "Only {$availSizeQty} left in stock for {$prodRow['name']} (Size: {$reqSize}). Please update your cart.";
                        }
                        break;
                    }
                }
            }
        }

        return null;
    }

    public function createOrderAndDeductStock(int $userId, array $orderData, array $cartItems): array
    {
        try {
            $this->pdo->beginTransaction();

            if (!empty($orderData['phone_number'])) {
                $this->users->updatePhone($userId, (string)$orderData['phone_number']);
            }

            $orderStmt = $this->pdo->prepare(
                "INSERT INTO orders (user_id, customer_name, phone_number, email, subtotal, shipping_fee, delivery_mode, payment_proof)
                 VALUES (:user_id, :customer_name, :phone_number, :email, :subtotal, :shipping_fee, :delivery_mode, :payment_proof)"
            );
            $orderStmt->execute([
                'user_id'       => $userId,
                'customer_name' => $orderData['customer_name'],
                'phone_number'  => $orderData['phone_number'],
                'email'         => $orderData['email'],
                'subtotal'      => $orderData['subtotal'],
                'shipping_fee'  => $orderData['shipping_fee'],
                'delivery_mode' => $orderData['delivery_mode'],
                'payment_proof' => $orderData['payment_proof'],
            ]);
            $orderId = (int)$this->pdo->lastInsertId();

            $itemStmt    = $this->pdo->prepare("INSERT INTO order_items (order_id, product_id, size, quantity, price) VALUES (:order_id, :product_id, :size, :quantity, :price)");
            $prodStmt    = $this->pdo->prepare("SELECT stock, sizes FROM products WHERE id = :id FOR UPDATE");
            $updProdStmt = $this->pdo->prepare("UPDATE products SET stock = :stock, sizes = :sizes WHERE id = :id");

            foreach ($cartItems as $item) {
                $productId   = (int)$item['product_id'];
                $orderedSize = strtoupper(trim((string)($item['size'] ?? '')));
                $orderedQty  = (int)$item['quantity'];

                $itemStmt->execute([
                    'order_id'   => $orderId,
                    'product_id' => $productId,
                    'size'       => $orderedSize,
                    'quantity'   => $orderedQty,
                    'price'      => $item['price'],
                ]);

                $prodStmt->execute(['id' => $productId]);
                $product = $prodStmt->fetch(PDO::FETCH_ASSOC);

                if ($product) {
                    $currentStock = (int)$product['stock'];
                    if ($orderedQty > $currentStock) {
                        throw new RuntimeException("Insufficient stock for product ID {$productId}.");
                    }

                    $newStock  = max(0, $currentStock - $orderedQty);
                    $sizesJson = $product['sizes'];

                    if (!empty($sizesJson)) {
                        $sizesArray = json_decode($sizesJson, true);
                        if (is_array($sizesArray)) {
                            foreach ($sizesArray as $sKey => $sVal) {
                                if (strtoupper(trim((string)$sKey)) === $orderedSize) {
                                    $currentSizeVal = (int)$sVal;
                                    if ($orderedQty > $currentSizeVal) {
                                        throw new RuntimeException("Insufficient stock for size {$orderedSize}.");
                                    }
                                    $sizesArray[$sKey] = max(0, $currentSizeVal - $orderedQty);
                                    break;
                                }
                            }
                            $sizesJson = json_encode($sizesArray);
                        }
                    }

                    $updProdStmt->execute([
                        'stock' => $newStock,
                        'sizes' => $sizesJson,
                        'id'    => $productId,
                    ]);
                }
            }

            $this->cart->clearForUser($userId);
            $this->pdo->commit();

            return ['success' => true, 'order_id' => $orderId];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

/**
 * ==========================================
 * MODULE 7: USER PROFILE, ORDERS & BOOKINGS
 * ==========================================
 */
class ProfileRepository
{
    public function __construct(private PDO $pdo) {}

    /**
     * Returns order counts per status bucket in a single lightweight query.
     */
    public function getOrderStatusSummary(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT payment_status, COUNT(*) AS cnt, COALESCE(SUM(subtotal + shipping_fee), 0) AS sum_total
             FROM orders
             WHERE user_id = :user_id
             GROUP BY payment_status"
        );
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll();

        $summary = [
            'all'         => 0,
            'pending'     => 0,
            'completed'   => 0,
            'cancelled'   => 0,
            'total_spent' => 0.0,
        ];

        foreach ($rows as $r) {
            $cnt = (int)$r['cnt'];
            $tot = (float)$r['sum_total'];
            $bucket = self::classifyStatus((string)($r['payment_status'] ?? ''));

            $summary['all'] += $cnt;
            $summary[$bucket] += $cnt;
            if ($bucket !== 'cancelled') {
                $summary['total_spent'] += $tot;
            }
        }

        return $summary;
    }

    public static function classifyStatus(string $status): string
    {
        $s = strtolower(trim($status));
        if (in_array($s, ['paid', 'verified', 'confirmed', 'completed', 'delivered'], true)) {
            return 'completed';
        }
        if (in_array($s, ['cancelled', 'rejected', 'failed'], true)) {
            return 'cancelled';
        }
        return 'pending';
    }

    /**
     * Fetches orders matching $bucket ('all' | 'pending' | 'completed' | 'cancelled')
     * and loads line items ONLY for those matching orders.
     */
    public function getOrdersByUserId(int $userId, string $bucket = 'all', int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));
        $sql = "SELECT id, user_id, customer_name, phone_number, email, subtotal, shipping_fee,
                       (subtotal + shipping_fee) AS total_amount,
                       delivery_mode, payment_proof, payment_status, created_at
                FROM orders
                WHERE user_id = :user_id";

        if ($bucket === 'completed') {
            $sql .= " AND LOWER(TRIM(payment_status)) IN ('paid', 'verified', 'confirmed', 'completed', 'delivered')";
        } elseif ($bucket === 'cancelled') {
            $sql .= " AND LOWER(TRIM(payment_status)) IN ('cancelled', 'rejected', 'failed')";
        } elseif ($bucket === 'pending') {
            $sql .= " AND LOWER(TRIM(payment_status)) NOT IN ('paid', 'verified', 'confirmed', 'completed', 'delivered', 'cancelled', 'rejected', 'failed')";
        }

        $sql .= " ORDER BY created_at DESC, id DESC LIMIT {$limit}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $orders = $stmt->fetchAll();

        if (empty($orders)) {
            return [];
        }

        $orderIds = array_column($orders, 'id');
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));

        $itemsStmt = $this->pdo->prepare(
            "SELECT oi.id, oi.order_id, oi.product_id, oi.size, oi.quantity, oi.price,
                    p.name AS product_name, p.image AS product_image
             FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id IN ($placeholders)
             ORDER BY oi.id ASC"
        );
        $itemsStmt->execute($orderIds);
        $allItems = $itemsStmt->fetchAll();

        $itemsByOrder = [];
        foreach ($allItems as $item) {
            $itemsByOrder[(int)$item['order_id']][] = $item;
        }

        foreach ($orders as &$order) {
            $order['items'] = $itemsByOrder[(int)$order['id']] ?? [];
        }
        unset($order);

        return $orders;
    }

    public function getBookingsForUser(string $username, ?string $phone, string $statusFilter = 'all'): array
    {
        $conditions = [];
        $params = [];

        if ($username !== '') {
            $conditions[] = "client_name = :client_name";
            $params['client_name'] = $username;
        }

        if (!empty($phone)) {
            $conditions[] = "contact = :contact";
            $params['contact'] = $phone;
        }

        if (empty($conditions)) {
            return [];
        }

        $sql = "SELECT id, service_name, client_name, contact, people_count, onsite_visit, booking_date, is_whatsapp, status, created_at
                FROM bookings
                WHERE (" . implode(' OR ', $conditions) . ")";

        if ($statusFilter !== '' && strtolower($statusFilter) !== 'all') {
            $sql .= " AND LOWER(TRIM(status)) = LOWER(:status)";
            $params['status'] = trim($statusFilter);
        }

        $sql .= " ORDER BY booking_date DESC, id DESC LIMIT 25";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

/**
 * ==========================================
 * BACKWARD-COMPATIBLE FUNCTION WRAPPERS
 * ==========================================
 */
function productRepo(PDO $pdo): ProductRepository
{
    static $repo = null;
    return $repo ??= new ProductRepository($pdo);
}

function serviceRepo(PDO $pdo): ServiceRepository
{
    static $repo = null;
    return $repo ??= new ServiceRepository($pdo);
}

function bookingRepo(PDO $pdo): BookingRepository
{
    static $repo = null;
    return $repo ??= new BookingRepository($pdo);
}

function cartRepo(PDO $pdo): CartRepository
{
    static $repo = null;
    return $repo ??= new CartRepository($pdo, productRepo($pdo));
}

function userRepo(PDO $pdo): UserRepository
{
    static $repo = null;
    return $repo ??= new UserRepository($pdo);
}

function orderRepo(PDO $pdo): OrderRepository
{
    static $repo = null;
    return $repo ??= new OrderRepository($pdo, productRepo($pdo), cartRepo($pdo), userRepo($pdo));
}

function profileRepo(PDO $pdo): ProfileRepository
{
    static $repo = null;
    return $repo ??= new ProfileRepository($pdo);
}

function getAllProducts($pdo)
{
    return productRepo($pdo)->getAll();
}
function getProductById($pdo, $id)
{
    return productRepo($pdo)->getById((int)$id);
}
function getAvailableShopProducts($pdo)
{
    return productRepo($pdo)->getAvailableForShop(cartRepo($pdo));
}

function getAllServices($pdo)
{
    return serviceRepo($pdo)->getAll();
}
function getServiceById($pdo, $id)
{
    return serviceRepo($pdo)->getById((int)$id);
}

function insertBooking($pdo, $serviceName, $clientName, $contact, $isWhatsapp, $peopleCount, $onsiteVisit, $bookingDate)
{
    return bookingRepo($pdo)->insert(
        (string)$serviceName,
        (string)$clientName,
        (string)$contact,
        $isWhatsapp,
        (int)$peopleCount,
        $onsiteVisit,
        (string)$bookingDate
    );
}

function getCartItems($pdo, $userId)
{
    return cartRepo($pdo)->getItems((int)$userId);
}
function getCartItemByIdForUser($pdo, $cartId, $userId)
{
    return cartRepo($pdo)->getItemByIdForUser((int)$cartId, (int)$userId);
}
function getCartQtyForProductSize($pdo, $userId, $productId, $size)
{
    return cartRepo($pdo)->getQtyForProductSize((int)$userId, (int)$productId, (string)$size);
}
function addToCartDb($pdo, $userId, $productId, $quantity = 1, $size = 'M')
{
    return cartRepo($pdo)->addItem((int)$userId, (int)$productId, (int)$quantity, (string)$size);
}
function updateCartItemQuantityDb($pdo, $cartId, $userId, $quantity)
{
    return cartRepo($pdo)->updateQuantity((int)$cartId, (int)$userId, (int)$quantity);
}
function removeFromCartDb($pdo, $cartId, $userId)
{
    return cartRepo($pdo)->removeItem((int)$cartId, (int)$userId);
}
function clearCartDb($pdo, $userId)
{
    return cartRepo($pdo)->clearForUser((int)$userId);
}
function getEffectiveSizeStock($pdo, $product)
{
    return cartRepo($pdo)->getEffectiveSizeStock((array)$product);
}

function getUserProfileById($pdo, $userId)
{
    return userRepo($pdo)->getProfileById((int)$userId);
}
function updateUserPhone($pdo, $userId, $phone)
{
    return userRepo($pdo)->updatePhone((int)$userId, (string)$phone);
}
function registerUser($pdo, $username, $password)
{
    return userRepo($pdo)->register((string)$username, (string)$password);
}
function getUserByUsername($pdo, $username)
{
    return userRepo($pdo)->getByUsername((string)$username);
}

function getFulfillmentMethods($pdo)
{
    return orderRepo($pdo)->getFulfillmentMethods();
}
function validateCartStockForCheckout($pdo, $cartItems)
{
    return orderRepo($pdo)->validateCartStock((array)$cartItems);
}
function createOrderAndDeductStock($pdo, $userId, $orderData, $cartItems)
{
    return orderRepo($pdo)->createOrderAndDeductStock((int)$userId, (array)$orderData, (array)$cartItems);
}

function getUserOrderSummary($pdo, $userId): array
{
    return profileRepo($pdo)->getOrderStatusSummary((int)$userId);
}
function getUserOrdersWithItems($pdo, $userId, $bucket = 'all', $limit = 20): array
{
    return profileRepo($pdo)->getOrdersByUserId((int)$userId, (string)$bucket, (int)$limit);
}
function getUserBookings($pdo, $username, $phone = null, $statusFilter = 'all'): array
{
    return profileRepo($pdo)->getBookingsForUser(
        (string)$username,
        $phone !== null ? (string)$phone : null,
        (string)$statusFilter
    );
}
