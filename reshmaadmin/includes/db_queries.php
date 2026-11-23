<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_helpers.php';

function getAllProducts(PDO $pdo): array
{
    return dbFetchAll($pdo, "SELECT * FROM products ORDER BY id DESC");
}

function getAllServices(PDO $pdo): array
{
    return dbFetchAll($pdo, "SELECT * FROM services ORDER BY id DESC");
}

function getAllBookings(PDO $pdo): array
{
    return dbFetchAll($pdo, "SELECT * FROM bookings ORDER BY booking_date DESC");
}

function getAllUsers(PDO $pdo): array
{
    return dbFetchAll($pdo, "SELECT * FROM users ORDER BY id DESC");
}

function getAdminUsers(PDO $pdo): array
{
    return dbFetchAll($pdo, "SELECT id, username, role, created_at FROM users WHERE role IN ('admin', 'manager') ORDER BY id DESC");
}

function buildUserFilterSql(string $alias, string $search, string $roleFilter): array
{
    $prefix = $alias !== '' ? $alias . '.' : '';
    $clauses = [];
    $params = [];

    $search = trim($search);
    if ($search !== '') {
        $like = '%' . $search . '%';
        if (ctype_digit($search)) {
            $clauses[] = "({$prefix}id = ? OR {$prefix}username LIKE ? OR COALESCE({$prefix}phone, '') LIKE ?)";
            $params[] = (int)$search;
            $params[] = $like;
            $params[] = $like;
        } else {
            $clauses[] = "({$prefix}username LIKE ? OR COALESCE({$prefix}phone, '') LIKE ?)";
            $params[] = $like;
            $params[] = $like;
        }
    }

    $roleFilter = trim($roleFilter);
    if (in_array($roleFilter, ['customer', 'user', 'manager', 'admin'], true)) {
        $clauses[] = "{$prefix}role = ?";
        $params[] = $roleFilter;
    }

    $whereSql = $clauses ? ('WHERE ' . implode(' AND ', $clauses)) : '';
    return [$whereSql, $params];
}

function searchUsersWithOrderCounts(PDO $pdo, string $search = '', string $roleFilter = ''): array
{
    [$whereWithAlias, $params] = buildUserFilterSql('u', $search, $roleFilter);

    try {
        return dbFetchAll(
            $pdo,
            "SELECT u.*,
                    (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count
             FROM users u
             {$whereWithAlias}
             ORDER BY u.id DESC",
            $params
        );
    } catch (Exception $e) {
        [$wherePlain, $plainParams] = buildUserFilterSql('', $search, $roleFilter);
        $rows = dbFetchAll($pdo, "SELECT * FROM users {$wherePlain} ORDER BY id DESC", $plainParams);

        foreach ($rows as &$row) {
            $row['order_count'] = $row['order_count'] ?? 0;
        }
        unset($row);

        return $rows;
    }
}

function updateUserAccountRole(PDO $pdo, int $targetUserId, string $newRole, int $currentUserId): array
{
    $allowedRoles = ['customer', 'user', 'manager', 'admin'];

    if ($targetUserId <= 0 || !in_array($newRole, $allowedRoles, true)) {
        return ['ok' => false, 'msg' => 'Invalid user or role selection.'];
    }

    if ($targetUserId === $currentUserId) {
        return ['ok' => false, 'msg' => 'You cannot change your own role from this list.'];
    }

    try {
        $user = dbFetchOne($pdo, "SELECT username, role FROM users WHERE id = ? LIMIT 1", [$targetUserId]);

        if (!$user) {
            return ['ok' => false, 'msg' => 'User account not found.'];
        }
        if ($user['role'] === $newRole) {
            return ['ok' => false, 'msg' => "Account '{$user['username']}' already has the '{$newRole}' role."];
        }

        dbExecute($pdo, "UPDATE users SET role = ? WHERE id = ?", [$newRole, $targetUserId]);

        return ['ok' => true, 'msg' => "Updated '{$user['username']}' (#{$targetUserId}) role to '{$newRole}'."];
    } catch (Exception $e) {
        return ['ok' => false, 'msg' => 'Failed to update account role.'];
    }
}

function getProductById(PDO $pdo, int $id): ?array
{
    if ($id <= 0) {
        return null;
    }

    return dbFetchOne($pdo, "SELECT * FROM products WHERE id = ? LIMIT 1", [$id]);
}

function createProduct(
    PDO $pdo,
    string $name,
    string $description,
    float $price,
    ?string $sizesJson,
    int $stock,
    string $status,
    string $imagePath
): bool {
    return dbExecute(
        $pdo,
        "INSERT INTO products (name, description, price, sizes, stock, status, image) VALUES (?, ?, ?, ?, ?, ?, ?)",
        [$name, $description, $price, $sizesJson, $stock, $status, $imagePath]
    );
}

function updateProductById(
    PDO $pdo,
    int $id,
    string $name,
    string $description,
    float $price,
    ?string $sizesJson,
    int $stock,
    string $status,
    string $imagePath
): bool {
    if ($id <= 0) {
        return false;
    }

    return dbExecute(
        $pdo,
        "UPDATE products SET name = ?, description = ?, price = ?, sizes = ?, stock = ?, status = ?, image = ? WHERE id = ?",
        [$name, $description, $price, $sizesJson, $stock, $status, $imagePath, $id]
    );
}

function deleteProductById(PDO $pdo, int $id): bool
{
    if ($id <= 0) {
        return false;
    }

    return dbExecute($pdo, "DELETE FROM products WHERE id = ?", [$id]);
} 

/**
 * Search, filter, and sort orders from the orders table.
 */
function getFilteredOrders(
    PDO $pdo,
    string $search = '',
    string $statusFilter = '',
    string $deliveryFilter = '',
    string $sortBy = 'newest'
): array {
    $clauses = [];
    $params = [];

    $search = trim($search);
    if ($search !== '') {
        $like = '%' . $search . '%';
        if (ctype_digit($search)) {
            $clauses[] = "(id = ? OR COALESCE(customer_name, '') LIKE ? OR COALESCE(phone_number, '') LIKE ?)";
            $params[] = (int)$search;
            $params[] = $like;
            $params[] = $like;
        } else {
            $clauses[] = "(COALESCE(customer_name, '') LIKE ? OR COALESCE(phone_number, '') LIKE ?)";
            $params[] = $like;
            $params[] = $like;
        }
    }

    $allowedStatuses = [
        'Pending Verification',
        'Processing',
        'Out for Delivery',
        'Delivered',
        'Cancelled',
        'Refunded',
    ];
    $statusFilter = trim($statusFilter);
    if ($statusFilter !== '' && in_array($statusFilter, $allowedStatuses, true)) {
        $clauses[] = "payment_status = ?";
        $params[] = $statusFilter;
    }

    $deliveryFilter = trim($deliveryFilter);
    if ($deliveryFilter !== '') {
        $clauses[] = "delivery_mode = ?";
        $params[] = $deliveryFilter;
    }

    $orderSql = match ($sortBy) {
        'oldest'        => 'id ASC',
        'customer_asc'  => 'customer_name ASC, id DESC',
        'customer_desc' => 'customer_name DESC, id DESC',
        'status_asc'    => 'payment_status ASC, id DESC',
        default         => 'id DESC',
    };

    $whereSql = $clauses ? ('WHERE ' . implode(' AND ', $clauses)) : '';

    try {
        return dbFetchAll($pdo, "SELECT * FROM orders {$whereSql} ORDER BY {$orderSql}", $params);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Update an order's payment/fulfillment status safely.
 */
function updateOrderStatusById(PDO $pdo, int $orderId, string $newStatus): bool
{
    $allowed = [
        'Pending Verification',
        'Processing',
        'Out for Delivery',
        'Delivered',
        'Cancelled',
        'Refunded',
    ];

    if ($orderId <= 0 || !in_array($newStatus, $allowed, true)) {
        return false;
    }

    try {
        return dbExecute($pdo, "UPDATE orders SET payment_status = ? WHERE id = ?", [$newStatus, $orderId]);
    } catch (Exception $e) {
        return false;
    }
} 

/**
 * Search, filter by rate range, and sort salon services.
 */
function getFilteredServices(
    PDO $pdo,
    string $search = '',
    string $rateFilter = '',
    string $sortBy = 'newest'
): array {
    $clauses = [];$params = [];

    $search = trim($search);
    if ($search !== '') {$like = '%' . $search . '\%';$clauses[] = "(title LIKE ? OR COALESCE(description, '') LIKE ?)";
        $params[] =$like;
        $params[] =$like;
    }

    switch ($rateFilter) {
        case 'free':
            $clauses[] = "price = 0";
            break;
        case 'under_500':
            $clauses[] = "price > 0 AND price < 500";
            break;
        case '500_1500':
            $clauses[] = "price >= 500 AND price <= 1500";
            break;
        case 'over_1500':
            $clauses[] = "price > 1500";
            break;
    }

    $orderSql = match ($sortBy) {
        'oldest'     => 'id ASC',
        'price_asc'  => 'price ASC, id DESC',
        'price_desc' => 'price DESC, id DESC',
        'title_asc'  => 'title ASC, id DESC',
        'title_desc' => 'title DESC, id DESC',
        default      => 'id DESC',
    };

    $whereSql = $clauses ? ('WHERE ' . implode(' AND ', $clauses)) : '';

    try {
        return dbFetchAll($pdo, "SELECT * FROM services {$whereSql} ORDER BY {$orderSql}", $params);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Fetch a single service by ID for editing.
 */
function getServiceById(PDO $pdo, int$id): ?array
{
    if ($id <= 0) {
        return null;
    }

    return dbFetchOne($pdo, "SELECT * FROM services WHERE id = ? LIMIT 1", [$id]);
}

/**
 * Insert a new service into the services table.
 */
function createService(
    PDO $pdo,
    string $title,
    string $description,
    float $price,
    string $imagePath
): bool {
    return dbExecute(
        $pdo,
        "INSERT INTO services (title, description, price, image) VALUES (?, ?, ?, ?)",
        [$title,$description, $price,$imagePath]
    );
}

/**
 * Update an existing service in the services table.
 */
function updateServiceById(
    PDO $pdo,
    int $id,
    string $title,
    string $description,
    float $price,
    string $imagePath
): bool {
    if ($id <= 0) {
        return false;
    }

    return dbExecute(
        $pdo,
        "UPDATE services SET title = ?, description = ?, price = ?, image = ? WHERE id = ?",
        [$title, $description,$price, $imagePath,$id]
    );
}

/**
 * Search, filter, and sort salon bookings.
 */
function getFilteredBookings(
    PDO $pdo,
    string $search = '',
    string $statusFilter = '',
    string $whatsappFilter = '',
    string $sortBy = 'newest'
): array {
    $clauses = [];$params = [];

    $search = trim($search);
    if ($search !== '') {
        $like = '\%' .$search . '%';
        if (ctype_digit($search)) {$clauses[] = "(id = ? OR COALESCE(client_name, '') LIKE ? OR COALESCE(service_name, '') LIKE ? OR COALESCE(contact, '') LIKE ?)";
            $params[] = (int)$search;
            $params[] =$like;
            $params[] =$like;
            $params[] =$like;
        } else {
            $clauses[] = "(COALESCE(client_name, '') LIKE ? OR COALESCE(service_name, '') LIKE ? OR COALESCE(contact, '') LIKE ?)";
            $params[] =$like;
            $params[] =$like;
            $params[] =$like;
        }
    }

    $allowedStatuses = ['Pending', 'Confirmed', 'Rescheduled', 'Completed', 'Cancelled'];
    $statusFilter = trim($statusFilter);
    if ($statusFilter !== '' && in_array($statusFilter, $allowedStatuses, true)) {$clauses[] = "status = ?";
        $params[] =$statusFilter;
    }

    if ($whatsappFilter === 'yes') {$clauses[] = "is_whatsapp = 1";
    } elseif ($whatsappFilter === 'no') {$clauses[] = "(is_whatsapp = 0 OR is_whatsapp IS NULL)";
    }

    $orderSql = match ($sortBy) {
        'oldest'       => 'id ASC',
        'date_asc'     => 'booking_date ASC, id ASC',
        'date_desc'    => 'booking_date DESC, id DESC',
        'client_asc'   => 'client_name ASC, id DESC',
        'client_desc'  => 'client_name DESC, id DESC',
        'service_asc'  => 'service_name ASC, id DESC',
        'status_asc'   => 'status ASC, id DESC',
        default        => 'id DESC',
    };

    $whereSql = $clauses ? ('WHERE ' . implode(' AND ', $clauses)) : '';

    try {
        return dbFetchAll($pdo, "SELECT * FROM bookings {$whereSql} ORDER BY {$orderSql}", $params);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Update a booking's status safely.
 */
function updateBookingStatusById(PDO $pdo, int $bookingId, string$newStatus): bool
{
    $allowed = ['Pending', 'Confirmed', 'Rescheduled', 'Completed', 'Cancelled'];

    if ($bookingId <= 0 || !in_array($newStatus,$allowed, true)) {
        return false;
    }

    try {
        return dbExecute($pdo, "UPDATE bookings SET status = ? WHERE id = ?", [$newStatus,$bookingId]);
    } catch (Exception $e) {
        return false;
    }
}


 
function getOrderAttachmentUrl(array $ord): ?string
{
    foreach (['payment_proof', 'receipt_image', 'proof_image', 'image'] as $col) {
        $val = trim((string)($ord[$col] ?? ''));
        if ($val === '') {
            continue;
        }

        // If already a full URL or root-relative path
        if (preg_match('#^(https?://|/)#i', $val)) {
            return $val;
        }

        // Normalize e.g. "uploads/pay_1.jpg" or "public/uploads/pay_1.jpg"
        $clean = ltrim($val, '/');
        if (strpos($clean, 'public/') === 0) {
            return '../' . $clean;
        }
        if (strpos($clean, 'uploads/') === 0) {
            // Check if saved in reshmaadmin/uploads/ vs public/uploads/
            if (is_file(__DIR__ . '/../' . $clean)) {
                return '../' . $clean;
            }
            return $clean;
        }

        return 'uploads/' . $clean;
    }

    return null;
}