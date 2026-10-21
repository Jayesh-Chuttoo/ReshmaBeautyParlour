<?php
$pageTitle = "Services - Reshma Beauty Parlour";
$pageCss   = "services.css";

require_once __DIR__ . '/../Includes/Components/header.php';
require_once __DIR__ . '/../Includes/Data/service_data.php';

?>

<main>
    <div class="services-top-action" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 class="services-title">Available Services</h2>
            <p class="services-header-text">Choose a service below or book a general appointment.</p>
        </div>
        <button class="btn" style="width: auto; flex-shrink: 0;" onclick="openBookingModal('General Appointment', 0)">Book Appointment</button>
    </div>

    <h1>Our Services</h1>
    <p style="color: var(--text-muted); margin-top: 0.5rem;">Discover our range of premium beauty treatments.</p>

    <div class="grid">
        <?php foreach ($services as $service): ?>
            <?php require __DIR__ . '/../Includes/Components/service_card.php'; ?>
        <?php endforeach; ?>
    </div>

    <div id="booking-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <span class="modal-close" onclick="closeBookingModal()">&times;</span>
            <h3>Book a Service</h3>
            <form id="booking-form" onsubmit="handleBookingSubmit(event)">
                <div class="form-group">
                    <label for="modal-service-name">Selected Service</label>
                    <input type="text" id="modal-service-name" name="service" readonly class="form-control">
                </div>

                <div class="form-group">
                    <label for="booking-people">Number of People</label>
                    <input type="number" id="booking-people" name="people" min="1" value="1" required class="form-control" oninput="checkOnsiteAvailability()">
                </div>

                <!-- Dynamic Onsite Visit Option (Appears if Bridal Makeup & > 5 people) -->
                <div class="form-group" id="onsite-container" style="display: none;">
                    <label class="checkbox-label" style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer;">
                        <input type="checkbox" id="onsite-visit" name="onsite_visit" value="1">
                        Request Onsite Visit (Come to client's house)
                    </label>
                    <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">Complimentary/Available for Bridal Makeup groups of more than 5 persons.</small>
                </div>

                <div class="form-group">
                    <label for="client-name">Full Name</label>
                    <input type="text" id="client-name" name="name" required class="form-control" placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label for="client-contact">Phone or Email</label>
                    <input type="text" id="client-contact" name="contact" required class="form-control" placeholder="e.g. +230 ... or email@domain.com">
                </div>
                <div class="form-group">
                    <label for="booking-date">Preferred Date & Time</label>
                    <input type="datetime-local" id="booking-date" name="date" required class="form-control">
                </div>
                <button type="submit" class="btn" style="width: 100%; margin-top: 1rem;">Confirm Booking</button>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../Includes/Components/footer.php'; ?>