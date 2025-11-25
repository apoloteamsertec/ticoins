<?php
session_start();
require_once '../config/supabase.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = $_POST["nombre"];
    $username = $_POST["username"];
    $pin = $_POST["pin"];   // PIN de 6 dígitos

    // Validaciones
    if(strlen($pin) !== 6 || !ctype_digit($pin)){
        $error = "El PIN debe tener exactamente 6 números.";
    } else {

        // Crear perfil directo en tabla profiles (rol niño)
        $insert = $supabase->from("profiles", "POST", [
            "nombre_completo" => $nombre,
            "username"        => $username,
            "pin"             => $pin,
            "rol"             => "niño",
            "coins"           => 0
        ]);

        header("Location: usuarios.php");
        exit;
    }
}
?>

<?php include 'header.php'; ?>



<h2>Nuevo Usuario</h2>

<?php if(isset($error)): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>

<form method="POST" class="form-box">

    <label>Nombre completo</label>
    <input type="text" name="nombre" required>

    <label>Username</label>
    <input type="text" name="username" required>

    <label>PIN (6 dígitos)</label>
    <input type="text" 
           name="pin" 
           minlength="6" maxlength="6" 
           pattern="[0-9]{6}" 
           required>

    <button type="submit">Crear usuario</button>
</form>

</div><!-- .container -->
</div><!-- .page -->
</body>
</html>


