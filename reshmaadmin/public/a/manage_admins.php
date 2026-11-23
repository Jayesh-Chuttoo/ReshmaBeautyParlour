<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db_queries.php';

// Restrict to logged-in admins only
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../public/index.php");
    exit;
}

$message = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Create New Admin / Manager
    if ($action === 'create_admin') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = trim($_POST['role'] ?? 'manager');

        if (!empty($username) && !empty($password)) {
            if (!in_array($role, ['admin', 'manager'])) {
                $role = 'manager';
            }

            // Check if username already exists
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $checkStmt->execute([$username]);
            if ($checkStmt->fetch()) {
                $message = 'Username already exists. Please choose another.';
                $msgType = 'error';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $success = $stmt->execute([$username, $hashedPassword, $role]);

                $message = $success ? "New {$role} account '{$username}' created successfully!" : 'Failed to create administrator account.';
                $msgType = $success ? 'success' : 'error';
            }
        } else {
            $message = 'Please provide both username and password.';
            $msgType = 'error';
        }
    }
}

// Fetch all admins and managers
$adminUsers = [];
try {
    $stmt = $pdo->query("SELECT * FROM users WHERE role IN ('admin', 'manager') ORDER BY id DESC");
    $adminUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

$pageTitle = "Admin & Manager Management - Reshma Beauty Parlour";
require_once __DIR__ . '/../../includes/components/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8 flex-1 w-full space-y-8">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">Administrator & Manager Portal</h1>
            <p class="text-xs text-slate-500">Securely create and oversee privileged system operators.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="users.php" class="text-xs bg-slate-200 hover:bg-slate-300 px-3 py-2 rounded-lg font-bold text-slate-700 transition">&larr; View Customers</a>
            <a href="index.php" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-lg font-bold transition">Dashboard</a>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="p-4 rounded-xl text-sm font-medium <?= $msgType === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Create Admin Form -->
    <div class="bg-white p-6 rounded-2xl border shadow-sm space-y-4 max-w-2xl">
        <h2 class="text-lg font-bold text-slate-800"><i class="fa-solid fa-user-plus mr-2 text-purple-600"></i>Create New Admin / Manager Account</h2>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="create_admin">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Username</label>
                    <input type="text" name="username" required class="w-full text-sm border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Password</label>
                    <input type="password" name="password" required class="w-full text-sm border rounded-lg px-3 py-2">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Permission Role</label>
                <select name="role" class="w-full text-sm border rounded-lg px-3 py-2 bg-white font-medium">
                    <option value="manager">Manager (Operational Access)</option>
                    <option value="admin">Admin (Full System Control)</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold py-2.5 rounded-lg transition shadow-sm">Create Privileged Account</button>
        </form>
    </div>

    <!-- Existing Admins List -->
    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b font-bold text-slate-700 text-sm">Active Administrators & Managers</div>
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50/50 border-b text-xs uppercase text-slate-600">
                <tr>
                    <th class="p-4">ID</th>
                    <th class="p-4">Username</th>
                    <th class="p-4">Role Level</th>
                    <th class="p-4">Created Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                <?php foreach ($adminUsers as $adm): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="p-4 font-mono text-slate-400">#<?= $adm['id'] ?></td>
                        <td class="p-4 font-bold text-slate-800"><?= htmlspecialchars($adm['username']) ?></td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 text-xs rounded-full font-bold <?= $adm['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' ?>">
                                <?= htmlspecialchars(ucfirst($adm['role'])) ?>
                            </span>
                        </td>
                        <td class="p-4 text-xs text-slate-500"><?= htmlspecialchars($adm['created_at'] ?? 'N/A') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
</body>

</html>