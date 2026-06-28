<?php
/**
 * Reports API — Generate & Export (CSV)
 */
require_once __DIR__ . '/../includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$db = getDB();
$action = $_GET['action'] ?? 'generate';

// ─── CSV EXPORT ───
if ($action === 'export_csv') {
    $type     = $_GET['type'] ?? 'engagement';
    $range    = $_GET['range'] ?? '30d';
    $platform = $_GET['platform'] ?? 'all';
    $days     = (int) str_replace('d', '', $range);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="socialpulse_' . $type . '_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');

    if ($type === 'engagement' || $type === 'full') {
        fputcsv($output, ['Date', 'Platform', 'Engagement Rate', 'Reach', 'Impressions', 'Followers']);
        $where = "WHERE date >= DATE_SUB(CURDATE(), INTERVAL $days DAY)";
        if ($platform !== 'all') $where .= " AND platform = " . $db->quote($platform);
        $rows = $db->query("SELECT date, platform, engagement_rate, total_reach, total_impressions, followers FROM daily_analytics $where ORDER BY date DESC")->fetchAll();
        foreach ($rows as $r) fputcsv($output, array_values($r));
    }

    if ($type === 'content' || $type === 'full') {
        fputcsv($output, []);
        fputcsv($output, ['Post Content', 'Platforms', 'Status', 'Likes', 'Comments', 'Shares', 'Reach', 'Published At']);
        $rows = $db->query("SELECT p.content, p.platforms, p.status, COALESCE(SUM(pa.likes),0), COALESCE(SUM(pa.comments),0), COALESCE(SUM(pa.shares),0), COALESCE(SUM(pa.reach),0), p.published_at FROM posts p LEFT JOIN post_analytics pa ON p.id = pa.post_id GROUP BY p.id ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_NUM);
        foreach ($rows as $r) fputcsv($output, $r);
    }

    if ($type === 'growth') {
        fputcsv($output, ['Date', 'Platform', 'Followers']);
        $where = "WHERE date >= DATE_SUB(CURDATE(), INTERVAL $days DAY)";
        if ($platform !== 'all') $where .= " AND platform = " . $db->quote($platform);
        $rows = $db->query("SELECT date, platform, followers FROM daily_analytics $where ORDER BY date DESC")->fetchAll();
        foreach ($rows as $r) fputcsv($output, array_values($r));
    }

    fclose($output);
    exit;
}

// ─── ANALYTICS JSON (for charts) ───
if ($action === 'chart_data') {
    $range    = $_GET['range'] ?? '30d';
    $platform = $_GET['platform'] ?? 'all';
    $days     = (int) str_replace('d', '', $range);

    $where = "WHERE date >= DATE_SUB(CURDATE(), INTERVAL $days DAY)";
    if ($platform !== 'all') $where .= " AND platform = " . $db->quote($platform);

    $daily = $db->query("SELECT platform, date, engagement_rate, total_reach, followers FROM daily_analytics $where ORDER BY date ASC")->fetchAll();
    
    // Group by platform
    $result = [];
    foreach ($daily as $row) {
        $result[$row['platform']][] = $row;
    }
    jsonResponse(['data' => $result]);
}

jsonResponse(['error' => 'Unknown action'], 400);
