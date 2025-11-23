<?php
session_start();
require '../config/supabase.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Login Supabase
    $login = $supabase->authLogin($email, $password);

    if (isset($login["access_token"])) {

        $_SESSION["token"] = $login["access_token"];
        $_SESSION["user_id"] = $login["user"]["id"];

        // Consultar perfil del usuario
$perfil = $supabase->from(
    "profiles",
    "GET",
    null,
    "id=eq." . $_SESSION['user_id']
);
        // Validar si es admin
        if (!empty($perfil) && isset($perfil[0]["rol"]) && $perfil[0]["rol"] === "admin") {
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
<title>Login Admin</title>
<link rel="stylesheet" href="../assets/styles.css">
</head>
<body>

<div class="login-box">
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

</body>
</html>
