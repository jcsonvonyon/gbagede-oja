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
 * Restrict access to Admins only
 */
function adminOnly() {
    requireLogin();
    if (!hasRole('Admin')) {
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
