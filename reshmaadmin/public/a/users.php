<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db_queries.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'manager'], true)) {
    header("Location: ../public/index.php");
    exit;
}

$isCurrentUserAdmin = (($_SESSION['role'] ?? '') === 'admin');
$currentUserId = (int)$_SESSION['user_id'];
$message = '';
$msgType = 'success';

$searchQuery = trim($_GET['q'] ?? '');
$roleFilter = trim($_GET['role'] ?? '');

// Handle inline role update from the table (admins only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_role') {
    if (!$isCurrentUserAdmin) {
        $message = 'Only administrators can change account roles.';
        $msgType = 'error';
    } else {
        $targetUserId = (int)($_POST['target_user_id'] ?? 0);
        $newRole = trim($_POST['new_role'] ?? '');

        $result = updateUserAccountRole($pdo, $targetUserId, $newRole, $currentUserId);
        $message = $result['msg'];
        $msgType = $result['ok'] ? 'success' : 'error';
    }
}

$users = searchUsersWithOrderCounts($pdo, $searchQuery, $roleFilter);

$pageTitle = "Customer Accounts & Order Tracking - Reshma Beauty Parlour";
$pageCss = "users.css";
require_once __DIR__ . '/../../includes/components/header.php';
?>

<main class="users-page">
    <div class="users-header">
        <div>
            <h1 class="users-title">Customer Accounts &amp; Order Analytics</h1>
            <p class="users-subtitle">Search registered clients, track order counts, and manage account roles.</p>
        </div>
        <div class="users-actions">
            <a href="manage_admins.php" class="users-btn users-btn-purple"><i class="fa-solid fa-users-gear"></i> Manage Admins</a>
            <a href="index.php" class="users-btn users-btn-secondary">&larr; Dashboard</a>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="users-alert <?= $msgType === 'success' ? 'users-alert-success' : 'users-alert-error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Search & Role Filter Bar -->
    <form method="GET" class="users-filter-bar">
        <div class="users-search-wrap">
            <label for="userSearch" class="sr-only">Search users</label>
            <input
                id="userSearch"
                type="text"
                name="q"
                value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Search by ID, username, or phone number..."
                class="users-search-input">
        </div>

        <div class="users-role-wrap">
            <label for="roleFilter" class="sr-only">Filter by role</label>
            <select id="roleFilter" name="role" class="users-role-select">
                <option value="">All Roles</option>
                <option value="customer" <?= $roleFilter === 'customer' ? 'selected' : '' ?>>Customer</option>
                <option value="user" <?= $roleFilter === 'user' ? 'selected' : '' ?>>User</option>
                <option value="manager" <?= $roleFilter === 'manager' ? 'selected' : '' ?>>Manager</option>
                <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <div class="users-filter-buttons">
            <button type="submit" class="users-submit-btn">Search</button>
            <?php if ($searchQuery !== '' || $roleFilter !== ''): ?>
                <a href="users.php" class="users-reset-btn">Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="users-table-card">
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Phone Number</th>
                    <th>Registered Date</th>
                    <th>Total Orders Placed</th>
                    <th>Account Role</th>
                    <?php if ($isCurrentUserAdmin): ?>
                        <th>Change Role</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="<?= $isCurrentUserAdmin ? 7 : 6 ?>" class="users-empty">
                            <?= ($searchQuery !== '' || $roleFilter !== '') ? 'No matching users found.' : 'No registered users found.' ?>
                        </td>
                    </tr>
                    <?php else: foreach ($users as $u):
                        $role = $u['role'] ?? 'customer';
                        $orderCount = (int)($u['order_count'] ?? 0);
                        $isSelf = ((int)$u['id'] === $currentUserId);
                        $roleBadgeClass = $role === 'admin'
                            ? 'users-role-admin'
                            : ($role === 'manager' ? 'users-role-manager' : 'users-role-customer');
                    ?>
                        <tr>
                            <td class="users-col-id">#<?= (int)$u['id'] ?></td>
                            <td class="users-col-username"><?= htmlspecialchars($u['username']) ?></td>
                            <td class="users-col-phone"><?= htmlspecialchars($u['phone'] ?? 'N/A') ?></td>
                            <td class="users-col-date"><?= htmlspecialchars($u['created_at'] ?? 'N/A') ?></td>
                            <td>
                                <span class="users-order-badge <?= $orderCount > 0 ? 'users-order-badge-active' : 'users-order-badge-empty' ?>">
                                    <?= $orderCount ?> <?= $orderCount === 1 ? 'Order' : 'Orders' ?>
                                </span>
                            </td>
                            <td>
                                <span class="users-role-badge <?= $roleBadgeClass ?>">
                                    <?= htmlspecialchars(ucfirst($role)) ?>
                                </span>
                            </td>
                            <?php if ($isCurrentUserAdmin): ?>
                                <td>
                                    <?php if ($isSelf): ?>
                                        <span class="users-current-session">Current session</span>
                                    <?php else: ?>
                                        <form method="POST" class="users-role-form" onsubmit="return confirm('Change role for <?= htmlspecialchars(addslashes($u['username'])) ?>?');">
                                            <input type="hidden" name="action" value="update_role">
                                            <input type="hidden" name="target_user_id" value="<?= (int)$u['id'] ?>">
                                            <input type="hidden" name="q" value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="role_filter" value="<?= htmlspecialchars($roleFilter, ENT_QUOTES, 'UTF-8') ?>">
                                            <select name="new_role" class="users-inline-select">
                                                <option value="customer" <?= $role === 'customer' ? 'selected' : '' ?>>Customer</option>
                                                <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User</option>
                                                <option value="manager" <?= $role === 'manager' ? 'selected' : '' ?>>Manager</option>
                                                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                            <button type="submit" class="users-save-btn">Save</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>

</html>