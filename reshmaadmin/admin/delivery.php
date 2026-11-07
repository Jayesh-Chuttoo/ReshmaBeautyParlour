<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_queries.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'manager'])) {
    header("Location: ../public/login.php");
    exit;
}

$message = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_fulfillment') {
        $code = strtolower(trim($_POST['code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        $fee = floatval($_POST['fee'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (!empty($name) && !empty($code)) {
            $stmt = $pdo->prepare("INSERT INTO fulfillment_methods (code, name, fee, description, is_active) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), fee=VALUES(fee), description=VALUES(description), is_active=VALUES(is_active)");
            $success = $stmt->execute([$code, $name, $fee, $description, $isActive]);
            $message = $success ? 'Delivery method saved successfully!' : 'Failed to save delivery method.';
            $msgType = $success ? 'success' : 'error';
        }
    }
}

$fulfillmentMethods = [];
try {
    $fStmt = $pdo->query("SELECT * FROM fulfillment_methods ORDER BY id ASC");
    $fulfillmentMethods = $fStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

$pageTitle = "Delivery Rates Management - Reshma Beauty Parlour";
require_once __DIR__ . '/../includes/components/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8 flex-1 w-full space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">Delivery & Fulfillment</h1>
            <p class="text-xs text-slate-500">Configure island-wide delivery charges and pickup rules.</p>
        </div>
        <a href="index.php" class="text-xs bg-slate-200 hover:bg-slate-300 px-3 py-2 rounded-lg font-bold text-slate-700 transition">&larr; Back to Dashboard</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="p-4 rounded-xl text-sm font-medium <?= $msgType === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Add / Update Delivery Method Form -->
    <div class="bg-white p-6 rounded-2xl border shadow-sm space-y-4">
        <h2 class="text-lg font-bold text-slate-800">Add or Update Delivery Option</h2>
        <form method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <input type="hidden" name="action" value="add_fulfillment">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Code (e.g. standard)</label>
                <input type="text" name="code" required class="w-full text-sm border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Method Name</label>
                <input type="text" name="name" required class="w-full text-sm border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Fee (Rs)</label>
                <input type="number" step="0.01" name="fee" required class="w-full text-sm border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Description</label>
                <input type="text" name="description" class="w-full text-sm border rounded-lg px-3 py-2">
            </div>
            <div class="sm:col-span-2 flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4">
                <label class="text-xs font-semibold text-slate-600">Active (Available at Checkout)</label>
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2.5 rounded-lg transition shadow-sm">Save Delivery Method</button>
            </div>
        </form>
    </div>

    <!-- Delivery List -->
    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b text-xs uppercase text-slate-600">
                <tr>
                    <th class="p-4">Code</th>
                    <th class="p-4">Method Name</th>
                    <th class="p-4">Fee</th>
                    <th class="p-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                <?php foreach ($fulfillmentMethods as $fm): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="p-4 font-mono text-indigo-600"><?= htmlspecialchars($fm['code']) ?></td>
                        <td class="p-4 font-bold text-slate-800"><?= htmlspecialchars($fm['name']) ?></td>
                        <td class="p-4 text-emerald-600 font-extrabold"><?= (float)$fm['fee'] == 0 ? 'Free' : 'Rs ' . number_format($fm['fee'], 2) ?></td>
                        <td class="p-4"><span class="px-2.5 py-1 text-xs rounded-full font-bold <?= $fm['is_active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>"><?= $fm['is_active'] ? 'Active' : 'Disabled' ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
</body>

</html>