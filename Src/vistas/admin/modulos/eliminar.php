<?php
$archivos = getFiles();
?>
<div class="main-content">
    <h1>Eliminar Archivos</h1>
    <form action="index.php" method="post">
        <ul>
            <?php foreach ($archivos as $archivo): ?>
                <li>
                    <label>
                        <input type="checkbox" name="archivos_a_eliminar[]" value="<?= htmlspecialchars($archivo['ruta']) ?>">
                        <?= htmlspecialchars($archivo['nombre']) ?> - <?= htmlspecialchars($archivo['tamano']) ?> bytes
                    </label>
                </li>
            <?php endforeach; ?>
        </ul>
        <input type="submit" name="eliminar_seleccionados" value="Eliminar Seleccionados">
    </form>
</div>