<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_queries.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'manager'])) {
    header("Location: ../public/login.php");
    exit;
}

// Fetch users with their respective order counts
$users = [];
try {
    $stmt = $pdo->query("
        SELECT u.*, 
               (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) as order_count 
        FROM users u 
        ORDER BY u.id DESC
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Fallback if user_id column isn't linked in orders table yet
    $users = function_exists('getAllUsers') ? getAllUsers($pdo) : [];
}

$pageTitle = "Customer Accounts & Order Tracking - Reshma Beauty Parlour";
require_once __DIR__ . '/../includes/components/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8 flex-1 w-full space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">Customer Accounts & Order Analytics</h1>
            <p class="text-xs text-slate-500">View registered clients and track how many orders each customer has placed.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="manage_admins.php" class="text-xs bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg font-bold transition shadow-sm"><i class="fa-solid fa-users-gear mr-1"></i> Manage Admins</a>
            <a href="index.php" class="text-xs bg-slate-200 hover:bg-slate-300 px-3 py-2 rounded-lg font-bold text-slate-700 transition">&larr; Dashboard</a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b text-xs uppercase text-slate-600">
                <tr>
                    <th class="p-4">ID</th>
                    <th class="p-4">Username</th>
                    <th class="p-4">Phone Number</th>
                    <th class="p-4">Registered Date</th>
                    <th class="p-4">Total Orders Placed</th>
                    <th class="p-4">Account Role</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-400">No registered users found.</td>
                    </tr>
                    <?php else: foreach ($users as $u):
                        $role = $u['role'] ?? 'customer';
                        $orderCount = $u['order_count'] ?? 0;
                    ?>
                        <tr class="hover:bg-slate-50">
                            <td class="p-4 font-mono text-slate-400">#<?= $u['id'] ?></td>
                            <td class="p-4 font-bold text-slate-800"><?= htmlspecialchars($u['username']) ?></td>
                            <td class="p-4 font-mono text-slate-600"><?= htmlspecialchars($u['phone'] ?? 'N/A') ?></td>
                            <td class="p-4 text-xs text-slate-500"><?= htmlspecialchars($u['created_at'] ?? 'N/A') ?></td>
                            <td class="p-4">
                                <span class="px-3 py-1 text-xs rounded-full font-extrabold <?= $orderCount > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500' ?>">
                                    <?= $orderCount ?> <?= $orderCount == 1 ? 'Order' : 'Orders' ?>
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 text-xs rounded-full font-bold <?= $role === 'admin' ? 'bg-purple-100 text-purple-800' : ($role === 'manager' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800') ?>">
                                    <?= htmlspecialchars(ucfirst($role)) ?>
                                </span>
                            </td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>

</html>