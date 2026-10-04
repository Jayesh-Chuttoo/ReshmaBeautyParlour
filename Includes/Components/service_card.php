<?php
if (!isset($service) || !is_array($service)) return;

$id          = $service['id'] ?? 0;
$title       = htmlspecialchars($service['title'] ?? 'Untitled Service');
$description = htmlspecialchars($service['description'] ?? 'No description available.');
$price       = number_format((float)($service['price'] ?? 0), 2);
$image       = htmlspecialchars($service['image'] ?? '');
$imagePath   = !empty($image) ? "assets/images/{$image}" : "https://via.placeholder.com/400x200?text=Service+Image";
?>

<div class="card" data-id="<?php echo $id; ?>">
    <img src="<?php echo $imagePath; ?>"
        alt="<?php echo $title; ?>"
        onerror="this.src='https://via.placeholder.com/400x200?text=Service+Image'">

    <div class="card-body">
        <h3 class="card-title"><?php echo $title; ?></h3>
        <p class="card-text"><?php echo $description; ?></p>

        <!-- Sticks to bottom of card -->
        <div class="card-price">Rs <?php echo $price; ?></div>

        <!-- <button class="btn" onclick="showNotification('Booking added for <?php echo addslashes($title); ?>!')">
            Book Appointment
        </button> -->
    </div>
</div>