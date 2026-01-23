<?php
// Get key metrics
// Total Coaches
$DB->sql = "SELECT COUNT(*) as total FROM " . $DB->pre . "ipa_coach WHERE status=1 AND coachStatus='Active'";
$totalCoaches = $DB->dbRow()["total"] ?? 0;

// Total Players
$DB->sql = "SELECT COUNT(*) as total FROM " . $DB->pre . "ipa_player WHERE status=1";
$totalPlayers = $DB->dbRow()["total"] ?? 0;

// Total Programs
$DB->sql = "SELECT COUNT(*) as total FROM " . $DB->pre . "ipa_program WHERE status=1";
$totalPrograms = $DB->dbRow()["total"] ?? 0;

// Sessions this month
$DB->sql = "SELECT COUNT(*) as total, SUM(totalRevenue) as revenue FROM " . $DB->pre . "ipa_session WHERE status=1 AND MONTH(sessionDate)=MONTH(CURRENT_DATE()) AND YEAR(sessionDate)=YEAR(CURRENT_DATE())";
$monthStats = $DB->dbRow();
$sessionsThisMonth = $monthStats["total"] ?? 0;
$revenueThisMonth = $monthStats["revenue"] ?? 0;

// Sessions today
$DB->sql = "SELECT COUNT(*) as total FROM " . $DB->pre . "ipa_session WHERE status=1 AND sessionDate=CURRENT_DATE()";
$sessionsToday = $DB->dbRow()["total"] ?? 0;

// Completed sessions
$DB->sql = "SELECT COUNT(*) as total FROM " . $DB->pre . "ipa_session WHERE status=1 AND sessionStatus='Completed'";
$completedSessions = $DB->dbRow()["total"] ?? 0;

// Total Revenue
$DB->sql = "SELECT SUM(totalRevenue) as total FROM " . $DB->pre . "ipa_session WHERE status=1 AND sessionStatus='Completed'";
$totalRevenue = $DB->dbRow()["total"] ?? 0;

// Total Sessions
$DB->sql = "SELECT COUNT(*) as total FROM " . $DB->pre . "ipa_session WHERE status=1";
$totalSessions = $DB->dbRow()["total"] ?? 0;

// Session Status Distribution
$DB->sql = "SELECT sessionStatus, COUNT(*) as cnt FROM " . $DB->pre . "ipa_session WHERE status=1 GROUP BY sessionStatus";
$sessionsByStatus = $DB->dbRows();

// Top Coaches by Rating
$DB->sql = "SELECT coachID, CONCAT(firstName, ' ', IFNULL(lastName,'')) as coachName, certificationLevel, avgStudentRating, totalSessionsConducted
            FROM " . $DB->pre . "ipa_coach
            WHERE status=1 AND coachStatus='Active' AND avgStudentRating > 0
            ORDER BY avgStudentRating DESC, totalSessionsConducted DESC
            LIMIT 5";
$topCoaches = $DB->dbRows();

// Recent Sessions
$DB->sql = "SELECT s.*, CONCAT(c.firstName, ' ', IFNULL(c.lastName,'')) as coachName, p.programName
            FROM " . $DB->pre . "ipa_session s
            LEFT JOIN " . $DB->pre . "ipa_coach c ON s.coachID = c.coachID
            LEFT JOIN " . $DB->pre . "ipa_program p ON s.programID = p.programID
            WHERE s.status=1
            ORDER BY s.sessionDate DESC, s.startTime DESC
            LIMIT 5";
$recentSessions = $DB->dbRows();

// Upcoming Sessions
$DB->sql = "SELECT s.*, CONCAT(c.firstName, ' ', IFNULL(c.lastName,'')) as coachName, p.programName
            FROM " . $DB->pre . "ipa_session s
            LEFT JOIN " . $DB->pre . "ipa_coach c ON s.coachID = c.coachID
            LEFT JOIN " . $DB->pre . "ipa_program p ON s.programID = p.programID
            WHERE s.status=1 AND s.sessionStatus='Scheduled' AND (s.sessionDate > CURRENT_DATE() OR (s.sessionDate = CURRENT_DATE() AND s.startTime > CURRENT_TIME()))
            ORDER BY s.sessionDate ASC, s.startTime ASC
            LIMIT 5";
$upcomingSessions = $DB->dbRows();

// New Players this month
$DB->sql = "SELECT COUNT(*) as total FROM " . $DB->pre . "ipa_player WHERE status=1 AND MONTH(created)=MONTH(CURRENT_DATE()) AND YEAR(created)=YEAR(CURRENT_DATE())";
$newPlayersThisMonth = $DB->dbRow()["total"] ?? 0;

// Players by Level
$DB->sql = "SELECT currentLevel, COUNT(*) as cnt FROM " . $DB->pre . "ipa_player WHERE status=1 GROUP BY currentLevel ORDER BY FIELD(currentLevel, 'Beginner', 'Intermediate', 'Advanced', 'Pro')";
$playersByLevel = $DB->dbRows();

// Session completion rate
$completionRate = $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 1) : 0;
?>

<style>
/*=============================================================================
  IPA DASHBOARD - "ACADEMY COMMAND CENTER"
  Bold, authoritative, high-contrast scoreboard aesthetic
  Massive typography for effortless readability
=============================================================================*/

@import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Manrope:wght@400;500;600;700;800&display=swap');

/* ========== DASHBOARD CONTAINER ========== */
.ipa-dashboard {
    font-family: 'Manrope', system-ui, sans-serif;
    background: transparent;
    min-height: auto;
    padding: 24px;
    color: #1a1f26;
    font-size: 16px;
    line-height: 1.5;
}

/* ========== HEADER ========== */
.ipa-header {
    margin-bottom: 32px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 20px;
    padding-bottom: 24px;
    border-bottom: 1px solid rgba(0,0,0,0.08);
}

.ipa-title {
    font-family: 'Bebas Neue', Impact, sans-serif !important;
    font-size: 56px;
    font-weight: 400;
    color: #1a1f26;
    margin: 0;
    letter-spacing: 3px;
    text-transform: uppercase;
    line-height: 1;
}

.ipa-subtitle {
    font-size: 16px;
    color: #8b95a5;
    margin-top: 10px;
    font-weight: 500;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.ipa-date {
    font-size: 15px;
    font-weight: 700;
    color: #7c3aed;
    background: rgba(139, 92, 246, 0.1);
    padding: 14px 24px;
    border-radius: 8px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* ========== HERO METRICS - MASSIVE NUMBERS ========== */
.ipa-hero-metrics {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}

.ipa-hero-card {
    background: #fff;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 16px;
    padding: 36px 32px;
    position: relative;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

/* Glowing top accent bar */
.ipa-hero-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--card-accent, #8b5cf6);
}

.ipa-hero-card:hover {
    transform: translateY(-4px);
    border-color: var(--card-accent, #8b5cf6);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1);
}

.ipa-hero-card.purple {
    --card-accent: #8b5cf6;
}
.ipa-hero-card.blue {
    --card-accent: #3b82f6;
}
.ipa-hero-card.green {
    --card-accent: #22c55e;
}
.ipa-hero-card.amber {
    --card-accent: #f59e0b;
}

.ipa-hero-label {
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #5c6878;
    margin-bottom: 16px;
}

.ipa-hero-value {
    font-family: 'Bebas Neue', Impact, sans-serif !important;
    font-size: 72px;
    font-weight: 400;
    color: #1a1f26;
    line-height: 1;
    margin-bottom: 12px;
    letter-spacing: 2px;
}

.ipa-hero-value a {
    color: inherit;
    text-decoration: none;
    transition: all 0.3s ease;
}

.ipa-hero-value a:hover {
    color: var(--card-accent, #8b5cf6);
}

.ipa-hero-sub {
    font-size: 15px;
    color: #8b95a5;
    font-weight: 600;
}

.ipa-hero-sub .trend-up {
    color: #22c55e;
    font-weight: 800;
}

/* ========== COMPLETION RING ========== */
.ipa-completion-ring {
    position: absolute;
    top: 50%;
    right: 32px;
    transform: translateY(-50%);
    width: 100px;
    height: 100px;
}

.ipa-completion-ring svg {
    transform: rotate(-90deg);
    width: 100px;
    height: 100px;
}

.ipa-completion-ring circle {
    fill: none;
    stroke-width: 8;
}

.ipa-completion-ring .bg {
    stroke: rgba(0,0,0,0.08);
}

.ipa-completion-ring .progress {
    stroke: #22c55e;
    stroke-linecap: round;
    stroke-dasharray: 251;
    stroke-dashoffset: calc(251 - (251 * var(--percent, 0)) / 100);
    transition: stroke-dashoffset 1.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.ipa-completion-value {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-family: 'Bebas Neue', Impact, sans-serif !important;
    font-size: 24px;
    font-weight: 400;
    color: #22c55e;
    letter-spacing: 1px;
}

/* ========== STATS GRID ========== */
.ipa-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 40px;
}

.ipa-stat-card {
    background: #fff;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 12px;
    padding: 28px 24px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.ipa-stat-card:hover {
    background: #fff;
    border-color: #8b5cf6;
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}

.ipa-stat-label {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #8b95a5;
    margin-bottom: 12px;
}

.ipa-stat-value {
    font-family: 'Bebas Neue', Impact, sans-serif !important;
    font-size: 42px;
    font-weight: 400;
    color: #1a1f26;
    letter-spacing: 1px;
}

.ipa-stat-value.green {
    color: #22c55e;
}

.ipa-stat-sub {
    font-size: 14px;
    color: #8b95a5;
    margin-top: 8px;
    font-weight: 500;
}

/* ========== TWO COLUMN LAYOUT ========== */
.ipa-two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 28px;
    margin-bottom: 28px;
}

/* ========== SECTION CARDS ========== */
.ipa-section {
    background: #fff;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    margin-bottom: 28px;
}

.ipa-section:last-child {
    margin-bottom: 0;
}

.ipa-section-header {
    padding: 20px 28px;
    border-bottom: 1px solid rgba(0,0,0,0.06);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #faf8f5;
}

.ipa-section-title {
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

.ipa-section-title::before {
    content: '';
    width: 5px;
    height: 28px;
    background: linear-gradient(180deg, #8b5cf6 0%, #6d28d9 100%);
    border-radius: 3px;
}

.ipa-section-badge {
    font-size: 14px;
    font-weight: 700;
    background: rgba(139, 92, 246, 0.12);
    color: #7c3aed;
    padding: 8px 16px;
    border-radius: 100px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* ========== DATA TABLES - LARGE & READABLE ========== */
.ipa-table {
    width: 100%;
    border-collapse: collapse;
}

.ipa-table th {
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #8b95a5;
    text-align: left;
    padding: 16px 24px;
    background: #faf8f5;
    border-bottom: 1px solid rgba(0,0,0,0.06);
}

.ipa-table td {
    font-size: 16px;
    font-weight: 500;
    padding: 18px 24px;
    border-bottom: 1px solid rgba(0,0,0,0.04);
    color: #1a1f26;
}

.ipa-table tr:hover td {
    background: rgba(139, 92, 246, 0.06);
}

.ipa-table tr:last-child td {
    border-bottom: none;
}

.ipa-table a {
    color: #7c3aed;
    text-decoration: none;
    font-weight: 700;
    transition: all 0.3s ease;
}

.ipa-table a:hover {
    color: #8b5cf6;
}

.ipa-table .mono {
    font-family: 'JetBrains Mono', 'Fira Code', monospace !important;
    font-size: 15px;
    color: #5c6878;
    font-weight: 500;
}

.ipa-table .amount {
    font-family: 'Bebas Neue', Impact, sans-serif !important;
    font-weight: 400;
    color: #22c55e;
    font-size: 22px;
    letter-spacing: 1px;
}

/* ========== EMPTY STATE ========== */
.ipa-empty {
    padding: 60px 32px;
    text-align: center;
    color: #8b95a5;
    font-size: 18px;
    font-weight: 500;
}

.ipa-empty-icon {
    font-size: 56px;
    margin-bottom: 16px;
    opacity: 0.4;
}

/* ========== PLAYER LEVELS - SCOREBOARD STYLE ========== */
.ipa-levels {
    margin-bottom: 0;
    margin-top: 28px;
}

.ipa-levels-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    padding: 32px;
}

.ipa-level-item {
    background: #fff;
    border-radius: 12px;
    padding: 32px 16px;
    text-align: center;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(0,0,0,0.08);
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

/* Colored bottom glow */
.ipa-level-item::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 100%;
    background: linear-gradient(to top, var(--level-color, #6b7280) 0%, transparent 60%);
    opacity: 0.08;
    transition: opacity 0.3s ease;
}

.ipa-level-item:hover {
    transform: translateY(-6px);
    border-color: var(--level-color, #6b7280);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1);
}

.ipa-level-item:hover::before {
    opacity: 0.15;
}

.ipa-level-item.beginner { --level-color: #6b7280; }
.ipa-level-item.intermediate { --level-color: #3b82f6; }
.ipa-level-item.advanced { --level-color: #f59e0b; }
.ipa-level-item.pro { --level-color: #22c55e; }

.ipa-level-num {
    font-family: 'Bebas Neue', Impact, sans-serif !important;
    font-size: 52px;
    font-weight: 400;
    color: #1a1f26;
    position: relative;
    z-index: 1;
    margin-bottom: 10px;
    letter-spacing: 2px;
}

.ipa-level-label {
    font-size: 14px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #8b95a5;
    position: relative;
    z-index: 1;
}

/* ========== STATUS BADGES ========== */
.ipa-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 100px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.ipa-badge.scheduled {
    background: rgba(59, 130, 246, 0.12);
    color: #3b82f6;
}

.ipa-badge.in-progress {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
}

.ipa-badge.completed {
    background: rgba(34, 197, 94, 0.12);
    color: #22c55e;
}

.ipa-badge.cancelled {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
}

.ipa-badge.active {
    background: rgba(34, 197, 94, 0.12);
    color: #22c55e;
}

.ipa-badge.active::before {
    content: '';
    width: 8px;
    height: 8px;
    background: #22c55e;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.4); }
}

/* ========== RATING STARS ========== */
.ipa-rating {
    display: flex;
    align-items: center;
    gap: 4px;
}

.ipa-rating-value {
    font-family: 'Bebas Neue', Impact, sans-serif !important;
    font-size: 24px;
    color: #f59e0b;
    letter-spacing: 1px;
}

.ipa-rating-star {
    color: #f59e0b;
    font-size: 16px;
}

/* ========== ACTION BUTTONS ========== */
.ipa-quick-actions {
    display: flex;
    gap: 12px;
}

.ipa-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-radius: 8px;
    border: 2px solid #8b5cf6;
    background: #fff;
    color: #7c3aed !important;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none !important;
}

.ipa-action-btn:hover {
    background: #8b5cf6;
    border-color: #8b5cf6;
    color: #fff !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(139, 92, 246, 0.25);
}

.ipa-action-btn.primary {
    background: #8b5cf6;
    border: 2px solid #8b5cf6;
    color: #fff !important;
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
}

.ipa-action-btn.primary:hover {
    background: #7c3aed;
    border-color: #7c3aed;
    box-shadow: 0 8px 20px rgba(139, 92, 246, 0.35);
    transform: translateY(-3px);
}

/* ========== RESPONSIVE ========== */
@media (max-width: 1600px) {
    .ipa-hero-value { font-size: 72px; }
    .ipa-level-num { font-size: 56px; }
}

@media (max-width: 1400px) {
    .ipa-hero-metrics { grid-template-columns: repeat(2, 1fr); }
    .ipa-stats-grid { grid-template-columns: repeat(2, 1fr); }
    .ipa-levels-grid { grid-template-columns: repeat(2, 1fr); }
    .ipa-hero-value { font-size: 64px; }
}

@media (max-width: 1000px) {
    .ipa-two-col { grid-template-columns: 1fr; }
    .ipa-levels-grid { grid-template-columns: repeat(2, 1fr); }
    .ipa-hero-value { font-size: 56px; }
    .ipa-dashboard { padding: 32px; }
    .ipa-title { font-size: 56px; }
}

@media (max-width: 600px) {
    .ipa-hero-metrics { grid-template-columns: 1fr; }
    .ipa-stats-grid { grid-template-columns: 1fr; }
    .ipa-levels-grid { grid-template-columns: 1fr; }
    .ipa-title { font-size: 42px; }
    .ipa-hero-value { font-size: 48px; }
    .ipa-level-num { font-size: 48px; }
    .ipa-dashboard { padding: 24px; }
}

/* ========== ANIMATIONS ========== */
@keyframes fadeSlideUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.ipa-hero-card,
.ipa-stat-card,
.ipa-section,
.ipa-level-item {
    animation: fadeSlideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.ipa-hero-card:nth-child(1) { animation-delay: 0.1s; }
.ipa-hero-card:nth-child(2) { animation-delay: 0.15s; }
.ipa-hero-card:nth-child(3) { animation-delay: 0.2s; }
.ipa-hero-card:nth-child(4) { animation-delay: 0.25s; }

.ipa-stat-card:nth-child(1) { animation-delay: 0.3s; }
.ipa-stat-card:nth-child(2) { animation-delay: 0.35s; }
.ipa-stat-card:nth-child(3) { animation-delay: 0.4s; }
.ipa-stat-card:nth-child(4) { animation-delay: 0.45s; }

.ipa-level-item:nth-child(1) { animation-delay: 0.5s; }
.ipa-level-item:nth-child(2) { animation-delay: 0.55s; }
.ipa-level-item:nth-child(3) { animation-delay: 0.6s; }
.ipa-level-item:nth-child(4) { animation-delay: 0.65s; }

.ipa-title {
    animation: fadeSlideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
}
</style>

<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data" style="background: transparent; overflow: visible;">

        <div class="ipa-dashboard">
            <!-- Header -->
            <div class="ipa-header">
                <div>
                    <h1 class="ipa-title">Indian Padel Academy</h1>
                    <p class="ipa-subtitle">Academy Management Dashboard</p>
                </div>
                <div class="ipa-date"><?php echo date("l, d M Y"); ?></div>
            </div>

            <!-- Hero Metrics -->
            <div class="ipa-hero-metrics">
                <div class="ipa-hero-card purple">
                    <div class="ipa-hero-label">Active Coaches</div>
                    <div class="ipa-hero-value">
                        <a href="<?php echo ADMINURL; ?>/ipa-coach-list/"><?php echo $totalCoaches; ?></a>
                    </div>
                    <div class="ipa-hero-sub">Certified instructors</div>
                </div>

                <div class="ipa-hero-card blue">
                    <div class="ipa-hero-label">Total Players</div>
                    <div class="ipa-hero-value">
                        <a href="<?php echo ADMINURL; ?>/ipa-player-list/"><?php echo $totalPlayers; ?></a>
                    </div>
                    <div class="ipa-hero-sub">
                        <span class="trend-up">+<?php echo $newPlayersThisMonth; ?></span> this month
                    </div>
                </div>

                <div class="ipa-hero-card green" style="position: relative;">
                    <div class="ipa-hero-label">Completed Sessions</div>
                    <div class="ipa-hero-value">
                        <a href="<?php echo ADMINURL; ?>/ipa-session-list/"><?php echo $completedSessions; ?></a>
                    </div>
                    <div class="ipa-hero-sub">
                        <span class="trend-up"><?php echo $completionRate; ?>%</span> completion rate
                    </div>
                    <div class="ipa-completion-ring" style="--percent: <?php echo $completionRate; ?>">
                        <svg viewBox="0 0 100 100">
                            <circle class="bg" cx="50" cy="50" r="40"/>
                            <circle class="progress" cx="50" cy="50" r="40"/>
                        </svg>
                        <div class="ipa-completion-value"><?php echo $completionRate; ?>%</div>
                    </div>
                </div>

                <div class="ipa-hero-card amber">
                    <div class="ipa-hero-label">Total Revenue</div>
                    <div class="ipa-hero-value" style="font-size: 56px;">
                        <?php echo number_format($totalRevenue, 0); ?>
                    </div>
                    <div class="ipa-hero-sub">Rs. this month: <?php echo number_format($revenueThisMonth, 0); ?></div>
                </div>
            </div>

            <!-- Session Stats -->
            <div class="ipa-stats-grid">
                <div class="ipa-stat-card">
                    <div class="ipa-stat-label">Sessions Today</div>
                    <div class="ipa-stat-value"><?php echo $sessionsToday; ?></div>
                    <div class="ipa-stat-sub">Scheduled for today</div>
                </div>

                <div class="ipa-stat-card">
                    <div class="ipa-stat-label">Sessions This Month</div>
                    <div class="ipa-stat-value"><?php echo $sessionsThisMonth; ?></div>
                    <div class="ipa-stat-sub"><?php echo date("F Y"); ?></div>
                </div>

                <div class="ipa-stat-card">
                    <div class="ipa-stat-label">Total Programs</div>
                    <div class="ipa-stat-value"><?php echo $totalPrograms; ?></div>
                    <div class="ipa-stat-sub">Active programs</div>
                </div>

                <div class="ipa-stat-card">
                    <div class="ipa-stat-label">Total Sessions</div>
                    <div class="ipa-stat-value"><?php echo $totalSessions; ?></div>
                    <div class="ipa-stat-sub">All time</div>
                </div>
            </div>

            <!-- Two Column: Upcoming Sessions & Top Coaches -->
            <div class="ipa-two-col">
                <!-- Upcoming Sessions -->
                <div class="ipa-section">
                    <div class="ipa-section-header">
                        <div class="ipa-section-title">Upcoming Sessions</div>
                        <span class="ipa-section-badge"><?php echo count($upcomingSessions); ?> scheduled</span>
                    </div>
                    <div class="ipa-section-body">
                        <?php if (count($upcomingSessions) > 0) { ?>
                            <table class="ipa-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Coach</th>
                                        <th>Enrolled</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcomingSessions as $session) { ?>
                                        <tr>
                                            <td class="mono"><?php echo date("d M", strtotime($session["sessionDate"])); ?></td>
                                            <td class="mono"><?php echo date("h:i A", strtotime($session["startTime"])); ?></td>
                                            <td><?php echo htmlspecialchars($session["coachName"] ?? '-'); ?></td>
                                            <td><span class="ipa-badge scheduled"><?php echo $session["enrolledCount"]; ?> Players</span></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } else { ?>
                            <div class="ipa-empty">
                                <div class="ipa-empty-icon">&#128197;</div>
                                No upcoming sessions scheduled
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Top Coaches -->
                <div class="ipa-section">
                    <div class="ipa-section-header">
                        <div class="ipa-section-title">Top Rated Coaches</div>
                        <a href="<?php echo ADMINURL; ?>/ipa-coach-list/" class="ipa-action-btn">View All</a>
                    </div>
                    <div class="ipa-section-body">
                        <?php if (count($topCoaches) > 0) { ?>
                            <table class="ipa-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Coach</th>
                                        <th>Rating</th>
                                        <th>Sessions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topCoaches as $idx => $coach) { ?>
                                        <tr>
                                            <td class="mono"><?php echo $idx + 1; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($coach["coachName"]); ?></strong>
                                                <br><small style="color: #8b95a5;"><?php echo $coach["certificationLevel"]; ?></small>
                                            </td>
                                            <td>
                                                <div class="ipa-rating">
                                                    <span class="ipa-rating-value"><?php echo number_format($coach["avgStudentRating"], 1); ?></span>
                                                    <span class="ipa-rating-star"><i class="fa fa-star"></i></span>
                                                </div>
                                            </td>
                                            <td class="mono"><?php echo $coach["totalSessionsConducted"]; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } else { ?>
                            <div class="ipa-empty">
                                <div class="ipa-empty-icon">&#127942;</div>
                                No coach ratings yet
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- Recent Sessions -->
            <div class="ipa-section">
                <div class="ipa-section-header">
                    <div class="ipa-section-title">Recent Sessions</div>
                    <div class="ipa-quick-actions">
                        <a href="<?php echo ADMINURL; ?>/ipa-session-list/" class="ipa-action-btn">View All</a>
                        <a href="<?php echo ADMINURL; ?>/?p=ipa-session&t=add" class="ipa-action-btn primary">+ New Session</a>
                    </div>
                </div>
                <div class="ipa-section-body">
                    <?php if (count($recentSessions) > 0) { ?>
                        <table class="ipa-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Coach</th>
                                    <th>Program</th>
                                    <th>Attendance</th>
                                    <th>Status</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentSessions as $session) {
                                    $statusClass = strtolower(str_replace(' ', '-', $session["sessionStatus"]));
                                ?>
                                    <tr>
                                        <td class="mono"><?php echo date("d M", strtotime($session["sessionDate"])); ?></td>
                                        <td class="mono"><?php echo date("h:i A", strtotime($session["startTime"])); ?></td>
                                        <td><?php echo htmlspecialchars($session["coachName"] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($session["programName"] ?? '-'); ?></td>
                                        <td class="mono"><?php echo $session["attendedCount"]; ?>/<?php echo $session["enrolledCount"]; ?></td>
                                        <td>
                                            <span class="ipa-badge <?php echo $statusClass; ?>"><?php echo $session["sessionStatus"]; ?></span>
                                        </td>
                                        <td class="amount"><?php echo number_format($session["totalRevenue"], 0); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    <?php } else { ?>
                        <div class="ipa-empty">
                            <div class="ipa-empty-icon">&#127934;</div>
                            No sessions yet
                        </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Players by Level -->
            <div class="ipa-section ipa-levels">
                <div class="ipa-section-header">
                    <div class="ipa-section-title">Players by Skill Level</div>
                    <a href="<?php echo ADMINURL; ?>/ipa-player-list/" class="ipa-action-btn">Manage Players</a>
                </div>
                <div class="ipa-levels-grid">
                    <?php
                    $levelClasses = array("Beginner" => "beginner", "Intermediate" => "intermediate", "Advanced" => "advanced", "Pro" => "pro");
                    $allLevels = array("Beginner" => 0, "Intermediate" => 0, "Advanced" => 0, "Pro" => 0);
                    foreach ($playersByLevel as $level) {
                        $allLevels[$level["currentLevel"]] = $level["cnt"];
                    }
                    foreach ($allLevels as $levelName => $count) {
                    ?>
                        <div class="ipa-level-item <?php echo $levelClasses[$levelName]; ?>">
                            <div class="ipa-level-num"><?php echo $count; ?></div>
                            <div class="ipa-level-label"><?php echo $levelName; ?></div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="ipa-section" style="margin-top: 28px;">
                <div class="ipa-section-header">
                    <div class="ipa-section-title">Quick Actions</div>
                </div>
                <div style="padding: 28px; display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
                    <a href="<?php echo ADMINURL; ?>/?p=ipa-coach&t=add" class="ipa-action-btn primary"><i class="fa fa-plus"></i> Add Coach</a>
                    <a href="<?php echo ADMINURL; ?>/?p=ipa-player&t=add" class="ipa-action-btn"><i class="fa fa-plus"></i> Add Player</a>
                    <a href="<?php echo ADMINURL; ?>/?p=ipa-session&t=add" class="ipa-action-btn"><i class="fa fa-plus"></i> Schedule Session</a>
                    <a href="<?php echo ADMINURL; ?>/?p=ipa-program&t=add" class="ipa-action-btn"><i class="fa fa-plus"></i> Create Program</a>
                </div>
            </div>

        </div>
    </div>
</div>
