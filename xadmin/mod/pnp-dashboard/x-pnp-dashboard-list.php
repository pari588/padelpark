<?php
// Get statistics
$today = date("Y-m-d");
$thisMonth = date("Y-m");

// Today's stats
$DB->vals = array($today);
$DB->types = "s";
$DB->sql = "SELECT COUNT(*) as cnt, COALESCE(SUM(totalAmount),0) as revenue FROM " . $DB->pre . "pnp_booking WHERE bookingDate=? AND status=1";
$todayBookings = $DB->dbRow();

// This month stats
$DB->vals = array($thisMonth . "%");
$DB->types = "s";
$DB->sql = "SELECT COUNT(*) as cnt, COALESCE(SUM(totalAmount),0) as revenue FROM " . $DB->pre . "pnp_booking WHERE bookingDate LIKE ? AND status=1";
$monthBookings = $DB->dbRow();

// Total locations & courts
$DB->sql = "SELECT COUNT(*) as cnt FROM " . $DB->pre . "pnp_location WHERE status=1";
$totalLocations = $DB->dbRow()["cnt"] ?? 0;

$DB->sql = "SELECT COUNT(*) as cnt FROM " . $DB->pre . "pnp_court WHERE status=1";
$totalCourts = $DB->dbRow()["cnt"] ?? 0;

// Rental stats
$DB->vals = array($today);
$DB->types = "s";
$DB->sql = "SELECT COUNT(*) as cnt, COALESCE(SUM(totalAmount),0) as revenue FROM " . $DB->pre . "pnp_rental WHERE rentalDate=? AND status=1";
$todayRentals = $DB->dbRow();

// Pending returns
$DB->sql = "SELECT COUNT(*) as cnt FROM " . $DB->pre . "pnp_rental WHERE rentalStatus='Issued' AND status=1";
$pendingReturns = $DB->dbRow()["cnt"] ?? 0;

// Booking status breakdown (today)
$DB->vals = array($today);
$DB->types = "s";
$DB->sql = "SELECT bookingStatus, COUNT(*) as cnt FROM " . $DB->pre . "pnp_booking WHERE bookingDate=? AND status=1 GROUP BY bookingStatus";
$statusBreakdown = $DB->dbRows();
$statusData = array();
foreach ($statusBreakdown as $s) {
    $statusData[$s["bookingStatus"]] = $s["cnt"];
}

// Recent bookings
$DB->sql = "SELECT b.*, l.locationName, c.courtName FROM " . $DB->pre . "pnp_booking b
            LEFT JOIN " . $DB->pre . "pnp_location l ON b.locationID=l.locationID
            LEFT JOIN " . $DB->pre . "pnp_court c ON b.courtID=c.courtID
            WHERE b.status=1 ORDER BY b.created DESC LIMIT 10";
$recentBookings = $DB->dbRows();

// Location-wise revenue (this month)
$DB->vals = array($thisMonth . "%");
$DB->types = "s";
$DB->sql = "SELECT l.locationName, COUNT(b.bookingID) as bookings, COALESCE(SUM(b.totalAmount),0) as revenue
            FROM " . $DB->pre . "pnp_location l
            LEFT JOIN " . $DB->pre . "pnp_booking b ON l.locationID=b.locationID AND b.bookingDate LIKE ? AND b.status=1
            WHERE l.status=1
            GROUP BY l.locationID
            ORDER BY revenue DESC";
$locationRevenue = $DB->dbRows();
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Manrope:wght@400;500;600;700;800&display=swap');

.pnp-dashboard {
    font-family: 'Manrope', system-ui, sans-serif;
    padding: 24px;
    color: #1a1f26;
}

.pnp-header {
    margin-bottom: 32px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 20px;
    padding-bottom: 24px;
    border-bottom: 1px solid rgba(0,0,0,0.08);
}

.pnp-title {
    font-family: 'Bebas Neue', Impact, sans-serif !important;
    font-size: 56px;
    font-weight: 400;
    color: #1a1f26;
    margin: 0;
    letter-spacing: 3px;
    text-transform: uppercase;
}

.pnp-subtitle {
    font-size: 16px;
    color: #8b95a5;
    margin-top: 10px;
    font-weight: 500;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.pnp-date {
    font-size: 15px;
    font-weight: 700;
    color: #0891b2;
    background: rgba(8, 145, 178, 0.1);
    padding: 14px 24px;
    border-radius: 8px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.pnp-hero-metrics {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 20px;
    margin-bottom: 32px;
}

.pnp-hero-card {
    background: #fff;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 16px;
    padding: 28px 24px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.pnp-hero-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--card-accent, #0891b2);
}

.pnp-hero-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1);
}

.pnp-hero-card.cyan { --card-accent: #0891b2; }
.pnp-hero-card.green { --card-accent: #22c55e; }
.pnp-hero-card.purple { --card-accent: #8b5cf6; }
.pnp-hero-card.orange { --card-accent: #f59e0b; }
.pnp-hero-card.red { --card-accent: #ef4444; }

.pnp-hero-label {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #5c6878;
    margin-bottom: 12px;
}

.pnp-hero-value {
    font-family: 'Bebas Neue', Impact, sans-serif !important;
    font-size: 56px;
    font-weight: 400;
    color: #1a1f26;
    line-height: 1;
    margin-bottom: 8px;
    letter-spacing: 2px;
}

.pnp-hero-sub {
    font-size: 14px;
    color: #8b95a5;
    font-weight: 600;
}

.pnp-section {
    background: #fff;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    margin-bottom: 24px;
}

.pnp-section-header {
    padding: 20px 28px;
    border-bottom: 1px solid rgba(0,0,0,0.06);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8fafc;
}

.pnp-section-title {
    font-family: 'Bebas Neue', Impact, sans-serif !important;
    font-size: 24px;
    font-weight: 400;
    color: #1a1f26;
    display: flex;
    align-items: center;
    gap: 14px;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.pnp-section-title::before {
    content: '';
    width: 5px;
    height: 28px;
    background: linear-gradient(180deg, #0891b2 0%, #0e7490 100%);
    border-radius: 3px;
}

.pnp-status-grid {
    display: flex;
    gap: 16px;
    padding: 24px 28px;
    flex-wrap: wrap;
}

.pnp-status-pill {
    padding: 12px 20px;
    border-radius: 100px;
    font-size: 14px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
}

.pnp-status-pill.confirmed { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.pnp-status-pill.checked-in { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.pnp-status-pill.completed { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
.pnp-status-pill.no-show { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

.pnp-quick-actions {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 16px;
    padding: 24px 28px;
}

.pnp-quick-action {
    padding: 24px 16px;
    background: #f8fafc;
    border-radius: 12px;
    text-align: center;
    text-decoration: none;
    color: #1a1f26;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.pnp-quick-action:hover {
    border-color: #0891b2;
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    background: #fff;
}

.pnp-quick-action i {
    font-size: 28px;
    color: #0891b2;
    margin-bottom: 12px;
    display: block;
}

.pnp-quick-action span {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pnp-table {
    width: 100%;
    border-collapse: collapse;
}

.pnp-table th {
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #8b95a5;
    text-align: left;
    padding: 16px 24px;
    background: #f8fafc;
    border-bottom: 1px solid rgba(0,0,0,0.06);
}

.pnp-table td {
    font-size: 15px;
    font-weight: 500;
    padding: 16px 24px;
    border-bottom: 1px solid rgba(0,0,0,0.04);
    color: #1a1f26;
}

.pnp-table tr:hover td {
    background: rgba(8, 145, 178, 0.04);
}

.pnp-table a {
    color: #0891b2;
    text-decoration: none;
    font-weight: 700;
}

.pnp-table a:hover {
    color: #0e7490;
}

.pnp-table .amount {
    font-family: 'Bebas Neue', Impact, sans-serif !important;
    font-size: 20px;
    color: #22c55e;
    letter-spacing: 1px;
}

.pnp-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 100px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}

.pnp-badge.confirmed { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.pnp-badge.checked-in { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.pnp-badge.completed { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
.pnp-badge.no-show { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.pnp-badge.cancelled { background: rgba(107, 114, 128, 0.1); color: #6b7280; }

.pnp-two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

@media (max-width: 1400px) {
    .pnp-hero-metrics { grid-template-columns: repeat(3, 1fr); }
    .pnp-quick-actions { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 1000px) {
    .pnp-hero-metrics { grid-template-columns: repeat(2, 1fr); }
    .pnp-two-col { grid-template-columns: 1fr; }
    .pnp-quick-actions { grid-template-columns: repeat(2, 1fr); }
}
</style>

<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data" style="background: transparent; overflow: visible;">
        <div class="pnp-dashboard">

            <!-- Header -->
            <div class="pnp-header">
                <div>
                    <h1 class="pnp-title">Pay & Play</h1>
                    <p class="pnp-subtitle">Booking & Session Management</p>
                </div>
                <div class="pnp-date"><?php echo date("l, d M Y"); ?></div>
            </div>

            <!-- Hero Metrics -->
            <div class="pnp-hero-metrics">
                <div class="pnp-hero-card cyan">
                    <div class="pnp-hero-label">Today's Bookings</div>
                    <div class="pnp-hero-value"><?php echo number_format($todayBookings["cnt"] ?? 0); ?></div>
                    <div class="pnp-hero-sub">Rs. <?php echo number_format($todayBookings["revenue"] ?? 0); ?> revenue</div>
                </div>
                <div class="pnp-hero-card green">
                    <div class="pnp-hero-label">This Month</div>
                    <div class="pnp-hero-value"><?php echo number_format($monthBookings["cnt"] ?? 0); ?></div>
                    <div class="pnp-hero-sub">Rs. <?php echo number_format($monthBookings["revenue"] ?? 0); ?> revenue</div>
                </div>
                <div class="pnp-hero-card purple">
                    <div class="pnp-hero-label">Locations / Courts</div>
                    <div class="pnp-hero-value"><?php echo $totalLocations; ?> / <?php echo $totalCourts; ?></div>
                    <div class="pnp-hero-sub">Active venues</div>
                </div>
                <div class="pnp-hero-card orange">
                    <div class="pnp-hero-label">Today's Rentals</div>
                    <div class="pnp-hero-value"><?php echo number_format($todayRentals["cnt"] ?? 0); ?></div>
                    <div class="pnp-hero-sub">Rs. <?php echo number_format($todayRentals["revenue"] ?? 0); ?> income</div>
                </div>
                <div class="pnp-hero-card red">
                    <div class="pnp-hero-label">Pending Returns</div>
                    <div class="pnp-hero-value"><?php echo number_format($pendingReturns); ?></div>
                    <div class="pnp-hero-sub">Equipment to return</div>
                </div>
            </div>

            <!-- Today's Status -->
            <div class="pnp-section">
                <div class="pnp-section-header">
                    <div class="pnp-section-title">Today's Booking Status</div>
                </div>
                <div class="pnp-status-grid">
                    <span class="pnp-status-pill confirmed"><i class="fa fa-clock"></i> Confirmed: <?php echo $statusData["Confirmed"] ?? 0; ?></span>
                    <span class="pnp-status-pill checked-in"><i class="fa fa-sign-in-alt"></i> Checked-In: <?php echo $statusData["Checked-In"] ?? 0; ?></span>
                    <span class="pnp-status-pill completed"><i class="fa fa-check-circle"></i> Completed: <?php echo $statusData["Completed"] ?? 0; ?></span>
                    <span class="pnp-status-pill no-show"><i class="fa fa-times-circle"></i> No-Show: <?php echo $statusData["No-Show"] ?? 0; ?></span>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="pnp-section">
                <div class="pnp-section-header">
                    <div class="pnp-section-title">Quick Actions</div>
                </div>
                <div class="pnp-quick-actions">
                    <a href="<?php echo ADMINURL; ?>/pnp-booking-list/" class="pnp-quick-action">
                        <i class="fa fa-calendar-alt"></i>
                        <span>View Bookings</span>
                    </a>
                    <a href="<?php echo ADMINURL; ?>/pnp-booking-add/" class="pnp-quick-action">
                        <i class="fa fa-plus-circle"></i>
                        <span>Walk-in Booking</span>
                    </a>
                    <a href="<?php echo ADMINURL; ?>/pnp-rental-add/" class="pnp-quick-action">
                        <i class="fa fa-hand-holding"></i>
                        <span>New Rental</span>
                    </a>
                    <a href="<?php echo ADMINURL; ?>/pnp-rental-list/?rentalStatus=Issued" class="pnp-quick-action">
                        <i class="fa fa-undo"></i>
                        <span>Pending Returns</span>
                    </a>
                    <a href="<?php echo ADMINURL; ?>/pnp-location-list/" class="pnp-quick-action">
                        <i class="fa fa-map-marker-alt"></i>
                        <span>Locations</span>
                    </a>
                    <a href="<?php echo ADMINURL; ?>/pnp-equipment-list/" class="pnp-quick-action">
                        <i class="fa fa-list"></i>
                        <span>Equipment</span>
                    </a>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="pnp-two-col">
                <!-- Location Revenue -->
                <div class="pnp-section">
                    <div class="pnp-section-header">
                        <div class="pnp-section-title">Location Revenue (<?php echo date("M Y"); ?>)</div>
                    </div>
                    <table class="pnp-table">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Bookings</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($locationRevenue) > 0) {
                                foreach ($locationRevenue as $loc) { ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($loc["locationName"]); ?></strong></td>
                                <td><?php echo number_format($loc["bookings"]); ?></td>
                                <td class="amount"><?php echo number_format($loc["revenue"]); ?></td>
                            </tr>
                            <?php }
                            } else { ?>
                            <tr><td colspan="3" style="text-align:center; color:#8b95a5; padding:40px;">No data available</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Recent Bookings -->
                <div class="pnp-section">
                    <div class="pnp-section-header">
                        <div class="pnp-section-title">Recent Bookings</div>
                    </div>
                    <table class="pnp-table">
                        <thead>
                            <tr>
                                <th>Booking</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recentBookings) > 0) {
                                foreach (array_slice($recentBookings, 0, 5) as $b) { ?>
                            <tr>
                                <td><a href="<?php echo ADMINURL; ?>/pnp-booking-edit/?id=<?php echo $b["bookingID"]; ?>"><?php echo $b["bookingNo"]; ?></a></td>
                                <td><?php echo htmlspecialchars($b["customerName"]); ?></td>
                                <td class="amount"><?php echo number_format($b["totalAmount"]); ?></td>
                                <td>
                                    <?php $statusClass = strtolower(str_replace(" ", "-", $b["bookingStatus"])); ?>
                                    <span class="pnp-badge <?php echo $statusClass; ?>"><?php echo $b["bookingStatus"]; ?></span>
                                </td>
                            </tr>
                            <?php }
                            } else { ?>
                            <tr><td colspan="4" style="text-align:center; color:#8b95a5; padding:40px;">No bookings yet</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
