<?php
// includes/functions.php

/**
 * Safely escapes a string for HTML output to prevent XSS attacks.
 * Usage: echo e($user_input);
 */
function e($string) {
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Formats a date cleanly for UI display
 */
function format_date_clean($date) {
    if (!$date) return '-';
    return date('d M Y, h:i A', strtotime($date));
}

/**
 * Safely calculates a percentage
 */
function calculate_percentage($part, $total) {
    if ($total <= 0) return 0;
    return round(($part / $total) * 100, 2);
}
?>