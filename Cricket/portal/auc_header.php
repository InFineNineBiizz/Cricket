<?php
    // Convert your datetime to 12-hour format with AM/PM
    $start = new DateTime($sdate);
    $end = new DateTime($edate);
?>

<!-- Tournament Header with Logo -->
<div class="tournament-header-card">
    <div class="tournament-logo-circle">
        <img src="../assets/images/<?php echo $tlogo;?>" alt="Tournament Logo" />
    </div>
    <div class="tournament-header-info">
        <h1><?php echo $tour_name;?></h1>
        <div class="tournament-details-row">
            <div class="detail-item-inline">
                <i class="fas fa-map-marker-alt"></i>
                <span><?php echo $venue;?></span>
            </div>
            <div class="detail-item-inline">
                <i class="fas fa-gavel"></i>
                <span><?php if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
                    // Same date: 22 Nov 2025 12:00 PM - 11:59 PM
                    echo $start->format('d M Y h:i A') . ' - ' . $end->format('h:i A');
                } else {
                    // Different dates: 22 Nov 2025 12:00 PM - 23 Nov 2025 12:00 PM
                    echo $start->format('d M Y h:i A') . ' - ' . $end->format('d M Y h:i A');
                }?>
                </span>
            </div>
            <div class="detail-item-inline">
                <i class="fas fa-users"></i>
                <span><?php echo $s_date;?> - <?php echo $e_date;?></span>
            </div>
        </div>
    </div>
    <div class="tournament-info-right">
        <div class="info-right-item">
            <span class="info-label">Credit Available Per Team :</span>
            <span class="info-value"><?php echo $camt ." ". $ctype;?></span>
        </div>
        <div class="info-right-item">
            <span class="info-label">Maximum Reserve Player Per Team :</span>
            <span class="info-value"><?php echo $reserve;?></span>
        </div>                        
    </div>
    <div class="tournament-header-actions">
        <a href="view-auction.php?id=<?php echo $id;?>" class="btn-icon-action btn-view-tournament" title="View Auction" style="text-decoration: none;">
            <i class="fas fa-eye"></i>
        </a>
        <a href="add-auction.php?id=<?php echo $id;?>" class="btn-icon-action btn-edit-tournament" title="Edit Auction" style="text-decoration: none;">
            <i class="fas fa-pen"></i>
        </a>
    </div>
    <div class="tournament-badge-corner"><?php echo $sname; ?></div>
</div>

<style>
.btn-icon-action {
    text-decoration: none !important;
}

.btn-icon-action:hover {
    text-decoration: none !important;
}

.btn-icon-action i {
    text-decoration: none !important;
}
</style>