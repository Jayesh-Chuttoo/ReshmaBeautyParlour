<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Services - Reshma Beauty Parlour";
$pageCss   = "services.css";

require_once __DIR__ . '/../Includes/Components/header.php';
require_once __DIR__ . '/../Includes/db_queries.php';

// Handle Add to Cart action with login check
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_to_cart') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $userId = $_SESSION['user_id'];
    $productId = (int)($_POST['product_id'] ?? 0);

    if ($productId > 0) {
        addToCartDb($pdo, $userId, $productId, 1);
    }

    header("Location: shop.php");
    exit;
}

$isLoggedIn = isset($_SESSION['user_id']);
$loggedInUsername = $_SESSION['username'] ?? '';
$message = '';
$msgType = 'success';

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_booking') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $serviceName = trim($_POST['service_name'] ?? '');
    $clientName  = trim($_POST['client_name'] ?? '');
    $contact     = trim($_POST['contact'] ?? '');
    $isWhatsapp  = isset($_POST['is_whatsapp']) ? 1 : 0;
    $peopleCount = intval($_POST['people_count'] ?? 1);
    $onsiteVisit = isset($_POST['onsite_visit']) ? 1 : 0;
    $bookingDate = trim($_POST['booking_date'] ?? '');

    if (!empty($clientName) && !empty($contact) && !empty($bookingDate) && !empty($serviceName)) {
        $success = insertBooking($pdo, $serviceName, $clientName, $contact, $isWhatsapp, $peopleCount, $onsiteVisit, $bookingDate);

        if ($success) {
            $message = 'Booking submitted successfully! We will contact you on your phone number shortly.';
            $msgType = 'success';
        } else {
            $message = 'Failed to save booking. Please try again.';
            $msgType = 'error';
        }
    } else {
        $message = 'Please fill out all required fields, including your phone number.';
        $msgType = 'error';
    }
}

$services = getAllServices($pdo);
?>

<main>
    <?php if (!empty($message)): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; border-radius: 8px; background-color: <?= $msgType === 'success' ? '#d4edda' : '#f8d7da' ?>; color: <?= $msgType === 'success' ? '#155724' : '#721c24' ?>;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="services-top-action" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 class="services-title">Available Services</h2>
            <p class="services-header-text">Choose a service below or book a general appointment.</p>
        </div>
        <button class="btn" style="width: auto; flex-shrink: 0;" onclick="openBookingModal('General Appointment', 0)">Book Appointment</button>
    </div>

    <h1>Our Services</h1>
    <p style="color: var(--text-muted); margin-top: 0.5rem;">Discover our range of premium beauty treatments.</p>

    <!-- Services Grid (Browsing works freely for everyone) -->
    <div class="grid">
        <?php if (!empty($services)): ?>
            <?php foreach ($services as $service): ?>
                <?php require __DIR__ . '/../Includes/Components/service_card.php'; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1/-1; padding: 2rem 0; text-align: center; color: var(--text-muted);">No services currently available.</p>
        <?php endif; ?>
    </div>

    <!-- BOOKING MODAL -->
    <div id="booking-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content" style="background: white; padding: 2rem; border-radius: 12px; max-width: 450px; width: 100%; position: relative;">
            <span class="modal-close" onclick="closeBookingModal()" style="position: absolute; top: 1rem; right: 1rem; cursor: pointer; font-size: 1.5rem;">&times;</span>
            <h3 style="margin-bottom: 1.5rem; font-weight: bold; color: #1e1b4b;">Book a Service</h3>

            <form method="POST" action="">
                <input type="hidden" name="action" value="submit_booking">

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="modal-service-name" style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Selected Service</label>
                    <input type="text" id="modal-service-name" name="service_name" readonly class="form-control" style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px; background: #f8fafc;">
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="booking-people" style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Number of People</label>
                    <input type="number" id="booking-people" name="people_count" min="1" value="1" required class="form-control" style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>

                <div class="form-group" id="onsite-container" style="display: none; margin-bottom: 1rem;">
                    <label class="checkbox-label" style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer;">
                        <input type="checkbox" id="onsite-visit" name="onsite_visit" value="1">
                        Request Onsite Visit (Come to client's house)
                    </label>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="client-name" style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Full Name</label>
                    <input type="text" id="client-name" name="client_name" value="<?= htmlspecialchars($loggedInUsername) ?>" required class="form-control" placeholder="John Doe" style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="client-contact" style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Phone Number</label>
                    <input type="tel" id="client-contact" name="contact" required class="form-control" placeholder="e.g. +230 5123 4567" style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                    <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">We will use this phone number to contact you.</small>
                </div>

                <div class="form-group" style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="is-whatsapp" name="is_whatsapp" value="1" checked style="width: 1rem; height: 1rem;">
                    <label for="is-whatsapp" style="font-size: 0.875rem; font-weight: normal; cursor: pointer;">This is a WhatsApp number</label>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="booking-date" style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Preferred Date & Time</label>
                    <input type="datetime-local" id="booking-date" name="booking_date" required class="form-control" style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>

                <button type="submit" class="btn" style="width: 100%; padding: 0.75rem; margin-top: 1rem; background: #4f46e5; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Confirm Booking</button>
            </form>
        </div>
    </div>
</main>

<script>
    // Injects PHP session login state into a JavaScript constant
    const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;

    function openBookingModal(serviceName, price) {
        // Checks login status when the user clicks the booking/card button
        if (!isLoggedIn) {
            window.location.href = 'login.php';
            return;
        }

        document.getElementById('modal-service-name').value = serviceName;
        document.getElementById('booking-modal').style.display = 'flex';
    }

    function closeBookingModal() {
        document.getElementById('booking-modal').style.display = 'none';
    }
</script>

<?php require_once __DIR__ . '/../Includes/Components/footer.php'; ?>