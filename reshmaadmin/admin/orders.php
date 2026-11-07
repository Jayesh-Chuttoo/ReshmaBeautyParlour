<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_queries.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'manager'])) {
    header("Location: ../public/index.php");
    exit;
}

$message = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_order_status') {
    $orderId = intval($_POST['order_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? '');
    $allowed = ['Pending Verification', 'Processing', 'Out for Delivery', 'Delivered', 'Cancelled', 'Refunded'];

    if ($orderId > 0 && in_array($newStatus, $allowed)) {
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
        $success = $stmt->execute([$newStatus, $orderId]);
        $message = $success ? "Order #{$orderId} status updated to '{$newStatus}'." : 'Failed to update order status.';
        $msgType = $success ? 'success' : 'error';
    }
}

$orders = [];
try {
    $orderStmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
    $orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

$pageTitle = "Orders Management - Reshma Beauty Parlour";
require_once __DIR__ . '/../includes/components/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8 flex-1 w-full space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">Customer Orders</h1>
            <p class="text-xs text-slate-500">Manage fulfillment, verification, and order statuses.</p>
        </div>
        <a href="index.php" class="text-xs bg-slate-200 hover:bg-slate-300 px-3 py-2 rounded-lg font-bold text-slate-700 transition">&larr; Back to Dashboard</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="p-4 rounded-xl text-sm font-medium <?= $msgType === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-4">
        <?php if (empty($orders)): ?>
            <div class="bg-white p-8 rounded-2xl text-center text-slate-400 border">No orders found.</div>
            <?php else: foreach ($orders as $ord):
                $status = $ord['payment_status'] ?? 'Pending Verification';
            ?>
                <div class="bg-white p-5 rounded-2xl border shadow-sm space-y-3">
                    <div class="flex justify-between items-center border-b pb-3">
                        <span class="font-mono font-bold text-indigo-600 text-base">Order #<?= $ord['id'] ?></span>
                        <span class="px-3 py-1 text-xs rounded-full font-bold bg-indigo-50 text-indigo-700 border border-indigo-200"><?= htmlspecialchars($status) ?></span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs bg-slate-50 p-3.5 rounded-xl">
                        <div><span class="text-slate-400 block uppercase font-semibold text-[10px]">Customer</span><strong><?= htmlspecialchars($ord['customer_name'] ?? 'Guest') ?></strong></div>
                        <div><span class="text-slate-400 block uppercase font-semibold text-[10px]">Phone</span><span class="font-mono text-indigo-600"><?= htmlspecialchars($ord['phone_number'] ?? 'N/A') ?></span></div>
                        <div><span class="text-slate-400 block uppercase font-semibold text-[10px]">Delivery Mode</span><strong class="text-purple-700"><?= htmlspecialchars($ord['delivery_mode'] ?? 'Pickup') ?></strong></div>
                    </div>
                    <form method="POST" class="flex items-center gap-3 pt-2">
                        <input type="hidden" name="action" value="update_order_status">
                        <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                        <select name="status" class="text-xs border rounded-lg px-3 py-2 bg-white font-medium">
                            <option value="Pending Verification" <?= $status === 'Pending Verification' ? 'selected' : '' ?>>Pending Verification</option>
                            <option value="Processing" <?= $status === 'Processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="Out for Delivery" <?= $status === 'Out for Delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                            <option value="Delivered" <?= $status === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="Cancelled" <?= $status === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            <option value="Refunded" <?= $status === 'Refunded' ? 'selected' : '' ?>>Refunded</option>
                        </select>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-4 py-2 rounded-lg font-bold transition">Update Status</button>
                    </form>
                </div>
        <?php endforeach;
        endif; ?>
    </div>
</main>
</body>

</html>