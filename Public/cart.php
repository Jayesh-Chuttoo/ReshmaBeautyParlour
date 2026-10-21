<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pageTitle = "Shopping Cart - Reshma Beauty Parlour";
$pageCss   = "cart.css";

// --- NOTE FOR FUTURE DATABASE INTEGRATION ---
// Later, instead of $_SESSION['cart'], you will fetch items from your database:
// $cartItems = $pdo->query("SELECT p.*, c.quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $userId")->fetchAll();

// Initialize mock array in session if not set (Simulating cart items)
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [
        1 => [
            'id' => 1,
            'title' => 'Professional Hair Serum',
            'price' => 25.00,
            'image' => 'serum.jpg',
            'quantity' => 2
        ],
        2 => [
            'id' => 2,
            'title' => 'Hydrating Shampoo',
            'price' => 18.50,
            'image' => 'shampoo.jpg',
            'quantity' => 1
        ]
    ];
}

// Handle cart actions (increment, decrement, remove, clear)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'increase' && isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity']++;
    } elseif ($action === 'decrease' && isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity']--;
        if ($_SESSION['cart'][$id]['quantity'] <= 0) {
            unset($_SESSION['cart'][$id]);
        }
    } elseif ($action === 'remove' && isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    } elseif ($action === 'clear') {
        $_SESSION['cart'] = [];
    }

    // Redirect to prevent form resubmission on refresh
    header("Location: cart.php");
    exit;
}

$cartItems = $_SESSION['cart'];
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
                            $itemTotal = $item['price'] * $item['quantity'];
                            $subtotal += $itemTotal;
                            $imagePath = !empty($item['image']) ? "assets/images/{$item['image']}" : "https://via.placeholder.com/60?text=Product";
                        ?>
                            <tr>
                                <td>
                                    <div class="cart-item-info">
                                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="cart-item-img" onerror="this.src='https://via.placeholder.com/60?text=Product'">
                                        <span><?php echo htmlspecialchars($item['title']); ?></span>
                                    </div>
                                </td>
                                <td>Rs <?php echo number_format($item['price'], 2); ?></td>
                                <td style="text-align: center;">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="action" value="decrease">
                                        <button type="submit" class="quantity-btn">-</button>
                                    </form>
                                    <span style="margin: 0 10px; font-weight: 600;"><?php echo $item['quantity']; ?></span>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="action" value="increase">
                                        <button type="submit" class="quantity-btn">+</button>
                                    </form>
                                </td>
                                <td style="font-weight: 600;">Rs <?php echo number_format($itemTotal, 2); ?></td>
                                <td style="text-align: center;">
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
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