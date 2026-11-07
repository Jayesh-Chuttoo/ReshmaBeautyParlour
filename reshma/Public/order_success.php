<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);

$pageTitle = "Order Success - Reshma Beauty Parlour";
$pageCss = "cart.css";

require_once __DIR__ . '/../Includes/db_queries.php';

$userId = $_SESSION['user_id'];
$orderId = (int)($_GET['order_id'] ?? 0);

if ($orderId <= 0) {
    header("Location: shop.php");
    exit;
}

// Fetch order details ensuring it belongs to the logged-in user
$orderStmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id AND user_id = :user_id LIMIT 1");
$orderStmt->execute(['id' => $orderId, 'user_id' => $userId]);
$order = $orderStmt->fetch();

if (!$order) {
    header("Location: shop.php");
    exit;
}

// Fetch items associated with this order
$itemsStmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = :order_id
");
$itemsStmt->execute(['order_id' => $orderId]);
$orderItems = $itemsStmt->fetchAll();

require_once __DIR__ . '/../Includes/Components/header.php';
?>

<main class="cart-main" style="max-width: 750px; margin: 2rem auto; padding: 0 1rem;">
    <div style="background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; text-align: center;">

        <!-- Success Icon -->
        <div style="width: 60px; height: 60px; background: #dcfce7; color: #16a34a; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin: 0 auto 1rem auto;">
            ✓
        </div>

        <h1 style="color: #1e1b4b; margin-bottom: 0.5rem; font-size: 1.75rem;">Order Successfully Placed!</h1>
        <p style="color: #64748b; margin-bottom: 2rem;">Thank you for shopping with Reshma Beauty Parlour. Your MCB Juice payment receipt has been received and is pending verification.</p>

        <!-- Order Meta Summary -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; text-align: left; background: #f8fafc; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 2rem;">
            <div>
                <span style="font-size: 0.8rem; color: #64748b; display: block;">Order Reference</span>
                <strong style="color: #1e1b4b; font-size: 1.05rem;">#<?php echo $order['id']; ?></strong>
            </div>
            <div>
                <span style="font-size: 0.8rem; color: #64748b; display: block;">Customer Name</span>
                <strong style="color: #1e1b4b; font-size: 1.05rem;"><?php echo htmlspecialchars($order['customer_name']); ?></strong>
            </div>
            <div>
                <span style="font-size: 0.8rem; color: #64748b; display: block;">Contact Number</span>
                <strong style="color: #1e1b4b; font-size: 1.05rem;"><?php echo htmlspecialchars($order['phone_number']); ?></strong>
            </div>
            <div>
                <span style="font-size: 0.8rem; color: #64748b; display: block;">Fulfillment Mode</span>
                <strong style="color: #1e1b4b; font-size: 1.05rem;"><?php echo htmlspecialchars($order['delivery_mode']); ?></strong>
            </div>
        </div>

        <!-- Purchased Items Table -->
        <div style="text-align: left; margin-bottom: 2rem;">
            <h3 style="font-size: 1.1rem; color: #1e1b4b; margin-bottom: 1rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 0.5rem;">Order Items Summary</h3>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <?php foreach ($orderItems as $item):
                    $lineTotal = (float)$item['price'] * (int)$item['quantity'];
                    $imagePath = !empty($item['image']) ? "assets/images/{$item['image']}" : "https://via.placeholder.com/50?text=Product";
                ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;" onerror="this.src='https://via.placeholder.com/50?text=Product'">
                            <div>
                                <span style="font-weight: 600; color: #1e1b4b; display: block; font-size: 0.95rem;"><?php echo htmlspecialchars($item['name']); ?></span>
                                <small style="color: #64748b;">Size: <?php echo htmlspecialchars($item['size']); ?> | Qty: <?php echo $item['quantity']; ?></small>
                            </div>
                        </div>
                        <span style="font-weight: 600; color: #059669;">Rs <?php echo number_format($lineTotal, 2); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cost Breakdown Calculation -->
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 1.25rem; border-radius: 8px; text-align: left; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #166534; margin-bottom: 0.3rem;">
                <span>Subtotal:</span>
                <span>Rs <?php echo number_format($order['subtotal'], 2); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #166534; margin-bottom: 0.5rem;">
                <span>Fulfillment Fee:</span>
                <span><?php echo (float)$order['shipping_fee'] == 0 ? 'Free' : 'Rs ' . number_format($order['shipping_fee'], 2); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: bold; color: #15803d; border-top: 1px solid #bbf7d0; padding-top: 0.5rem;">
                <span>Grand Total Paid:</span>
                <span>Rs <?php echo number_format((float)$order['subtotal'] + (float)$order['shipping_fee'], 2); ?></span>
            </div>
        </div>

        <!-- Action Navigation -->
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="shop.php" class="btn" style="padding: 0.75rem 1.5rem; text-decoration: none; background: #4f46e5; color: white; border-radius: 6px; font-weight: 600;">Continue Shopping</a>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../Includes/Components/footer.php'; ?>