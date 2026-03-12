<?php
session_start();

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Check if user has a specific role
 */
function hasRole($role_name) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role_name;
}

/**
 * Check if user has permission for a specific module and action
 */
function hasPermission($module, $action = 'view') {
    // Admins have bypass for everything
    if (hasRole('Admin')) return true;

    if (!isset($_SESSION['permissions'])) return false;
    
    $perms = $_SESSION['permissions'];
    
    // Check if module exists in permissions and the specific action is allowed
    return isset($perms[$module]) && in_array($action, (array)$perms[$module]);
}

/**
 * Restrict access based on permission
 */
function requirePermission($module, $action = 'view') {
    requireLogin();
    if (!hasPermission($module, $action)) {
        header("Location: dashboard.php?error=unauthorized");
        exit();
    }
}

/**
 * Logout user
 */
function logout() {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
