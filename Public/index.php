<?php
$pageTitle = "Owner Portfolio & Salon - Elegance Saloon";
$pageCss   = "portfolio.css";

require_once __DIR__ . '/../Includes/Components/header.php';
require_once __DIR__ . '/../Includes/Data/portfolio_data.php';

// Fast local SVG fallback for missing gallery images
$defaultSvg = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='600' height='400' viewBox='0 0 600 400'><rect width='100%' height='100%' fill='%23e2d6c8'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' fill='%232c3e50' font-family='sans-serif' font-size='20'>Portfolio Showcase</text></svg>";
?>

<main class="portfolio-page">

    <!-- 1. HERO SECTION -->
    <section class="hero-banner">
        <div class="hero-content">
            <span class="hero-subtitle">Welcome to Elegance Saloon</span>
            <h1 class="hero-title">Crafting Timeless Beauty & Personal Style</h1>
            <p class="hero-description">
                Where luxury care meets master artistry. Led by Jane Doe, we offer bespoke hair styling, premium coloring, and personalized beauty treatments tailored to you.
            </p>
            <div class="hero-actions">
                <a href="services.php" class="btn btn-hero">Explore Services</a>
                <a href="shop.php" class="btn btn-outline">Visit Boutique</a>
            </div>
        </div>
    </section>

    <!-- 2. STATS HIGHLIGHT BAR -->
    <section class="stats-bar">
        <?php foreach ($stats as $stat): ?>
            <div class="stat-item">
                <div class="stat-number"><?php echo htmlspecialchars($stat['number']); ?></div>
                <div class="stat-label"><?php echo htmlspecialchars($stat['label']); ?></div>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- 3. OWNER BIO & PHILOSOPHY -->
    <section class="owner-section">
        <div class="owner-grid">
            <div class="owner-image-wrapper">
                <img src="assets/images/owner.jpg"
                    alt="Jane Doe - Master Stylist"
                    class="owner-img"
                    onerror="this.onerror=null; this.src='https://via.placeholder.com/500x600?text=Jane+Doe+Portrait';">
                <div class="experience-badge">12+ Years Experience</div>
            </div>

            <div class="owner-info">
                <span class="section-tag">About the Founder</span>
                <h2>Meet Jane Doe</h2>
                <h3 class="owner-subtitle">Master Stylist & Beauty Director</h3>

                <p class="portfolio-text">
                    Trained in Paris and New York, Jane established Elegance Saloon to bridge high-fashion styling with approachable, everyday care. She believes that a great hair style isn't just about trends—it's about highlighting your natural essence.
                </p>

                <p class="portfolio-text">
                    Every service begins with a comprehensive consultation to evaluate texture, skin undertones, and personal lifestyle, ensuring results that look effortlessly gorgeous long after you leave the chair.
                </p>

                <!-- Specialties List -->
                <div class="specialties-container">
                    <h4>Areas of Expertise</h4>
                    <ul class="specialties-list">
                        <?php foreach ($specialties as $specialty): ?>
                            <li><span class="check-icon">✓</span> <?php echo htmlspecialchars($specialty); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. RECENT WORK / GALLERY SHOWCASE -->
    <section class="gallery-section">
        <div class="section-header">
            <span class="section-tag">Creative Work</span>
            <h2>Recent Transformations</h2>
            <p>A peek at some of our favorite client styles and finishes.</p>
        </div>

        <div class="gallery-grid">
            <?php foreach ($gallery as $item): ?>
                <?php
                $imgSrc = !empty($item['image']) && file_exists(__DIR__ . '/public/assets/images/' . $item['image'])
                    ? "assets/images/{$item['image']}"
                    : $defaultSvg;
                ?>
                <div class="gallery-card">
                    <img src="<?php echo $imgSrc; ?>"
                        alt="<?php echo htmlspecialchars($item['title']); ?>"
                        onerror="this.onerror=null; this.src='<?php echo $defaultSvg; ?>';">
                    <div class="gallery-overlay">
                        <span class="gallery-category"><?php echo htmlspecialchars($item['category']); ?></span>
                        <h4 class="gallery-title"><?php echo htmlspecialchars($item['title']); ?></h4>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- 5. TESTIMONIALS -->
    <section class="testimonials-section">
        <div class="section-header">
            <span class="section-tag">Client Love</span>
            <h2>What Our Clients Say</h2>
        </div>

        <div class="testimonials-grid">
            <?php foreach ($testimonials as $review): ?>
                <div class="testimonial-card">
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
    <section class="cta-banner">
        <div class="cta-content">
            <h2>Ready to Transform Your Look?</h2>
            <p>Book your appointment today or explore our curated boutique products.</p>
            <div class="cta-buttons">
                <a href="services.php" class="btn btn-hero">Book Appointment</a>
                <a href="shop.php" class="btn btn-light">Shop Boutique</a>
            </div>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/../Includes/Components/footer.php'; ?>