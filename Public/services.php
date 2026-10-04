<?php
$pageTitle = "Services - Reshma Beauty Parlour";
$pageCss   = "services.css";

require_once __DIR__ . '/../Includes/Components/header.php';
require_once __DIR__ . '/../Includes/Data/service_data.php';

?>

<main>
    <h1>Our Services</h1>
    <p style="color: var(--text-muted); margin-top: 0.5rem;">Discover our range of premium beauty treatments.</p>

    <div class="grid">
        <?php foreach ($services as $service): ?>
            <?php require __DIR__ . '/../Includes/Components/service_card.php'; ?>
        <?php endforeach; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../Includes/Components/footer.php'; ?>