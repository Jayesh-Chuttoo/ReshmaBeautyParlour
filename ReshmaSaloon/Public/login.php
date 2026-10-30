<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true, // Change to true if running on HTTPS
    'httponly' => true, // Prevents JavaScript XSS theft
    'samesite' => 'Strict'
]);
session_start();

// If user is already logged in, redirect them away from the login page
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/php-error.log');

require_once __DIR__ . '/../Includes/db.php'; // Secure connection from Includes folder

$error = '';
$successMessage = '';

// Check if redirected from signup page
if (isset($_GET['signup']) && $_GET['signup'] === 'success') {
    $successMessage = "Account created successfully! Please log in.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        // Securely fetch user via Prepared Statement
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        // Verify password against secure database hash
        if ($user && password_verify($password, $user['password'])) {
            // Prevent Session Fixation attacks
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            header("Location: index.php");
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
    <div class="login-card">
        <h2>Admin Login</h2>

        <?php if (!empty($successMessage)): ?>
            <div style="background: rgba(72, 187, 120, 0.1); color: #38a169; padding: 0.75rem 1rem; border-radius: var(--radius, 8px); margin-bottom: 1.5rem; font-size: 0.9rem; text-align: center;">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="login-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required class="form-control" autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required class="form-control" autocomplete="current-password">
            </div>

            <button type="submit" class="login-btn">Log In</button>
        </form>

        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
            Don't have an account? <a href="signup.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Sign up</a>
        </p>
    </div>
</main>

<?php require_once __DIR__ . '/../Includes/Components/footer.php'; ?>