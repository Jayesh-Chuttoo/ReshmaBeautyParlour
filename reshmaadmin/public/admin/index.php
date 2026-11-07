<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/admin_auth.php';

requireAdminSession();
if (!checkPermission('dashboard') && ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: orders.php");
    exit;
}

// Fetch totals reliably via direct queries with fallbacks
$totalOrders = 0;
$totalBookings = 0;
$totalProducts = 0;

try {
    $totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0;
} catch (Exception $e) {
}

try {
    // Counts only active bookings (excludes Completed and Cancelled)
    $totalBookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status NOT IN ('Completed', 'Cancelled')")->fetchColumn() ?: 0;
} catch (Exception $e) {
    $totalBookings = 0;
}

try {
    $totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() ?: 0;
} catch (Exception $e) {
    $totalProducts = count(function_exists('getAllProducts') ? getAllProducts($pdo) : []);
}

$pageTitle = "Admin Dashboard - Reshma Beauty Parlour";
require_once __DIR__ . '/../../includes/components/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8 flex-1 w-full space-y-8">
    <div class="bg-gradient-to-r from-indigo-900 to-slate-900 text-white p-8 rounded-3xl shadow-xl flex flex-col md:flex-row justify-between items-center gap-6">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight">Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>!</h1>
            <p class="text-indigo-200 mt-1 text-sm">Reshma Beauty Parlour Management System — Petit Raffray, Mauritius</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-4 py-1.5 bg-pink-500/20 border border-pink-400/30 text-pink-300 rounded-full text-xs font-bold uppercase tracking-wider">Role: <?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Manager')) ?></span>
        </div>
    </div>

    <!-- KPI Analytics Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <a href="orders.php" class="bg-gradient-to-br from-indigo-600 to-indigo-800 text-white p-6 rounded-2xl shadow-lg hover:scale-[1.02] transition block">
            <div class="flex justify-between items-center text-indigo-200"><span class="text-xs uppercase font-semibold">Orders</span><i class="fa-solid fa-bag-shopping text-xl"></i></div>
            <div class="text-3xl font-extrabold mt-3"><?= $totalOrders ?></div>
        </a>
        <a href="products.php" class="bg-gradient-to-br from-pink-500 to-rose-600 text-white p-6 rounded-2xl shadow-lg hover:scale-[1.02] transition block">
            <div class="flex justify-between items-center text-pink-100"><span class="text-xs uppercase font-semibold">Products</span><i class="fa-solid fa-box-open text-xl"></i></div>
            <div class="text-3xl font-extrabold mt-3"><?= $totalProducts ?></div>
        </a>
        <a href="bookings.php" class="bg-gradient-to-br from-blue-600 to-teal-700 text-white p-6 rounded-2xl shadow-lg hover:scale-[1.02] transition block">
            <div class="flex justify-between items-center text-blue-100"><span class="text-xs uppercase font-semibold">Bookings</span><i class="fa-solid fa-calendar-check text-xl"></i></div>
            <div class="text-3xl font-extrabold mt-3"><?= $totalBookings ?></div>
        </a>
    </div>

    <!-- Module Navigation Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="orders.php" class="bg-white p-5 rounded-2xl border shadow-sm hover:border-indigo-500 transition space-y-2 block">
            <i class="fa-solid fa-cart-shopping text-indigo-600 text-xl"></i>
            <h3 class="font-bold text-slate-800">Orders Management</h3>
            <p class="text-xs text-slate-500">Track fulfillment and order statuses.</p>
        </a>
        <a href="products.php" class="bg-white p-5 rounded-2xl border shadow-sm hover:border-pink-500 transition space-y-2 block">
            <i class="fa-solid fa-box-open text-pink-600 text-xl"></i>
            <h3 class="font-bold text-slate-800">Products & Catalog</h3>
            <p class="text-xs text-slate-500">Add products, update pricing and stock.</p>
        </a>
        <a href="services.php" class="bg-white p-5 rounded-2xl border shadow-sm hover:border-purple-500 transition space-y-2 block">
            <i class="fa-solid fa-scissors text-purple-600 text-xl"></i>
            <h3 class="font-bold text-slate-800">Salon Services</h3>
            <p class="text-xs text-slate-500">Manage beauty treatments and standard rates.</p>
        </a>
        <a href="delivery.php" class="bg-white p-5 rounded-2xl border shadow-sm hover:border-blue-500 transition space-y-2 block">
            <i class="fa-solid fa-truck-fast text-blue-600 text-xl"></i>
            <h3 class="font-bold text-slate-800">Delivery Rates</h3>
            <p class="text-xs text-slate-500">Configure island-wide fulfillment charges.</p>
        </a>
        <a href="bookings.php" class="bg-white p-5 rounded-2xl border shadow-sm hover:border-teal-500 transition space-y-2 block">
            <i class="fa-solid fa-calendar-days text-teal-600 text-xl"></i>
            <h3 class="font-bold text-slate-800">Client Bookings</h3>
            <p class="text-xs text-slate-500">Review scheduled appointments.</p>
        </a>
        <a href="users.php" class="bg-white p-5 rounded-2xl border shadow-sm hover:border-amber-500 transition space-y-2 block">
            <i class="fa-solid fa-users text-amber-600 text-xl"></i>
            <h3 class="font-bold text-slate-800">Customer Accounts</h3>
            <p class="text-xs text-slate-500">View clients and their total order counts.</p>
        </a>
    </div>

    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
        <div class="bg-indigo-50 border border-indigo-200 p-5 rounded-2xl flex justify-between items-center">
            <div>
                <h4 class="font-bold text-indigo-900 text-sm">Administrator Security & Permissions Hub</h4>
                <p class="text-xs text-indigo-700 mt-0.5">Manage operator accounts, assign granular permissions, and inspect audit logs.</p>
            </div>
            <div class="flex gap-2">
                <a href="manage_admins.php" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3 py-2 rounded-lg transition">Manage Admins</a>
                <a href="manage_permissions.php" class="bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold px-3 py-2 rounded-lg transition">Permissions</a>
                <a href="activity_logs.php" class="bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold px-3 py-2 rounded-lg transition">Activity Logs</a>
                <a href="settings.php" class="bg-slate-600 hover:bg-slate-700 text-white text-xs font-bold px-3 py-2 rounded-lg transition">Settings</a>
            </div>
        </div>
    <?php endif; ?>
</main>
</body>

</html>