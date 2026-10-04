<?php
if (!isset($product) || !is_array($product)) return;

$id          = $product['id'] ?? 0;
$name        = htmlspecialchars($product['name'] ?? 'Untitled Product');
$description = htmlspecialchars($product['description'] ?? 'No description available.');
$price       = number_format((float)($product['price'] ?? 0), 2);
$stock       = (int)($product['stock'] ?? 0);
$inStock     = $stock > 0;

$rawSizes       = is_array($product['sizes'] ?? null) ? $product['sizes'] : [];
$sizesFormatted = !empty($rawSizes) ? htmlspecialchars(implode(', ', $rawSizes)) : 'N/A';

// Fast local inline SVG fallback (doesn't require internet)
$defaultSvg = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='400' height='200' viewBox='0 0 400 200'><rect width='100%' height='100%' fill='%23eee'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' fill='%23777' font-family='sans-serif' font-size='18'>No Image Available</text></svg>";

$image     = htmlspecialchars($product['image'] ?? '');
$imagePath = !empty($image) && file_exists(__DIR__ . '/../../public/assets/images/' . $image)
    ? "assets/images/{$image}"
    : $defaultSvg;
?>

<div class="card" data-id="<?php echo $id; ?>">
    <img src="<?php echo $imagePath; ?>"
        alt="<?php echo $name; ?>"
        onerror="this.onerror=null; this.src='<?php echo $defaultSvg; ?>';">

    <div class="card-body">
        <div>
            <?php if ($inStock): ?>
                <span class="stock-tag"><?php echo $stock; ?> left in stock</span>
            <?php else: ?>
                <span class="stock-tag stock-out">Out of Stock</span>
            <?php endif; ?>
        </div>

        <h3 class="card-title"><?php echo $name; ?></h3>
        <p class="card-text"><?php echo $description; ?></p>

        <div class="sizes">
            <strong>Sizes:</strong> <?php echo $sizesFormatted; ?>
        </div>

        <div class="card-price">Rs <?php echo $price; ?></div>

        <button class="btn"
            <?php echo !$inStock ? 'disabled' : ''; ?>
            onclick="showNotification('Added <?php echo addslashes($name); ?> to cart!')">
            <?php echo !$inStock ? 'Sold Out' : 'Add to Cart'; ?>
        </button>
    </div>
</div>