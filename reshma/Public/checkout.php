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

$pageTitle = "Checkout - Reshma Beauty Parlour";
$pageCss = "cart.css";

require_once __DIR__ . '/../Includes/db_queries.php';

$userId = $_SESSION['user_id'];

// Fetch user data
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$userStmt->execute(['id' => $userId]);
$userData = $userStmt->fetch();
$userEmail = $userData['email'] ?? ($userData['username'] ?? '');
$userName = $userData['name'] ?? ($userData['username'] ?? '');
$userPhone = $userData['phone'] ?? '';

// Fetch cart & compute subtotal
$cartItems = getCartItems($pdo, $userId);
if (empty($cartItems)) {
    header("Location: cart.php");
    exit;
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += (float)$item['price'] * (int)$item['quantity'];
}

// Fetch dynamic fulfillment options from DB
$fulfillmentMethods = getFulfillmentMethods($pdo);

$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = trim($_POST['customer_name'] ?? '');
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $selectedCode = trim($_POST['delivery_mode'] ?? 'pickup');

    // Find selected fulfillment fee
    $shippingFee = 0.00;
    $deliveryModeName = 'Store Pickup';
    foreach ($fulfillmentMethods as $method) {
        if ($method['code'] === $selectedCode) {
            $shippingFee = (float)$method['fee'];
            $deliveryModeName = $method['name'];
            break;
        }
    }

    $grandTotal = $subtotal + $shippingFee;

    if (empty($customerName)) {
        $errorMsg = "Please enter your full name.";
    } elseif (empty($phoneNumber)) {
        $errorMsg = "Please enter your mobile number.";
    } elseif (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] === UPLOAD_ERR_NO_FILE) {
        $errorMsg = "Please upload your Juice payment receipt screenshot.";
    } else {
        $file = $_FILES['payment_proof'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = "Error uploading payment proof. Please try again.";
        } elseif (!in_array($file['type'], $allowedTypes)) {
            $errorMsg = "Invalid file type. Please upload a JPG, PNG, or PDF.";
        } else {
            $uploadDir = __DIR__ . '/../assets/uploads/payments/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = 'juice_' . $userId . '_' . time() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                try {
                    $pdo->beginTransaction();

                    // Update user phone
                    updateUserPhone($pdo, $userId, $phoneNumber);

                    // Insert order
                    $orderStmt = $pdo->prepare("
                        INSERT INTO orders (user_id, customer_name, phone_number, email, subtotal, shipping_fee, delivery_mode, payment_proof) 
                        VALUES (:user_id, :customer_name, :phone_number, :email, :subtotal, :shipping_fee, :delivery_mode, :payment_proof)
                    ");
                    $orderStmt->execute([
                        'user_id' => $userId,
                        'customer_name' => $customerName,
                        'phone_number' => $phoneNumber,
                        'email' => $email,
                        'subtotal' => $subtotal,
                        'shipping_fee' => $shippingFee,
                        'delivery_mode' => $deliveryModeName,
                        'payment_proof' => $fileName
                    ]);
                    $orderId = $pdo->lastInsertId();

                    // Prepared statements for order items and stock deduction
                    $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, size, quantity, price) VALUES (:order_id, :product_id, :size, :quantity, :price)");
                    $prodStmt = $pdo->prepare("SELECT stock, sizes FROM products WHERE id = :id FOR UPDATE");
                    $updProdStmt = $pdo->prepare("UPDATE products SET stock = :stock, sizes = :sizes WHERE id = :id");

                    foreach ($cartItems as $item) {
                        $productId = $item['product_id'];
                        $orderedSize = strtoupper(trim($item['size'] ?? ''));
                        $orderedQty = (int)$item['quantity'];

                        // 1. Insert into order_items
                        $itemStmt->execute([
                            'order_id' => $orderId,
                            'product_id' => $productId,
                            'size' => $orderedSize,
                            'quantity' => $orderedQty,
                            'price' => $item['price']
                        ]);

                        // 2. Fetch current stock & sizes for product
                        $prodStmt->execute(['id' => $productId]);
                        $product = $prodStmt->fetch(PDO::FETCH_ASSOC);

                        if ($product) {
                            $newStock = max(0, (int)$product['stock'] - $orderedQty);
                            $sizesJson = $product['sizes'];

                            // Deduct quantity from specific size in JSON array if present
                            if (!empty($sizesJson)) {
                                $sizesArray = json_decode($sizesJson, true);
                                if (is_array($sizesArray)) {
                                    foreach ($sizesArray as $sKey => $sVal) {
                                        if (strtoupper(trim($sKey)) === $orderedSize) {
                                            $sizesArray[$sKey] = max(0, (int)$sVal - $orderedQty);
                                            break;
                                        }
                                    }
                                    $sizesJson = json_encode($sizesArray);
                                }
                            }

                            // Update stock in products table
                            $updProdStmt->execute([
                                'stock' => $newStock,
                                'sizes' => $sizesJson,
                                'id'    => $productId
                            ]);
                        }
                    }

                    // Clear user cart
                    clearCartDb($pdo, $userId);

                    $pdo->commit();

                    header("Location: order_success.php?order_id=" . $orderId);
                    exit;
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $errorMsg = "Database error processing order: " . $e->getMessage();
                }
            } else {
                $errorMsg = "Failed to save the uploaded payment proof.";
            }
        }
    }
}

require_once __DIR__ . '/../Includes/Components/header.php';
?>

<main class="cart-main" style="max-width: 700px; margin: 2rem auto; padding: 0 1rem;">
    <h1>Checkout & Payment</h1>
    <p style="color: var(--text-muted); margin-top: 0.5rem; margin-bottom: 2rem;">Select your fulfillment preference and complete MCB Juice transfer.</p>

    <?php if (!empty($errorMsg)): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #f87171;">
            <?php echo htmlspecialchars($errorMsg); ?>
        </div>
    <?php endif; ?>

    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
        <form action="checkout.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.25rem;">
            <div>
                <label for="customer_name" style="display: block; font-weight: 500; margin-bottom: 0.3rem; color: #334155;">Full Name:</label>
                <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($userName); ?>" placeholder="Enter your full name" required style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label for="email" style="display: block; font-weight: 500; margin-bottom: 0.3rem; color: #334155;">Email Address:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" required readonly style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; background: #f8fafc; color: #64748b; font-size: 0.95rem;">
            </div>

            <div>
                <label for="phone_number" style="display: block; font-weight: 500; margin-bottom: 0.3rem; color: #334155;">Mobile Number (Juice Contact):</label>
                <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($userPhone); ?>" placeholder="e.g. 59123456" required style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <!-- Dynamic Fulfillment Selection -->
            <div>
                <label style="display: block; font-weight: 500; margin-bottom: 0.4rem; color: #334155;">Select Fulfillment Option:</label>
                <div style="display: flex; flex-direction: column; gap: 0.5rem; background: #f8fafc; padding: 1rem; border: 1px solid #cbd5e1; border-radius: 8px;">
                    <?php foreach ($fulfillmentMethods as $index => $method): ?>
                        <label style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0; cursor: pointer;">
                            <span style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="radio" name="delivery_mode" value="<?php echo htmlspecialchars($method['code']); ?>" data-fee="<?php echo $method['fee']; ?>" <?php echo $index === 0 ? 'checked' : ''; ?> onchange="calculateGrandTotal()" style="accent-color: #4f46e5;">
                                <strong><?php echo htmlspecialchars($method['name']); ?></strong>
                                <small style="color: #64748b; margin-left: 0.25rem;">(<?php echo htmlspecialchars($method['description']); ?>)</small>
                            </span>
                            <span style="font-weight: 600; color: #334155;">
                                <?php echo (float)$method['fee'] == 0 ? 'Free' : 'Rs ' . number_format($method['fee'], 2); ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Payment Summary -->
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 1rem; border-radius: 8px;">
                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #166534; margin-bottom: 0.25rem;">
                    <span>Items Subtotal:</span>
                    <span>Rs <?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #166534; margin-bottom: 0.5rem;">
                    <span>Fulfillment Fee:</span>
                    <span id="displayShippingFee">Rs 0.00</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: bold; color: #15803d; border-top: 1px solid #bbf7d0; padding-top: 0.5rem;">
                    <span>Total Amount to Transfer:</span>
                    <span id="displayGrandTotal">Rs <?php echo number_format($subtotal, 2); ?></span>
                </div>
            </div>

            <div>
                <label for="payment_proof" style="display: block; font-weight: 500; margin-bottom: 0.3rem; color: #334155;">Upload Juice Payment Receipt (Screenshot):</label>
                <input type="file" id="payment_proof" name="payment_proof" accept="image/png, image/jpeg, image/jpg, application/pdf" required style="width: 100%; padding: 0.5rem; border: 1px dashed #cbd5e1; border-radius: 6px; background: #f8fafc; font-size: 0.9rem;">
            </div>

            <button type="submit" class="btn" style="width: 100%; padding: 0.85rem; background: #4f46e5; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem; margin-top: 0.5rem;">Submit Order & Receipt</button>
        </form>
    </div>
</main>

<script>
    const subtotal = <?php echo (float)$subtotal; ?>;

    function calculateGrandTotal() {
        const selectedRadio = document.querySelector('input[name="delivery_mode"]:checked');
        const fee = parseFloat(selectedRadio.getAttribute('data-fee')) || 0;
        const grandTotal = subtotal + fee;

        document.getElementById('displayShippingFee').innerText = fee === 0 ? 'Free' : 'Rs ' + fee.toFixed(2);
        document.getElementById('displayGrandTotal').innerText = 'Rs ' + grandTotal.toFixed(2);
    }

    // Initialize total calculation on page load
    document.addEventListener('DOMContentLoaded', calculateGrandTotal);
</script>

<?php require_once __DIR__ . '/../Includes/Components/footer.php'; ?>