<?php
if (!isset($product) || !is_array($product)) return;

$id        = (int)($product['id'] ?? 0);
$title     = htmlspecialchars($product['name'] ?? 'Untitled Product');
$price     = (float)($product['price'] ?? 0);
$image     = htmlspecialchars($product['image'] ?? '');
$stock     = (int)($product['stock'] ?? 0);
$imagePath = !empty($image) ? "assets/images/{$image}" : "https://via.placeholder.com/400x200?text=Product+Image";
?>

<div class="card product-card" data-id="<?php echo $id; ?>">
    <img src="<?php echo $imagePath; ?>" alt="<?php echo $title; ?>" onerror="this.src='https://via.placeholder.com/400x200?text=Product+Image'">

    <div class="card-body">
        <h3 class="card-title"><?php echo $title; ?></h3>
        <p class="card-text"><?php echo htmlspecialchars($product['description'] ?? ''); ?></p>
        <div class="card-price">Rs <?php echo number_format($price, 2); ?></div>

        <form method="POST" style="margin-top: 1rem;">
            <input type="hidden" name="product_id" value="<?php echo $id; ?>">
            <input type="hidden" name="action" value="add_to_cart">
            <button type="submit" class="btn" style="width: 100%;">Add to Cart</button>
        </form>
    </div>
</div>