<?php
$pageTitle = "Owner Portfolio & Salon - Reshma Beauty Parlour";
$pageCss   = "portfolio.css";

require_once __DIR__ . '/../Includes/Components/header.php';
require_once __DIR__ . '/../Includes/Data/portfolio_data.php';

// Fast local SVG fallback for missing gallery images
$defaultSvg = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='600' height='400' viewBox='0 0 600 400'><rect width='100%' height='100%' fill='%23fa8072'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' fill='%23ffffff' font-family='sans-serif' font-size='20'>Portfolio Showcase</text></svg>";
?>

<main class="portfolio-page">

    <!-- Ambient background glow elements -->
    <div class="ambient-glow glow-1"></div>
    <div class="ambient-glow glow-2"></div>

    <!-- 1. HERO SECTION -->
    <section class="hero-banner animate-fade-in">
        <div class="hero-overlay-shimmer"></div>
        <div class="hero-content">
            <span class="hero-subtitle badge-pill">Welcome to Reshma Beauty Parlour</span>
            <h1 class="hero-title">Crafting Timeless Beauty <br><span class="text-gradient">&amp; Personal Style</span></h1>
            <p class="hero-description">
                Where luxury care meets master artistry. Led by Jane Doe, we offer bespoke hair styling, premium coloring, and personalized beauty treatments tailored to you.
            </p>
            <div class="hero-actions">
                <a href="services.php" class="btn btn-hero btn-pulse">Explore Services</a>
                <a href="shop.php" class="btn btn-outline">Visit Boutique</a>
            </div>
        </div>
    </section>

    <!-- 2. STATS HIGHLIGHT BAR -->
    <section class="stats-bar animate-on-scroll">
        <?php foreach ($stats as $index => $stat): ?>
            <div class="stat-item" style="--delay: <?php echo $index * 0.1; ?>s;">
                <div class="stat-number-wrapper">
                    <span class="stat-number"><?php echo htmlspecialchars($stat['number']); ?></span>
                </div>
                <div class="stat-label"><?php echo htmlspecialchars($stat['label']); ?></div>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- 3. OWNER BIO & PHILOSOPHY -->
    <section class="owner-section animate-on-scroll">
        <div class="owner-grid">
            <div class="owner-image-wrapper floating-card">
                <div class="img-frame">
                    <img src="assets/images/owner.jpg"
                        alt="Jane Doe - Master Stylist"
                        class="owner-img"
                        onerror="this.onerror=null; this.src='https://via.placeholder.com/500x600?text=Jane+Doe+Portrait';">
                </div>
                <div class="experience-badge float-animation">
                    <span class="badge-icon">★</span>
                    <div>
                        <strong>12+ Years</strong>
                        <small>Master Experience</small>
                    </div>
                </div>
            </div>

            <div class="owner-info">
                <span class="section-tag">About the Founder</span>
                <h2>Meet Jane Doe</h2>
                <h3 class="owner-subtitle">Master Stylist &amp; Beauty Director</h3>

                <p class="portfolio-text">
                    Trained in Paris and New York, Jane established Reshma Beauty Parlour to bridge high-fashion styling with approachable, everyday care. She believes that a great hair style isn't just about trends—it's about highlighting your natural essence.
                </p>

                <p class="portfolio-text">
                    Every service begins with a comprehensive consultation to evaluate texture, skin undertones, and personal lifestyle, ensuring results that look effortlessly gorgeous long after you leave the chair.
                </p>

                <!-- Specialties List -->
                <div class="specialties-container highlight-card">
                    <h4>Areas of Expertise</h4>
                    <ul class="specialties-list">
                        <?php foreach ($specialties as $specialty): ?>
                            <li class="specialty-item">
                                <span class="check-icon">✓</span>
                                <span><?php echo htmlspecialchars($specialty); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. RECENT WORK / GALLERY SHOWCASE -->
    <section class="gallery-section animate-on-scroll">
        <div class="section-header">
            <span class="section-tag">Creative Work</span>
            <h2>Recent Transformations</h2>
            <p>A peek at some of our favorite client styles and finishes.</p>
        </div>

        <div class="gallery-grid">
            <?php foreach ($gallery as $index => $item): ?>
                <?php
                $imgSrc = !empty($item['image']) && file_exists(__DIR__ . '/public/assets/images/' . $item['image'])
                    ? "assets/images/{$item['image']}"
                    : $defaultSvg;
                ?>
                <div class="gallery-card interactive-card" style="--delay: <?php echo $index * 0.15; ?>s;">
                    <div class="gallery-image-container">
                        <img src="<?php echo $imgSrc; ?>"
                            alt="<?php echo htmlspecialchars($item['title']); ?>"
                            onerror="this.onerror=null; this.src='<?php echo $defaultSvg; ?>';">
                    </div>
                    <div class="gallery-overlay">
                        <span class="gallery-category"><?php echo htmlspecialchars($item['category']); ?></span>
                        <h4 class="gallery-title"><?php echo htmlspecialchars($item['title']); ?></h4>
                        <span class="view-link">View Details &rarr;</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- 5. TESTIMONIALS -->
    <section class="testimonials-section animate-on-scroll">
        <div class="section-header">
            <span class="section-tag">Client Love</span>
            <h2>What Our Clients Say</h2>
        </div>

        <div class="testimonials-grid">
            <?php foreach ($testimonials as $index => $review): ?>
                <div class="testimonial-card interactive-card" style="--delay: <?php echo $index * 0.12; ?>s;">
                    <div class="quote-icon">“</div>
                    <div class="stars">
                        <?php echo str_repeat('★', (int)$review['rating']); ?>
                    </div>
                    <p class="testimonial-comment">"<?php echo htmlspecialchars($review['comment']); ?>"</p>
                    <div class="testimonial-author">
                        <strong><?php echo htmlspecialchars($review['name']); ?></strong>
                        <span><?php echo htmlspecialchars($review['role']); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- 6. CALL TO ACTION BANNER -->
    <section class="cta-banner animate-on-scroll">
        <div class="cta-shimmer"></div>
        <div class="cta-content">
            <h2>Ready to Transform Your Look?</h2>
            <p>Book your appointment today or explore our curated boutique products.</p>
            <div class="cta-buttons">
                <a href="services.php" class="btn btn-hero btn-pulse">Book Appointment</a>
                <a href="shop.php" class="btn btn-light">Shop Boutique</a>
            </div>
        </div>
    </section>

</main>

<script>
    // Scroll Reveal Observer
    document.addEventListener('DOMContentLoaded', function() {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.15
        };

        const observer = new IntersectionObserver((entries, observer) => {
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