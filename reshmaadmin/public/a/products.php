<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db_queries.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'manager'])) {
    header("Location: ../public/index.php");
    exit;
}

$message = '';
$msgType = 'success';

// Handle Add / Edit / Delete Product submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_product') {
        $deleteId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        if ($deleteId > 0) {
            $existing = getProductById($pdo, $deleteId);
            $success = deleteProductById($pdo, $deleteId);
            if ($success) {
                if (!empty($existing['image'])) {
                    $diskImage = realpath(__DIR__ . '/../' . ltrim($existing['image'], '/'));
                    $uploadsRoot = realpath(__DIR__ . '/../uploads');
                    if ($diskImage && $uploadsRoot && strpos($diskImage, $uploadsRoot . DIRECTORY_SEPARATOR) === 0 && is_file($diskImage)) {
                        @unlink($diskImage);
                    }
                }
                $message = "Product #{$deleteId} deleted successfully!";
                $msgType = 'success';
            } else {
                $message = 'Failed to delete product.';
                $msgType = 'error';
            }
        } else {
            $message = 'Invalid product ID.';
            $msgType = 'error';
        }
    } elseif ($action === 'add_product' || $action === 'edit_product') {
        $id          = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = floatval($_POST['price'] ?? 0);
        $status      = trim($_POST['status'] ?? 'Available');

        // Process dynamic sizes and quantities into JSON format
        $sizeNames = $_POST['size_names'] ?? [];
        $sizeQtys  = $_POST['size_qtys'] ?? [];
        $sizesArray = [];
        $calculatedStock = 0;

        for ($i = 0; $i < count($sizeNames); $i++) {
            $sName = strtoupper(trim($sizeNames[$i]));
            $sQty  = max(0, intval($sizeQtys[$i] ?? 0));
            if (!empty($sName)) {
                $sizesArray[$sName] = $sQty;
                $calculatedStock += $sQty;
            }
        }

        $sizesJson = !empty($sizesArray) ? json_encode($sizesArray) : null;

        // If no individual sizes were specified, fallback to the direct stock field
        $finalStock = (!empty($sizesArray)) ? $calculatedStock : max(0, intval($_POST['stock'] ?? 0));

        // Handle Image Upload
        $imagePath = $_POST['existing_image'] ?? '';
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileExtension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
            $fileName = 'prod_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadDir . $fileName)) {
                $imagePath = 'uploads/' . $fileName;
            }
        }

        if (!empty($name) && $price > 0) {
            if ($action === 'add_product') {
                $success = createProduct($pdo, $name, $description, $price, $sizesJson, $finalStock, $status, $imagePath);
                $message = $success ? 'Product added successfully!' : 'Failed to add product.';
            } else {
                $success = updateProductById($pdo, $id, $name, $description, $price, $sizesJson, $finalStock, $status, $imagePath);
                $message = $success ? 'Product updated successfully!' : 'Failed to update product.';
            }
            $msgType = $success ? 'success' : 'error';
        } else {
            $message = 'Please provide a valid product name and price.';
            $msgType = 'error';
        }
    }
}

// Fetch single product for editing if 'edit_id' is passed in URL
$editProduct = null;
$existingSizes = [];
if (isset($_GET['edit_id'])) {
    $editProduct = getProductById($pdo, intval($_GET['edit_id']));

    if ($editProduct && !empty($editProduct['sizes'])) {
        $decoded = json_decode($editProduct['sizes'], true);
        if (is_array($decoded)) {
            $existingSizes = $decoded;
        }
    }
}

$isFormOpen = ($editProduct !== null) || ($msgType === 'error' && $_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') !== 'delete_product'));
$products = function_exists('getAllProducts') ? getAllProducts($pdo) : [];
$pageTitle = "Products Management - Reshma Beauty Parlour";
$pageCss = "products.css";
require_once __DIR__ . '/../../includes/components/header.php';
?>

<main class="products-page">
    <div class="products-header">
        <div>
            <h1 class="products-title">Inventory &amp; Products</h1>
            <p class="products-subtitle">Add beauty items or update pricing, sizes, stock, and availability.</p>
        </div>
        <a href="index.php" class="products-back-link">&larr; Back to Dashboard</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="products-alert <?= $msgType === 'success' ? 'products-alert-success' : 'products-alert-error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="products-stack">
        <!-- Collapsible Product Form (Add / Edit) -->
        <section class="products-card" aria-labelledby="productFormHeading">
            <div
                class="collapsible-header"
                role="button"
                tabindex="0"
                aria-expanded="<?= $isFormOpen ? 'true' : 'false' ?>"
                aria-controls="productFormBody"
                onclick="toggleProductForm()"
                onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); toggleProductForm(); }">
                <h2 id="productFormHeading" class="products-panel-title">
                    <?= $editProduct ? 'Edit Product #' . (int)$editProduct['id'] : 'Add New Product' ?>
                </h2>
                <span class="collapsible-toggle-btn" id="productFormToggleLabel">
                    <span><?= $isFormOpen ? 'Hide Form' : 'Expand Form' ?></span>
                    <i class="fa-solid fa-chevron-down collapsible-chevron" aria-hidden="true"></i>
                </span>
            </div>

            <div id="productFormBody" class="collapsible-body <?= $isFormOpen ? 'is-open' : '' ?>">
                <form method="POST" enctype="multipart/form-data" class="products-form">
                    <input type="hidden" name="action" value="<?= $editProduct ? 'edit_product' : 'add_product' ?>">
                    <?php if ($editProduct): ?>
                        <input type="hidden" name="product_id" value="<?= (int)$editProduct['id'] ?>">
                        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($editProduct['image'] ?? '') ?>">
                    <?php endif; ?>

                    <div class="products-fields-grid">
                        <div class="products-field">
                            <label for="prodName" class="products-label">Product Name *</label>
                            <input id="prodName" type="text" name="name" value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>" required class="products-input">
                        </div>
                        <div class="products-field">
                            <label for="prodPrice" class="products-label">Price (Rs) *</label>
                            <input id="prodPrice" type="number" step="0.01" name="price" value="<?= htmlspecialchars($editProduct['price'] ?? '') ?>" required class="products-input">
                        </div>
                        <div class="products-field">
                            <label for="prodStatus" class="products-label">Status</label>
                            <select id="prodStatus" name="status" class="products-select">
                                <option value="Available" <?= ($editProduct['status'] ?? '') === 'Available' ? 'selected' : '' ?>>Available</option>
                                <option value="Out of Stock" <?= ($editProduct['status'] ?? '') === 'Out of Stock' ? 'selected' : '' ?>>Out of Stock</option>
                                <option value="Discontinued" <?= ($editProduct['status'] ?? '') === 'Discontinued' ? 'selected' : '' ?>>Discontinued</option>
                            </select>
                        </div>
                        <div class="products-field">
                            <label for="prodImage" class="products-label">Product Image</label>
                            <input id="prodImage" type="file" name="product_image" class="products-file-input">
                        </div>
                    </div>

                    <!-- DYNAMIC SIZE & QUANTITY BUILDER -->
                    <div class="sizes-box">
                        <div class="sizes-header">
                            <div>
                                <span class="sizes-title">Sizes &amp; Stock Breakdown</span>
                                <p class="sizes-help">Add size variations (e.g., S, M, L or 100ml) and quantities.</p>
                            </div>
                            <button type="button" onclick="addSizeRow()" class="add-size-btn">
                                <i class="fa-solid fa-plus"></i> Add Size
                            </button>
                        </div>

                        <div id="size-rows-container" class="size-rows-list">
                            <?php if (!empty($existingSizes)): ?>
                                <?php foreach ($existingSizes as $sName => $sQty): ?>
                                    <div class="size-row">
                                        <input type="text" name="size_names[]" value="<?= htmlspecialchars($sName) ?>" placeholder="Size (e.g. S, M, 250ml)" class="size-name-input">
                                        <input type="number" name="size_qtys[]" value="<?= intval($sQty) ?>" placeholder="Quantity" min="0" oninput="calculateTotalStock()" class="size-qty-input">
                                        <button type="button" onclick="removeSizeRow(this)" class="remove-size-btn" aria-label="Remove size"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="size-row">
                                    <input type="text" name="size_names[]" placeholder="Size (e.g. S, M, 250ml)" class="size-name-input">
                                    <input type="number" name="size_qtys[]" value="0" placeholder="Quantity" min="0" oninput="calculateTotalStock()" class="size-qty-input">
                                    <button type="button" onclick="removeSizeRow(this)" class="remove-size-btn" aria-label="Remove size"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="stock-summary">
                            <span>Total Calculated Stock:</span>
                            <span id="total-stock-display" class="stock-total-value"><?= htmlspecialchars($editProduct['stock'] ?? 0) ?> units</span>
                            <input type="hidden" name="stock" id="stock-hidden-input" value="<?= htmlspecialchars($editProduct['stock'] ?? 0) ?>">
                        </div>
                    </div>

                    <div class="products-field">
                        <label for="prodDescription" class="products-label">Description</label>
                        <textarea id="prodDescription" name="description" rows="3" class="products-textarea"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
                    </div>

                    <div class="products-form-actions">
                        <button type="submit" class="products-submit-btn"><?= $editProduct ? 'Update Product' : 'Add Product' ?></button>
                        <?php if ($editProduct): ?>
                            <a href="products.php" class="products-cancel-btn">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </section>

        <!-- Product List Table -->
        <section class="products-card products-table-card" aria-label="Products list">
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Sizes &amp; Quantities</th>
                        <th>Total Stock</th>
                        <th>Status</th>
                        <th class="prod-action-col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $hasVisibleProducts = false;
                    if (!empty($products)):
                        foreach ($products as $p):
                            // Skip product entirely if stock is 0 or less
                            $totalStock = intval($p['stock'] ?? 0);
                            if ($totalStock <= 0) {
                                continue;
                            }

                            // Filter out sizes with 0 or negative quantities
                            $sizesObj = !empty($p['sizes']) ? json_decode($p['sizes'], true) : null;
                            $availableSizes = [];
                            if (is_array($sizesObj)) {
                                foreach ($sizesObj as $sKey => $sVal) {
                                    if (intval($sVal) > 0) {
                                        $availableSizes[$sKey] = intval($sVal);
                                    }
                                }
                            }

                            $hasVisibleProducts = true;
                    ?>
                            <tr>
                                <td>
                                    <?php if (!empty($p['image'])): ?>
                                        <img src="../public/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="prod-thumb">
                                    <?php else: ?>
                                        <span class="prod-no-image">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td class="prod-name"><?= htmlspecialchars($p['name']) ?></td>
                                <td class="prod-desc"><?= htmlspecialchars($p['description'] ?? 'N/A') ?></td>
                                <td class="prod-price">Rs <?= number_format($p['price'], 2) ?></td>
                                <td class="prod-sizes-cell">
                                    <?php if (!empty($availableSizes)): ?>
                                        <?php foreach ($availableSizes as $sKey => $sVal): ?>
                                            <span class="prod-size-chip">
                                                <strong><?= htmlspecialchars($sKey) ?>:</strong> <?= $sVal ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="prod-standard-label">Standard</span>
                                    <?php endif; ?>
                                </td>
                                <td class="prod-stock"><?= $totalStock ?></td>
                                <td><span class="prod-status-badge"><?= htmlspecialchars($p['status'] ?? 'Available') ?></span></td>
                                <td class="prod-action-col">
                                    <a href="products.php?edit_id=<?= (int)$p['id'] ?>" class="prod-edit-btn">Edit</a>
                                    <form method="POST" class="inline-form" data-product-name="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Delete product &quot;' + this.dataset.productName + '&quot;? This cannot be undone.');">
                                        <input type="hidden" name="action" value="delete_product">
                                        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                        <button type="submit" class="prod-delete-btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php
                        endforeach;
                    endif;

                    if (!$hasVisibleProducts):
                        ?>
                        <tr>
                            <td colspan="8" class="prod-empty-state">No active products with available stock found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
</main>

<script>
    function toggleProductForm() {
        const headerEl = document.querySelector('.collapsible-header');
        const bodyEl = document.getElementById('productFormBody');
        const labelSpan = document.querySelector('#productFormToggleLabel span');
        if (!headerEl || !bodyEl) return;

        const isOpen = bodyEl.classList.toggle('is-open');
        headerEl.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        if (labelSpan) {
            labelSpan.textContent = isOpen ? 'Hide Form' : 'Expand Form';
        }
    }

    function addSizeRow() {
        const container = document.getElementById('size-rows-container');
        const row = document.createElement('div');
        row.className = 'size-row';
        row.innerHTML = `
            <input type="text" name="size_names[]" placeholder="Size (e.g. S, M, 250ml)" class="size-name-input">
            <input type="number" name="size_qtys[]" value="0" placeholder="Quantity" min="0" oninput="calculateTotalStock()" class="size-qty-input">
            <button type="button" onclick="removeSizeRow(this)" class="remove-size-btn" aria-label="Remove size"><i class="fa-solid fa-trash"></i></button>
        `;
        container.appendChild(row);
    }

    function removeSizeRow(button) {
        const row = button.closest('.size-row');
        row.remove();
        calculateTotalStock();
    }

    function calculateTotalStock() {
        let total = 0;
        document.querySelectorAll('.size-qty-input').forEach(input => {
            total += parseInt(input.value) || 0;
        });
        document.getElementById('total-stock-display').innerText = total + ' units';
        document.getElementById('stock-hidden-input').value = total;
    }

    // Initial calculation on load
    document.addEventListener('DOMContentLoaded', calculateTotalStock);
</script>
</body>

</html>