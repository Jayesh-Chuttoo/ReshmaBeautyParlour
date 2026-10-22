<?php
// Ensure session is available
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$currentPage = basename($_SERVER['PHP_SELF']);

// Define navigation items dynamically based on auth state
$navItems = [
    ['url' => 'index.php', 'label' => 'Portfolio'],
    ['url' => 'services.php', 'label' => 'Services'],
    ['url' => 'shop.php', 'label' => 'Shop'],
    ['url' => 'cart.php', 'label' => 'Cart'],
    ['url' => 'contact.php', 'label' => 'Contact Us']
];

if ($isLoggedIn) {
    $navItems[] = ['url' => 'logout.php', 'label' => 'Logout'];
} else {
    $navItems[] = ['url' => 'login.php', 'label' => 'Login'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Reshma Beauty Parlour'; ?></title>

    <!-- Always load Global CSS -->
    <link rel="stylesheet" href="assets/css/global.css">

    <!-- Dynamically load Page Specific CSS if set -->
    <?php if (isset($pageCss) && !empty($pageCss)): ?>
        <link rel="stylesheet" href="assets/css/<?php echo htmlspecialchars($pageCss); ?>">
    <?php endif; ?>
</head>

<body>
    <header>
        <div class="logo">Reshma Beauty Parlour</div>

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
                <a href="<?php echo $item['url']; ?>" class="<?php echo $isActive; ?>">
                    <?php echo $item['label']; ?>
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