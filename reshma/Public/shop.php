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

    $userId    = (int)$_SESSION['user_id'];
    $productId = (int)($_POST['product_id'] ?? 0);
    $size      = trim($_POST['size'] ?? '');
    $quantity  = max(1, (int)($_POST['quantity'] ?? 1));

    if ($productId > 0 && $size !== '') {
        $product = getProductById($pdo, $productId);
        if ($product) {
            $effectiveSizes = getEffectiveSizeStock($pdo, $product);
            $normalizedSize = strtoupper($size);
            $sizeStock      = max(0, (int)($effectiveSizes[$size] ?? ($effectiveSizes[$normalizedSize] ?? 0)));
            $maxAllowed     = min($sizeStock, max(0, (int)($product['stock'] ?? $sizeStock)));

            $alreadyInCart  = getCartQtyForProductSize($pdo, $userId, $productId, $normalizedSize);
            $remainingStock = max(0, $maxAllowed - $alreadyInCart);

            if ($remainingStock <= 0) {
                $_SESSION['cart_error'] = "You already have the maximum available stock ({$maxAllowed}) for size {$normalizedSize} in your cart.";
            } else {
                $finalQty = min($quantity, $remainingStock);
                addToCartDb($pdo, $userId, $productId, $finalQty, $normalizedSize);

                if ($finalQty < $quantity) {
                    $_SESSION['cart_notice'] = "Quantity capped to {$finalQty} for size {$normalizedSize} based on available stock.";
                }
            }
        }
    }

    header("Location: shop.php");
    exit;
}

// --- 1. FETCH & FILTER AVAILABLE PRODUCTS VIA DB QUERY HELPER ---
$filteredProducts = getAvailableShopProducts($pdo);

// --- 2. PAGINATION LOGIC ---
$itemsPerPage = 6;
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

    <?php if (!empty($_SESSION['cart_error'])): ?>
        <div style="margin-top: 1rem; padding: 0.75rem 1rem; border-radius: 6px; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;">
            <?php echo htmlspecialchars($_SESSION['cart_error'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <?php unset($_SESSION['cart_error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['cart_notice'])): ?>
        <div style="margin-top: 1rem; padding: 0.75rem 1rem; border-radius: 6px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">
            <?php echo htmlspecialchars($_SESSION['cart_notice'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <?php unset($_SESSION['cart_notice']); ?>
    <?php endif; ?>

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
                <input type="number" name="quantity" id="modalQuantityInput" value="1" min="1" oninput="enforceQuantityCap()" onchange="enforceQuantityCap()" style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem;">
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
    let currentMaxQty = 0;

    function getSizeMap(product) {
        let sizes = product.effective_sizes ?? product.sizes ?? {};
        if (typeof sizes === 'string') {
            try {
                sizes = JSON.parse(sizes);
            } catch (e) {
                sizes = {};
            }
        }
        return sizes && typeof sizes === 'object' ? sizes : {};
    }

    function openCartModal(product) {
        currentProductData = product;
        baseProductPrice = parseFloat(product.price) || 0;

        document.getElementById('modalProductId').value = product.id;
        document.getElementById('modalProductName').innerText = product.name || 'Untitled Product';
        document.getElementById('modalProductDescription').innerText = product.description || 'No description available.';

        const imgEl = document.getElementById('modalProductImage');
        if (product.image) {
            imgEl.src = "https://reshmaadmin.infinitydigitalsls.com/" + product.image;
            imgEl.style.display = 'block';
        } else {
            imgEl.src = "https://via.placeholder.com/400x200?text=Product+Image";
        }

        const sizes = getSizeMap(product);
        const totalProductStock = parseInt(product.stock, 10);
        const hasProductCap = Number.isFinite(totalProductStock);

        const sizeSelect = document.getElementById('modalSizeSelect');
        sizeSelect.innerHTML = '';

        let hasAvailableSizes = false;
        for (const [sizeName, qty] of Object.entries(sizes)) {
            const rawQty = parseInt(qty, 10) || 0;
            const cappedQty = hasProductCap ? Math.min(rawQty, Math.max(0, totalProductStock)) : Math.max(0, rawQty);

            if (cappedQty > 0) {
                hasAvailableSizes = true;
                const option = document.createElement('option');
                option.value = sizeName;
                option.text = `${sizeName} (${cappedQty} available)`;
                option.dataset.maxQty = cappedQty;
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
            option.dataset.maxQty = 0;
            sizeSelect.appendChild(option);
            submitBtn.disabled = true;
            submitBtn.style.opacity = "0.5";
            currentMaxQty = 0;
            qtyInput.value = 0;
            qtyInput.max = 0;
            warningEl.innerText = "All sizes are currently sold out.";
            warningEl.style.color = "#ef4444";
            updateTotalPrice();
        } else {
            submitBtn.disabled = false;
            submitBtn.style.opacity = "1";
            qtyInput.value = 1;
            updateSizeLimit();
        }

        document.getElementById('cartModal').style.display = 'flex';
    }

    function updateSizeLimit() {
        const sizeSelect = document.getElementById('modalSizeSelect');
        const selectedOption = sizeSelect.options[sizeSelect.selectedIndex];
        const qtyInput = document.getElementById('modalQuantityInput');
        const warningEl = document.getElementById('stockLimitWarning');

        currentMaxQty = selectedOption ? (parseInt(selectedOption.dataset.maxQty, 10) || 0) : 0;
        qtyInput.max = currentMaxQty > 0 ? currentMaxQty : 1;

        if (currentMaxQty <= 0) {
            qtyInput.value = 0;
            warningEl.innerText = "No stock available for this size.";
            warningEl.style.color = "#ef4444";
            updateTotalPrice();
            return;
        }

        let currentVal = parseInt(qtyInput.value, 10);
        if (!Number.isFinite(currentVal) || currentVal < 1) {
            currentVal = 1;
        }
        if (currentVal > currentMaxQty) {
            currentVal = currentMaxQty;
        }
        qtyInput.value = currentVal;

        warningEl.innerText = "Available stock for size " + sizeSelect.value + ": " + currentMaxQty;
        warningEl.style.color = "#64748b";
        updateTotalPrice();
    }

    function enforceQuantityCap() {
        const qtyInput = document.getElementById('modalQuantityInput');
        const warningEl = document.getElementById('stockLimitWarning');
        const sizeSelect = document.getElementById('modalSizeSelect');

        if (currentMaxQty <= 0) {
            qtyInput.value = 0;
            updateTotalPrice();
            return;
        }

        let val = parseInt(qtyInput.value, 10);
        if (!Number.isFinite(val) || val < 1) {
            val = 1;
        }

        if (val > currentMaxQty) {
            val = currentMaxQty;
            warningEl.innerText = "Capped at maximum available stock (" + currentMaxQty + ") for size " + sizeSelect.value + ".";
            warningEl.style.color = "#d97706";
        } else {
            warningEl.innerText = "Available stock for size " + sizeSelect.value + ": " + currentMaxQty;
            warningEl.style.color = "#64748b";
        }

        qtyInput.value = val;
        updateTotalPrice();
    }

    function updateTotalPrice() {
        const qty = parseInt(document.getElementById('modalQuantityInput').value, 10) || 0;
        const totalPrice = baseProductPrice * qty;
        document.getElementById('modalProductPrice').innerText = 'Rs ' + totalPrice.toFixed(2);
    }

    function closeCartModal() {
        document.getElementById('cartModal').style.display = 'none';
    }
</script>

<?php require_once __DIR__ . '/../Includes/Components/footer.php'; ?>