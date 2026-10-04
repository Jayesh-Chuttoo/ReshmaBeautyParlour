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
    <?php
    // Get the current file name to detect active page
    $currentPage = basename($_SERVER['PHP_SELF']);

    // Define navigation items in an array
    $navItems = [
        ['url' => 'index.php', 'label' => 'Portfolio'],
        ['url' => 'services.php', 'label' => 'Services'],
        ['url' => 'shop.php', 'label' => 'Shop'],
        ['url' => 'contact.php', 'label' => 'Contact Us']
    ];
    ?>
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
                // Check if the current script matches the nav item URL
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
    <div id="notification-area"></div>