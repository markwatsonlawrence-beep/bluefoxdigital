<?php

require_once 'config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

/* Si tu config.php usa db(), obtenemos la conexión */
if (!isset($pdo)) {
    $pdo = db();
}

try {
    $pdo->beginTransaction();

    $pdo->exec("DELETE FROM participantes");

    $pdo->exec("
        UPDATE numeros
        SET estado = 'disponible',
            participante_id = NULL
    ");

    $pdo->commit();

    header('Location: dashboard.php?reiniciado=1');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die('Error: ' . $e->getMessage());
}