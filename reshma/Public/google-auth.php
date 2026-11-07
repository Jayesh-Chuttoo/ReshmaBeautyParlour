<?php

/**
 * Google OAuth 2.0 Authentication Handler (Native PHP)
 * Reshma Beauty Parlour
 */
session_start();

require_once __DIR__ . '/../Includes/db.php';

// 1. Load environment variables natively from reshma/Includes/.env
$envPath = __DIR__ . '/../Includes/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

define('GOOGLE_CLIENT_ID', $_ENV['GOOGLE_CLIENT_ID'] ?? '');
define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
define('GOOGLE_REDIRECT_URI', $_ENV['GOOGLE_REDIRECT_URI'] ?? '');

// Step 2: Handle callback code returned from Google
if (isset($_GET['code'])) {
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $postData = [
        'code' => $_GET['code'],
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $tokenData = json_decode($response, true);

    if (isset($tokenData['access_token'])) {
        // Step 3: Fetch user profile information from Google
        $userInfoUrl = 'https://www.googleapis.com/oauth2/v3/userinfo?access_token=' . $tokenData['access_token'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $userInfoResponse = curl_exec($ch);
        curl_close($ch);

        $userInfo = json_decode($userInfoResponse, true);

        if (isset($userInfo['sub'])) {
            $googleId = $userInfo['sub'];
            $email    = $userInfo['email'] ?? '';
            $name     = $userInfo['name'] ?? explode('@', $email)[0];

            // Check if user already exists in database by google_id or username
            $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = :google_id OR username = :username LIMIT 1");
            $stmt->execute(['google_id' => $googleId, 'username' => $email]);
            $user = $stmt->fetch();

            $userId = null;

            if ($user) {
                // Link google_id if existing traditional account matches email
                if (empty($user['google_id'])) {
                    $updateStmt = $pdo->prepare("UPDATE users SET google_id = :google_id WHERE id = :id");
                    $updateStmt->execute(['google_id' => $googleId, 'id' => $user['id']]);
                }

                $userId = $user['id'];
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $user['username'];
            } else {
                // Automatically register new user via Google
                $insertStmt = $pdo->prepare("INSERT INTO users (username, password, google_id) VALUES (:username, :password, :google_id)");
                $insertStmt->execute([
                    'username'  => $email ?: $name,
                    'password'  => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                    'google_id' => $googleId
                ]);

                $userId = $pdo->lastInsertId();
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $email ?: $name;
            }

            // --- Record Google Login Information / Activity Log ---
            try {
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

                $logStmt = $pdo->prepare("INSERT INTO user_login_logs (user_id, ip_address, user_agent, login_time) VALUES (:user_id, :ip_address, :user_agent, NOW())");
                $logStmt->execute([
                    'user_id'    => $userId,
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent
                ]);
            } catch (PDOException $e) {
                error_log("Google Login Log Error: " . $e->getMessage());
            }

            session_regenerate_id(true);

            // Redirect back to intended page or index
            $redirect = $_SESSION['auth_redirect'] ?? 'index.php';
            unset($_SESSION['auth_redirect']);
            header("Location: " . $redirect);
            exit;
        }
    }

    header("Location: login.php?error=google_failed");
    exit;
}

// Step 1: Redirect user to Google OAuth Consent Screen
$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'access_type'   => 'offline',
    'prompt'        => 'select_account'
]);

header("Location: " . $authUrl);
exit;
