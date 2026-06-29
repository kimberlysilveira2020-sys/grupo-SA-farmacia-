<?php
/**
 * Arquivo de Configuração Central (PHP)
 */

class Config {
    const DEBUG = true;

    // Configurações do Banco de Dados (MySQL)
    const DB_HOST = '127.0.0.1';
    const DB_PORT = '3306';
    const DB_NAME = 'farmavida';
    const DB_USER = 'root';
    const DB_PASS = ''; 

    const PRODUTOS_POR_PAGINA = 20;
    const DIAS_ALERTA_VALIDADE = 30;
    
    // Senha mestra do supervisor
    const SENHA_SUPERVISOR_MESTRA = 'farmacia_VS';
    const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];

    // ── Configurações PIX ──────────────────────────────────────────
    // Preencha com os dados da sua conta PIX
    const PIX_CHAVE       = '137.277.219-79'; // Chave PIX: CPF, CNPJ, e-mail, telefone ou chave aleatória
    const PIX_NOME        = 'Caio Eduardo Aguiar';         // Nome do recebedor (máx 25 chars)
    const PIX_CIDADE      = 'JOINVILLE';                      // Cidade do recebedor (máx 15 chars)
    const PIX_IDENTIFICADOR = 'FARMAVIDA';                    // Identificador da loja no QR Code (máx 25 chars)
    // ──────────────────────────────────────────────────────────────

    // ── Configurações PayPal ───────────────────────────────────────
    // Obtenha em: https://developer.paypal.com/dashboard/applications
    const PAYPAL_SANDBOX   = true;                            // true = Sandbox (testes) | false = Produção
    const PAYPAL_CLIENT_ID = 'SEU_PAYPAL_CLIENT_ID_AQUI';    // Client ID do app PayPal
    const PAYPAL_SECRET    = 'SEU_PAYPAL_SECRET_AQUI';        // Secret do app PayPal
    // ──────────────────────────────────────────────────────────────

    public static function initApp() {
        if (self::DEBUG) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
        }
        ini_set('session.gc_maxlifetime', 7200);
        session_set_cookie_params([
            'lifetime' => 7200, 'path' => '/', 'secure' => false, 'httponly' => true, 'samesite' => 'Lax'
        ]);
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
    }

    private static $_pdo = null;

    public static function getDbConnection() {
        if (self::$_pdo !== null) return self::$_pdo;
        try {
            $dsn = "mysql:host=" . self::DB_HOST . ";port=" . self::DB_PORT . ";dbname=" . self::DB_NAME . ";charset=utf8mb4";
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
?>
