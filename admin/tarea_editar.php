<?php
include "header.php";
require_once "../config/supabase.php";

if (!isset($_GET["id"])) {
    die("ID de tarea no recibido.");
}

$id = $_GET["id"];
$mensaje = "";

// ===============================
// OBTENER LA TAREA
// ===============================
$tareaDB = $supabase->from("tareas", "GET", null, "id=eq.$id");

if (!$tareaDB || empty($tareaDB)) {
    die("La tarea no existe.");
}

$tarea = $tareaDB[0];

// ===============================
// SI ENVÍAN FORMULARIO DE EDICIÓN
// ===============================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["accion"]) && $_POST["accion"] === "editar") {

    $titulo   = $_POST["titulo"];
    $descripcion = $_POST["descripcion"];
    $valor    = (int)$_POST["valor"];
    $tipo     = $_POST["tipo"];
    $usuario_asignado = $_POST["usuario_asignado"] ?? null;

    $supabase->from("tareas", "PATCH", [
        "titulo" => $titulo,
        "descripcion" => $descripcion,
        "coins_valor" => $valor,
        "tipo_asignacion" => $tipo,
        "usuario_asignado" => $tipo === "individual" ? $usuario_asignado : null
    ], "id=eq.$id");

    $mensaje = "Tarea actualizada correctamente.";
}

// ===============================
// SI ELIMINA LA TAREA
// ===============================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["accion"]) && $_POST["accion"] === "eliminar") {

    // Primero borrar relaciones si las hay
    $supabase->from("tareas_realizadas", "DELETE", null, "tarea_id=eq.$id");

    // Luego borrar la tarea
    $supabase->from("tareas", "DELETE", null, "id=eq.$id");

    header("Location: tareas.php");
    exit;
}

// ===============================
// OBTENER NIÑOS PARA LISTA INDIVIDUAL
// ===============================
$usuarios = $supabase->from("profiles", "GET", null, "rol=eq.nino");

?>

<div class="container">
<h2>Editar Tarea</h2>

<?php if ($mensaje): ?>
    <p class="success"><?= $mensaje ?></p>
<?php endif; ?>

<form method="POST" class="form-box">

    <input type="hidden" name="accion" value="editar">

    <label>Título</label>
    <input type="text" name="titulo" value="<?= htmlspecialchars($tarea["titulo"]) ?>" required>

    <label>Descripción</label>
    <textarea name="descripcion" rows="3"><?= htmlspecialchars($tarea["descripcion"]) ?></textarea>

    <label>Valor en TI-Coins</label>
    <input type="number" name="valor" value="<?= (int)$tarea["coins_valor"] ?>" required>

    <label>Tipo de asignación</label>
    <select name="tipo" id="tipoAsignacion" onchange="toggleUsuario()">
        <option value="general" <?= $tarea["tipo_asignacion"] === "general" ? "selected" : "" ?>>General</option>
        <option value="individual" <?= $tarea["tipo_asignacion"] === "individual" ? "selected" : "" ?>>Individual</option>
    </select>

    <div id="usuarioSelect" style="margin-top:10px; display: <?= $tarea["tipo_asignacion"] === 'individual' ? 'block' : 'none' ?>;">
        <label>Asignar a un niño</label>
        <select name="usuario_asignado">
            <?php foreach($usuarios as $u): ?>
                <option value="<?= $u["id"] ?>" <?= $tarea["usuario_asignado"] === $u["id"] ? "selected" : "" ?>>
                    <?= $u["nombre_completo"] ?> (@<?= $u["username"] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <button type="submit" class="btn">Guardar cambios</button>
</form>

<hr style="margin:30px 0;">

<h3>Eliminar Tarea</h3>

<form method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta tarea? Esta acción no se puede deshacer.');">
    <input type="hidden" name="accion" value="eliminar">
    <button type="submit" class="btn" style="background:#E53935;">Eliminar tarea</button>
</form>

</div>

<script>
function toggleUsuario() {
    let tipo = document.getElementById("tipoAsignacion").value;
    document.getElementById("usuarioSelect").style.display = 
        tipo === "individual" ? "block" : "none";
}
</script>

</body>
</html>
