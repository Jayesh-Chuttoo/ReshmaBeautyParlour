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

/**
 * Revoke admin/manager privileges by downgrading the account role to 'user'
 * without deleting the user record.
 */
function revokeAdminPrivileges(PDO $pdo, int $targetUserId, int $currentAdminId): array
{
    if ($targetUserId <= 0) {
        return ['ok' => false, 'msg' => 'Invalid user ID.'];
    }

    if ($targetUserId === $currentAdminId) {
        return ['ok' => false, 'msg' => 'You cannot remove your own admin privileges.'];
    }

    $stmt = $pdo->prepare("UPDATE users SET role = 'user' WHERE id = ? AND role IN ('admin', 'manager')");
    $stmt->execute([$targetUserId]);

    if ($stmt->rowCount() > 0) {
        return ['ok' => true, 'msg' => "Privileges removed for user #{$targetUserId}. Their account remains active as a standard user."];
    }

    return ['ok' => false, 'msg' => 'User was not found or no privilege change was needed.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Create New Admin / Manager
    if ($action === 'create_admin') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = trim($_POST['role'] ?? 'manager');

        if (!empty($username) && !empty($password)) {
            if (!in_array($role, ['admin', 'manager'], true)) {
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

    // Revoke Admin / Manager Privileges (keep account)
    if ($action === 'revoke_admin') {
        $targetUserId = (int)($_POST['target_user_id'] ?? 0);
        $currentAdminId = (int)$_SESSION['user_id'];

        $result = revokeAdminPrivileges($pdo, $targetUserId, $currentAdminId);
        $message = $result['msg'];
        $msgType = $result['ok'] ? 'success' : 'error';
    }
}

// Fetch all admins and managers
$adminUsers = [];
try {
    $stmt = $pdo->query("SELECT id, username, role, created_at FROM users WHERE role IN ('admin', 'manager') ORDER BY id DESC");
    $adminUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

$pageTitle = "Admin & Manager Management - Reshma Beauty Parlour";
$pageCss = "manageAdmin.css";
require_once __DIR__ . '/../../includes/components/header.php';
?>

<main class="manage-admin-page">
    <div class="page-header">
        <div>
            <h1 class="page-title">Administrator &amp; Manager Portal</h1>
            <p class="page-subtitle">Securely create and oversee privileged system operators.</p>
        </div>
        <div class="header-actions">
            <a href="users.php" class="nav-pill nav-pill-secondary">&larr; View Customers</a>
            <a href="index.php" class="nav-pill nav-pill-primary">Dashboard</a>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert-banner <?= $msgType === 'success' ? 'alert-success' : 'alert-error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Side-by-Side Layout: Create Form (Left) & Active Admins Table (Right) -->
    <div class="admin-split-grid">
        <!-- Create Admin Form -->
        <section class="panel-card create-admin-panel" aria-labelledby="createAdminHeading">
            <h2 id="createAdminHeading" class="panel-title">
                <i class="fa-solid fa-user-plus panel-icon"></i>
                Create New Admin / Manager Account
            </h2>

            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="create_admin">

                <div class="form-row-2col">
                    <div class="form-group">
                        <label for="adminUsername" class="form-label">Username</label>
                        <input id="adminUsername" type="text" name="username" required class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="adminPassword" class="form-label">Password</label>
                        <input id="adminPassword" type="password" name="password" required class="form-input">
                    </div>
                </div>

                <div class="form-group">
                    <label for="adminRole" class="form-label">Permission Role</label>
                    <select id="adminRole" name="role" class="form-select">
                        <option value="manager">Manager (Operational Access)</option>
                        <option value="admin">Admin (Full System Control)</option>
                    </select>
                </div>

                <button type="submit" class="submit-admin-btn">Create Privileged Account</button>
            </form>
        </section>

        <!-- Existing Admins List -->
        <section class="panel-card admin-list-panel" aria-labelledby="activeAdminsHeading">
            <div class="panel-header">
                <h2 id="activeAdminsHeading" class="panel-header-title">Active Administrators &amp; Managers</h2>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role Level</th>
                            <th>Created Date</th>
                            <th>Remove Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($adminUsers)): ?>
                            <tr>
                                <td colspan="5" class="empty-state">No administrators or managers found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($adminUsers as $adm): ?>
                                <?php $isSelf = ((int)$adm['id'] === (int)$_SESSION['user_id']); ?>
                                <tr>
                                    <td class="col-id">#<?= (int)$adm['id'] ?></td>
                                    <td class="col-username"><?= htmlspecialchars($adm['username']) ?></td>
                                    <td>
                                        <span class="role-badge <?= $adm['role'] === 'admin' ? 'role-admin' : 'role-manager' ?>">
                                            <?= htmlspecialchars(ucfirst($adm['role'])) ?>
                                        </span>
                                    </td>
                                    <td class="col-date"><?= htmlspecialchars($adm['created_at'] ?? 'N/A') ?></td>
                                    <td class="col-action">
                                        <?php if ($isSelf): ?>
                                            <span class="current-session-label">Current session</span>
                                        <?php else: ?>
                                            <form method="POST" onsubmit="return confirm('Remove admin/manager privileges for <?= htmlspecialchars(addslashes($adm['username'])) ?>? Their account will stay active.');" class="inline-form">
                                                <input type="hidden" name="action" value="revoke_admin">
                                                <input type="hidden" name="target_user_id" value="<?= (int)$adm['id'] ?>">
                                                <button type="submit" title="Remove admin privileges" class="revoke-btn" aria-label="Remove admin privileges for <?= htmlspecialchars($adm['username'], ENT_QUOTES, 'UTF-8') ?>">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                                    </svg>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
</body>

</html>