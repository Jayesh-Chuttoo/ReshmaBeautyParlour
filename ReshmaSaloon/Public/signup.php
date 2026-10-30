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

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/php-error.log');

require_once __DIR__ . '/../Includes/db.php';
require_once __DIR__ . '/../Includes/db_queries.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username        = trim($_POST['username'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($password) || empty($confirmPassword)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        if (getUserByUsername($pdo, $username)) {
            $error = "Username is already taken. Please choose another.";
        } else {
            if (registerUser($pdo, $username, $password)) {
                // Automatically log the user in upon successful signup
                session_regenerate_id(true);
                $newUser = getUserByUsername($pdo, $username);

                $_SESSION['user_id'] = $newUser['id'];
                $_SESSION['username'] = $newUser['username'];

                header("Location: index.php");
                exit;
            } else {
                $error = "An error occurred during registration. Please try again.";
            }
        }
    }
}

$pageTitle = "Sign Up - Reshma Beauty Parlour";
$pageCss   = "signup.css";

require_once __DIR__ . '/../Includes/Components/header.php';
?>

<main class="signup-page">
    <div class="signup-card">
        <h2>Create Account</h2>

        <?php if (!empty($error)): ?>
            <div class="signup-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="signup-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required class="form-control" autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required class="form-control" autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required class="form-control" autocomplete="new-password">
            </div>

            <button type="submit" class="signup-btn">Sign Up</button>
        </form>

        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
            Already have an account? <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Log in</a>
        </p>
    </div>
</main>

<?php require_once __DIR__ . '/../Includes/Components/footer.php'; ?>