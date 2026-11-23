<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db_queries.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'manager'], true)) {
    header("Location: ../public/index.php");
    exit;
}

$message = '';
$msgType = 'success';

$searchQuery = trim($_GET['q'] ?? '');
$rateFilter  = trim($_GET['rate'] ?? '');
$sortBy      = trim($_GET['sort'] ?? 'newest');

// Handle Add / Edit Service submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_service' || $action === 'edit_service') {
        $id          = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = floatval($_POST['price'] ?? 0);

        // Handle Image Upload
        $imagePath = $_POST['existing_image'] ?? '';
        if (isset($_FILES['service_image']) && $_FILES['service_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/';
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
                $success = createService($pdo, $title, $description, $price, $imagePath);
                $message = $success ? 'Service added successfully!' : 'Failed to add service.';
            } else {
                $success = updateServiceById($pdo, $id, $title, $description, $price, $imagePath);
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
    $editService = getServiceById($pdo, intval($_GET['edit_id']));
}

$services = getFilteredServices($pdo, $searchQuery, $rateFilter, $sortBy);
$pageTitle = "Services Management - Reshma Beauty Parlour";
$pageCss = "services.css";
require_once __DIR__ . '/../../includes/components/header.php';
?>

<main class="services-page">
    <div class="services-header">
        <div>
            <h1 class="services-title">Salon Hair &amp; Beauty Services</h1>
            <p class="services-subtitle">Add beauty treatments, update standard rates, descriptions, and promotional images.</p>
        </div>
        <a href="index.php" class="services-back-link">&larr; Back to Dashboard</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="services-alert <?= $msgType === 'success' ? 'services-alert-success' : 'services-alert-error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Service Form (Dynamic Add / Edit) -->
    <section class="services-card services-form-panel" aria-labelledby="serviceFormHeading">
        <h2 id="serviceFormHeading" class="services-panel-title">
            <i class="fa-solid fa-scissors services-panel-icon" aria-hidden="true"></i>
            <?= $editService ? 'Edit Service #' . (int)$editService['id'] : 'Add New Service' ?>
        </h2>

        <form method="POST" enctype="multipart/form-data" class="services-form-grid">
            <input type="hidden" name="action" value="<?= $editService ? 'edit_service' : 'add_service' ?>">
            <?php if ($editService): ?>
                <input type="hidden" name="service_id" value="<?= (int)$editService['id'] ?>">
                <input type="hidden" name="existing_image" value="<?= htmlspecialchars($editService['image'] ?? '') ?>">
            <?php endif; ?>

            <div class="services-field">
                <label for="serviceTitle" class="services-label">Service Title *</label>
                <input id="serviceTitle" type="text" name="title" value="<?= htmlspecialchars($editService['title'] ?? $editService['name'] ?? '') ?>" required class="services-input">
            </div>
            <div class="services-field">
                <label for="servicePrice" class="services-label">Standard Rate (Rs) *</label>
                <input id="servicePrice" type="number" step="0.01" name="price" value="<?= htmlspecialchars($editService['price'] ?? '') ?>" required class="services-input">
            </div>
            <div class="services-field">
                <label for="serviceImage" class="services-label">Service Image</label>
                <input id="serviceImage" type="file" name="service_image" class="services-file-input">
            </div>
            <div class="services-field services-span-full">
                <label for="serviceDescription" class="services-label">Full Description</label>
                <textarea id="serviceDescription" name="description" rows="3" class="services-textarea"><?= htmlspecialchars($editService['description'] ?? '') ?></textarea>
            </div>
            <div class="services-span-full services-form-actions">
                <button type="submit" class="services-submit-btn"><?= $editService ? 'Update Service' : 'Add Service' ?></button>
                <?php if ($editService): ?>
                    <a href="services.php" class="services-cancel-btn">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <!-- Search, Rate Filter & Sort Bar -->
    <form method="GET" class="services-filter-bar">
        <div>
            <label for="serviceSearch" class="sr-only">Search services</label>
            <input
                id="serviceSearch"
                type="text"
                name="q"
                value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Search by title or description..."
                class="services-search-input">
        </div>

        <div>
            <label for="rateFilter" class="sr-only">Filter by rate</label>
            <select id="rateFilter" name="rate" class="services-filter-select">
                <option value="">All Rates</option>
                <option value="free" <?= $rateFilter === 'free' ? 'selected' : '' ?>>Complimentary (Rs 0)</option>
                <option value="under_500" <?= $rateFilter === 'under_500' ? 'selected' : '' ?>>Under Rs 500</option>
                <option value="500_1500" <?= $rateFilter === '500_1500' ? 'selected' : '' ?>>Rs 500 – Rs 1,500</option>
                <option value="over_1500" <?= $rateFilter === 'over_1500' ? 'selected' : '' ?>>Over Rs 1,500</option>
            </select>
        </div>

        <div>
            <label for="serviceSort" class="sr-only">Sort services</label>
            <select id="serviceSort" name="sort" class="services-filter-select">
                <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newest First</option>
                <option value="oldest" <?= $sortBy === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                <option value="price_asc" <?= $sortBy === 'price_asc' ? 'selected' : '' ?>>Rate: Low to High</option>
                <option value="price_desc" <?= $sortBy === 'price_desc' ? 'selected' : '' ?>>Rate: High to Low</option>
                <option value="title_asc" <?= $sortBy === 'title_asc' ? 'selected' : '' ?>>Title (A–Z)</option>
                <option value="title_desc" <?= $sortBy === 'title_desc' ? 'selected' : '' ?>>Title (Z–A)</option>
            </select>
        </div>

        <div class="services-controls-row">
            <button type="submit" class="services-apply-btn">Apply</button>
            <?php if ($searchQuery !== '' || $rateFilter !== '' || $sortBy !== 'newest'): ?>
                <a href="services.php" class="services-reset-btn">Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Services List Table -->
    <section class="services-card services-table-card" aria-label="Services list">
        <table class="services-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Service Title</th>
                    <th>Description</th>
                    <th>Standard Rate</th>
                    <th>Created Date</th>
                    <th class="serv-action-col">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($services)): ?>
                    <tr>
                        <td colspan="6" class="serv-empty-state">
                            <?= ($searchQuery !== '' || $rateFilter !== '') ? 'No matching services found.' : 'No services found.' ?>
                        </td>
                    </tr>
                    <?php else: foreach ($services as $s): ?>
                        <tr>
                            <td>
                                <?php if (!empty($s['image'])): ?>
                                    <img src="../public/<?= htmlspecialchars($s['image']) ?>" alt="<?= htmlspecialchars($s['title'] ?? $s['name'] ?? '') ?>" class="serv-thumb">
                                <?php else: ?>
                                    <span class="serv-no-image">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td class="serv-title"><?= htmlspecialchars($s['title'] ?? $s['name'] ?? '') ?></td>
                            <td class="serv-desc"><?= htmlspecialchars($s['description'] ?? 'N/A') ?></td>
                            <td class="serv-price">Rs <?= number_format($s['price'] ?? 0, 2) ?></td>
                            <td class="serv-date"><?= htmlspecialchars($s['created_at'] ?? 'N/A') ?></td>
                            <td class="serv-action-col">
                                <a href="services.php?edit_id=<?= (int)$s['id'] ?>" class="serv-edit-btn">Edit</a>
                            </td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>
    </section>
</main>
</body>

</html>