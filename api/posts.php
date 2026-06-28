<?php
/**
 * Posts API — Real CRUD operations
 * POST   /api/posts.php          → Create (draft/published/scheduled)
 * DELETE /api/posts.php?id=X     → Delete/Cancel post
 * GET    /api/posts.php          → List posts
 * PUT    /api/posts.php?id=X     → Update post
 */
require_once __DIR__ . '/../includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// ─── CREATE ───
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    
    $content   = trim($data['content'] ?? '');
    $platforms = $data['platforms'] ?? [];
    $status    = $data['status'] ?? 'draft'; // draft | published | scheduled
    $schedDate = $data['scheduled_at'] ?? null;
    $repeat    = $data['repeat_type'] ?? 'none';
    $timezone  = $data['timezone'] ?? 'UTC';

    if (empty($content)) jsonResponse(['error' => 'Content is required'], 400);
    if (empty($platforms)) jsonResponse(['error' => 'Select at least one platform'], 400);

    $stmt = $db->prepare("INSERT INTO posts (user_id, content, platforms, status, scheduled_at, published_at, repeat_type, timezone) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        $content,
        json_encode(array_values($platforms)),
        $status,
        $status === 'scheduled' ? $schedDate : null,
        $status === 'published' ? date('Y-m-d H:i:s') : null,
        $repeat,
        $timezone
    ]);

    $postId = $db->lastInsertId();

    // If publishing, create initial analytics rows
    if ($status === 'published') {
        foreach ($platforms as $p) {
            $db->prepare("INSERT INTO post_analytics (post_id, platform, likes, comments, shares, reach, impressions, clicks) VALUES (?, ?, 0, 0, 0, 0, 0, 0)")->execute([$postId, $p]);
        }
    }

    jsonResponse(['success' => true, 'id' => $postId, 'message' => ucfirst($status) . ' successfully!']);
}

// ─── LIST ───
if ($method === 'GET' && !isset($_GET['id'])) {
    $status = $_GET['status'] ?? null;
    $where = "1=1";
    $params = [];
    if ($status) { $where .= " AND p.status = ?"; $params[] = $status; }
    
    $stmt = $db->prepare("SELECT p.*, u.name as author FROM posts p JOIN users u ON p.user_id = u.id WHERE $where ORDER BY p.created_at DESC LIMIT 50");
    $stmt->execute($params);
    jsonResponse(['posts' => $stmt->fetchAll()]);
}

// ─── DELETE ───
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'Post ID required'], 400);
    $db->prepare("DELETE FROM posts WHERE id = ?")->execute([$id]);
    jsonResponse(['success' => true, 'message' => 'Post deleted']);
}

// ─── UPDATE ───
if ($method === 'PUT') {
    $id = (int)($_GET['id'] ?? 0);
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$id) jsonResponse(['error' => 'Post ID required'], 400);
    
    $fields = []; $params = [];
    if (isset($data['content']))    { $fields[] = "content = ?";    $params[] = $data['content']; }
    if (isset($data['platforms']))  { $fields[] = "platforms = ?";  $params[] = json_encode($data['platforms']); }
    if (isset($data['status']))     { $fields[] = "status = ?";     $params[] = $data['status'];
        if ($data['status'] === 'published') { $fields[] = "published_at = NOW()"; }
    }
    if (isset($data['scheduled_at'])) { $fields[] = "scheduled_at = ?"; $params[] = $data['scheduled_at']; }
    
    if (empty($fields)) jsonResponse(['error' => 'Nothing to update'], 400);
    $params[] = $id;
    $db->prepare("UPDATE posts SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    jsonResponse(['success' => true, 'message' => 'Post updated']);
}

jsonResponse(['error' => 'Method not allowed'], 405);
