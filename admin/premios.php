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

    <td><?= htmlspecialchars($p["nombre"]) ?></td>
    <td><?= number_format($p["costo_coins"], 0, ",", ".") ?></td>
    <td><?= $p["activo"] ? "Activo" : "Inactivo" ?></td>

    <td>
        <a href="premio_editar.php?id=<?= $p['id'] ?>" class="btn-editar">Editar</a>

        <a href="premio_toggle.php?id=<?= $p['id'] ?>" class="btn-toggle">
            Toggle
        </a>
    </td>

</tr>
<?php endforeach; ?>

</table>

</div><!-- .container -->
</div><!-- .page -->
</body>
</html>

