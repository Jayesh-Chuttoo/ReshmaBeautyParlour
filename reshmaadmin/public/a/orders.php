<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db_queries.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'manager'], true)) {
    header("Location: ../public/index.php");
    exit;
}

$message = '';
$msgType = 'success';

$searchQuery    = trim($_GET['q'] ?? '');
$statusFilter   = trim($_GET['status'] ?? '');
$deliveryFilter = trim($_GET['delivery'] ?? '');
$sortBy         = trim($_GET['sort'] ?? 'newest');

$allowedStatuses = [
    'Pending Verification',
    'Processing',
    'Out for Delivery',
    'Delivered',
    'Cancelled',
    'Refunded',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_order_status') {
    $orderId   = intval($_POST['order_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? '');

    if ($orderId > 0 && in_array($newStatus, $allowedStatuses, true)) {
        $success = updateOrderStatusById($pdo, $orderId, $newStatus);
        $message = $success ? "Order #{$orderId} status updated to '{$newStatus}'." : 'Failed to update order status.';
        $msgType = $success ? 'success' : 'error';
    } else {
        $message = 'Invalid order or status selection.';
        $msgType = 'error';
    }
}

$orders = getFilteredOrders($pdo, $searchQuery, $statusFilter, $deliveryFilter, $sortBy);

$pageTitle = "Orders Management - Reshma Beauty Parlour";
$pageCss = "orders.css";
require_once __DIR__ . '/../../includes/components/header.php';
?>

<main class="orders-page">
    <div class="orders-header">
        <div>
            <h1 class="orders-title">Customer Orders</h1>
            <p class="orders-subtitle">Manage fulfillment, verification, and order statuses.</p>
        </div>
        <a href="index.php" class="orders-back-link">&larr; Back to Dashboard</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="orders-alert <?= $msgType === 'success' ? 'orders-alert-success' : 'orders-alert-error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Search, Filter & Sort Controls -->
    <form method="GET" class="orders-filter-bar">
        <div class="orders-search-col">
            <label for="orderSearch" class="sr-only">Search orders</label>
            <input
                id="orderSearch"
                type="text"
                name="q"
                value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Search by Order ID, customer name, or phone..."
                class="orders-search-input">
        </div>

        <div>
            <label for="statusFilter" class="sr-only">Filter by status</label>
            <select id="statusFilter" name="status" class="orders-filter-select">
                <option value="">All Statuses</option>
                <?php foreach ($allowedStatuses as $st): ?>
                    <option value="<?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?>" <?= $statusFilter === $st ? 'selected' : '' ?>>
                        <?= htmlspecialchars($st) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="deliveryFilter" class="sr-only">Filter by delivery mode</label>
            <select id="deliveryFilter" name="delivery" class="orders-filter-select">
                <option value="">All Delivery Modes</option>
                <option value="Pickup" <?= $deliveryFilter === 'Pickup' ? 'selected' : '' ?>>Pickup</option>
                <option value="Delivery" <?= $deliveryFilter === 'Delivery' ? 'selected' : '' ?>>Delivery</option>
            </select>
        </div>

        <div class="orders-controls-row">
            <label for="orderSort" class="sr-only">Sort orders</label>
            <select id="orderSort" name="sort" class="orders-filter-select">
                <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newest First</option>
                <option value="oldest" <?= $sortBy === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                <option value="customer_asc" <?= $sortBy === 'customer_asc' ? 'selected' : '' ?>>Customer (A–Z)</option>
                <option value="customer_desc" <?= $sortBy === 'customer_desc' ? 'selected' : '' ?>>Customer (Z–A)</option>
                <option value="status_asc" <?= $sortBy === 'status_asc' ? 'selected' : '' ?>>Status (A–Z)</option>
            </select>

            <button type="submit" class="orders-apply-btn">Apply</button>

            <?php if ($searchQuery !== '' || $statusFilter !== '' || $deliveryFilter !== '' || $sortBy !== 'newest'): ?>
                <a href="orders.php" class="orders-reset-btn">Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="orders-list">
        <?php if (empty($orders)): ?>
            <div class="orders-empty-card">
                <?= ($searchQuery !== '' || $statusFilter !== '' || $deliveryFilter !== '') ? 'No matching orders found.' : 'No orders found.' ?>
            </div>
            <?php else: foreach ($orders as $ord):
                $status = $ord['payment_status'] ?? 'Pending Verification';
            ?>
                <div class="order-card">
                    <div class="order-card-header">
                        <span class="order-id">Order #<?= (int)$ord['id'] ?></span>
                        <span class="order-status-badge"><?= htmlspecialchars($status) ?></span>
                    </div>
                    <div class="order-meta-grid">
                        <div>
                            <span class="order-meta-label">Customer</span>
                            <strong class="order-customer-name"><?= htmlspecialchars($ord['customer_name'] ?? 'Guest') ?></strong>
                        </div>
                        <div>
                            <span class="order-meta-label">Phone</span>
                            <span class="order-phone"><?= htmlspecialchars($ord['phone_number'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="order-meta-label">Delivery Mode</span>
                            <strong class="order-delivery-mode"><?= htmlspecialchars($ord['delivery_mode'] ?? 'Pickup') ?></strong>
                        </div>
                    </div>
                    <form method="POST" class="order-status-form">
                        <input type="hidden" name="action" value="update_order_status">
                        <input type="hidden" name="order_id" value="<?= (int)$ord['id'] ?>">
                        <select name="status" class="order-status-select">
                            <?php foreach ($allowedStatuses as $st): ?>
                                <option value="<?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?>" <?= $status === $st ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($st) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="order-update-btn">Update Status</button>
                    </form>
                    <?php $attachmentUrl = getOrderAttachmentUrl($ord); ?>
                    <?php if ($attachmentUrl): ?>
                        <div class="order-attachment-box">
                            <span class="order-meta-label">Attached Image</span>
                            <div class="order-attachment-row">
                                <img
                                    src="<?= htmlspecialchars($attachmentUrl, ENT_QUOTES, 'UTF-8') ?>"
                                    alt="Order #<?= (int)$ord['id'] ?> attachment"
                                    class="order-attachment-thumb"
                                    onclick="openOrderImageModal(this.src, 'Order #<?= (int)$ord['id'] ?> Attachment')">
                                <button
                                    type="button"
                                    class="order-view-img-btn"
                                    onclick="openOrderImageModal('<?= htmlspecialchars($attachmentUrl, ENT_QUOTES, 'UTF-8') ?>', 'Order #<?= (int)$ord['id'] ?> Attachment')">
                                    <i class="fa-solid fa-up-right-and-down-left-from-center"></i> View Full Image
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
        <?php endforeach;
        endif; ?>
    </div>
</main>
<!-- Full Image Lightbox Modal -->
<div id="orderImageModal" class="order-modal-backdrop" onclick="if (event.target === this) closeOrderImageModal();">
    <div class="order-modal-card">
        <div class="order-modal-header">
            <span id="orderImageModalTitle" class="order-modal-title">Attachment</span>
            <button type="button" class="order-modal-close" onclick="closeOrderImageModal()" aria-label="Close preview">&times;</button>
        </div>
        <div class="order-modal-body">
            <img id="orderImageModalImg" src="" alt="Order attachment preview" class="order-modal-img">
        </div>
    </div>
</div>

<script>
    function openOrderImageModal(src, title) {
        const modal = document.getElementById('orderImageModal');
        const img = document.getElementById('orderImageModalImg');
        const heading = document.getElementById('orderImageModalTitle');
        if (!modal || !img) return;
        img.src = src;
        if (heading) heading.textContent = title || 'Attachment';
        modal.classList.add('is-open');
    }

    function closeOrderImageModal() {
        const modal = document.getElementById('orderImageModal');
        const img = document.getElementById('orderImageModalImg');
        if (modal) modal.classList.remove('is-open');
        if (img) img.src = '';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeOrderImageModal();
    });
</script>
</body>

</html>