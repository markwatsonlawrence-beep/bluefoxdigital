<?php
// ==============================
// CONFIGURACIÓN GENERAL
// ==============================

// Cambia estos datos por los datos de tu hosting/cPanel.
define('DB_HOST', 'localhost');
define('DB_NAME', 'cocodigi_sorteo_db');
define('DB_USER', 'cocodigi_sorteo-user');
define('DB_PASS', 'Liga2026');
define('DB_CHARSET', 'utf8mb4');

// Datos fáciles de editar.
define('NOMBRE_SORTEO', 'Sorteo Especial de Gratitud!!');
define('WHATSAPP_NUMBER', '50670687045'); // Ejemplo Costa Rica: 50688887777

// Usuario administrador.
define('ADMIN_USER', 'mercadomarisa82');
define('ADMIN_PASS_HASH', '$2y$12$puVwuoLb9kI3/sccj9twmOxqnYRJT/yUBqSjzj3MNYQzqS5jdxknW');

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    return $pdo;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function require_admin(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['admin_logged'])) {
        header('Location: login.php');
        exit;
    }
}
?>
