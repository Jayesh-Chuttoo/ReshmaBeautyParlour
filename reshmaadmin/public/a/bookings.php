<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db_queries.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'manager'], true)) {
    header("Location: ../public/index.php");
    exit;
}

$message = '';
$msgType = 'success';

$searchQuery    = trim($_GET['q'] ?? '');
$statusFilter   = trim($_GET['status'] ?? '');
$whatsappFilter = trim($_GET['whatsapp'] ?? '');
$sortBy         = trim($_GET['sort'] ?? 'newest');

$allowedStatuses = ['Pending', 'Confirmed', 'Rescheduled', 'Completed', 'Cancelled'];

// Handle Booking Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_booking_status') {
    $bookingId = intval($_POST['booking_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? '');

    if ($bookingId > 0 && in_array($newStatus, $allowedStatuses, true)) {
        $success = updateBookingStatusById($pdo, $bookingId, $newStatus);
        $message = $success ? "Booking #{$bookingId} status updated to '{$newStatus}'." : 'Failed to update status.';
        $msgType = $success ? 'success' : 'error';
    } else {
        $message = 'Invalid booking or status selection.';
        $msgType = 'error';
    }
}

$bookings = getFilteredBookings($pdo, $searchQuery, $statusFilter, $whatsappFilter, $sortBy);

$pageTitle = "Bookings Management - Reshma Beauty Parlour";
$pageCss = "bookings.css";
require_once __DIR__ . '/../../includes/components/header.php';
?>

<main class="bookings-page">
    <div class="bookings-header">
        <div>
            <h1 class="bookings-title">Client Appointments &amp; Bookings</h1>
            <p class="bookings-subtitle">Manage client reservations, confirm appointments, and track booking statuses.</p>
        </div>
        <a href="index.php" class="bookings-back-link">&larr; Back to Dashboard</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="bookings-alert <?= $msgType === 'success' ? 'bookings-alert-success' : 'bookings-alert-error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Search, Filter & Sort Controls -->
    <form method="GET" class="bookings-filter-bar">
        <div>
            <label for="bookingSearch" class="sr-only">Search bookings</label>
            <input
                id="bookingSearch"
                type="text"
                name="q"
                value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Search ID, client, service, or contact..."
                class="bookings-search-input">
        </div>

        <div>
            <label for="statusFilter" class="sr-only">Filter by status</label>
            <select id="statusFilter" name="status" class="bookings-filter-select">
                <option value="">All Statuses</option>
                <?php foreach ($allowedStatuses as $st): ?>
                    <option value="<?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?>" <?= $statusFilter === $st ? 'selected' : '' ?>>
                        <?= htmlspecialchars($st) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="whatsappFilter" class="sr-only">Filter by WhatsApp</label>
            <select id="whatsappFilter" name="whatsapp" class="bookings-filter-select">
                <option value="">All Contacts</option>
                <option value="yes" <?= $whatsappFilter === 'yes' ? 'selected' : '' ?>>WhatsApp Enabled</option>
                <option value="no" <?= $whatsappFilter === 'no' ? 'selected' : '' ?>>Non-WhatsApp</option>
            </select>
        </div>

        <div class="bookings-controls-row">
            <label for="bookingSort" class="sr-only">Sort bookings</label>
            <select id="bookingSort" name="sort" class="bookings-filter-select">
                <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newest Created</option>
                <option value="oldest" <?= $sortBy === 'oldest' ? 'selected' : '' ?>>Oldest Created</option>
                <option value="date_asc" <?= $sortBy === 'date_asc' ? 'selected' : '' ?>>Appointment Date (Soonest)</option>
                <option value="date_desc" <?= $sortBy === 'date_desc' ? 'selected' : '' ?>>Appointment Date (Latest)</option>
                <option value="client_asc" <?= $sortBy === 'client_asc' ? 'selected' : '' ?>>Client (A–Z)</option>
                <option value="client_desc" <?= $sortBy === 'client_desc' ? 'selected' : '' ?>>Client (Z–A)</option>
                <option value="service_asc" <?= $sortBy === 'service_asc' ? 'selected' : '' ?>>Service (A–Z)</option>
                <option value="status_asc" <?= $sortBy === 'status_asc' ? 'selected' : '' ?>>Status (A–Z)</option>
            </select>

            <button type="submit" class="bookings-apply-btn">Apply</button>

            <?php if ($searchQuery !== '' || $statusFilter !== '' || $whatsappFilter !== '' || $sortBy !== 'newest'): ?>
                <a href="bookings.php" class="bookings-reset-btn">Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="bookings-table-card">
        <table class="bookings-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client Name</th>
                    <th>Service Name</th>
                    <th>Contact</th>
                    <th>Scheduled Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="7" class="bookings-empty-state">
                            <?= ($searchQuery !== '' || $statusFilter !== '' || $whatsappFilter !== '') ? 'No matching bookings found.' : 'No bookings recorded.' ?>
                        </td>
                    </tr>
                    <?php else: foreach ($bookings as $b):
                        $bStatus = $b['status'] ?? 'Pending';
                        $badgeClass = match ($bStatus) {
                            'Completed'   => 'booking-status-completed',
                            'Confirmed'   => 'booking-status-confirmed',
                            'Rescheduled' => 'booking-status-rescheduled',
                            'Cancelled'   => 'booking-status-cancelled',
                            default       => 'booking-status-pending',
                        };
                    ?>
                        <tr>
                            <td class="booking-col-id">#<?= (int)$b['id'] ?></td>
                            <td class="booking-client-name"><?= htmlspecialchars($b['client_name'] ?? '') ?></td>
                            <td class="booking-service-name"><?= htmlspecialchars($b['service_name'] ?? '') ?></td>
                            <td class="booking-contact">
                                <?= htmlspecialchars($b['contact'] ?? '') ?>
                                <?php if (!empty($b['is_whatsapp'])): ?>
                                    <span class="booking-whatsapp-icon" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></span>
                                <?php endif; ?>
                            </td>
                            <td class="booking-date"><?= htmlspecialchars($b['booking_date'] ?? '') ?></td>
                            <td>
                                <span class="booking-status-badge <?= $badgeClass ?>">
                                    <?= htmlspecialchars($bStatus) ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" class="booking-update-form">
                                    <input type="hidden" name="action" value="update_booking_status">
                                    <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                                    <select name="status" class="booking-status-select">
                                        <?php foreach ($allowedStatuses as $st): ?>
                                            <option value="<?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?>" <?= $bStatus === $st ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($st) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="booking-save-btn">Save</button>
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