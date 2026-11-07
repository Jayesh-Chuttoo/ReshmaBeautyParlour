<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_queries.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'manager'])) {
    header("Location: ../public/index.php");
    exit;
}

$message = '';
$msgType = 'success';

// Handle Add / Edit Product submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_product' || $action === 'edit_product') {
        $id          = $_POST['product_id'] ?? null;
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
            $uploadDir = __DIR__ . '/../public/uploads/';
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
                $stmt = $pdo->prepare("INSERT INTO products (name, description, price, sizes, stock, status, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $success = $stmt->execute([$name, $description, $price, $sizesJson, $finalStock, $status, $imagePath]);
                $message = $success ? 'Product added successfully!' : 'Failed to add product.';
            } else {
                $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, sizes = ?, stock = ?, status = ?, image = ? WHERE id = ?");
                $success = $stmt->execute([$name, $description, $price, $sizesJson, $finalStock, $status, $imagePath, $id]);
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
    $editStmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $editStmt->execute([intval($_GET['edit_id'])]);
    $editProduct = $editStmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($editProduct['sizes'])) {
        $decoded = json_decode($editProduct['sizes'], true);
        if (is_array($decoded)) {
            $existingSizes = $decoded;
        }
    }
}

$products = function_exists('getAllProducts') ? getAllProducts($pdo) : [];
$pageTitle = "Products Management - Reshma Beauty Parlour";
require_once __DIR__ . '/../includes/components/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8 flex-1 w-full space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">Inventory & Products</h1>
            <p class="text-xs text-slate-500">Add beauty items or update pricing, sizes, stock, and availability.</p>
        </div>
        <a href="index.php" class="text-xs bg-slate-200 hover:bg-slate-300 px-3 py-2 rounded-lg font-bold text-slate-700 transition">&larr; Back to Dashboard</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="p-4 rounded-xl text-sm font-medium <?= $msgType === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Product Form (Dynamic Add / Edit) -->
    <div class="bg-white p-6 rounded-2xl border shadow-sm space-y-4">
        <h2 class="text-lg font-bold text-slate-800"><?= $editProduct ? 'Edit Product #' . $editProduct['id'] : 'Add New Product' ?></h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="<?= $editProduct ? 'edit_product' : 'add_product' ?>">
            <?php if ($editProduct): ?>
                <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>">
                <input type="hidden" name="existing_image" value="<?= htmlspecialchars($editProduct['image'] ?? '') ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Product Name *</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>" required class="w-full text-sm border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Price (Rs) *</label>
                    <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($editProduct['price'] ?? '') ?>" required class="w-full text-sm border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                    <select name="status" class="w-full text-sm border rounded-lg px-3 py-2 bg-white font-medium">
                        <option value="Available" <?= ($editProduct['status'] ?? '') === 'Available' ? 'selected' : '' ?>>Available</option>
                        <option value="Out of Stock" <?= ($editProduct['status'] ?? '') === 'Out of Stock' ? 'selected' : '' ?>>Out of Stock</option>
                        <option value="Discontinued" <?= ($editProduct['status'] ?? '') === 'Discontinued' ? 'selected' : '' ?>>Discontinued</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Product Image</label>
                    <input type="file" name="product_image" class="w-full text-xs border rounded-lg p-1.5 bg-white">
                </div>
            </div>

            <!-- DYNAMIC SIZE & QUANTITY BUILDER -->
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                <div class="flex justify-between items-center">
                    <div>
                        <label class="block text-xs font-bold text-slate-800">Sizes & Stock Breakdown</label>
                        <p class="text-[11px] text-slate-500">Add individual size variations (e.g., S, M, L or 100ml, 250ml) and their available quantities.</p>
                    </div>
                    <button type="button" onclick="addSizeRow()" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-3 py-1.5 rounded-lg transition">
                        <i class="fa-solid fa-plus mr-1"></i> Add Size
                    </button>
                </div>

                <div id="size-rows-container" class="space-y-2">
                    <?php if (!empty($existingSizes)): ?>
                        <?php foreach ($existingSizes as $sName => $sQty): ?>
                            <div class="size-row flex items-center gap-3">
                                <input type="text" name="size_names[]" value="<?= htmlspecialchars($sName) ?>" placeholder="Size (e.g. S, M, 250ml)" class="text-xs border rounded-lg px-3 py-1.5 w-1/2 bg-white">
                                <input type="number" name="size_qtys[]" value="<?= intval($sQty) ?>" placeholder="Quantity" min="0" oninput="calculateTotalStock()" class="size-qty-input text-xs border rounded-lg px-3 py-1.5 w-1/3 bg-white">
                                <button type="button" onclick="removeSizeRow(this)" class="text-xs bg-rose-100 hover:bg-rose-200 text-rose-700 font-bold px-2.5 py-1.5 rounded-lg transition"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="size-row flex items-center gap-3">
                            <input type="text" name="size_names[]" placeholder="Size (e.g. S, M, 250ml)" class="text-xs border rounded-lg px-3 py-1.5 w-1/2 bg-white">
                            <input type="number" name="size_qtys[]" value="0" placeholder="Quantity" min="0" oninput="calculateTotalStock()" class="size-qty-input text-xs border rounded-lg px-3 py-1.5 w-1/3 bg-white">
                            <button type="button" onclick="removeSizeRow(this)" class="text-xs bg-rose-100 hover:bg-rose-200 text-rose-700 font-bold px-2.5 py-1.5 rounded-lg transition"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex justify-between items-center pt-2 border-t text-xs font-semibold text-slate-700">
                    <span>Total Calculated Stock:</span>
                    <span id="total-stock-display" class="font-mono text-indigo-600 font-extrabold text-sm"><?= htmlspecialchars($editProduct['stock'] ?? 0) ?> units</span>
                    <input type="hidden" name="stock" id="stock-hidden-input" value="<?= htmlspecialchars($editProduct['stock'] ?? 0) ?>">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full text-sm border rounded-lg px-3 py-2"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold px-5 py-2.5 rounded-lg transition shadow-sm"><?= $editProduct ? 'Update Product' : 'Add Product' ?></button>
                <?php if ($editProduct): ?>
                    <a href="products.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold px-4 py-2.5 rounded-lg transition">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Product List Table -->
    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b text-xs uppercase text-slate-600">
                <tr>
                    <th class="p-4">Image</th>
                    <th class="p-4">Name</th>
                    <th class="p-4">Description</th>
                    <th class="p-4">Price</th>
                    <th class="p-4">Sizes & Quantities</th>
                    <th class="p-4">Total Stock</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
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
                        <tr class="hover:bg-slate-50">
                            <td class="p-4">
                                <?php if (!empty($p['image'])): ?>
                                    <img src="../public/<?= htmlspecialchars($p['image']) ?>" class="w-10 h-10 object-cover rounded-lg border">
                                <?php else: ?>
                                    <span class="text-xs text-slate-400">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 font-bold text-slate-800"><?= htmlspecialchars($p['name']) ?></td>
                            <td class="p-4 text-xs text-slate-500 max-w-xs truncate"><?= htmlspecialchars($p['description'] ?? 'N/A') ?></td>
                            <td class="p-4 text-emerald-600 font-extrabold">Rs <?= number_format($p['price'], 2) ?></td>
                            <td class="p-4 text-xs">
                                <?php if (!empty($availableSizes)): ?>
                                    <?php foreach ($availableSizes as $sKey => $sVal): ?>
                                        <span class="inline-block bg-slate-100 border text-slate-700 px-2 py-0.5 rounded font-mono mr-1 mb-1">
                                            <strong><?= htmlspecialchars($sKey) ?>:</strong> <?= $sVal ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-slate-400">Standard</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 font-mono font-bold text-slate-700"><?= $totalStock ?></td>
                            <td class="p-4"><span class="px-2.5 py-1 text-xs rounded-full bg-emerald-100 text-emerald-700 font-bold"><?= htmlspecialchars($p['status'] ?? 'Available') ?></span></td>
                            <td class="p-4 text-right">
                                <a href="products.php?edit_id=<?= $p['id'] ?>" class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold px-3 py-1.5 rounded-lg transition">Edit</a>
                            </td>
                        </tr>
                    <?php
                    endforeach;
                endif;

                if (!$hasVisibleProducts):
                    ?>
                    <tr>
                        <td colspan="8" class="p-6 text-center text-slate-400">No active products with available stock found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
    function addSizeRow() {
        const container = document.getElementById('size-rows-container');
        const row = document.createElement('div');
        row.className = 'size-row flex items-center gap-3';
        row.innerHTML = `
        <input type="text" name="size_names[]" placeholder="Size (e.g. S, M, 250ml)" class="text-xs border rounded-lg px-3 py-1.5 w-1/2 bg-white">
        <input type="number" name="size_qtys[]" value="0" placeholder="Quantity" min="0" oninput="calculateTotalStock()" class="size-qty-input text-xs border rounded-lg px-3 py-1.5 w-1/3 bg-white">
        <button type="button" onclick="removeSizeRow(this)" class="text-xs bg-rose-100 hover:bg-rose-200 text-rose-700 font-bold px-2.5 py-1.5 rounded-lg transition"><i class="fa-solid fa-trash"></i></button>
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