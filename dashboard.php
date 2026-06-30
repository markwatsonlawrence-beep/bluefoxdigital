<?php
require_once 'config.php';
require_admin();
$pdo = db();

$q = trim($_GET['q'] ?? '');
$estado = trim($_GET['estado'] ?? '');
$numeroFiltro = trim($_GET['numero'] ?? '');

$params = [];
$where = [];

if ($q !== '') {
    $where[] = '(p.nombre LIKE ? OR p.correo LIKE ? OR p.telefono LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($estado !== '') {
    $where[] = 'p.estado = ?';
    $params[] = $estado;
}
if ($numeroFiltro !== '') {
    $where[] = 'p.numero_seleccionado LIKE ?';
    $params[] = '%' . str_pad($numeroFiltro, 2, '0', STR_PAD_LEFT) . '%';
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$numeros = $pdo->query("SELECT n.*, p.nombre, p.telefono, p.correo FROM numeros n LEFT JOIN participantes p ON n.participante_id = p.id ORDER BY n.numero ASC")->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM participantes p $whereSql ORDER BY p.fecha_registro DESC");
$stmt->execute($params);
$participantes = $stmt->fetchAll();

$resumen = $pdo->query("SELECT estado, COUNT(*) total FROM numeros GROUP BY estado")->fetchAll();
$stats = ['disponible' => 0, 'reservado' => 0, 'vendido' => 0, 'bloqueado' => 0];
foreach ($resumen as $row) $stats[$row['estado']] = (int)$row['total'];
$total = array_sum($stats);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= e(NOMBRE_SORTEO) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">
<?php if(isset($_GET['reiniciado'])): ?>
<div class="success-message">
    ✅ El sorteo fue reiniciado correctamente.
</div>
<?php endif; ?>
<header class="admin-header">
    <div>
        <span class="dashboard-label">Dashboard</span>
        <h1><?= e(NOMBRE_SORTEO) ?></h1>
    </div>
    <div class="admin-actions">

    <a href="agregar_participante.php" class="btn primary">
        ➕ Agregar participante
    </a>

    <a href="index.php" class="btn ghost">
        🌐 Ver Landing
    </a>

    <form action="reiniciar_sorteo.php"
          method="POST"
          style="display:inline;"
          onsubmit="return confirm('¿Deseas reiniciar completamente el sorteo?');">

        <button type="submit" class="btn warning">
            🔄 Reiniciar Sorteo
        </button>

    </form>

</div>
</header>

<main class="admin-main">
    <section class="stats-grid">
        <article><strong><?= $total ?></strong><span>Total</span></article>
        <article><strong><?= $stats['disponible'] ?></strong><span>Disponibles</span></article>
        <article><strong><?= $stats['vendido'] ?></strong><span>Vendidos</span></article>
    </section>

    <section class="admin-panel">
        <div class="panel-head">
            <h2>Administrar números</h2>
            <p>Cambia manualmente el estado de cada número.</p>
        </div>
        <div class="admin-numbers-grid">
            <?php foreach ($numeros as $item): ?>
                <form action="actualizar_numero.php" method="POST" class="admin-number-card <?= e($item['estado']) ?>">
                    <input type="hidden" name="numero" value="<?= e($item['numero']) ?>">
                    <strong><?= e($item['numero']) ?></strong>
                    <small><?= e($item['estado']) ?></small>
                    <?php if ($item['nombre']): ?>
                        <span title="<?= e($item['correo']) ?>"><?= e($item['nombre']) ?></span>
                    <?php endif; ?>
                    <select name="estado">
                        <?php foreach (['disponible','vendido'] as $op): ?>
                            <option value="<?= $op ?>" <?= $item['estado'] === $op ? 'selected' : '' ?>><?= ucfirst($op) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Guardar</button>
                </form>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="admin-panel">
        <div class="panel-head">
            <h2>Participantes registrados</h2>
            <p>Consulta, filtra y administra los registros guardados.</p>
        </div>

        <form class="filters" method="GET">
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Buscar nombre, teléfono o correo">
            <input type="text" name="numero" value="<?= e($numeroFiltro) ?>" placeholder="Número">
            <select name="estado">
                <option value="">Todos los estados</option>
                <?php foreach (['reservado','vendido','bloqueado','disponible'] as $op): ?>
                    <option value="<?= $op ?>" <?= $estado === $op ? 'selected' : '' ?>><?= ucfirst($op) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn primary" type="submit">Filtrar</button>
            <a href="dashboard.php" class="btn ghost">Limpiar</a>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participantes as $p): ?>
                        <tr>
                            <td data-label="Nombre"><?= e($p['nombre']) ?></td>
                            <td data-label="Correo"><?= e($p['correo']) ?></td>
                            <td data-label="Teléfono"><?= e($p['telefono']) ?></td>
                            <td data-label="Número"><strong><?= e($p['numero_seleccionado']) ?></strong></td>
                            <td data-label="Fecha"><?= e($p['fecha_registro']) ?></td>
                            <td data-label="Estado"><span class="pill <?= e($p['estado']) ?>"><?= e($p['estado']) ?></span></td>
                            <td class="row-actions" data-label="Acciones">
                                <form class="form-eliminar"
                                      action="eliminar_participante.php"
                                      method="POST"
                                      onsubmit="return confirm('¿Seguro que deseas eliminar este registro y liberar el número?');">
                                    <input type="hidden" name="id" value="<?= e((string)$p['id']) ?>">
                                    <button class="delete btn-eliminar" type="submit">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$participantes): ?>
                        <tr><td colspan="8">No hay registros con esos filtros.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
