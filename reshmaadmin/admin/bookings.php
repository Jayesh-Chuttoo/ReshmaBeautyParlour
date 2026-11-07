<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_queries.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'manager'])) {
    header("Location: ../public/login.php");
    exit;
}

$message = '';
$msgType = 'success';

// Handle Booking Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_booking_status') {
    $bookingId = intval($_POST['booking_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? '');
    $allowed = ['Pending', 'Confirmed', 'Rescheduled', 'Completed', 'Cancelled'];

    if ($bookingId > 0 && in_array($newStatus, $allowed)) {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $success = $stmt->execute([$newStatus, $bookingId]);
        $message = $success ? "Booking #{$bookingId} status updated to '{$newStatus}'." : 'Failed to update status.';
        $msgType = $success ? 'success' : 'error';
    }
}

$bookings = [];
try {
    $stmt = $pdo->query("SELECT * FROM bookings ORDER BY id DESC");
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

$pageTitle = "Bookings Management - Reshma Beauty Parlour";
require_once __DIR__ . '/../includes/components/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8 flex-1 w-full space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">Client Appointments & Bookings</h1>
            <p class="text-xs text-slate-500">Manage client reservations, confirm appointments, and track booking statuses.</p>
        </div>
        <a href="index.php" class="text-xs bg-slate-200 hover:bg-slate-300 px-3 py-2 rounded-lg font-bold text-slate-700 transition">&larr; Back to Dashboard</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="p-4 rounded-xl text-sm font-medium <?= $msgType === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b text-xs uppercase text-slate-600">
                <tr>
                    <th class="p-4">ID</th>
                    <th class="p-4">Client Name</th>
                    <th class="p-4">Service Name</th>
                    <th class="p-4">Contact</th>
                    <th class="p-4">Scheduled Date</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="7" class="p-6 text-center text-slate-400">No bookings recorded.</td>
                    </tr>
                    <?php else: foreach ($bookings as $b):
                        $bStatus = $b['status'] ?? 'Pending';
                        $badgeStyle = match ($bStatus) {
                            'Completed'   => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                            'Confirmed'   => 'bg-blue-100 text-blue-800 border-blue-300',
                            'Rescheduled' => 'bg-amber-100 text-amber-800 border-amber-300',
                            'Cancelled'   => 'bg-rose-100 text-rose-800 border-rose-300',
                            default       => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                        };
                    ?>
                        <tr class="hover:bg-slate-50">
                            <td class="p-4 font-mono text-slate-400">#<?= $b['id'] ?></td>
                            <td class="p-4 font-bold text-slate-800"><?= htmlspecialchars($b['client_name']) ?></td>
                            <td class="p-4 text-indigo-600 font-bold"><?= htmlspecialchars($b['service_name']) ?></td>
                            <td class="p-4 font-mono text-slate-600">
                                <?= htmlspecialchars($b['contact']) ?>
                                <?php if (!empty($b['is_whatsapp'])): ?>
                                    <span class="text-emerald-600 font-bold text-xs ml-1"><i class="fa-brands fa-whatsapp"></i></span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-slate-500 text-xs"><?= htmlspecialchars($b['booking_date']) ?></td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 text-xs rounded-full font-bold border <?= $badgeStyle ?>">
                                    <?= htmlspecialchars($bStatus) ?>
                                </span>
                            </td>
                            <td class="p-4">
                                <form method="POST" class="flex items-center gap-2">
                                    <input type="hidden" name="action" value="update_booking_status">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <select name="status" class="text-xs border rounded-lg px-2.5 py-1 bg-white font-medium">
                                        <option value="Pending" <?= $bStatus === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="Confirmed" <?= $bStatus === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                        <option value="Rescheduled" <?= $bStatus === 'Rescheduled' ? 'selected' : '' ?>>Rescheduled</option>
                                        <option value="Completed" <?= $bStatus === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="Cancelled" <?= $bStatus === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1 rounded-lg font-bold transition">Save</button>
                                </form>
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