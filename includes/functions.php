<?php
/**
 * Utility functions for Gbàgede-Ọjà
 */

/**
 * Log a user activity to the database
 * 
 * @param PDO $pdo Database connection
 * @param int $userId ID of the user performing the action
 * @param string $actionType Type of action (e.g., 'CREATE', 'UPDATE', 'DELETE')
 * @param string $module The module where the action occurred
 * @param string $details Descriptive details about the action
 * @return bool
 */
function logActivity($pdo, $userId, $actionType, $module, $details) {
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, module, details) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $actionType, $module, $details]);
    } catch (PDOException $e) {
        // Silently fail logging to avoid breaking core flows, but consider errors in development
        error_log("Logging error: " . $e->getMessage());
        return false;
    }
}
?>
