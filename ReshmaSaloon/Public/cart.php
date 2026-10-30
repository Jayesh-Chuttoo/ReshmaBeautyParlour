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

// Security: Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/php-error.log');

$pageTitle = "Shopping Cart - Reshma Beauty Parlour";
$pageCss   = "cart.css";

require_once __DIR__ . '/../Includes/db_queries.php';

$userId = $_SESSION['user_id'];

// --- HANDLE CART ACTIONS VIA DATABASE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $cartId = (int)($_POST['cart_id'] ?? 0);
    $productId = (int)($_POST['product_id'] ?? 0);

    if ($action === 'increase' && $cartId > 0) {
        $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $cartId, 'user_id' => $userId]);
        $item = $stmt->fetch();
        if ($item) {
            updateCartItemQuantityDb($pdo, $cartId, $userId, $item['quantity'] + 1);
        }
    } elseif ($action === 'decrease' && $cartId > 0) {
        $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $cartId, 'user_id' => $userId]);
        $item = $stmt->fetch();
        if ($item) {
            updateCartItemQuantityDb($pdo, $cartId, $userId, $item['quantity'] - 1);
        }
    } elseif ($action === 'remove' && $cartId > 0) {
        removeFromCartDb($pdo, $cartId, $userId);
    } elseif ($action === 'clear') {
        clearCartDb($pdo, $userId);
    } elseif ($action === 'add' && $productId > 0) {
        addToCartDb($pdo, $userId, $productId, 1);
    }

    header("Location: cart.php");
    exit;
}

// Fetch live cart items restricted strictly to the logged-in user
$cartItems = getCartItems($pdo, $userId);
$subtotal = 0;

require_once __DIR__ . '/../Includes/Components/header.php';
?>

<main class="cart-main">
    <h1>Your Shopping Cart</h1>
    <p style="color: var(--text-muted); margin-top: 0.5rem;">Review your selected products before proceeding to checkout.</p>

    <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <p style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 1.5rem;">Your cart is currently empty.</p>
            <a href="shop.php" class="btn" style="text-decoration: none;">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="cart-summary-section">
            <div class="cart-table-wrapper">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th style="text-align: center;">Quantity</th>
                            <th>Total</th>
                            <th style="text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item):
                            $itemTotal = (float)$item['price'] * (int)$item['quantity'];
                            $subtotal += $itemTotal;
                            $imagePath = !empty($item['image']) ? "assets/images/{$item['image']}" : "https://via.placeholder.com/60?text=Product";
                        ?>
                            <tr>
                                <td>
                                    <div class="cart-item-info">
                                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-img" onerror="this.src='https://via.placeholder.com/60?text=Product'">
                                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                                    </div>
                                </td>
                                <td>Rs <?php echo number_format($item['price'], 2); ?></td>
                                <td style="text-align: center;">
                                    <div class="quantity-form" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                            <input type="hidden" name="action" value="decrease">
                                            <button type="submit" class="quantity-btn">-</button>
                                        </form>
                                        <span class="quantity-display" style="padding: 0 0.4rem; font-weight: 600;"><?php echo $item['quantity']; ?></span>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                            <input type="hidden" name="action" value="increase">
                                            <button type="submit" class="quantity-btn">+</button>
                                        </form>
                                    </div>
                                </td>
                                <td style="font-weight: 600;">Rs <?php echo number_format($itemTotal, 2); ?></td>
                                <td style="text-align: center;">
                                    <form method="POST">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                        <input type="hidden" name="action" value="remove">
                                        <button type="submit" class="remove-btn" title="Remove item">&times;</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-summary-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3 style="margin: 0;">Order Summary</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" style="background: none; border: none; color: var(--danger, #e53e3e); cursor: pointer; font-size: 0.9rem;">Clear Cart</button>
                    </form>
                </div>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>Rs <?php echo number_format($subtotal, 2); ?></span>
                </div>
                <button class="btn" style="width: 100%;" onclick="alert('Proceeding to database checkout...')">Proceed to Checkout</button>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../Includes/Components/footer.php'; ?>