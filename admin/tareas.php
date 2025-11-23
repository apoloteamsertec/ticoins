<?php include "header.php"; require "../config/supabase.php"; ?>

<h2>Tareas</h2>
<a href="tarea_nueva.php" class="btn">➕ Nueva tarea</a>
<br><br>

<?php
$tareas = $supabase->from("tareas", "GET");
?>

<table class="tabla">
<tr>
    <th>Título</th>
    <th>Coins</th>
    <th>Asignada a</th>
    <th>Activa</th>
    <th>Acciones</th>
</tr>

<?php foreach($tareas as $t): ?>

<?php
$asignados = $supabase->from(
    "tareas_asignadas",
    "GET",
    null,
    "tarea_id=eq.{$t['id']}"
);

$lista = [];
foreach ($asignados as $a) {
    $u = $supabase->from("profiles", "GET", null, "id=eq.{$a['usuario_id']}")[0];
    $lista[] = $u["username"];
}
?>

<tr>
    <td><?= $t["titulo"] ?></td>
    <td><?= $t["coins_valor"] ?></td>
    <td><?= implode(", ", $lista) ?></td>
    <td><?= $t["activa"] ? "Sí" : "No" ?></td>
    <td>
        <a href="tarea_editar.php?id=<?= $t['id'] ?>">✏️</a>
        <a href="tarea_toggle.php?id=<?= $t['id'] ?>">⏯</a>
    </td>
</tr>

<?php endforeach; ?>
</table>
