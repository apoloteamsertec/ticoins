<?php 
include "header.php"; 
require_once "../config/supabase.php"; 

$id = $_GET["id"];
$mensaje = "";

// Obtener perfil actual
$perfil = $supabase->from("profiles", "GET", null, "id=eq.$id");
$perfil = $perfil ? $perfil[0] : null;

if (!$perfil) {
    die("Usuario no encontrado.");
}

// Si envía el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nuevoPin = trim($_POST["pin"]);

    if (strlen($nuevoPin) !== 6 || !ctype_digit($nuevoPin)) {
        $mensaje = "El PIN debe tener exactamente 6 números.";
    } else {

        // Actualizar PIN en Supabase
        $supabase->from("profiles", "PATCH", [
            "pin" => $nuevoPin
        ], "id=eq.$id");

        $mensaje = "PIN actualizado correctamente.";
    }
}
?>

<h2>Editar PIN</h2>

<?php if($mensaje): ?>
    <p class="error" style="margin-bottom:15px;"><?= $mensaje ?></p>
<?php endif; ?>

<form method="POST" class="form-box">

    <label>PIN actual</label>
    <input type="text" 
           value="<?= htmlspecialchars($perfil['pin']) ?>" 
           disabled 
           style="background:#eee;">

    <label>Nuevo PIN (6 dígitos)</label>
    <input type="text" 
           name="pin" 
           minlength="6" 
           maxlength="6"
           pattern="[0-9]{6}"
           required>

    <button type="submit">Actualizar PIN</button>
</form>

</div><!-- .container -->
</div><!-- .page -->
</body>
</html>
