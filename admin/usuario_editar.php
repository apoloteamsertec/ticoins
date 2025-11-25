<?php
include "header.php";
require_once "../config/supabase.php";

$id = $_GET["id"];
$mensaje = "";

// Obtener info del usuario
$user = $supabase->from("profiles", "GET", null, "id=eq.$id")[0];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $supabase->from("profiles", "PATCH", [
        "nombre_completo" => $_POST["nombre"],
        "username" => $_POST["username"],
        "coins" => intval($_POST["coins"])
    ], "id=eq.$id");

    $mensaje = "Perfil actualizado!";
}
?>


<h2>Editar usuario</h2>

<?php if ($mensaje): ?>
<p><?= $mensaje ?></p>
<?php endif; ?>

<form method="POST" class="form-box">

    <label>Nombre completo</label>
    <input type="text" name="nombre" value="<?= $user['nombre_completo'] ?>" required>

    <label>Username</label>
    <input type="text" name="username" value="<?= $user['username'] ?>" required>

    <label>Coins (manual)</label>
    <input type="number" name="coins" value="<?= $user['coins'] ?>" min="0" required>

    <button type="submit">Guardar cambios</button>
</form>

 </div><!-- .container -->
</div><!-- .page -->
</body>
</html>