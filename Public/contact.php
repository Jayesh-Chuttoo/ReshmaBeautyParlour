<?php
$pageTitle = "Contact Us - Reshma Beauty Parlour";
$pageCss   = "contact.css";

require_once __DIR__ . '/../Includes/Components/header.php';
?>

<main class="contact-page">
    <!-- Ambient background glow elements -->
    <div class="ambient-glow glow-1"></div>
    <div class="ambient-glow glow-2"></div>

    <!-- Hero Section -->
    <section class="contact-hero animate-fade-in">
        <div class="section-header">
            <span class="section-tag">Get in Touch</span>
            <h2>Visit or Connect With Us</h2>
            <p>We would love to hear from you. Reach out via WhatsApp or visit our salon location.</p>
        </div>
    </section>

    <!-- Contact Cards Section (Location & WhatsApp Only) -->
    <section class="contact-content-section animate-on-scroll">
        <div class="contact-card-container">

            <!-- Address / Location Card -->
            <div class="contact-info-card">
                <div class="icon-box">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                </div>
                <h3>Our Location</h3>
                <p class="contact-detail-text">
                    <strong>Reshma Beauty Parlour</strong><br>
                    Main Road, Petit Raffray<br>
                    Rivière du Rempart, Mauritius
                </p>
                <a href="https://maps.google.com/?q=Petit+Raffray+Mauritius" target="_blank" rel="noopener noreferrer" class="btn btn-outline">View on Google Maps</a>
            </div>

            <!-- WhatsApp Number Card -->
            <div class="contact-info-card">
                <div class="icon-box">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                    </svg>
                </div>
                <h3>WhatsApp Chat</h3>
                <p class="contact-detail-text">
                    Quick responses for appointment bookings, inquiries, and custom style consultations.<br>
                    <strong>+230 5000 0000</strong>
                </p>
                <a href="https://wa.me/23050000000" target="_blank" rel="noopener noreferrer" class="btn btn-hero btn-pulse">Chat on WhatsApp</a>
            </div>

        </div>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.15
        };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));
    });
</script>

<?php require_once __DIR__ . '/../Includes/Components/footer.php'; ?>