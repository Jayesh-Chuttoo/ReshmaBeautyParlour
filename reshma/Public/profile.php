<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);

$pageTitle = "My Account - Reshma Beauty Parlour";
$pageCss   = "cart.css";

require_once __DIR__ . '/../Includes/db_queries.php';

$userId = (int)$_SESSION['user_id'];
$statusMessage = '';
$errorMessage  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_phone') {
    $newPhone = trim($_POST['phone'] ?? '');
    if ($newPhone === '') {
        $errorMessage = "Please enter your mobile number.";
    } elseif (strlen($newPhone) > 20) {
        $errorMessage = "Mobile number is too long.";
    } else {
        if (updateUserPhone($pdo, $userId, $newPhone)) {
            $statusMessage = "Mobile number saved.";
        } else {
            $errorMessage = "Could not update mobile number.";
        }
    }
}

$userProfile = getUserProfileById($pdo, $userId);
if (!$userProfile) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$orderBucket = strtolower(trim((string)($_GET['orders'] ?? 'all')));
if (!in_array($orderBucket, ['all', 'pending', 'completed', 'cancelled'], true)) {
    $orderBucket = 'all';
}

$bookingStatus = trim((string)($_GET['bookings'] ?? 'all'));
if (!in_array(strtolower($bookingStatus), ['all', 'pending', 'confirmed', 'completed'], true)) {
    $bookingStatus = 'all';
}

// Fetch summary counts + only the filtered orders & bookings needed
$summary        = getUserOrderSummary($pdo, $userId);
$filteredOrders = getUserOrdersWithItems($pdo, $userId, $orderBucket, 20);
$bookings       = getUserBookings(
    $pdo,
    (string)($userProfile['username'] ?? ''),
    $userProfile['phone'] ?? null,
    $bookingStatus
);

function getBadgeColors(string $status): array
{
    $bucket = ProfileRepository::classifyStatus($status);
    if ($bucket === 'completed') {
        return ['bg' => '#dcfce7', 'text' => '#166534'];
    }
    if ($bucket === 'cancelled') {
        return ['bg' => '#fee2e2', 'text' => '#991b1b'];
    }
    return ['bg' => '#fef3c7', 'text' => '#92400e'];
}

require_once __DIR__ . '/../Includes/Components/header.php';
?>

<main class="cart-main" style="max-width: 1000px; margin: 2rem auto; padding: 0 1rem;">
    <!-- Header Row -->
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h1 style="margin: 0; font-size: 1.75rem;">My Account</h1>
            <p style="color: var(--text-muted); margin-top: 0.25rem;">
                <?php echo htmlspecialchars((string)$userProfile['username'], ENT_QUOTES, 'UTF-8'); ?>
                &bull; Joined <?php echo htmlspecialchars(date('d M Y', strtotime((string)$userProfile['created_at'])), ENT_QUOTES, 'UTF-8'); ?>
            </p>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 0.6rem;">
            <a href="shop.php" class="btn" style="width: auto; text-decoration: none; padding: 0.6rem 1.15rem;">Shop</a>
            <a href="cart.php" style="display: inline-flex; align-items: center; text-decoration: none; padding: 0.6rem 1.15rem; border-radius: 8px; border: 1px solid #d1d5db; color: var(--text-main); font-weight: 600; background: #fff;">Cart</a>
            <a href="logout.php" style="display: inline-flex; align-items: center; text-decoration: none; padding: 0.6rem 1.15rem; border-radius: 8px; border: 1px solid #fca5a5; color: #991b1b; font-weight: 600; background: #fef2f2;">Logout</a>
        </div>
    </div>

    <?php if ($statusMessage !== ''): ?>
        <div style="background: #dcfce7; color: #166534; padding: 0.8rem 1rem; border-radius: 8px; margin-bottom: 1.25rem;">
            <?php echo htmlspecialchars($statusMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage !== ''): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 0.8rem 1rem; border-radius: 8px; margin-bottom: 1.25rem;">
            <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <!-- Key Numbers at a Glance -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.75rem;">
        <div style="background: var(--surface, #fff); padding: 1.1rem; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
            <small style="color: var(--text-muted); display: block;">Orders Placed</small>
            <strong style="font-size: 1.4rem;"><?php echo (int)$summary['all']; ?></strong>
        </div>
        <div style="background: var(--surface, #fff); padding: 1.1rem; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
            <small style="color: var(--text-muted); display: block;">Pending Payment Check</small>
            <strong style="font-size: 1.4rem; color: #b45309;"><?php echo (int)$summary['pending']; ?></strong>
        </div>
        <div style="background: var(--surface, #fff); padding: 1.1rem; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
            <small style="color: var(--text-muted); display: block;">Total Spent</small>
            <strong style="font-size: 1.4rem; color: var(--primary, #fa8072);">Rs <?php echo number_format((float)$summary['total_spent'], 2); ?></strong>
        </div>
    </div>

    <!-- Phone Number Form -->
    <div style="background: var(--surface, #fff); padding: 1.25rem 1.5rem; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.04); margin-bottom: 2rem;">
        <form method="POST" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.75rem;">
            <input type="hidden" name="action" value="update_phone">
            <div style="flex: 1; min-width: 220px;">
                <label for="phone" style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 0.35rem;">Mobile / Juice Number</label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    value="<?php echo htmlspecialchars((string)($userProfile['phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                    placeholder="e.g. 58261129"
                    style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem;">
            </div>
            <button type="submit" class="btn" style="width: auto; padding: 0.65rem 1.25rem;">Update Number</button>
        </form>
    </div>

    <!-- Orders & Payment Receipts -->
    <section style="margin-bottom: 2.25rem;">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
            <h2 style="margin: 0; font-size: 1.3rem;">Orders &amp; Receipts</h2>

            <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                <?php
                $orderTabs = [
                    'all'       => 'All (' . $summary['all'] . ')',
                    'pending'   => 'Pending (' . $summary['pending'] . ')',
                    'completed' => 'Completed (' . $summary['completed'] . ')',
                    'cancelled' => 'Cancelled (' . $summary['cancelled'] . ')',
                ];
                foreach ($orderTabs as $key => $label):
                    $active = ($orderBucket === $key);
                ?>
                    <a
                        href="?orders=<?php echo urlencode($key); ?>&amp;bookings=<?php echo urlencode($bookingStatus); ?>"
                        style="text-decoration: none; padding: 0.38rem 0.8rem; border-radius: 999px; font-size: 0.85rem; font-weight: 600; border: 1px solid <?php echo $active ? 'var(--primary, #fa8072)' : '#d1d5db'; ?>; background: <?php echo $active ? 'var(--primary, #fa8072)' : '#fff'; ?>; color: <?php echo $active ? '#fff' : 'var(--text-main, #332d2e)'; ?>;">
                        <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (empty($filteredOrders)): ?>
            <div style="background: var(--surface, #fff); padding: 1.75rem; border-radius: 8px; text-align: center; color: var(--text-muted); box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
                No orders in this view.
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($filteredOrders as $order):
                    $status = (string)($order['payment_status'] ?? 'Pending Verification');
                    $badge = getBadgeColors($status);
                    $totalAmt = (float)($order['total_amount'] ?? ((float)$order['subtotal'] + (float)$order['shipping_fee']));
                    $receiptFile = basename((string)($order['payment_proof'] ?? ''));
                    $receiptUrl = $receiptFile !== '' ? "assets/uploads/payments/" . rawurlencode($receiptFile) : '';
                ?>
                    <div style="background: var(--surface, #fff); border-radius: 8px; padding: 1.25rem; box-shadow: 0 2px 6px rgba(0,0,0,0.04); border: 1px solid #eee;">
                        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                            <div>
                                <strong>Order #<?php echo (int)$order['id']; ?></strong>
                                <span style="color: var(--text-muted); font-size: 0.88rem; margin-left: 0.4rem;">
                                    <?php echo htmlspecialchars(date('d M Y, H:i', strtotime((string)$order['created_at'])), ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </div>
                            <span style="padding: 0.25rem 0.7rem; border-radius: 999px; font-size: 0.82rem; font-weight: 600; background: <?php echo $badge['bg']; ?>; color: <?php echo $badge['text']; ?>;">
                                <?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>

                        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 1rem; font-size: 0.92rem;">
                            <div>
                                <p style="margin-bottom: 0.25rem;"><strong>Fulfillment:</strong> <?php echo htmlspecialchars((string)$order['delivery_mode'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p style="margin-bottom: 0;"><strong>Contact:</strong> <?php echo htmlspecialchars((string)$order['phone_number'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <div style="text-align: right;">
                                <p style="margin-bottom: 0.25rem;">
                                    Subtotal Rs <?php echo number_format((float)$order['subtotal'], 2); ?>
                                    + Fee Rs <?php echo number_format((float)$order['shipping_fee'], 2); ?>
                                </p>
                                <p style="margin-bottom: 0; font-size: 1.02rem;"><strong>Total: Rs <?php echo number_format($totalAmt, 2); ?></strong></p>
                            </div>
                        </div>

                        <?php if (!empty($order['items'])): ?>
                            <div style="margin-top: 0.85rem; padding-top: 0.75rem; border-top: 1px solid #f1f5f9; font-size: 0.88rem; color: var(--text-muted);">
                                <?php foreach ($order['items'] as $oi): ?>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                        <span>
                                            <?php echo htmlspecialchars((string)($oi['product_name'] ?? ('Product #' . $oi['product_id'])), ENT_QUOTES, 'UTF-8'); ?>
                                            <?php if (!empty($oi['size'])): ?>[<?php echo htmlspecialchars((string)$oi['size'], ENT_QUOTES, 'UTF-8'); ?>]<?php endif; ?>
                                            &times; <?php echo (int)$oi['quantity']; ?>
                                        </span>
                                        <span>Rs <?php echo number_format((float)$oi['price'] * (int)$oi['quantity'], 2); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($receiptUrl !== ''): ?>
                            <div style="margin-top: 0.85rem; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">
                                <a href="<?php echo htmlspecialchars($receiptUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" style="color: var(--primary, #fa8072); font-weight: 600; font-size: 0.9rem; text-decoration: none;">
                                    View Payment Receipt &rarr;
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Service Appointments -->
    <section>
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
            <h2 style="margin: 0; font-size: 1.3rem;">Appointments</h2>

            <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                <?php
                $bookingTabs = [
                    'all'       => 'All',
                    'pending'   => 'Pending',
                    'confirmed' => 'Confirmed',
                    'completed' => 'Completed',
                ];
                foreach ($bookingTabs as $bKey => $bLabel):
                    $bActive = (strtolower($bookingStatus) === $bKey);
                ?>
                    <a
                        href="?orders=<?php echo urlencode($orderBucket); ?>&amp;bookings=<?php echo urlencode($bKey); ?>"
                        style="text-decoration: none; padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.82rem; font-weight: 600; border: 1px solid <?php echo $bActive ? 'var(--primary, #fa8072)' : '#d1d5db'; ?>; background: <?php echo $bActive ? 'var(--primary, #fa8072)' : '#fff'; ?>; color: <?php echo $bActive ? '#fff' : 'var(--text-main, #332d2e)'; ?>;">
                        <?php echo htmlspecialchars($bLabel, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (empty($bookings)): ?>
            <div style="background: var(--surface, #fff); padding: 1.5rem; border-radius: 8px; text-align: center; color: var(--text-muted); box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
                No appointments matching this filter.
            </div>
        <?php else: ?>
            <div class="cart-table-wrapper">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Date &amp; Time</th>
                            <th>Guests</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $bk):
                            $bkStatus = (string)($bk['status'] ?? 'Pending');
                            $bkBadge = getBadgeColors($bkStatus);
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars((string)$bk['service_name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td><?php echo htmlspecialchars(date('d M Y, H:i', strtotime((string)$bk['booking_date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int)$bk['people_count']; ?></td>
                                <td>
                                    <span style="padding: 0.22rem 0.65rem; border-radius: 999px; font-size: 0.8rem; font-weight: 600; background: <?php echo $bkBadge['bg']; ?>; color: <?php echo $bkBadge['text']; ?>;">
                                        <?php echo htmlspecialchars($bkStatus, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../Includes/Components/footer.php'; ?>