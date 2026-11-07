<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/admin_auth.php';

requireAdminSession();
if (!checkPermission('reports') && ($_SESSION['role'] ?? '') !== 'admin') {
    die("<div style='padding:2rem;font-family:sans-serif;'><h2>Access Denied</h2><p>You do not have permission to view activity logs.</p><a href='index.php'>Back to Dashboard</a></div>");
}

$logs = [];
try {
    $stmt = $pdo->query("SELECT * FROM activity_logs ORDER BY id DESC LIMIT 100");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

$pageTitle = "Activity Logs & Audit Trail - Reshma Beauty Parlour";
require_once __DIR__ . '/../includes/components/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8 flex-1 w-full space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">System Activity Logs & Audit Trail</h1>
            <p class="text-xs text-slate-500">Immutable record of all administrative and operational actions performed.</p>
        </div>
        <a href="index.php" class="text-xs bg-slate-200 hover:bg-slate-300 px-3 py-2 rounded-lg font-bold text-slate-700 transition">&larr; Dashboard</a>
    </div>

    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs sm:text-sm">
            <thead class="bg-slate-50 border-b text-slate-600 uppercase">
                <tr>
                    <th class="p-4">Timestamp</th>
                    <th class="p-4">Operator</th>
                    <th class="p-4">Role</th>
                    <th class="p-4">Action Performed</th>
                    <th class="p-4">Section</th>
                    <th class="p-4">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-400">No activity logs recorded yet.</td>
                    </tr>
                    <?php else: foreach ($logs as $log): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="p-4 font-mono text-slate-500"><?= htmlspecialchars($log['created_at']) ?></td>
                            <td class="p-4 font-bold text-slate-800"><?= htmlspecialchars($log['user_name']) ?></td>
                            <td class="p-4"><span class="px-2 py-0.5 rounded-full text-xs font-bold <?= $log['user_role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' ?>"><?= htmlspecialchars(ucfirst($log['user_role'])) ?></span></td>
                            <td class="p-4 font-semibold text-indigo-600"><?= htmlspecialchars($log['action_performed']) ?></td>
                            <td class="p-4 text-slate-600 uppercase font-mono text-xs"><?= htmlspecialchars($log['affected_section']) ?></td>
                            <td class="p-4 text-slate-500 text-xs"><?= htmlspecialchars($log['details'] ?? 'N/A') ?></td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>

</html>