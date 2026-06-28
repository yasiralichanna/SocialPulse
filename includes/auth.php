<?php
/**
 * Authentication & Authorization Helper
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/**
 * Check if user is logged in, redirect to login if not
 */
function requireLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Check if user has required role
 */
function requireRole(string ...$roles): void {
    requireLogin();
    if (!in_array($_SESSION['user_role'], $roles)) {
        http_response_code(403);
        die('<h1>403 — Access Denied</h1><p>You do not have permission to access this page.</p>');
    }
}

/**
 * Get current logged-in user data
 */
function currentUser(): ?array {
    if (!isset($_SESSION['user_id'])) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role'  => $_SESSION['user_role'],
    ];
}

/**
 * Authenticate user by email & password
 */
function authenticate(string $email, string $password): bool {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && $user['status'] === 'active' && password_verify($password, $user['password'])) {
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];
        return true;
    }
    return false;
}

/**
 * Logout
 */
function logout(): void {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

/**
 * Check permission for a specific action
 */
function can(string $action): bool {
    $role = $_SESSION['user_role'] ?? 'viewer';
    $permissions = [
        'admin'  => ['create_post', 'edit_post', 'delete_post', 'schedule_post', 'publish_post', 'view_analytics', 'manage_users', 'export_reports', 'manage_settings'],
        'editor' => ['create_post', 'edit_post', 'schedule_post', 'publish_post', 'view_analytics', 'export_reports'],
        'viewer' => ['view_analytics'],
    ];
    return in_array($action, $permissions[$role] ?? []);
}

/**
 * Send JSON response helper
 */
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
