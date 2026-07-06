<?php
define('BASE_URL', '/farmavida/');
define('ROOT_PATH', __DIR__);
define('UPLOAD_PATH', __DIR__ . '/uploads/');

class Config {
    const DEBUG   = true;
    const DB_HOST = '127.0.0.1';
    const DB_PORT = '3306';
    const DB_NAME = 'farmavida';
    const DB_USER = 'root';
    const DB_PASS = '';

    const PRODUTOS_POR_PAGINA     = 20;
    const DIAS_ALERTA_VALIDADE    = 30;
    const SENHA_SUPERVISOR_MESTRA = 'farmacia_VS';
    const ALLOWED_EXTENSIONS      = ['pdf', 'jpg', 'jpeg', 'png'];

    private static ?PDO $_pdo = null;

    public static function initApp(): void {
        if (self::DEBUG) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
        }
        ini_set('session.gc_maxlifetime', 7200);
        session_set_cookie_params([
            'lifetime' => 7200, 'path' => '/', 'secure' => false,
            'httponly' => true, 'samesite' => 'Lax'
        ]);
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public static function getDbConnection(): PDO {
        if (self::$_pdo !== null) return self::$_pdo;
        try {
            $dsn = "mysql:host=" . self::DB_HOST . ";port=" . self::DB_PORT
                 . ";dbname=" . self::DB_NAME . ";charset=utf8mb4";
            self::$_pdo = new PDO($dsn, self::DB_USER, self::DB_PASS);
            self::$_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$_pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return self::$_pdo;
        } catch (PDOException $e) {
            die("<br><strong>[ERRO DB]</strong> Falha na conexão: " . $e->getMessage());
        }
    }
}
Config::initApp();
