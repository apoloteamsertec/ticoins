<?php
session_start();
require_once "../config/supabase.php";

$error = "";

// -----------------------------------------
// SI SE ENVÍA EL FORMULARIO
// -----------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $pin      = trim($_POST["pin"]);

    // Validar formato
    if (strlen($pin) !== 6 || !ctype_digit($pin)) {
        $error = "El PIN debe tener exactamente 6 números.";
    } else {

        // Buscar usuario en Supabase
        $perfil = $supabase->from(
            "profiles",
            "GET",
            null,
            "username=eq.$username"
        );

        if (!$perfil) {
            $error = "Usuario incorrecto.";
        } else {

            $perfil = $perfil[0];

            // Verificar rol
            if ($perfil["rol"] !== "nino") {
                $error = "Este usuario no es válido para el panel de niños.";
            }
            // Verificar PIN
            else if ($perfil["pin"] !== $pin) {
                $error = "PIN incorrecto.";
            }
            else {
                // LOGIN CORRECTO
                $_SESSION["child_id"] = $perfil["id"];

                header("Location: index.php");
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login Niño</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/children.css">

<style>
.login-child-box{
    max-width:350px;
    margin:60px auto;
    background:white;
    padding:28px;
    border-radius:18px;
    box-shadow:0 8px 26px rgba(0,0,0,0.18);
    text-align:center;
}
.login-child-box h2{
    margin-bottom:12px;
}
.login-child-box input{
    width:100%;
    padding:12px;
    border-radius:10px;
    border:1px solid #ccc;
    margin-bottom:12px;
    font-size:15px;
}
.login-child-box button{
    width:100%;
    padding:12px;
    background:#3EB04B;
    color:white;
    border:none;
    border-radius:10px;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
}
.error-msg{
    background:#ffebee;
    border-left:4px solid #e53935;
    padding:10px;
    border-radius:8px;
    margin-bottom:14px;
    font-size:14px;
}
</style>
</head>

<body>

<div class="login-child-box">
    <h2>Ingresar</h2>
    <p>Panel de Tareas</p>

    <?php if($error): ?>
    <div class="error-msg"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Usuario" required>

        <input type="password" 
               name="pin" 
               placeholder="PIN de 6 dígitos" 
               minlength="6" maxlength="6"
               inputmode="numeric"
               required>

        <button type="submit">Entrar</button>
    </form>
</div>

</body>
</html>
