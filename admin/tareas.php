<?php include "header.php"; require_once "../config/supabase.php"; ?>

<h2>Tareas</h2>

<?php
// Obtener todas las tareas
$tareas = $supabase->from("tareas", "GET", null, "");

// Obtener todas las tareas aprobadas
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
    // OCULTAR tareas aprobadas
    if (isset($tareasAprobadas[$tarea["id"]])) {
        continue;
    }
    ?>

    <tr>
        <td><?= htmlspecialchars($tarea["titulo"]) ?></td>
        <td><?= $tarea["coins_valor"] ?> TI-Coins</td>
        <td><?= ucfirst($tarea["tipo_asignacion"]) ?></td>
        <td><?= $tarea["activa"] ? "Activa" : "Inactiva" ?></td>

        <td>
            <a href="tarea_editar.php?id=<?= $tarea["id"] ?>" class="btn">Editar</a>
            <a href="tarea_toggle.php?id=<?= $tarea["id"] ?>&estado=<?= $tarea["activa"] ? 1 : 0 ?>" 
               class="btn" style="background:#FFC107;">Toggle</a>
        </td>
    </tr>

<?php endforeach; ?>
</table>
