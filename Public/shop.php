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

$pageTitle = "Boutique Shop - Reshma Beauty Parlour";
$pageCss   = "shop.css";

require_once __DIR__ . '/../Includes/Components/header.php';
require_once __DIR__ . '/../Includes/db_queries.php'; // Includes your PDO connection and query helper functions

// --- HANDLE ADD TO CART ACTION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    // Optional: Redirect guests to login if they try to add items to cart
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $userId = $_SESSION['user_id'];
    $productId = (int)($_POST['product_id'] ?? 0);

    if ($productId > 0) {
        addToCartDb($pdo, $userId, $productId, 1);
    }

    // Redirect to prevent form resubmission on refresh
    header("Location: shop.php");
    exit;
}

// --- 1. FETCH PRODUCTS FROM DATABASE ---
$allProducts = getAllProducts($pdo);

// --- 2. FILTER LOGIC (Out-of-stock items hidden automatically) ---
$filteredProducts = array_filter($allProducts, function ($product) {
    return ((int)($product['stock'] ?? 0)) > 0;
});

// --- 3. PAGINATION LOGIC ---
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
                // If your database 'sizes' field is stored as JSON, decode it back into an array for the card component if needed
                if (isset($product['sizes']) && is_string($product['sizes'])) {
                    $product['sizes'] = json_decode($product['sizes'], true);
                }

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