<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Admin</title>
<link rel="stylesheet" href="../assets/styles.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
<nav class="topbar">
    <div class="logo">TI Coins Admin</div>

    <div class="menu-toggle" onclick="toggleMenu()">☰</div>

    <ul class="menu" id="menu">
        <li><a href="index.php">Dashboard</a></li>
        <li><a href="usuarios.php">Usuarios</a></li>
        <li><a href="tareas.php">Tareas</a></li>
        <li><a href="premios.php">Premios</a></li>
        <li><a href="revisar.php">Revisar Tareas</a></li>
        <li><a href="../auth/logout.php">Salir</a></li>
    </ul>
</nav>

<script>
function toggleMenu() {
    document.getElementById("menu").classList.toggle("show");
}
</script>

<div class="container">
