<?php
// index.php
require_once 'includes/auth.php';

// Redirect to dashboard or login based on session state
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
} else {
    header('Location: login.php');
    exit;
}