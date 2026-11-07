<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireAdminSession()
{
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'manager'])) {
        header("Location: ../public/index.php");
        exit;
    }
}

function checkPermission($section)
{
    requireAdminSession();
    if (($_SESSION['role'] ?? '') === 'admin') {
        return true; // Administrators have unrestricted access
    }

    global $pdo;
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM manager_permissions WHERE user_id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $perms = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$perms) {
        return false; // No permissions assigned
    }

    $column = 'can_' . $section;
    return isset($perms[$column]) && $perms[$column] == 1;
}

function logActivity($pdo, $action, $section, $recordId = null, $details = '')
{
    if (!isset($_SESSION['user_id'])) return;
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, user_name, user_role, action_performed, affected_section, affected_record_id, details) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $_SESSION['username'] ?? 'Operator',
            $_SESSION['role'] ?? 'manager',
            $action,
            $section,
            $recordId,
            $details
        ]);
    } catch (Exception $e) {
        // Fail silently to prevent interrupting user actions
    }
}
