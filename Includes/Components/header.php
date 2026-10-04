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
        <nav>
            <a href="index.php">Portfolio</a>
            <a href="services.php">Services</a>
            <a href="shop.php">Shop</a>
        </nav>
    </header>
    <div id="notification-area"></div>