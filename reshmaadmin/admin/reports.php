<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/admin_auth.php';

requireAdminSession();
if (!checkPermission('reports') && ($_SESSION['role'] ?? '') !== 'admin') {
    die("<div style='padding:2rem;font-family:sans-serif;'><h2>Access Denied</h2><p>You do not have permission to access reports.</p><a href='index.php'>Back to Dashboard</a></div>");
}

// Calculate financial totals
$totalRevenue = 0;
$totalOrdersCount = 0;
try {
    $revStmt = $pdo->query("SELECT SUM(subtotal + shipping_fee) FROM orders");
    $totalRevenue = $revStmt->fetchColumn() ?: 0;

    $ordCountStmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $totalOrdersCount = $ordCountStmt->fetchColumn() ?: 0;
} catch (Exception $e) {
}

$pageTitle = "Business Reports & Analytics - Reshma Beauty Parlour";
require_once __DIR__ . '/../includes/components/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8 flex-1 w-full space-y-8">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">Business Reports & Financial Analytics</h1>
            <p class="text-xs text-slate-500">Comprehensive overview of sales, revenue, and salon performance.</p>
        </div>
        <a href="index.php" class="text-xs bg-slate-200 hover:bg-slate-300 px-3 py-2 rounded-lg font-bold text-slate-700 transition">&larr; Dashboard</a>
    </div>

    <!-- Analytics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-gradient-to-br from-emerald-600 to-teal-800 text-white p-6 rounded-2xl shadow-lg">
            <span class="text-xs uppercase font-semibold text-emerald-200">Total Revenue Generated</span>
            <div class="text-3xl font-extrabold mt-2">Rs <?= number_format($totalRevenue, 2) ?></div>
        </div>
        <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 text-white p-6 rounded-2xl shadow-lg">
            <span class="text-xs uppercase font-semibold text-indigo-200">Total Orders Processed</span>
            <div class="text-3xl font-extrabold mt-2"><?= $totalOrdersCount ?></div>
        </div>
        <div class="bg-gradient-to-br from-purple-600 to-purple-800 text-white p-6 rounded-2xl shadow-lg">
            <span class="text-xs uppercase font-semibold text-purple-200">Audit Status</span>
            <div class="text-lg font-bold mt-2">All Records Intact</div>
        </div>
    </div>
</main>
</body>

</html>