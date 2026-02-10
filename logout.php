<?php
require_once __DIR__ . '/config/database.php';

// Destroy session
session_destroy();

// Redirect to home
redirect('index.php');
?>