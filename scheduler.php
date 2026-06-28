<?php
/**
 * Content Calendar / Scheduler Page
 */
require_once __DIR__ . '/includes/auth.php';
requireLogin();
requireRole('admin', 'editor');

$pageTitle = 'Scheduler';
$db = getDB();

// Fetch all scheduled & published posts for calendar
$posts = $db->query("SELECT id, content, platforms, status, scheduled_at, published_at, created_at FROM posts ORDER BY COALESCE(scheduled_at, published_at, created_at) DESC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section class="page active" id="page-scheduler">
    <div class="page-header">
        <div><h1>Content Calendar</h1><p class="page-subtitle">Manage your scheduled posts</p></div>
        <div class="header-actions">
            <a href="compose.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Post</a>
        </div>
    </div>

    <div class="calendar-controls">
        <button class="btn btn-ghost" id="calPrev"><i class="fas fa-chevron-left"></i></button>
        <h3 id="calMonth"></h3>
        <button class="btn btn-ghost" id="calNext"><i class="fas fa-chevron-right"></i></button>
        <div class="view-toggle">
            <button class="view-btn active" data-view="month">Month</button>
            <button class="view-btn" data-view="list">List</button>
        </div>
    </div>

    <div class="calendar-grid" id="calendarGrid"></div>

    <!-- List View -->
    <div class="card" id="listView" style="display:none">
        <div class="card-body">
            <table class="data-table">
                <thead><tr><th>Content</th><th>Platforms</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($posts as $post):
                        $plats = json_decode($post['platforms'], true);
                        $date = $post['scheduled_at'] ?? $post['published_at'] ?? $post['created_at'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars(substr($post['content'], 0, 50)) ?>...</td>
                        <td><?php foreach ($plats as $p): ?><i class="fab fa-<?= $p ?>" style="color:var(--<?= $p ?>);margin-right:6px"></i><?php endforeach; ?></td>
                        <td><span class="status-badge <?= $post['status'] === 'published' ? 'connected' : ($post['status'] === 'scheduled' ? 'active' : 'pending') ?>"><?= ucfirst($post['status']) ?></span></td>
                        <td><?= date('M j, Y g:ia', strtotime($date)) ?></td>
                        <td>
                            <?php if ($post['status'] === 'scheduled'): ?>
                            <button class="btn btn-ghost btn-sm cancel-post" data-id="<?= $post['id'] ?>"><i class="fas fa-times"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Pass post data to JS for calendar rendering -->
<script>
    window.calendarPosts = <?= json_encode(array_map(function($p) {
        return [
            'id' => $p['id'],
            'content' => substr($p['content'], 0, 30),
            'platforms' => json_decode($p['platforms'], true),
            'status' => $p['status'],
            'date' => $p['scheduled_at'] ?? $p['published_at'] ?? $p['created_at']
        ];
    }, $posts)) ?>;
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
