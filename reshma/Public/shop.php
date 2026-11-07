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
require_once __DIR__ . '/../Includes/db_queries.php';

// --- HANDLE ADD TO CART ACTION --- 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $userId = $_SESSION['user_id'];
    $productId = (int)($_POST['product_id'] ?? 0);
    $size = trim($_POST['size'] ?? '');
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));

    if ($productId > 0 && !empty($size)) {
        addToCartDb($pdo, $userId, $productId, $quantity, $size);
    }

    header("Location: shop.php");
    exit;
}

// --- 1. FETCH PRODUCTS FROM DATABASE ---
$allProducts = getAllProducts($pdo);

// --- 2. FILTER LOGIC (Hides products if all sizes/stock are sold out) --- 
$filteredProducts = array_filter($allProducts, function ($product) use ($pdo) {
    if (isset($product['sizes']) && is_string($product['sizes'])) {
        $product['sizes'] = json_decode($product['sizes'], true);
    }

    // Get true effective stock for each size
    $effectiveSizes = getEffectiveSizeStock($pdo, $product);

    // Filter out sizes with 0 or negative effective stock
    $availableEffectiveSizes = array_filter($effectiveSizes, function ($qty) {
        return intval($qty) > 0;
    });

    // Keep product in shop only if at least one size still has stock available
    return count($availableEffectiveSizes) > 0 && intval($product['stock'] ?? 0) > 0;
});

// --- 3. PAGINATION LOGIC ---
$itemsPerPage = 6;
$totalItems   = count($filteredProducts);
$totalPages   = max(1, (int)ceil($totalItems / $itemsPerPage));

$currentPage = max(1, (int)($_GET['page'] ?? 1));
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$offset      = ($currentPage - 1) * $itemsPerPage;
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

<!-- ADD TO CART POPUP MODAL -->
<div id="cartModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: white; border-radius: 12px; max-width: 450px; width: 100%; padding: 1.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.1); position: relative;">
        <button type="button" onclick="closeCartModal()" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #64748b;">&times;</button>

        <form action="shop.php" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
            <input type="hidden" name="action" value="add_to_cart">
            <input type="hidden" name="product_id" id="modalProductId">

            <!-- Product Image -->
            <div style="text-align: center;">
                <img id="modalProductImage" src="" alt="Product Image" style="max-height: 180px; width: 100%; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;" onerror="this.src='https://via.placeholder.com/400x200?text=Product+Image'">
            </div>

            <!-- Product Details -->
            <div>
                <h3 id="modalProductName" style="font-size: 1.15rem; font-weight: bold; color: #1e1b4b; margin-bottom: 0.25rem;"></h3>
                <p id="modalProductPrice" style="color: #059669; font-weight: 600; margin-bottom: 0.5rem;"></p>
                <p id="modalProductDescription" style="font-size: 0.875rem; color: #64748b; line-height: 1.4;"></p>
            </div>

            <!-- Size Selection (Dynamically populated with ONLY available sizes) -->
            <div>
                <label for="modalSizeSelect" style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem; color: #334155;">Select Size:</label>
                <select name="size" id="modalSizeSelect" onchange="updateSizeLimit()" style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem;">
                    <!-- Options injected via JavaScript -->
                </select>
            </div>

            <!-- Quantity Selection & Limit Warning -->
            <div>
                <label for="modalQuantityInput" style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem; color: #334155;">Quantity:</label>
                <input type="number" name="quantity" id="modalQuantityInput" value="1" min="1" oninput="updateTotalPrice()" style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem;">
                <small id="stockLimitWarning" style="color: #64748b; font-size: 0.75rem; margin-top: 0.25rem; display: block;"></small>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
                <button type="button" onclick="closeCartModal()" style="flex: 1; padding: 0.75rem; border: 1px solid #cbd5e1; background: white; border-radius: 6px; font-weight: 600; cursor: pointer; color: #64748b;">Cancel</button>
                <button type="submit" id="modalSubmitBtn" style="flex: 1; padding: 0.75rem; background: #4f46e5; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Add to Cart</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Control Script -->
<script>
    let currentProductData = {};
    let baseProductPrice = 0;

    function openCartModal(product) {
        currentProductData = product;
        baseProductPrice = parseFloat(product.price) || 0;

        document.getElementById('modalProductId').value = product.id;
        document.getElementById('modalProductName').innerText = product.name || 'Untitled Product';
        document.getElementById('modalProductDescription').innerText = product.description || 'No description available.';

        // Format image path
        const imgEl = document.getElementById('modalProductImage');
        if (product.image) {
            imgEl.src = "https://reshmaadmin.infinitydigitalsls.com/" + product.image;
            imgEl.style.display = 'block';
        } else {
            imgEl.src = "https://via.placeholder.com/400x200?text=Product+Image";
        }

        // Parse sizes safely
        let sizes = product.sizes;
        if (typeof sizes === 'string') {
            try {
                sizes = JSON.parse(sizes);
            } catch (e) {
                sizes = {};
            }
        }

        // Populate size dropdown ONLY with sizes that have stock > 0
        const sizeSelect = document.getElementById('modalSizeSelect');
        sizeSelect.innerHTML = '';

        let hasAvailableSizes = false;
        for (const [sizeName, qty] of Object.entries(sizes)) {
            const stockQty = parseInt(qty) || 0;
            if (stockQty > 0) {
                hasAvailableSizes = true;
                const option = document.createElement('option');
                option.value = sizeName;
                option.text = `${sizeName} (${stockQty} available)`;
                sizeSelect.appendChild(option);
            }
        }

        const submitBtn = document.getElementById('modalSubmitBtn');
        const qtyInput = document.getElementById('modalQuantityInput');
        const warningEl = document.getElementById('stockLimitWarning');

        if (!hasAvailableSizes) {
            const option = document.createElement('option');
            option.value = '';
            option.text = 'Out of Stock';
            sizeSelect.appendChild(option);
            submitBtn.disabled = true;
            submitBtn.style.opacity = "0.5";
            warningEl.innerText = "⚠️ All sizes are currently sold out.";
            warningEl.style.color = "#ef4444";
        } else {
            submitBtn.disabled = false;
            submitBtn.style.opacity = "1";
            warningEl.innerText = "";
        }

        document.getElementById('modalQuantityInput').value = 1;

        updateSizeLimit();
        updateTotalPrice();

        document.getElementById('cartModal').style.display = 'flex';
    }

    function updateTotalPrice() {
        const qty = parseInt(document.getElementById('modalQuantityInput').value) || 1;
        const totalPrice = baseProductPrice * qty;
        document.getElementById('modalProductPrice').innerText = 'Rs ' + totalPrice.toFixed(2);
    }

    function updateSizeLimit() {
        const selectedSize = document.getElementById('modalSizeSelect').value;
        const qtyInput = document.getElementById('modalQuantityInput');
        const warningEl = document.getElementById('stockLimitWarning');

        let sizes = currentProductData.sizes;
        if (typeof sizes === 'string') {
            try {
                sizes = JSON.parse(sizes);
            } catch (e) {
                sizes = {};
            }
        }

        const availableStock = parseInt(sizes[selectedSize]) || 0;

        qtyInput.max = availableStock > 0 ? availableStock : 1;
        if (parseInt(qtyInput.value) > availableStock) {
            qtyInput.value = Math.max(1, availableStock);
        }

        if (availableStock > 0) {
            warningEl.innerText = "Available stock for size " + selectedSize + ": " + availableStock;
            warningEl.style.color = "#64748b";
        }
    }

    function closeCartModal() {
        document.getElementById('cartModal').style.display = 'none';
    }
</script>

<?php require_once __DIR__ . '/../Includes/Components/footer.php'; ?>
```[cite: 16]