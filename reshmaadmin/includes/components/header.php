<?php
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'manager'])) {
    header("Location: ../public/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin Dashboard - Reshma Beauty Parlour') ?></title>
    <!-- Tailwind CSS v4 -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Salon Theme CSS -->
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>

<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">
    <!-- Top Navigation Bar -->
    <header class="bg-gradient-to-r from-indigo-900 via-indigo-950 to-slate-900 text-white shadow-lg sticky top-0 z-30 border-b border-indigo-800/50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col sm:flex-row justify-between items-center gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-pink-500/20 border border-pink-400/30 flex items-center justify-center text-pink-400 shadow-inner">
                    <i class="fa-solid fa-spa text-xl"></i>
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-bold tracking-wide flex items-center gap-2">
                        Reshma Beauty Parlour
                        <span class="text-[10px] uppercase tracking-wider bg-gradient-to-r from-pink-500 to-rose-500 text-white px-2 py-0.5 rounded-full font-semibold shadow-sm">Admin Portal</span>
                    </h1>
                    <p class="text-xs text-indigo-300 font-light">Inventory, Bookings & Operations Command Center</p>
                </div>
            </div>

            <div class="flex items-center space-x-3 bg-indigo-900/60 px-3.5 py-1.5 rounded-xl border border-indigo-700/50">
                <div class="flex items-center space-x-2 text-xs sm:text-sm text-indigo-200">
                    <i class="fa-solid fa-user-shield text-pink-400"></i>
                    <span class="font-medium"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
                </div>
                <div class="h-4 w-[1px] bg-indigo-700"></div>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <a href="manage_admins.php" class="text-xs bg-indigo-800 hover:bg-indigo-700 px-2.5 py-1 rounded-lg text-indigo-100 font-medium transition flex items-center gap-1 shadow-sm"><i class="fa-solid fa-users-gear"></i> Admins</a>
                <?php endif; ?>
                <a href="../public/logout.php" class="text-xs bg-rose-600 hover:bg-rose-700 px-3 py-1 rounded-lg text-white font-medium transition flex items-center gap-1 shadow-sm"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    </header>