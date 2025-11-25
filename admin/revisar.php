<?php include "auth_check.php"; ?>

<?php include "header.php"; require_once "../config/supabase.php"; ?>



<h2>Revisión de Tareas</h2>

<?php
// obtener solo tareas en "revision"
$pendientes = $supabase->from(
    "tareas_realizadas",
    "GET",
    null,
    "estado=eq.revision&order=fecha_envio.desc"
);
?>

<table class="tabla">
<tr>
    <th>Niño</th>
    <th>Tarea</th>
    <th>Foto</th>
    <th>Fecha</th>
    <th>Acciones</th>
</tr>

<?php foreach ($pendientes as $p): ?>

<?php
// obtener usuario
$usuario = $supabase->from("profiles", "GET", null, "id=eq.{$p['usuario_id']}")[0];

// obtener tarea
$tarea = $supabase->from("tareas", "GET", null, "id=eq.{$p['tarea_id']}")[0];
?>

<tr>
    <td>
        <?= $usuario["nombre_completo"] ?><br>
        <small>@<?= $usuario["username"] ?></small>
    </td>

    <td>
        <?= $tarea["titulo"] ?><br>
        <small><?= $tarea["coins_valor"] ?> coins</small>
    </td>

    <td>
        <?php if($p["foto_evidencia"]): ?>
            <a href="<?= $p['foto_evidencia'] ?>" target="_blank">
                <img src="<?= $p['foto_evidencia'] ?>" width="80" style="border-radius:6px;">
            </a>
        <?php else: ?>
            (sin foto)
        <?php endif; ?>
    </td>

    <td><?= date("d/m/Y H:i", strtotime($p["fecha_envio"])) ?></td>

    <td>
        <a 
            class="btn-aceptar" 
            href="revisar_accion.php?id=<?= $p['id'] ?>&accion=aprobar"
        >✔ Aprobar</a>

        <a 
            class="btn-rechazar" 
            href="revisar_accion.php?id=<?= $p['id'] ?>&accion=rechazar"
        >✖ Rechazar</a>
    </td>
</tr>

<?php endforeach; ?>

</table>

 </div><!-- .container -->
</div><!-- .page -->
</body>
</html>