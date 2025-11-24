<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/supabase.php';

if (!isset($_SESSION["token"])) {
    header("Location: ../auth/login.php");
    exit;
}
