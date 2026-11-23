<?php
// Ensure session is available before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$currentPage = basename($_SERVER['PHP_SELF']);

$logoPath = '../Includes/Components/assets/logoReshma.svg';
$logoSvg  = (is_file($logoPath) && is_readable($logoPath)) ? file_get_contents($logoPath) : '';
$faviconDataUri = $logoSvg !== '' ? 'data:image/svg+xml;base64,' . base64_encode($logoSvg) : '';

// Define navigation items dynamically based on auth state
$navItems = [
    ['url' => 'index.php', 'label' => 'Portfolio'],
    ['url' => 'services.php', 'label' => 'Services'],
    ['url' => 'shop.php', 'label' => 'Shop'],
];

if ($isLoggedIn) {
    $navItems[] = ['url' => 'cart.php', 'label' => 'Cart'];
}

$navItems[] = ['url' => 'contact.php', 'label' => 'Contact Us'];

if ($isLoggedIn) {
    $navItems[] = ['url' => 'profile.php', 'label' => 'Profile'];
} else {
    $navItems[] = ['url' => 'login.php', 'label' => 'Login'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Reshma Beauty Parlour', ENT_QUOTES, 'UTF-8'); ?></title>

    <!-- Always load Global CSS -->
    <link rel="stylesheet" href="assets/css/global.css">
    <?php if ($faviconDataUri !== ''): ?>
        <link rel="icon" type="image/svg+xml" href="<?php echo $faviconDataUri; ?>">
    <?php endif; ?>

    <!-- Dynamically load Page Specific CSS if set -->
    <?php if (isset($pageCss) && !empty($pageCss)): ?>
        <link rel="stylesheet" href="assets/css/<?php echo htmlspecialchars($pageCss, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
</head>

<body>
    <header>
        <a href="index.php" class="brand">
            <?php if ($logoSvg !== ''): ?>
                <span class="brand-logo" aria-hidden="true"><?php echo $logoSvg; ?></span>
            <?php endif; ?>
            <span class="logo">Reshma Beauty Parlour</span>
        </a>

        <!-- Mobile Hamburger Toggle Button -->
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle navigation">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>

        <nav id="mainNav">
            <?php foreach ($navItems as $item): ?>
                <?php
                $isActive = ($currentPage === $item['url']) ? 'active' : '';
                ?>
                <a href="<?php echo htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $isActive; ?>">
                    <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </header>

    <div id="notification-area"></div>

    <!-- Mobile Menu Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const mainNav = document.getElementById('mainNav');

            hamburgerBtn.addEventListener('click', () => {
                hamburgerBtn.classList.toggle('active');
                mainNav.classList.toggle('active');
            });

            // Close menu when clicking any nav link on mobile
            mainNav.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    hamburgerBtn.classList.remove('active');
                    mainNav.classList.remove('active');
                });
            });
        });
    </script>