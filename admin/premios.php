<?php include "header.php"; require_once "../config/supabase.php"; ?>

<h2>Premios</h2>

<a class="btn" href="premio_nuevo.php">➕ Nuevo premio</a>
<br><br>

<?php
$premios = $supabase->from("premios", "GET");
?>

<table class="tabla">
<tr>
    <th>Imagen</th>
    <th>Nombre</th>
    <th>Coins</th>
    <th>Estado</th>
    <th>Acciones</th>
</tr>

<?php foreach($premios as $p): ?>
<tr>

    <td>
        <?php if($p["imagen"]): ?>
            <img src="<?= $p['imagen'] ?>" width="70">
        <?php else: ?>
            (sin imagen)
        <?php endif; ?>
    </td>

    <td><?= $p["nombre"] ?></td>
    <td><?= $p["costo_coins"] ?></td>
    <td><?= $p["activo"] ? "Activo" : "Inactivo" ?></td>

    <td>
        <a href="premio_editar.php?id=<?= $p['id'] ?>">✏️ Editar</a> |
        <a href="premio_toggle.php?id=<?= $p['id'] ?>">⏯ Activar/Desactivar</a>
    </td>

</tr>
<?php endforeach; ?>

</table>
</div> <!-- cierre container -->
</body>
</html>
