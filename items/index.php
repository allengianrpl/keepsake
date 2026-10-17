<?php
// items/index.php - redirect to unified dashboard
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Location: ../dashboard.php');
exit;
