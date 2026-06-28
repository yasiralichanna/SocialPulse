<?php
/**
 * Analytics Page
 */
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pageTitle = 'Analytics';
$db = getDB();

// Fetch daily analytics for charts
$platform = $_GET['platform'] ?? 'all';
$range    = $_GET['range'] ?? '30d';
$days     = (int) str_replace('d', '', $range);

$where = "WHERE date >= DATE_SUB(CURDATE(), INTERVAL $days DAY)";
if ($platform !== 'all') {
    $where .= " AND platform = " . $db->quote($platform);
}

$dailyData = $db->query("SELECT platform, date, engagement_rate, total_reach, followers FROM daily_analytics $where ORDER BY date ASC")->fetchAll();

// Top posts by engagement
$topPosts = $db->query("
    SELECT p.content, p.platforms, p.published_at,
           SUM(pa.likes) as total_likes, SUM(pa.comments) as total_comments,
           SUM(pa.shares) as total_shares, SUM(pa.reach) as total_reach
    FROM posts p
    JOIN post_analytics pa ON p.id = pa.post_id
    WHERE p.status = 'published'
    GROUP BY p.id
    ORDER BY (SUM(pa.likes) + SUM(pa.comments) + SUM(pa.shares)) DESC
    LIMIT 5
")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section class="page active" id="page-analytics">
    <div class="page-header">
        <div><h1>Analytics</h1><p class="page-subtitle">Deep dive into your performance metrics</p></div>
        <div class="header-actions">
            <form method="GET" style="display:flex;gap:10px">
                <select class="select-styled" name="platform" onchange="this.form.submit()">
                    <option value="all" <?= $platform === 'all' ? 'selected' : '' ?>>All Platforms</option>
                    <option value="facebook" <?= $platform === 'facebook' ? 'selected' : '' ?>>Facebook</option>
                    <option value="twitter" <?= $platform === 'twitter' ? 'selected' : '' ?>>Twitter</option>
                    <option value="instagram" <?= $platform === 'instagram' ? 'selected' : '' ?>>Instagram</option>
                </select>
                <select class="select-styled" name="range" onchange="this.form.submit()">
                    <option value="7d" <?= $range === '7d' ? 'selected' : '' ?>>7 Days</option>
                    <option value="30d" <?= $range === '30d' ? 'selected' : '' ?>>30 Days</option>
                    <option value="90d" <?= $range === '90d' ? 'selected' : '' ?>>90 Days</option>
                </select>
            </form>
        </div>
    </div>

    <div class="analytics-grid">
        <!-- Engagement Chart -->
        <div class="card">
            <div class="card-header"><h3>Engagement Over Time</h3></div>
            <div class="card-body"><canvas id="analyticsLineChart" height="300"></canvas></div>
        </div>

        <!-- Top Posts -->
        <div class="card">
            <div class="card-header"><h3>Top Performing Posts</h3></div>
            <div class="card-body">
                <div class="post-list">
                    <?php foreach ($topPosts as $i => $tp):
                        $plats = json_decode($tp['platforms'], true);
                    ?>
                    <div class="post-item">
                        <span class="post-platform" style="color:var(--accent);font-weight:800">#<?= $i + 1 ?></span>
                        <span class="post-text"><?= htmlspecialchars(substr($tp['content'], 0, 50)) ?></span>
                        <span class="post-meta">
                            <i class="fas fa-heart" style="color:var(--instagram)"></i> <?= number_format($tp['total_likes']) ?>
                            <i class="fas fa-share" style="color:var(--twitter);margin-left:8px"></i> <?= number_format($tp['total_shares']) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Demographics (Bar Chart) -->
        <div class="card">
            <div class="card-header"><h3>Audience Demographics</h3></div>
            <div class="card-body"><canvas id="demographicsChart" height="250"></canvas></div>
        </div>

        <!-- Best Posting Times Heatmap -->
        <div class="card">
            <div class="card-header"><h3>Best Posting Times</h3></div>
            <div class="card-body"><div class="heatmap" id="heatmapGrid"></div></div>
        </div>
    </div>
</section>

<script>
    window.analyticsData = <?= json_encode($dailyData) ?>;
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
