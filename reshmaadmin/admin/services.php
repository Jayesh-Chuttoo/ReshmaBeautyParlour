<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_queries.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'manager'])) {
    header("Location: ../public/login.php");
    exit;
}

$message = '';
$msgType = 'success';

// Handle Add / Edit Service submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_service' || $action === 'edit_service') {
        $id          = $_POST['service_id'] ?? null;
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = floatval($_POST['price'] ?? 0);

        // Handle Image Upload
        $imagePath = $_POST['existing_image'] ?? '';
        if (isset($_FILES['service_image']) && $_FILES['service_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileExtension = pathinfo($_FILES['service_image']['name'], PATHINFO_EXTENSION);
            $fileName = 'serv_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
            if (move_uploaded_file($_FILES['service_image']['tmp_name'], $uploadDir . $fileName)) {
                $imagePath = 'uploads/' . $fileName;
            }
        }

        if (!empty($title) && $price >= 0) {
            if ($action === 'add_service') {
                $stmt = $pdo->prepare("INSERT INTO services (title, description, price, image) VALUES (?, ?, ?, ?)");
                $success = $stmt->execute([$title, $description, $price, $imagePath]);
                $message = $success ? 'Service added successfully!' : 'Failed to add service.';
            } else {
                $stmt = $pdo->prepare("UPDATE services SET title = ?, description = ?, price = ?, image = ? WHERE id = ?");
                $success = $stmt->execute([$title, $description, $price, $imagePath, $id]);
                $message = $success ? 'Service updated successfully!' : 'Failed to update service.';
            }
            $msgType = $success ? 'success' : 'error';
        } else {
            $message = 'Please provide a valid service title and price.';
            $msgType = 'error';
        }
    }
}

// Fetch single service for editing if 'edit_id' is passed in URL
$editService = null;
if (isset($_GET['edit_id'])) {
    $editStmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $editStmt->execute([intval($_GET['edit_id'])]);
    $editService = $editStmt->fetch(PDO::FETCH_ASSOC);
}

$services = function_exists('getAllServices') ? getAllServices($pdo) : [];
$pageTitle = "Services Management - Reshma Beauty Parlour";
require_once __DIR__ . '/../includes/components/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8 flex-1 w-full space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">Salon Hair & Beauty Services</h1>
            <p class="text-xs text-slate-500">Add beauty treatments, update standard rates, descriptions, and promotional images.</p>
        </div>
        <a href="index.php" class="text-xs bg-slate-200 hover:bg-slate-300 px-3 py-2 rounded-lg font-bold text-slate-700 transition">&larr; Back to Dashboard</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="p-4 rounded-xl text-sm font-medium <?= $msgType === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Service Form (Dynamic Add / Edit) -->
    <div class="bg-white p-6 rounded-2xl border shadow-sm space-y-4">
        <h2 class="text-lg font-bold text-slate-800"><i class="fa-solid fa-scissors text-purple-600 mr-2"></i><?= $editService ? 'Edit Service #' . $editService['id'] : 'Add New Service' ?></h2>
        <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <input type="hidden" name="action" value="<?= $editService ? 'edit_service' : 'add_service' ?>">
            <?php if ($editService): ?>
                <input type="hidden" name="service_id" value="<?= $editService['id'] ?>">
                <input type="hidden" name="existing_image" value="<?= htmlspecialchars($editService['image'] ?? '') ?>">
            <?php endif; ?>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Service Title *</label>
                <input type="text" name="title" value="<?= htmlspecialchars($editService['title'] ?? $editService['name'] ?? '') ?>" required class="w-full text-sm border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Standard Rate (Rs) *</label>
                <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($editService['price'] ?? '') ?>" required class="w-full text-sm border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Service Image</label>
                <input type="file" name="service_image" class="w-full text-xs border rounded-lg p-1.5 bg-white">
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Full Description</label>
                <textarea name="description" rows="3" class="w-full text-sm border rounded-lg px-3 py-2"><?= htmlspecialchars($editService['description'] ?? '') ?></textarea>
            </div>
            <div class="sm:col-span-2 lg:col-span-3 flex gap-2">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold px-5 py-2.5 rounded-lg transition shadow-sm"><?= $editService ? 'Update Service' : 'Add Service' ?></button>
                <?php if ($editService): ?>
                    <a href="services.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold px-4 py-2.5 rounded-lg transition">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Services List Table -->
    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b text-xs uppercase text-slate-600">
                <tr>
                    <th class="p-4">Image</th>
                    <th class="p-4">Service Title</th>
                    <th class="p-4">Description</th>
                    <th class="p-4">Standard Rate</th>
                    <th class="p-4">Created Date</th>
                    <th class="p-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                <?php if (empty($services)): ?>
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-400">No services found.</td>
                    </tr>
                    <?php else: foreach ($services as $s): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="p-4">
                                <?php if (!empty($s['image'])): ?>
                                    <img src="../public/<?= htmlspecialchars($s['image']) ?>" class="w-10 h-10 object-cover rounded-lg border">
                                <?php else: ?>
                                    <span class="text-xs text-slate-400">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 font-bold text-slate-800"><?= htmlspecialchars($s['title'] ?? $s['name'] ?? '') ?></td>
                            <td class="p-4 text-xs text-slate-500 max-w-xs truncate"><?= htmlspecialchars($s['description'] ?? 'N/A') ?></td>
                            <td class="p-4 text-emerald-600 font-extrabold">Rs <?= number_format($s['price'] ?? 0, 2) ?></td>
                            <td class="p-4 text-xs font-mono text-slate-400"><?= htmlspecialchars($s['created_at'] ?? 'N/A') ?></td>
                            <td class="p-4 text-right">
                                <a href="services.php?edit_id=<?= $s['id'] ?>" class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold px-3 py-1.5 rounded-lg transition">Edit</a>
                            </td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>

</html>