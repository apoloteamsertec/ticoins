<?php

// Garantiza que la sesión esté iniciada sin causar error
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Evita cualquier salida accidental
ob_start();

// Verifica acceso
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

?>
