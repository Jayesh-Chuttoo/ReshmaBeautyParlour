<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Handle Form POST Actions
$message = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_product' || $action === 'edit_product') {
        $id = $_POST['product_id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $sizes = $_POST['sizes'] ?? []; // Array of sizes
        $file = $_FILES['product_image'] ?? null;

        if (!empty($name) && $price >= 0) {
            if ($action === 'add_product') {
                $result = addProduct($pdo, $name, $description, $price, $stock, $sizes, $file);
            } else {
                $result = updateProduct($pdo, $id, $name, $description, $price, $stock, $sizes, $file);
            }
            $message = $result['message'];
            $msgType = $result['success'] ? 'success' : 'error';
        } else {
            $message = 'Please provide a valid product name and price.';
            $msgType = 'error';
        }
    } elseif ($action === 'add_service') {
        $serviceName = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!empty($serviceName)) {
            $stmt = $pdo->prepare("INSERT INTO services (name, description) VALUES (:name, :description)");
            $success = $stmt->execute(['name' => $serviceName, 'description' => $description]);
            $message = $success ? 'Service added successfully!' : 'Failed to add service.';
            $msgType = $success ? 'success' : 'error';
        } else {
            $message = 'Service name is required.';
            $msgType = 'error';
        }
    } elseif ($action === 'add_booking') {
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

// Fetch orders safely (handles case if orders table is named differently)
$orders = [];
try {
    $orderStmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
    $orders = $orderStmt->fetchAll();
} catch (Exception $e) {
    // Orders table might be structured differently or pending creation
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
        <!-- Top Navigation Bar -->
        <header class="bg-indigo-900 text-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-spa text-2xl text-pink-400"></i>
                    <h1 class="text-xl font-bold tracking-wide">Reshma Beauty Parlour <span class="text-xs bg-indigo-700 px-2 py-1 rounded text-indigo-200">Admin Panel</span></h1>
                </div>
                <div class="text-sm text-indigo-200">
                    <i class="fa-solid fa-user-shield mr-1"></i> Administrator
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="max-w-7xl mx-auto px-4 py-8 flex-1 w-full">

            <?php if (!empty($message)): ?>
                <div class="mb-6 p-4 rounded shadow-sm <?= $msgType === 'success' ? 'bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700' : 'bg-red-100 border-l-4 border-red-500 text-red-700' ?>">
                    <p><i class="fa-solid <?= $msgType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?> mr-2"></i><?= htmlspecialchars($message) ?></p>
                </div>
            <?php endif; ?>

            <!-- Dashboard Navigation Tabs -->
            <div class="flex border-b border-gray-200 mb-6 space-x-4 overflow-x-auto">
                <button onclick="switchTab('products')" id="btn-products" class="tab-btn px-4 py-2 font-medium text-indigo-600 border-b-2 border-indigo-600 focus:outline-none whitespace-nowrap">
                    <i class="fa-solid fa-box-open mr-2"></i> Products (<?= count($products) ?>)
                </button>
                <button onclick="switchTab('orders')" id="btn-orders" class="tab-btn px-4 py-2 font-medium text-gray-500 hover:text-indigo-600 focus:outline-none whitespace-nowrap">
                    <i class="fa-solid fa-bag-shopping mr-2"></i> Orders (<?= count($orders) ?>)
                </button>
                <button onclick="switchTab('services')" id="btn-services" class="tab-btn px-4 py-2 font-medium text-gray-500 hover:text-indigo-600 focus:outline-none whitespace-nowrap">
                    <i class="fa-solid fa-scissors mr-2"></i> Services (<?= count($services) ?>)
                </button>
                <button onclick="switchTab('bookings')" id="btn-bookings" class="tab-btn px-4 py-2 font-medium text-gray-500 hover:text-indigo-600 focus:outline-none whitespace-nowrap">
                    <i class="fa-solid fa-calendar-check mr-2"></i> Bookings (<?= count($bookings) ?>)
                </button>
                <button onclick="switchTab('users')" id="btn-users" class="tab-btn px-4 py-2 font-medium text-gray-500 hover:text-indigo-600 focus:outline-none whitespace-nowrap">
                    <i class="fa-solid fa-users mr-2"></i> Users (<?= count($users) ?>)
                </button>
            </div>

            <!-- TAB 1: PRODUCTS -->
            <div id="tab-products" class="tab-content space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">Product Catalog & Stock</h2>
                    <button onclick="openProductModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow">
                        <i class="fa-solid fa-plus mr-1"></i> Add Product
                    </button>
                </div>

                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b">
                                <th class="p-4">Image</th>
                                <th class="p-4">Name</th>
                                <th class="p-4">Price</th>
                                <th class="p-4">Stock Count</th>
                                <th class="p-4">Sizes Available</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-gray-500">No products found.</td>
                                </tr>
                                <?php else: foreach ($products as $p): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-4">
                                            <?php if (!empty($p['image'])): ?>
                                                <img src="<?= htmlspecialchars($p['image']) ?>" alt="" class="w-12 h-12 object-cover rounded-lg border">
                                            <?php else: ?>
                                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400"><i class="fa-solid fa-image"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 font-semibold text-gray-800"><?= htmlspecialchars($p['name']) ?></td>
                                        <td class="p-4 text-emerald-600 font-medium">$<?= number_format($p['price'], 2) ?></td>
                                        <td class="p-4 font-bold <?= $p['stock'] > 0 ? 'text-gray-700' : 'text-red-500' ?>"><?= $p['stock'] ?></td>
                                        <td class="p-4">
                                            <?php if (!empty($p['sizes']) && is_array($p['sizes'])): ?>
                                                <div class="flex flex-wrap gap-1">
                                                    <?php foreach ($p['sizes'] as $sz): ?>
                                                        <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded text-xs font-mono"><?= htmlspecialchars($sz) ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400 text-xs">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 text-right">
                                            <button onclick='openEditProductModal(<?= json_encode($p) ?>)' class="text-indigo-600 hover:text-indigo-900 font-medium text-sm bg-indigo-50 px-3 py-1.5 rounded-lg">
                                                <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                            <?php endforeach;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: ORDERS -->
            <div id="tab-orders" class="tab-content hidden space-y-6">
                <h2 class="text-xl font-bold text-gray-800">Customer Orders</h2>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b">
                                <th class="p-4">Order ID</th>
                                <th class="p-4">Customer</th>
                                <th class="p-4">Total Amount</th>
                                <th class="p-4">Status</th>
                                <th class="p-4">Order Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-gray-500">No orders recorded yet.</td>
                                </tr>
                                <?php else: foreach ($orders as $ord): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-4 font-mono font-semibold text-indigo-600">#<?= $ord['id'] ?></td>
                                        <td class="p-4 font-medium text-gray-800"><?= htmlspecialchars($ord['customer_name'] ?? $ord['user_id'] ?? 'Guest') ?></td>
                                        <td class="p-4 text-emerald-600 font-semibold">$<?= number_format($ord['total_amount'] ?? 0, 2) ?></td>
                                        <td class="p-4">
                                            <span class="px-2.5 py-1 text-xs rounded-full font-medium bg-amber-100 text-amber-700">
                                                <?= htmlspecialchars($ord['status'] ?? 'Completed') ?>
                                            </span>
                                        </td>
                                        <td class="p-4 text-gray-600"><?= htmlspecialchars($ord['created_at'] ?? 'N/A') ?></td>
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
                    <h2 class="text-xl font-bold text-gray-800">Salon Services</h2>
                    <button onclick="openModal('serviceModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow">
                        <i class="fa-solid fa-plus mr-1"></i> Add Service
                    </button>
                </div>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b">
                                <th class="p-4">ID</th>
                                <th class="p-4">Service Name</th>
                                <th class="p-4">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <?php if (empty($services)): ?>
                                <tr>
                                    <td colspan="3" class="p-6 text-center text-gray-500">No services found.</td>
                                </tr>
                                <?php else: foreach ($services as $s): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-4 text-gray-500">#<?= $s['id'] ?></td>
                                        <td class="p-4 font-semibold text-gray-800"><?= htmlspecialchars($s['name'] ?? $s['service_name'] ?? '') ?></td>
                                        <td class="p-4 text-gray-600"><?= htmlspecialchars($s['description'] ?? '') ?></td>
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
                    <button onclick="openModal('bookingModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow">
                        <i class="fa-solid fa-plus mr-1"></i> Add Booking
                    </button>
                </div>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b">
                                <th class="p-4">Client</th>
                                <th class="p-4">Service</th>
                                <th class="p-4">Contact</th>
                                <th class="p-4">People</th>
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
                                        <td class="p-4 text-gray-600"><?= $b['people_count'] ?></td>
                                        <td class="p-4">
                                            <span class="px-2.5 py-1 text-xs rounded-full font-medium <?= $b['onsite_visit'] ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
                                                <?= $b['onsite_visit'] ? 'On-site Visit' : 'Salon Visit' ?>
                                            </span>
                                        </td>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price ($)</label>
                        <input type="number" step="0.01" name="price" id="productPrice" required class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock Count</label>
                        <input type="number" name="stock" id="productStock" value="0" required class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="productDescription" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sizes (Array Elements)</label>
                    <div id="sizesContainer" class="space-y-2 mb-2"></div>
                    <button type="button" onclick="addSizeInput('')" class="text-xs text-indigo-600 font-medium hover:underline">+ Add Size Option</button>
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

    <!-- MODAL: ADD SERVICE -->
    <div id="serviceModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Add New Service</h3>
                <button onclick="closeModal('serviceModal')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_service">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Service Name</label>
                    <input type="text" name="name" required class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
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
                    <label for="onsiteCheck" class="text-sm font-medium text-gray-700">On-site Visit</label>
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
            document.getElementById('productStock').value = '0';
            document.getElementById('productDescription').value = '';
            document.getElementById('sizesContainer').innerHTML = '';
            addSizeInput('');
            openModal('productModal');
        }

        function openEditProductModal(product) {
            document.getElementById('productModalTitle').innerText = 'Edit Product #' + product.id;
            document.getElementById('productFormAction').value = 'edit_product';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productStock').value = product.stock;
            document.getElementById('productDescription').value = product.description;

            const container = document.getElementById('sizesContainer');
            container.innerHTML = '';
            if (product.sizes && Array.isArray(product.sizes) && product.sizes.length > 0) {
                product.sizes.forEach(sz => addSizeInput(sz));
            } else {
                addSizeInput('');
            }
            openModal('productModal');
        }

        function addSizeInput(value) {
            const container = document.getElementById('sizesContainer');
            const div = document.createElement('div');
            div.className = 'flex items-center space-x-2';
            div.innerHTML = `
                <input type="text" name="sizes[]" value="${value}" placeholder="e.g. S, M, L or 250ml" class="w-full border rounded-lg px-3 py-1.5 text-sm outline-none">
                <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 px-2"><i class="fa-solid fa-trash"></i></button>
            `;
            container.appendChild(div);
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