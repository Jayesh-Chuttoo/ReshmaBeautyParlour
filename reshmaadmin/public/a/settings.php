<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/admin_auth.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../public/index.php");
    exit;
}

$message = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bName = trim($_POST['business_name'] ?? '');
    $bPhone = trim($_POST['business_phone'] ?? '');
    $bEmail = trim($_POST['business_email'] ?? '');
    $bAddress = trim($_POST['business_address'] ?? '');

    if (!empty($bName)) {
        $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('business_name', ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")->execute([$bName]);
        $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('business_phone', ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")->execute([$bPhone]);
        $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('business_email', ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")->execute([$bEmail]);
        $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('business_address', ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")->execute([$bAddress]);

        logActivity($pdo, 'Updated System Settings', 'settings', null, 'Modified general business settings');
        $message = 'Settings updated successfully!';
        $msgType = 'success';
    }
}

// Fetch settings
$settings = [];
try {
    $stmt = $pdo->query("SELECT * FROM system_settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
}

$pageTitle = "System Settings - Reshma Beauty Parlour";
require_once __DIR__ . '/../../includes/components/header.php';
?>

<main class="max-w-4xl mx-auto px-4 py-8 flex-1 w-full space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">System & Business Settings</h1>
            <p class="text-xs text-slate-500">Configure core business information and operational parameters.</p>
        </div>
        <a href="index.php" class="text-xs bg-slate-200 hover:bg-slate-300 px-3 py-2 rounded-lg font-bold text-slate-700 transition">&larr; Dashboard</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="p-4 rounded-xl text-sm font-medium <?= $msgType === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded-2xl border shadow-sm">
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Business Name</label>
                <input type="text" name="business_name" value="<?= htmlspecialchars($settings['business_name'] ?? 'Reshma Beauty Parlour') ?>" required class="w-full text-sm border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Business Phone Number</label>
                <input type="text" name="business_phone" value="<?= htmlspecialchars($settings['business_phone'] ?? '') ?>" class="w-full text-sm border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Business Email</label>
                <input type="email" name="business_email" value="<?= htmlspecialchars($settings['business_email'] ?? '') ?>" class="w-full text-sm border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Business Address</label>
                <input type="text" name="business_address" value="<?= htmlspecialchars($settings['business_address'] ?? '') ?>" class="w-full text-sm border rounded-lg px-3 py-2">
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-5 py-2.5 rounded-lg transition shadow-sm">Save Settings</button>
        </form>
    </div>
</main>
</body>

</html>