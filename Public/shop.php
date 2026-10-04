<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pageTitle = "Boutique Shop - Elegance Saloon";
$pageCss   = "shop.css";

require_once __DIR__ . '/../Includes/Components/header.php';
require_once __DIR__ . '/../Includes/Data/products_data.php';

// --- 1. FILTER LOGIC (Out-of-stock items hidden automatically) ---
$filteredProducts = array_filter($products, function ($product) {
    return ((int)($product['stock'] ?? 0)) > 0;
});

// --- 2. PAGINATION LOGIC ---
$itemsPerPage = 6; // Set desired items per page (adjust as needed)
$totalItems   = count($filteredProducts);
$totalPages   = max(1, (int)ceil($totalItems / $itemsPerPage));

$currentPage = max(1, (int)($_GET['page'] ?? 1));
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$offset        = ($currentPage - 1) * $itemsPerPage;
$pagedProducts = array_slice($filteredProducts, $offset, $itemsPerPage);
?>

<main>
    <h1>Salon Boutique</h1>
    <p style="color: var(--text-muted); margin-top: 0.5rem;">Exclusive clothing, tools, and hair care products.</p>

    <!-- Products Grid -->
    <div class="grid">
        <?php
        if (!empty($pagedProducts)) {
            foreach ($pagedProducts as $product) {
                require __DIR__ . '/../Includes/Components/product_card.php';
            }
        } else {
            echo '<p style="grid-column: 1/-1; padding: 2rem 0; text-align: center; color: var(--text-muted);">No products currently available.</p>';
        }
        ?>
    </div>

    <!-- Pagination Controls -->
    <?php require __DIR__ . '/../Includes/Components/pagination.php'; ?>
</main>

<?php require_once __DIR__ . '/../Includes/Components/footer.php'; ?>