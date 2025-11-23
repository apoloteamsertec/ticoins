<?php
session_start();
require '../config/supabase.php';

if (!isset($_SESSION["token"])) {
    header("Location: ../auth/login.php");
    exit;
}
