<?php include "header.php"; require "../config/supabase.php"; ?>

<h2>Usuarios</h2>

<a href="usuario_nuevo.php" class="btn">➕ Crear usuario</a>
<br><br>

<?php
$usuarios = $supabase->from("profiles", "GET");
?>

<table class="tabla">
    <tr>
        <th>Nombre</th>
        <th>Username</th>
        <th>Coins</th>
        <th>Rol</th>
        <th>Acciones</th>
    </tr>

<?php foreach($usuarios as $u): ?>
    <tr>
        <td><?= $u["nombre_completo"] ?></td>
        <td>@<?= $u["username"] ?></td>
        <td><?= $u["coins"] ?></td>
        <td><?= $u["rol"] ?></td>
        <td>
            <a href="usuario_editar.php?id=<?= $u['id'] ?>">✏️ Editar</a>
            <a href="usuario_reset.php?id=<?= $u['id'] ?>">🔐 Reset Pass</a>
        </td>
    </tr>
<?php endforeach; ?>
</table>
