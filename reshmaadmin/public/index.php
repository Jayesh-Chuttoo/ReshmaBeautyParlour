<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Handle Form POST Actions
$message = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- PRODUCT ACTIONS ---
    if ($action === 'add_product' || $action === 'edit_product') {
        $id = $_POST['product_id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);

        // Capture S, M, L stock counts and calculate total stock
        $stockS = intval($_POST['stock_s'] ?? 0);
        $stockM = intval($_POST['stock_m'] ?? 0);
        $stockL = intval($_POST['stock_l'] ?? 0);
        $totalStock = $stockS + $stockM + $stockL;

        $sizesJson = json_encode(['S' => $stockS, 'M' => $stockM, 'L' => $stockL]);
        $status = $_POST['status'] ?? 'available';
        $file = $_FILES['product_image'] ?? null;

        if (!empty($name) && $price >= 0) {
            if ($action === 'add_product') {
                $result = addProductWithSizes($pdo, $name, $description, $price, $totalStock, $sizesJson, $status, $file);
            } else {
                $result = updateProductWithSizes($pdo, $id, $name, $description, $price, $totalStock, $sizesJson, $status, $file);
            }
            $message = $result['message'];
            $msgType = $result['success'] ? 'success' : 'error';
        } else {
            $message = 'Please provide a valid product name and price.';
            $msgType = 'error';
        }
    } elseif ($action === 'delete_product') {
        $id = intval($_POST['product_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $success = $stmt->execute([$id]);
        $message = $success ? 'Product deleted successfully!' : 'Failed to delete product.';
        $msgType = $success ? 'success' : 'error';
    } elseif ($action === 'toggle_product_status') {
        $id = intval($_POST['product_id'] ?? 0);
        $newStatus = $_POST['status'] === 'sold' ? 'available' : 'sold';
        $stmt = $pdo->prepare("UPDATE products SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);
        $message = 'Product status updated!';
        $msgType = 'success';
    }

    // --- SERVICE ACTIONS ---
    // --- SERVICE ACTIONS ---
    elseif ($action === 'add_service' || $action === 'edit_service') {
        $id = $_POST['service_id'] ?? null;
        $serviceName = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $allowsDisplacement = isset($_POST['allows_displacement']) ? 1 : 0;
        $displacementFee = floatval($_POST['displacement_fee'] ?? 0);
        $file = $_FILES['service_image'] ?? null;

        // Handle image upload if provided
        $imagePath = null;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'service_' . uniqid() . '.' . $ext;
            $uploadDir = __DIR__ . '/../public/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                $imagePath = 'uploads/' . $filename;
            }
        }

        if (!empty($serviceName)) {
            if ($action === 'add_service') {
                // FIXED: Changed 'name' to 'title'
                $stmt = $pdo->prepare("INSERT INTO services (title, description, price, image, allows_displacement, displacement_fee) VALUES (?, ?, ?, ?, ?, ?)");
                $success = $stmt->execute([$serviceName, $description, $price, $imagePath, $allowsDisplacement, $displacementFee]);
            } else {
                if ($imagePath) {
                    // FIXED: Changed 'name' to 'title'
                    $stmt = $pdo->prepare("UPDATE services SET title=?, description=?, price=?, image=?, allows_displacement=?, displacement_fee=? WHERE id=?");
                    $success = $stmt->execute([$serviceName, $description, $price, $imagePath, $allowsDisplacement, $displacementFee, $id]);
                } else {
                    // FIXED: Changed 'name' to 'title'
                    $stmt = $pdo->prepare("UPDATE services SET title=?, description=?, price=?, allows_displacement=?, displacement_fee=? WHERE id=?");
                    $success = $stmt->execute([$serviceName, $description, $price, $allowsDisplacement, $displacementFee, $id]);
                }
            }
            $message = $success ? 'Service saved successfully!' : 'Failed to save service.';
            $msgType = $success ? 'success' : 'error';
        } else {
            $message = 'Service name is required.';
            $msgType = 'error';
        }
    }

    // --- BOOKING ACTIONS ---
    elseif ($action === 'add_booking') {
        $serviceName = $_POST['service_name'] ?? '';
        $clientName = $_POST['client_name'] ?? '';
        $contact = $_POST['contact'] ?? '';
        $peopleCount = intval($_POST['people_count'] ?? 1);
        $onsiteVisit = isset($_POST['onsite_visit']) ? 1 : 0;
        $bookingDate = $_POST['booking_date'] ?? date('Y-m-d H:i:s');

        $success = insertBooking($pdo, $serviceName, $clientName, $contact, $peopleCount, $onsiteVisit, $bookingDate);
        $message = $success ? 'Booking created successfully!' : 'Failed to create booking.';
        $msgType = $success ? 'success' : 'error';
    }
}

// Fetch all database records safely
$products = function_exists('getAllProducts') ? getAllProducts($pdo) : [];
$services = function_exists('getAllServices') ? getAllServices($pdo) : [];
$bookings = function_exists('getAllBookings') ? getAllBookings($pdo) : [];
$users = function_exists('getAllUsers') ? getAllUsers($pdo) : [];

$orders = [];
try {
    $orderStmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
    $orders = $orderStmt->fetchAll();
} catch (Exception $e) {
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reshma Beauty Parlour - Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body class="bg-gray-50 font-sans antialiased">
    <div class="min-h-screen flex flex-col">
        <header class="bg-indigo-900 text-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-spa text-2xl text-pink-400"></i>
                    <h1 class="text-xl font-bold tracking-wide">Reshma Beauty Parlour <span class="text-xs bg-indigo-700 px-2 py-1 rounded text-indigo-200">Admin Panel</span></h1>
                </div>
                <div class="text-sm text-indigo-200"><i class="fa-solid fa-user-shield mr-1"></i> Administrator</div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 py-8 flex-1 w-full">
            <?php if (!empty($message)): ?>
                <div class="mb-6 p-4 rounded shadow-sm <?= $msgType === 'success' ? 'bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700' : 'bg-red-100 border-l-4 border-red-500 text-red-700' ?>">
                    <p><i class="fa-solid <?= $msgType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?> mr-2"></i><?= htmlspecialchars($message) ?></p>
                </div>
            <?php endif; ?>

            <div class="flex border-b border-gray-200 mb-6 space-x-4 overflow-x-auto">
                <button onclick="switchTab('products')" id="btn-products" class="tab-btn px-4 py-2 font-medium text-indigo-600 border-b-2 border-indigo-600 focus:outline-none whitespace-nowrap"><i class="fa-solid fa-box-open mr-2"></i> Products (<?= count($products) ?>)</button>
                <button onclick="switchTab('orders')" id="btn-orders" class="tab-btn px-4 py-2 font-medium text-gray-500 hover:text-indigo-600 focus:outline-none whitespace-nowrap"><i class="fa-solid fa-bag-shopping mr-2"></i> Orders & Delivery (<?= count($orders) ?>)</button>
                <button onclick="switchTab('services')" id="btn-services" class="tab-btn px-4 py-2 font-medium text-gray-500 hover:text-indigo-600 focus:outline-none whitespace-nowrap"><i class="fa-solid fa-scissors mr-2"></i> Services (<?= count($services) ?>)</button>
                <button onclick="switchTab('bookings')" id="btn-bookings" class="tab-btn px-4 py-2 font-medium text-gray-500 hover:text-indigo-600 focus:outline-none whitespace-nowrap"><i class="fa-solid fa-calendar-check mr-2"></i> Bookings (<?= count($bookings) ?>)</button>
                <button onclick="switchTab('users')" id="btn-users" class="tab-btn px-4 py-2 font-medium text-gray-500 hover:text-indigo-600 focus:outline-none whitespace-nowrap"><i class="fa-solid fa-users mr-2"></i> Users (<?= count($users) ?>)</button>
            </div>

            <!-- TAB 1: PRODUCTS -->
            <div id="tab-products" class="tab-content space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">Product Catalog & S/M/L Stock</h2>
                    <button onclick="openProductModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow"><i class="fa-solid fa-plus mr-1"></i> Add Product</button>
                </div>

                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b">
                                <th class="p-4">Image</th>
                                <th class="p-4">Name</th>
                                <th class="p-4">Price</th>
                                <th class="p-4">Stock Breakdown (S / M / L)</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-gray-500">No products found.</td>
                                </tr>
                                <?php else: foreach ($products as $p):
                                    $sizesData = is_array($p['sizes'] ?? null) ? $p['sizes'] : (json_decode($p['sizes'] ?? '{}', true) ?: ['S' => 0, 'M' => 0, 'L' => 0]);
                                    $isSold = ($p['status'] ?? 'available') === 'sold';
                                ?>
                                    <tr class="hover:bg-gray-50 <?= $isSold ? 'opacity-60 bg-gray-50' : '' ?>">
                                        <td class="p-4">
                                            <?php if (!empty($p['image'])): ?>
                                                <img src="<?= htmlspecialchars($p['image']) ?>" alt="" class="w-12 h-12 object-cover rounded-lg border">
                                            <?php else: ?>
                                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400"><i class="fa-solid fa-image"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 font-semibold text-gray-800"><?= htmlspecialchars($p['name']) ?></td>
                                        <td class="p-4 text-emerald-600 font-medium">Rs <?= number_format($p['price'], 2) ?></td>
                                        <td class="p-4">
                                            <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded text-xs font-mono mr-1">S: <?= $sizesData['S'] ?? 0 ?></span>
                                            <span class="bg-emerald-50 text-emerald-700 px-2 py-1 rounded text-xs font-mono mr-1">M: <?= $sizesData['M'] ?? 0 ?></span>
                                            <span class="bg-purple-50 text-purple-700 px-2 py-1 rounded text-xs font-mono">L: <?= $sizesData['L'] ?? 0 ?></span>
                                        </td>
                                        <td class="p-4">
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="action" value="toggle_product_status">
                                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                                <input type="hidden" name="status" value="<?= $p['status'] ?? 'available' ?>">
                                                <button type="submit" class="px-3 py-1 text-xs rounded-full font-medium <?= $isSold ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
                                                    <?= $isSold ? 'Sold Out' : 'Available' ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="p-4 text-right space-x-2">
                                            <button onclick='openEditProductModal(<?= json_encode($p) ?>)' class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1.5 rounded-lg text-xs font-medium"><i class="fa-solid fa-pen-to-square"></i></button>
                                            <form method="POST" class="inline" onsubmit="return confirm('Delete this product?');">
                                                <input type="hidden" name="action" value="delete_product">
                                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 px-3 py-1.5 rounded-lg text-xs font-medium"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                            <?php endforeach;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: ORDERS & DELIVERY -->
            <div id="tab-orders" class="tab-content hidden space-y-6">
                <h2 class="text-xl font-bold text-gray-800">Customer Orders & Delivery Management</h2>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b">
                                <th class="p-4">Order ID</th>
                                <th class="p-4">Customer</th>
                                <th class="p-4">Delivery Option</th>
                                <th class="p-4">Delivery Fee</th>
                                <th class="p-4">Total Amount</th>
                                <th class="p-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-gray-500">No orders recorded yet.</td>
                                </tr>
                                <?php else: foreach ($orders as $ord): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-4 font-mono font-semibold text-indigo-600">#<?= $ord['id'] ?></td>
                                        <td class="p-4 font-medium text-gray-800"><?= htmlspecialchars($ord['customer_name'] ?? 'Guest') ?></td>
                                        <td class="p-4 text-gray-600 font-medium">
                                            <?php
                                            $dtype = $ord['delivery_type'] ?? 'standard_postal';
                                            if ($dtype === 'express_personal') echo 'Express Delivery (Personal)';
                                            elseif ($dtype === 'express_postal') echo 'Express Delivery (Postal)';
                                            else echo 'Standard Delivery (Postal)';
                                            ?>
                                        </td>
                                        <td class="p-4 text-indigo-600 font-medium">Rs <?= number_format($ord['delivery_fee'] ?? 0, 2) ?></td>
                                        <td class="p-4 text-emerald-600 font-semibold">Rs <?= number_format($ord['total_amount'] ?? 0, 2) ?></td>
                                        <td class="p-4"><span class="px-2.5 py-1 text-xs rounded-full font-medium bg-amber-100 text-amber-700"><?= htmlspecialchars($ord['status'] ?? 'Completed') ?></span></td>
                                    </tr>
                            <?php endforeach;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: SERVICES -->
            <div id="tab-services" class="tab-content hidden space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">Salon Services & Displacement Fees</h2>
                    <button onclick="openServiceModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow"><i class="fa-solid fa-plus mr-1"></i> Add Service</button>
                </div>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b">
                                <th class="p-4">Image</th>
                                <th class="p-4">Service Name</th>
                                <th class="p-4">Standard Price</th>
                                <th class="p-4">Displacement & Fee</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <?php if (empty($services)): ?>
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-gray-500">No services found.</td>
                                </tr>
                                <?php else: foreach ($services as $s): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-4">
                                            <?php if (!empty($s['image'])): ?>
                                                <img src="<?= htmlspecialchars($s['image']) ?>" alt="" class="w-12 h-12 object-cover rounded-lg border">
                                            <?php else: ?>
                                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400"><i class="fa-solid fa-scissors"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 font-semibold text-gray-800"><?= htmlspecialchars($s['name'] ?? $s['title'] ?? '') ?></td>
                                        <td class="p-4 text-emerald-600 font-semibold">Rs <?= number_format($s['price'] ?? 0, 2) ?></td>
                                        <td class="p-4">
                                            <?php if (!empty($s['allows_displacement'])): ?>
                                                <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded text-xs">Displacement (Rs <?= number_format($s['displacement_fee'] ?? 0, 2) ?>)</span>
                                            <?php else: ?>
                                                <span class="text-gray-400 text-xs">Salon Only</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 text-right space-x-2">
                                            <button onclick='openEditServiceModal(<?= json_encode($s) ?>)' class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1.5 rounded-lg text-xs font-medium"><i class="fa-solid fa-pen-to-square"></i></button>
                                            <form method="POST" class="inline" onsubmit="return confirm('Delete this service?');">
                                                <input type="hidden" name="action" value="delete_service">
                                                <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 px-3 py-1.5 rounded-lg text-xs font-medium"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                            <?php endforeach;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: BOOKINGS -->
            <div id="tab-bookings" class="tab-content hidden space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">Client Bookings</h2>
                    <button onclick="openModal('bookingModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow"><i class="fa-solid fa-plus mr-1"></i> Add Booking</button>
                </div>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b">
                                <th class="p-4">Client</th>
                                <th class="p-4">Service</th>
                                <th class="p-4">Contact</th>
                                <th class="p-4">People Count</th>
                                <th class="p-4">Type</th>
                                <th class="p-4">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-gray-500">No bookings found.</td>
                                </tr>
                                <?php else: foreach ($bookings as $b): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-4 font-semibold text-gray-800"><?= htmlspecialchars($b['client_name']) ?></td>
                                        <td class="p-4 text-indigo-600"><?= htmlspecialchars($b['service_name']) ?></td>
                                        <td class="p-4 text-gray-600"><?= htmlspecialchars($b['contact']) ?></td>
                                        <td class="p-4 font-bold text-gray-700"><?= $b['people_count'] ?> People</td>
                                        <td class="p-4"><span class="px-2.5 py-1 text-xs rounded-full font-medium <?= $b['onsite_visit'] ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>"><?= $b['onsite_visit'] ? 'Displacement / On-site' : 'Salon Visit' ?></span></td>
                                        <td class="p-4 text-gray-600"><?= htmlspecialchars($b['booking_date']) ?></td>
                                    </tr>
                            <?php endforeach;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 5: USERS -->
            <div id="tab-users" class="tab-content hidden space-y-6">
                <h2 class="text-xl font-bold text-gray-800">Registered Users</h2>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b">
                                <th class="p-4">ID</th>
                                <th class="p-4">Username</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="2" class="p-6 text-center text-gray-500">No users found.</td>
                                </tr>
                                <?php else: foreach ($users as $u): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-4 text-gray-500">#<?= $u['id'] ?></td>
                                        <td class="p-4 font-semibold text-gray-800"><?= htmlspecialchars($u['username']) ?></td>
                                    </tr>
                            <?php endforeach;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL: ADD/EDIT PRODUCT -->
    <div id="productModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl relative max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 id="productModalTitle" class="text-lg font-bold text-gray-800">Add New Product</h3>
                <button onclick="closeModal('productModal')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" id="productFormAction" value="add_product">
                <input type="hidden" name="product_id" id="productId">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                    <input type="text" name="name" id="productName" required class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price (Rs)</label>
                        <input type="number" step="0.01" name="price" id="productPrice" required class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="productStatus" class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="available">Available</option>
                            <option value="sold">Sold Out</option>
                        </select>
                    </div>
                </div>

                <div class="border p-3 rounded-lg bg-gray-50 space-y-3">
                    <label class="block text-sm font-semibold text-gray-700">Stock Count per Size</label>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Small (S)</label>
                            <input type="number" name="stock_s" id="stockS" value="0" min="0" class="w-full border rounded px-2 py-1 text-sm bg-white">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Medium (M)</label>
                            <input type="number" name="stock_m" id="stockM" value="0" min="0" class="w-full border rounded px-2 py-1 text-sm bg-white">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Large (L)</label>
                            <input type="number" name="stock_l" id="stockL" value="0" min="0" class="w-full border rounded px-2 py-1 text-sm bg-white">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="productDescription" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
                    <input type="file" name="product_image" accept="image/*" class="w-full text-sm text-gray-500">
                </div>
                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" onclick="closeModal('productModal')" class="px-4 py-2 border rounded-lg text-sm text-gray-600">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ADD/EDIT SERVICE -->
    <div id="serviceModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 id="serviceModalTitle" class="text-lg font-bold text-gray-800">Add New Service</h3>
                <button onclick="closeModal('serviceModal')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" id="serviceFormAction" value="add_service">
                <input type="hidden" name="service_id" id="serviceId">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Service Name</label>
                    <input type="text" name="name" id="serviceName" required class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Standard Price (Rs)</label>
                        <input type="number" step="0.01" name="price" id="servicePrice" required class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Displacement Fee (Rs)</label>
                        <input type="number" step="0.01" name="displacement_fee" id="serviceDisplacementFee" value="0" class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="flex items-center space-x-2 pt-1">
                    <input type="checkbox" name="allows_displacement" value="1" id="allowsDisplacement" class="rounded text-indigo-600" onchange="togglePeopleCountRequirement(this)">
                    <label for="allowsDisplacement" class="text-sm font-medium text-gray-700">Allow Displacement (Asks for People Count)</label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="serviceDescription" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Service Image</label>
                    <input type="file" name="service_image" accept="image/*" class="w-full text-sm text-gray-500">
                </div>
                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" onclick="closeModal('serviceModal')" class="px-4 py-2 border rounded-lg text-sm text-gray-600">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Save Service</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ADD BOOKING -->
    <div id="bookingModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Add New Booking</h3>
                <button onclick="closeModal('bookingModal')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_booking">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Service Name</label>
                    <input type="text" name="service_name" required class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client Name</label>
                    <input type="text" name="client_name" required class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Info</label>
                    <input type="text" name="contact" required class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">People Count</label>
                        <input type="number" name="people_count" value="1" min="1" required class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Booking Date</label>
                        <input type="datetime-local" name="booking_date" required class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="flex items-center space-x-2 pt-1">
                    <input type="checkbox" name="onsite_visit" value="1" id="onsiteCheck" class="rounded text-indigo-600">
                    <label for="onsiteCheck" class="text-sm font-medium text-gray-700">On-site Visit / Displacement</label>
                </div>
                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" onclick="closeModal('bookingModal')" class="px-4 py-2 border rounded-lg text-sm text-gray-600">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Create Booking</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript for Tabs & Modals -->
    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('text-indigo-600', 'border-b-2', 'border-indigo-600');
                btn.classList.add('text-gray-500');
            });
            document.getElementById('tab-' + tabId).classList.remove('hidden');
            const activeBtn = document.getElementById('btn-' + tabId);
            activeBtn.classList.remove('text-gray-500');
            activeBtn.classList.add('text-indigo-600', 'border-b-2', 'border-indigo-600');
        }

        function openProductModal() {
            document.getElementById('productModalTitle').innerText = 'Add New Product';
            document.getElementById('productFormAction').value = 'add_product';
            document.getElementById('productId').value = '';
            document.getElementById('productName').value = '';
            document.getElementById('productPrice').value = '';
            document.getElementById('productStatus').value = 'available';
            document.getElementById('stockS').value = '0';
            document.getElementById('stockM').value = '0';
            document.getElementById('stockL').value = '0';
            document.getElementById('productDescription').value = '';
            openModal('productModal');
        }

        function openEditProductModal(product) {
            document.getElementById('productModalTitle').innerText = 'Edit Product #' + product.id;
            document.getElementById('productFormAction').value = 'edit_product';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productStatus').value = product.status || 'available';
            document.getElementById('productDescription').value = product.description;

            let sizes = {};
            try {
                sizes = typeof product.sizes === 'string' ? JSON.parse(product.sizes) : product.sizes;
            } catch (e) {
                sizes = {};
            }

            document.getElementById('stockS').value = sizes.S || 0;
            document.getElementById('stockM').value = sizes.M || 0;
            document.getElementById('stockL').value = sizes.L || 0;
            openModal('productModal');
        }

        function openServiceModal() {
            document.getElementById('serviceModalTitle').innerText = 'Add New Service';
            document.getElementById('serviceFormAction').value = 'add_service';
            document.getElementById('serviceId').value = '';
            document.getElementById('serviceName').value = '';
            document.getElementById('servicePrice').value = '';
            document.getElementById('serviceDisplacementFee').value = '0';
            document.getElementById('serviceDescription').value = '';
            document.getElementById('allowsDisplacement').checked = false;
            openModal('serviceModal');
        }

        function openEditServiceModal(service) {
            document.getElementById('serviceModalTitle').innerText = 'Edit Service #' + service.id;
            document.getElementById('serviceFormAction').value = 'edit_service';
            document.getElementById('serviceId').value = service.id;
            document.getElementById('serviceName').value = service.name || service.title || '';
            document.getElementById('servicePrice').value = service.price || 0;
            document.getElementById('serviceDisplacementFee').value = service.displacement_fee || 0;
            document.getElementById('serviceDescription').value = service.description || '';
            document.getElementById('allowsDisplacement').checked = service.allows_displacement == 1;
            openModal('serviceModal');
        }

        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
            document.getElementById(modalId).classList.add('flex');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            document.getElementById(modalId).classList.remove('flex');
        }
    </script>
</body>

</html>