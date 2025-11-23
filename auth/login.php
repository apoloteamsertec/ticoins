<?php
session_start();
require '../config/supabase.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $login = $supabase->authLogin($email, $password);

    if (isset($login["access_token"])) {
        $_SESSION["token"] = $login["access_token"];
        $_SESSION["user_id"] = $login["user"]["id"];

        $perfil = $supabase->from("profiles", "GET", null, "id=eq.{$_SESSION['user_id']}");

        if (!empty($perfil) && $perfil[0]["rol"] === "admin") {
            header("Location: ../admin/index.php");
            exit;
        } else {
            $error = "No tienes permisos de administrador.";
        }
    } else {
        $error = "Credenciales incorrectas.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Admin</title>
<link rel="stylesheet" href="../assets/styles.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>

<div class="login-box-wrapper">
    <div class="login-box">

        <img src="../assets/logo.png" class="login-logo" alt="logo">
        <h2>Panel Admin</h2>

        <?php if(isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Email del admin" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>

    </div>
</div>

</body>
</html>
