<?php
/**
 * Compose Post Page
 */
require_once __DIR__ . '/includes/auth.php';
requireLogin();
requireRole('admin', 'editor');

$pageTitle = 'Compose Post';
include __DIR__ . '/includes/header.php';
?>

<section class="page active" id="page-composer">
    <div class="page-header">
        <div><h1>Compose Post</h1><p class="page-subtitle">Create and publish across platforms</p></div>
    </div>
    <div class="composer-layout">
        <div class="card composer-card">
            <div class="card-body">
                <form id="composeForm">
                    <div class="platform-selector">
                        <label class="platform-check active" data-platform="facebook"><i class="fab fa-facebook-f"></i> Facebook</label>
                        <label class="platform-check active" data-platform="twitter"><i class="fab fa-twitter"></i> Twitter</label>
                        <label class="platform-check active" data-platform="instagram"><i class="fab fa-instagram"></i> Instagram</label>
                    </div>
                    <textarea class="compose-textarea" id="postContent" name="content" placeholder="What's on your mind? Write your post here..." maxlength="2200"></textarea>
                    <div class="compose-footer">
                        <div class="compose-tools">
                            <button type="button" class="tool-btn" id="addEmoji" title="Add Emoji"><i class="fas fa-smile"></i></button>
                            <button type="button" class="tool-btn" id="addHashtag" title="Add Hashtag"><i class="fas fa-hashtag"></i></button>
                            <span class="char-count"><span id="charCount">0</span>/2200</span>
                        </div>
                        <div class="compose-actions">
                            <button type="button" class="btn btn-secondary" id="saveDraft">Save Draft</button>
                            <button type="button" class="btn btn-primary" id="schedulePostBtn"><i class="fas fa-clock"></i> Schedule</button>
                            <button type="button" class="btn btn-accent" id="publishNow"><i class="fas fa-paper-plane"></i> Publish Now</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card preview-card">
            <div class="card-header"><h3>Live Preview</h3></div>
            <div class="card-body">
                <div class="preview-tabs">
                    <button class="preview-tab active" data-preview="facebook"><i class="fab fa-facebook-f"></i></button>
                    <button class="preview-tab" data-preview="twitter"><i class="fab fa-twitter"></i></button>
                    <button class="preview-tab" data-preview="instagram"><i class="fab fa-instagram"></i></button>
                </div>
                <div class="preview-frame" id="previewFrame">
                    <div class="preview-header">
                        <div class="preview-avatar"><?= strtoupper(substr($user['name'], 0, 2)) ?></div>
                        <div><strong><?= htmlspecialchars($user['name']) ?></strong><br><small>Just now · <i class="fas fa-globe"></i></small></div>
                    </div>
                    <div class="preview-content" id="previewContent">Your post preview will appear here...</div>
                    <div class="preview-actions">
                        <span><i class="far fa-thumbs-up"></i> Like</span>
                        <span><i class="far fa-comment"></i> Comment</span>
                        <span><i class="far fa-share-square"></i> Share</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Modal -->
    <div class="modal-overlay" id="scheduleModal">
        <div class="modal">
            <div class="modal-header"><h3>Schedule Post</h3><button class="modal-close" id="closeScheduleModal"><i class="fas fa-times"></i></button></div>
            <div class="modal-body">
                <div class="form-group"><label>Date</label><input type="date" class="input-styled" id="schedDate" name="sched_date"></div>
                <div class="form-group"><label>Time</label><input type="time" class="input-styled" id="schedTime" name="sched_time"></div>
                <div class="form-group"><label>Repeat</label>
                    <select class="select-styled" id="schedRepeat" name="repeat_type">
                        <option value="none">No Repeat</option><option value="daily">Daily</option><option value="weekly">Weekly</option><option value="monthly">Monthly</option>
                    </select>
                </div>
                <div class="form-group"><label>Timezone</label>
                    <select class="select-styled" id="schedTimezone" name="timezone">
                        <option value="UTC">UTC</option><option value="EST">EST (UTC-5)</option><option value="PST" selected>PST (UTC-8)</option><option value="IST">IST (UTC+5:30)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelSchedule">Cancel</button>
                <button class="btn btn-primary" id="confirmSchedule"><i class="fas fa-check"></i> Confirm Schedule</button>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
