<?php include "header.php"; require_once "../config/supabase.php"; ?>

<?php
$mensaje = "";

// Obtener lista de usuarios (solo niños)
$usuarios = $supabase->from("profiles", "GET", null, "rol=eq.nino");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $titulo = $_POST["titulo"];
    $descripcion = $_POST["descripcion"];
    $valor = $_POST["valor"];

    // Crear tarea base
    $tarea = $supabase->from("tareas", "POST", [
        "titulo" => $titulo,
        "descripcion" => $descripcion,
        "coins_valor" => $valor
    ]);

    if (isset($tarea[0]["id"])) {
        $idTarea = $tarea[0]["id"];

        // Si asigna a TODOS
        if (isset($_POST["todos"])) {
            foreach ($usuarios as $u) {
                $supabase->from("tareas_asignadas", "POST", [
                    "tarea_id" => $idTarea,
                    "usuario_id" => $u["id"]
                ]);
            }
        }

        // Si asigna a usuarios seleccionados
        if (isset($_POST["asignados"])) {
            foreach ($_POST["asignados"] as $uid) {
                $supabase->from("tareas_asignadas", "POST", [
                    "tarea_id" => $idTarea,
                    "usuario_id" => $uid
                ]);
            }
        }

        $mensaje = "Tarea creada correctamente.";
    } else {
        $mensaje = "Error al crear tarea.";
    }
}
?>

<h2>Nueva tarea</h2>

<?php if($mensaje): ?>
<p><strong><?= $mensaje ?></strong></p>
<?php endif; ?>

<form method="POST" class="form-box">

    <label>Título</label>
    <input type="text" name="titulo" required>

    <label>Descripción</label>
    <textarea name="descripcion"></textarea>

    <label>Coins que vale</label>
    <input type="number" name="valor" required>

    <label>Asignar a niños</label>

    <button type="button" onclick="selectAll()" class="btn-small">Asignar a TODOS</button>

    <div class="multi-select">
        <?php foreach($usuarios as $u): ?>
            <label>
                <input type="checkbox" name="asignados[]" value="<?= $u['id'] ?>">
                <?= $u["nombre_completo"] ?> (@<?= $u["username"] ?>)
            </label>
        <?php endforeach; ?>
    </div>

    <input type="hidden" name="todos" id="todosFlag">

    <button type="submit">Crear tarea</button>
</form>

<script>
function selectAll() {
    document.querySelectorAll('input[name="asignados[]"]').forEach(c => c.checked = true);
    document.getElementById("todosFlag").value = "1";
}
</script>

