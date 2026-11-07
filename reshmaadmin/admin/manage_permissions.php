<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/admin_auth.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../public/login.php");
    exit;
}

$message = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_permissions') {
    $managerId = intval($_POST['user_id'] ?? 0);
    if ($managerId > 0) {
        $dash  = isset($_POST['can_dashboard']) ? 1 : 0;
        $prod  = isset($_POST['can_products']) ? 1 : 0;
        $inv   = isset($_POST['can_inventory']) ? 1 : 0;
        $ord   = isset($_POST['can_orders']) ? 1 : 0;
        $cust  = isset($_POST['can_customers']) ? 1 : 0;
        $book  = isset($_POST['can_bookings']) ? 1 : 0;
        $serv  = isset($_POST['can_services']) ? 1 : 0;
        $ful   = isset($_POST['can_fulfillment']) ? 1 : 0;
        $rep   = isset($_POST['can_reports']) ? 1 : 0;

        $stmt = $pdo->prepare("INSERT INTO manager_permissions (user_id, can_dashboard, can_products, can_inventory, can_orders, can_customers, can_bookings, can_services, can_fulfillment, can_reports) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE can_dashboard=VALUES(can_dashboard), can_products=VALUES(can_products), can_inventory=VALUES(can_inventory), can_orders=VALUES(can_orders), can_customers=VALUES(can_customers), can_bookings=VALUES(can_bookings), can_services=VALUES(can_services), can_fulfillment=VALUES(can_fulfillment), can_reports=VALUES(can_reports)");

        $success = $stmt->execute([$managerId, $dash, $prod, $inv, $ord, $cust, $book, $serv, $ful, $rep]);
        logActivity($pdo, 'Updated Manager Permissions', 'managers', $managerId, "Modified permissions for manager ID {$managerId}");

        $message = $success ? 'Permissions updated successfully!' : 'Failed to update permissions.';
        $msgType = $success ? 'success' : 'error';
    }
}

// Fetch managers
$managers = [];
try {
    $stmt = $pdo->query("SELECT u.id, u.username, mp.* FROM users u LEFT JOIN manager_permissions mp ON u.id = mp.user_id WHERE u.role = 'manager'");
    $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

$pageTitle = "Manager Permissions Control - Reshma Beauty Parlour";
require_once __DIR__ . '/../includes/components/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8 flex-1 w-full space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">Manager Access Control & Permissions</h1>
            <p class="text-xs text-slate-500">Configure exact operational access rights for each manager account.</p>
        </div>
        <div class="flex gap-2">
            <a href="manage_admins.php" class="text-xs bg-purple-600 text-white px-3 py-2 rounded-lg font-bold">Manage Accounts</a>
            <a href="index.php" class="text-xs bg-slate-200 px-3 py-2 rounded-lg font-bold text-slate-700">&larr; Dashboard</a>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="p-4 rounded-xl text-sm font-medium <?= $msgType === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="space-y-6">
        <?php if (empty($managers)): ?>
            <div class="bg-white p-8 rounded-2xl text-center text-slate-400 border">No manager accounts found. Create one via the Admin Portal first.</div>
            <?php else: foreach ($managers as $mgr): ?>
                <div class="bg-white p-6 rounded-2xl border shadow-sm space-y-4">
                    <div class="flex justify-between items-center border-b pb-3">
                        <h3 class="font-bold text-lg text-slate-800"><i class="fa-solid fa-user-shield text-indigo-600 mr-2"></i><?= htmlspecialchars($mgr['username']) ?> <span class="text-xs text-slate-400 font-mono">(ID: #<?= $mgr['id'] ?>)</span></h3>
                    </div>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="update_permissions">
                        <input type="hidden" name="user_id" value="<?= $mgr['id'] ?>">

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs font-medium text-slate-700">
                            <label class="flex items-center gap-2 bg-slate-50 p-2.5 rounded-lg border"><input type="checkbox" name="can_dashboard" value="1" <?= ($mgr['can_dashboard'] ?? 1) ? 'checked' : '' ?>> Dashboard</label>
                            <label class="flex items-center gap-2 bg-slate-50 p-2.5 rounded-lg border"><input type="checkbox" name="can_products" value="1" <?= ($mgr['can_products'] ?? 0) ? 'checked' : '' ?>> Products</label>
                            <label class="flex items-center gap-2 bg-slate-50 p-2.5 rounded-lg border"><input type="checkbox" name="can_inventory" value="1" <?= ($mgr['can_inventory'] ?? 0) ? 'checked' : '' ?>> Inventory</label>
                            <label class="flex items-center gap-2 bg-slate-50 p-2.5 rounded-lg border"><input type="checkbox" name="can_orders" value="1" <?= ($mgr['can_orders'] ?? 0) ? 'checked' : '' ?>> Orders</label>
                            <label class="flex items-center gap-2 bg-slate-50 p-2.5 rounded-lg border"><input type="checkbox" name="can_customers" value="1" <?= ($mgr['can_customers'] ?? 0) ? 'checked' : '' ?>> Customers</label>
                            <label class="flex items-center gap-2 bg-slate-50 p-2.5 rounded-lg border"><input type="checkbox" name="can_bookings" value="1" <?= ($mgr['can_bookings'] ?? 0) ? 'checked' : '' ?>> Bookings</label>
                            <label class="flex items-center gap-2 bg-slate-50 p-2.5 rounded-lg border"><input type="checkbox" name="can_services" value="1" <?= ($mgr['can_services'] ?? 0) ? 'checked' : '' ?>> Services</label>
                            <label class="flex items-center gap-2 bg-slate-50 p-2.5 rounded-lg border"><input type="checkbox" name="can_fulfillment" value="1" <?= ($mgr['can_fulfillment'] ?? 0) ? 'checked' : '' ?>> Fulfillment</label>
                        </div>

                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-lg transition shadow-sm">Save Permissions</button>
                    </form>
                </div>
        <?php endforeach;
        endif; ?>
    </div>
</main>
</body>

</html>