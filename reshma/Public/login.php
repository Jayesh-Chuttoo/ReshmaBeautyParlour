<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// If user is already logged in, redirect them away (or back to redirect target if specified)
if (isset($_SESSION['user_id'])) {
    $redirectUrl = $_GET['redirect'] ?? 'index.php';
    header("Location: " . $redirectUrl);
    exit;
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/php-error.log');

require_once __DIR__ . '/../Includes/db.php';

$error = '';
$successMessage = '';
$redirectParam = $_GET['redirect'] ?? '';

if (isset($_GET['signup']) && $_GET['signup'] === 'success') {
    $successMessage = "Account created successfully! Please log in.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $targetRedirect = trim($_POST['redirect_to'] ?? 'index.php');

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // --- Record Login Information / Activity Log ---
            // --- Record Login Information / Activity Log ---
            try {
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

                $logStmt = $pdo->prepare("INSERT INTO user_login_logs (user_id, ip_address, user_agent, login_time) VALUES (:user_id, :ip_address, :user_agent, NOW())");
                $logStmt->execute([
                    'user_id'    => $user['id'],
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent
                ]);
            } catch (PDOException $e) {
                error_log("Failed to record login log: " . $e->getMessage());
            }

            // Safely redirect back to the intended page (default to index.php)
            header("Location: " . (!empty($targetRedirect) ? $targetRedirect : 'index.php'));
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}

$pageTitle = "Login - Reshma Beauty Parlour";
$pageCss   = "login.css";

require_once __DIR__ . '/../Includes/Components/header.php';
?>

<main class="login-page">
    <div class="login-card" style="max-width: 400px; margin: 2rem auto; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 style="text-align: center; margin-bottom: 1.5rem; color: #1e1b4b;">Portal Login</h2>

        <?php if (!empty($successMessage)): ?>
            <div style="background: rgba(72, 187, 120, 0.1); color: #38a169; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem; text-align: center;">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="login-error" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; text-align: center; font-size: 0.9rem;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Standard Login Form -->
        <form method="POST" class="login-form">
            <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($redirectParam); ?>">

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="username" style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Username</label>
                <input type="text" id="username" name="username" required class="form-control" autocomplete="username" style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="password" style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Password</label>
                <input type="password" id="password" name="password" required class="form-control" autocomplete="current-password" style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <button type="submit" class="login-btn" style="width: 100%; padding: 0.75rem; background: #4f46e5; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Log In</button>
        </form>

        <div style="display: flex; align-items: center; text-align: center; color: #94a3b8; margin: 1.25rem 0;">
            <div style="flex-grow: 1; border-top: 1px solid #e2e8f0;"></div>
            <span style="padding: 0 0.75rem; font-size: 0.8rem; text-transform: uppercase;">Or</span>
            <div style="flex-grow: 1; border-top: 1px solid #e2e8f0;"></div>
        </div>

        <!-- Google Sign-In Button Option -->
        <a href="google-auth.php" class="google-btn" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; width: 100%; padding: 0.75rem; background: #ffffff; color: #334155; border: 1px solid #cbd5e1; border-radius: 6px; font-weight: 600; text-decoration: none; cursor: pointer; transition: background 0.2s;">
            <i class="fa-brands fa-google text-red-500"></i> Continue with Google
        </a>

        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: #64748b;">
            Don't have an account? <a href="signup.php" style="color: #4f46e5; text-decoration: none; font-weight: 600;">Sign up</a>
        </p>
    </div>
</main>

<?php require_once __DIR__ . '/../Includes/Components/footer.php'; ?>