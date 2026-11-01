<?php
// Guard: Don't show pagination if there's only 1 page or invalid page count
if (!isset($totalPages) || $totalPages <= 1) {
    return;
}

$currentPage = $currentPage ?? 1;
?>

<div class="pagination">
    <!-- Previous Page Button -->
    <?php if ($currentPage > 1): ?>
        <?php $_GET['page'] = $currentPage - 1; ?>
        <a href="?<?php echo http_build_query($_GET); ?>" class="page-link">&laquo; Prev</a>
    <?php endif; ?>

    <!-- Page Number Links -->
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php $_GET['page'] = $i; ?>
        <a href="?<?php echo http_build_query($_GET); ?>"
            class="page-link <?php echo $i === $currentPage ? 'active' : ''; ?>">
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>

    <!-- Next Page Button -->
    <?php if ($currentPage < $totalPages): ?>
        <?php $_GET['page'] = $currentPage + 1; ?>
        <a href="?<?php echo http_build_query($_GET); ?>" class="page-link">Next &raquo;</a>
    <?php endif; ?>
</div>