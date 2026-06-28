<?php
/**
 * Settings & Integrations Page
 */
require_once __DIR__ . '/includes/auth.php';
requireLogin();
requireRole('admin');

$pageTitle = 'Settings';
$db = getDB();

$accountRows = $db->query("SELECT * FROM social_accounts WHERE user_id = " . (int)$_SESSION['user_id'])->fetchAll();
$accounts = [];
foreach ($accountRows as $row) {
    $accounts[$row['platform']] = $row;
}
$cronLogs = $db->query("SELECT * FROM cron_logs ORDER BY created_at DESC LIMIT 5")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section class="page active" id="page-settings">
    <div class="page-header"><div><h1>Settings</h1><p class="page-subtitle">Configure your platform integrations</p></div></div>
    
    <div class="settings-grid">
        <div class="card">
            <div class="card-header"><h3>Connected Accounts</h3></div>
            <div class="card-body">
                <?php
                $platformConfig = [
                    'facebook'  => ['icon' => 'fab fa-facebook-f', 'label' => 'Facebook'],
                    'twitter'   => ['icon' => 'fab fa-twitter', 'label' => 'Twitter'],
                    'instagram' => ['icon' => 'fab fa-instagram', 'label' => 'Instagram'],
                ];
                foreach ($platformConfig as $pKey => $pConf):
                    $acct = $accounts[$pKey] ?? null;
                    $isConnected = $acct && $acct['status'] === 'connected';
                ?>
                <div class="integration-item" id="int-<?= $pKey ?>">
                    <div class="integration-icon <?= $pKey ?>"><i class="<?= $pConf['icon'] ?>"></i></div>
                    <div class="integration-info">
                        <strong><?= $pConf['label'] ?></strong>
                        <p><?= $acct ? htmlspecialchars($acct['account_name'] ?? 'Linked') : 'Not connected' ?></p>
                    </div>
                    <?php if ($isConnected): ?>
                        <span class="status-badge connected">Connected</span>
                        <button class="btn btn-ghost btn-sm">Disconnect</button>
                    <?php else: ?>
                        <span class="status-badge <?= $acct ? 'pending' : 'inactive' ?>"><?= $acct ? 'Reconnect' : 'Disconnected' ?></span>
                        <button class="btn btn-primary btn-sm">Connect</button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>API Configuration</h3></div>
            <div class="card-body">
                <div class="form-group"><label>Facebook App ID</label><input type="password" class="input-styled" value="fb_app_123456789" disabled></div>
                <div class="form-group"><label>Twitter API Key</label><input type="password" class="input-styled" value="tw_key_987654321" disabled></div>
                <div class="form-group"><label>Instagram Client ID</label><input type="password" class="input-styled" value="ig_client_456123789" disabled></div>
                <button class="btn btn-secondary" onclick="document.querySelectorAll('input[type=password]').forEach(i => i.type='text')"><i class="fas fa-eye"></i> Reveal Keys</button>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Cron Job Settings</h3></div>
            <div class="card-body">
                <div class="cron-status">
                    <div class="cron-indicator running"></div>
                    <span>Scheduler Service: <strong>Running</strong></span>
                </div>
                <div class="form-group"><label>Post Check Interval</label>
                    <select class="select-styled" id="cronInterval">
                        <option value="1">Every 1 minute</option>
                        <option value="5" selected>Every 5 minutes</option>
                        <option value="15">Every 15 minutes</option>
                    </select>
                </div>
                <div class="cron-logs">
                    <h4 style="font-size:0.8rem;margin-bottom:0.5rem;color:var(--text-muted)">Recent Execution Logs</h4>
                    <?php foreach ($cronLogs as $log): ?>
                        <div class="log-entry <?= $log['status'] ?>">
                            <span class="log-time"><?= date('H:i:s', strtotime($log['created_at'])) ?></span>
                            <?= htmlspecialchars($log['details']) ?>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($cronLogs)): ?>
                        <div class="log-entry success">
                            <span class="log-time"><?= date('H:i:s') ?></span> ✓ Cron service initialized, waiting for schedule...
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
