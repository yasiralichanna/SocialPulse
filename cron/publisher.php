<?php
/**
 * Cron Job — Post Publisher
 * This script should be run via cron (e.g., every minute)
 * * * * * * php /path/to/task3/cron/publisher.php
 */
require_once __DIR__ . '/../config/database.php';

function logCron(PDO $db, string $action, string $status, string $details): void {
    $stmt = $db->prepare("INSERT INTO cron_logs (action, status, details) VALUES (?, ?, ?)");
    $stmt->execute([$action, $status, $details]);
}

try {
    $db = getDB();
    
    // Find posts scheduled for now or past that haven't been published yet
    $stmt = $db->prepare("SELECT * FROM posts WHERE status = 'scheduled' AND scheduled_at <= NOW()");
    $stmt->execute();
    $postsToPublish = $stmt->fetchAll();
    
    $publishedCount = 0;
    
    if (count($postsToPublish) > 0) {
        foreach ($postsToPublish as $post) {
            // In a real scenario, here we would call Facebook/Twitter/Instagram APIs
            $platforms = json_decode($post['platforms'], true);
            $platformStr = implode(', ', array_map('ucfirst', $platforms));
            
            // Mark as published
            $update = $db->prepare("UPDATE posts SET status = 'published', published_at = NOW() WHERE id = ?");
            if ($update->execute([$post['id']])) {
                $publishedCount++;
                logCron($db, 'Publish Post', 'success', "✓ Published post #{$post['id']} to {$platformStr}");
                
                // Initialize analytics for this post
                foreach ($platforms as $p) {
                    $db->prepare("INSERT INTO post_analytics (post_id, platform) VALUES (?, ?)")->execute([$post['id'], $p]);
                }
            } else {
                logCron($db, 'Publish Post', 'error', "✗ Failed to update post #{$post['id']} status to published");
            }
        }
    } else {
        // Just log that we checked (optional, usually you wouldn't log empty runs to save space)
        // logCron($db, 'Check Queue', 'success', "✓ Checked queue — 0 pending posts");
    }
    
    echo "Cron run completed successfully. Published $publishedCount posts.\n";
    
} catch (Exception $e) {
    echo "Cron error: " . $e->getMessage() . "\n";
}
