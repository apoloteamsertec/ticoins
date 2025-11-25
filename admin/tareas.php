<?php include "header.php"; require_once "../config/supabase.php"; ?>



<h2>Tareas</h2>

<a href="tarea_nueva.php" class="btn" style="margin-bottom:15px; display:inline-block;">
    + Nueva tarea
</a>

<?php
// ============================
// 1) Obtener todas las tareas
// ============================
$tareas = $supabase->from("tareas", "GET", null, "");

// ============================
// 2) Obtener tareas aprobadas (para ocultarlas)
// ============================
$realizadas = $supabase->from(
    "tareas_realizadas",
    "GET",
    null,
    "estado=eq.aprobada"
);

$tareasAprobadas = [];

if ($realizadas) {
    foreach ($realizadas as $r) {
        $tareasAprobadas[$r["tarea_id"]] = true; 
    }
}
?>

<div class="tabla-container">
<table class="tabla">
<tr>
    <th>Título</th>
    <th>Valor</th>
    <th>Asignación</th>
    <th>Estado</th>
    <th>Acciones</th>
</tr>

<?php foreach ($tareas as $tarea): ?>

    <?php 
    // ============================
    //  OCULTAR tareas aprobadas
    // ============================
    if (isset($tareasAprobadas[$tarea["id"]])) {
        continue;
    }
    ?>

    <tr>
        <td><?= htmlspecialchars($tarea["titulo"]) ?></td>
        <td><?= intval($tarea["coins_valor"]) ?> TI-Coins</td>

        <td>
            <?= $tarea["tipo_asignacion"] === "general" 
                ? "General" 
                : "Individual (@".$tarea["usuario_asignado"].")" ?>
        </td>

        <td><?= $tarea["activa"] ? "Activa" : "Inactiva" ?></td>

        <td>
            <a href="tarea_editar.php?id=<?= $tarea["id"] ?>" class="btn">Editar</a>

            <a href="tarea_toggle.php?id=<?= $tarea["id"] ?>&estado=<?= $tarea["activa"] ? 1 : 0 ?>" 
               class="btn" style="background:#FFC107;">
                Toggle
            </a>
        </td>
    </tr>

<?php endforeach; ?>

</table>
</div>
