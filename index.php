<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
$mensaje = '';
$error = '';
$editar = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'La sesión expiró o la solicitud no es válida. Recarga la página e inténtalo de nuevo.';
    } else {
        $accion = $_POST['accion'] ?? '';
        if ($accion === 'guardar' || $accion === 'actualizar') {
            $nombre = trim($_POST['nombre'] ?? '');
            $marca = trim($_POST['marca'] ?? '');
            $unidad = trim($_POST['unidad'] ?? '');
            $ubicacion = trim($_POST['ubicacion'] ?? '');
            if ($nombre === '' || $marca === '' || $unidad === '' || $ubicacion === '') {
                $error = 'Completa todos los campos.';
            } elseif (mb_strlen($nombre) > 150 || mb_strlen($marca) > 100 || mb_strlen($unidad) > 60 || mb_strlen($ubicacion) > 150) {
                $error = 'Uno o más campos superan la longitud permitida.';
            } else {
                if ($accion === 'guardar') {
                    $stmt = $pdo->prepare('INSERT INTO articulos (nombre, marca, unidad, ubicacion) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$nombre, $marca, $unidad, $ubicacion]);
                    $mensaje = 'Artículo registrado correctamente.';
                } else {
                    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                    if (!$id || $id < 1) {
                        $error = 'El artículo seleccionado no es válido.';
                    } else {
                        $stmt = $pdo->prepare('UPDATE articulos SET nombre=?, marca=?, unidad=?, ubicacion=? WHERE id=?');
                        $stmt->execute([$nombre, $marca, $unidad, $ubicacion, $id]);
                        $mensaje = $stmt->rowCount() ? 'Artículo actualizado correctamente.' : 'No hubo cambios en el artículo.';
                    }
                }
            }
        } elseif ($accion === 'eliminar') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id && $id > 0) {
                $stmt = $pdo->prepare('DELETE FROM articulos WHERE id = ?');
                $stmt->execute([$id]);
                $mensaje = $stmt->rowCount() ? 'Artículo eliminado.' : 'No se encontró el artículo.';
            } else {
                $error = 'El artículo seleccionado no es válido.';
            }
        }
    }
}

if (isset($_GET['editar'])) {
    $idEditar = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);
    if ($idEditar && $idEditar > 0) {
        $stmt = $pdo->prepare('SELECT * FROM articulos WHERE id = ?');
        $stmt->execute([$idEditar]);
        $editar = $stmt->fetch() ?: null;
    }
}
$busqueda = trim($_GET['q'] ?? '');
if ($busqueda !== '') {
    $stmt = $pdo->prepare('SELECT * FROM articulos WHERE nombre LIKE ? OR marca LIKE ? OR unidad LIKE ? OR ubicacion LIKE ? ORDER BY id DESC');
    $like = '%' . $busqueda . '%';
    $stmt->execute([$like, $like, $like, $like]);
    $articulos = $stmt->fetchAll();
} else {
    $articulos = $pdo->query('SELECT * FROM articulos ORDER BY id DESC')->fetchAll();
}
$total = (int)$pdo->query('SELECT COUNT(*) FROM articulos')->fetchColumn();
$valores = $editar ?: ['id'=>'', 'nombre'=>'', 'marca'=>'', 'unidad'=>'', 'ubicacion'=>''];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de artículos</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="ambient ambient-one"></div><div class="ambient ambient-two"></div>
<main class="container">
    <header class="topbar">
        <div class="brand-mark">A<span>.</span></div>
        <div><p class="eyebrow">INVENTARIO / CONTROL</p><h1>Gestión de artículos</h1></div>
        <div class="total-card"><span>Artículos registrados</span><strong><?= $total ?></strong></div>
    </header>

    <?php if ($mensaje): ?><div class="notice success"><?= h($mensaje) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="notice error"><?= h($error) ?></div><?php endif; ?>

    <section class="panel form-panel">
        <div class="section-heading">
            <div><p class="eyebrow"><?= $editar ? 'EDICIÓN DE REGISTRO' : 'NUEVO REGISTRO' ?></p>
            <h2><?= $editar ? 'Modificar artículo' : 'Agregar artículo' ?></h2></div>
            <span class="section-icon"><?= $editar ? '✎' : '+' ?></span>
        </div>
        <form method="post" action="index.php" class="article-form">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="accion" value="<?= $editar ? 'actualizar' : 'guardar' ?>">
            <?php if ($editar): ?><input type="hidden" name="id" value="<?= h($valores['id']) ?>"><?php endif; ?>
            <label>Nombre del artículo<input name="nombre" maxlength="150" required placeholder="Ej. Cable de red" value="<?= h($valores['nombre']) ?>"></label>
            <label>Marca<input name="marca" maxlength="100" required placeholder="Ej. Nexxt" value="<?= h($valores['marca']) ?>"></label>
            <label>Unidad<input name="unidad" maxlength="60" required placeholder="Ej. Unidad, caja, metro" value="<?= h($valores['unidad']) ?>"></label>
            <label>Ubicación<input name="ubicacion" maxlength="150" required placeholder="Ej. Bodega A - Estante 2" value="<?= h($valores['ubicacion']) ?>"></label>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit"><?= $editar ? 'Guardar cambios' : 'Registrar artículo' ?></button>
                <?php if ($editar): ?><a class="btn btn-ghost" href="index.php">Cancelar</a><?php else: ?><button class="btn btn-ghost" type="reset">Limpiar</button><?php endif; ?>
            </div>
        </form>
    </section>

    <section class="panel list-panel">
        <div class="list-heading">
            <div><p class="eyebrow">BASE DE DATOS</p><h2>Artículos registrados</h2></div>
            <form method="get" class="search-form">
                <input type="search" name="q" value="<?= h($busqueda) ?>" placeholder="Buscar artículo...">
                <button class="btn btn-small btn-primary" type="submit">Buscar</button>
                <?php if ($busqueda !== ''): ?><a class="btn btn-small btn-ghost" href="index.php">Ver todos</a><?php endif; ?>
            </form>
        </div>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Nombre</th><th>Marca</th><th>Unidad</th><th>Ubicación</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php if (!$articulos): ?>
                <tr><td colspan="5" class="empty">No hay artículos para mostrar.</td></tr>
            <?php else: foreach ($articulos as $articulo): ?>
                <tr>
                    <td data-label="Nombre"><strong><?= h($articulo['nombre']) ?></strong></td>
                    <td data-label="Marca"><?= h($articulo['marca']) ?></td>
                    <td data-label="Unidad"><span class="tag"><?= h($articulo['unidad']) ?></span></td>
                    <td data-label="Ubicación"><?= h($articulo['ubicacion']) ?></td>
                    <td data-label="Acciones"><div class="row-actions">
                        <a class="btn btn-small btn-edit" href="?editar=<?= (int)$articulo['id'] ?>">Editar</a>
                        <form method="post" onsubmit="return confirm('¿Seguro que deseas eliminar este artículo?');">
                            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?= (int)$articulo['id'] ?>">
                            <button class="btn btn-small btn-delete" type="submit">Eliminar</button>
                        </form>
                    </div></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
        <p class="footnote">Los datos se guardan en MySQL. La tabla <code>articulos</code> se crea automáticamente si no existe.</p>
    </section>
    <footer>GESTIÓN DE INVENTARIO <span>•</span> PHP + MYSQL</footer>
</main>
</body>
</html>
