<?php
if (!isset($product) || !is_array($product)) return;

$id        = (int)($product['id'] ?? 0);
$title     = htmlspecialchars($product['name'] ?? 'Untitled Product');
$price     = (float)($product['price'] ?? 0);
$image     = $product['image'] ?? ''; // e.g. "uploads/prod_6ae4d0e96adbe4.jpg"
$stock     = (int)($product['stock'] ?? 0);

// Corrected subdomain spelling: reshmaadmin.infinitydigitalsls.com
$imagePath = !empty($image) ? "https://reshmaadmin.infinitydigitalsls.com/{$image}" : "https://via.placeholder.com/400x200?text=Product+Image";
?>

<div class="card product-card" data-id="<?php echo $id; ?>">
    <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo $title; ?>" onerror="this.src='https://via.placeholder.com/400x200?text=Product+Image'">

    <div class="card-body">
        <h3 class="card-title"><?php echo $title; ?></h3>
        <p class="card-text"><?php echo htmlspecialchars($product['description'] ?? ''); ?></p>
        <div class="card-price">Rs <?php echo number_format($price, 2); ?></div>

        <!-- Updated button to trigger the popup modal instead of submitting directly -->
        <div style="margin-top: 1rem;">
            <button type="button"
                id="modalSubmitBtn"
                onclick='openCartModal(<?php echo htmlspecialchars(json_encode($product), ENT_QUOTES, 'UTF-8'); ?>)'
                class="btn"
                style="width: 100%;">
                Add to Cart
            </button>
        </div>
    </div>
</div>