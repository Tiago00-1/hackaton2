<?php
/**
 * Configuração de Conexão com o Banco de Dados
 * Sistema de Gerenciamento de TI e Manutenção - SENAI Alagoinhas
 * 
 * Este arquivo contém as configurações de conexão com o banco MySQL
 * e fornece uma classe singleton para gerenciar as conexões
 */

class Database {
    // Configurações de conexão - AJUSTAR CONFORME SEU AMBIENTE
    private static $host = 'localhost';
    private static $port = '3306';      // Porta MySQL padrão do XAMPP
    private static $dbname = 'senai_manutencao';
    private static $username = 'root';  // Usuário padrão do XAMPP
    private static $password = '';      // Senha padrão do XAMPP (vazia)
    private static $charset = 'utf8mb4';
    
    // Instância singleton da conexão
    private static $pdo = null;
    
    /**
     * Método para obter a conexão PDO (Singleton)
     * @return PDO Instância da conexão
     * @throws Exception Se houver erro na conexão
     */
    public static function getConnection() {
        if (self::$pdo === null) {
            try {
                $dsn = "mysql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$dbname . ";charset=" . self::$charset;
                
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ];
                
                self::$pdo = new PDO($dsn, self::$username, self::$password, $options);
                
                // Log de conexão bem-sucedida (apenas para desenvolvimento)
                if (isset($_SESSION['debug_mode'])) {
                    error_log("Conexão com banco estabelecida com sucesso");
                }
                
            } catch (PDOException $e) {
                // Log do erro (não expor detalhes em produção)
                error_log("Erro de conexão com banco: " . $e->getMessage());
                throw new Exception("Erro de conexão com o banco de dados. Verifique as configurações.");
            }
        }
        
        return self::$pdo;
    }
    
    /**
     * Testa a conexão com o banco
     * @return bool True se conexão bem-sucedida
     */
    public static function testConnection() {
        try {
            $pdo = self::getConnection();
            $pdo->query("SELECT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Executa uma query preparada de forma segura
     * @param string $sql Query SQL com placeholders
     * @param array $params Parâmetros para bind
     * @return PDOStatement Resultado da execução
     */
    public static function execute($sql, $params = []) {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Erro na execução da query: " . $e->getMessage());
            throw new Exception("Erro na execução da operação no banco de dados.");
        }
    }
    
    /**
     * Inicia uma transação
     */
    public static function beginTransaction() {
        self::getConnection()->beginTransaction();
    }
    
    /**
     * Confirma uma transação
     */
    public static function commit() {
        self::getConnection()->commit();
    }
    
    /**
     * Desfaz uma transação
     */
    public static function rollback() {
        self::getConnection()->rollback();
    }
    
    /**
     * Sanitiza dados de entrada
     * @param mixed $data Dados a serem sanitizados
     * @return mixed Dados sanitizados
     */
    public static function sanitize($data) {
        if (is_string($data)) {
            return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }
    
    /**
     * Método para fechar a conexão (opcional)
     */
    public static function closeConnection() {
        self::$pdo = null;
    }
}

/**
 * Função auxiliar global para obter conexão
 * @return PDO
 */
function getDB() {
    return Database::getConnection();
}

/**
 * Função auxiliar para sanitizar dados
 * @param mixed $data
 * @return mixed
 */
function sanitize($data) {
    return Database::sanitize($data);
}

// Configurações de exibição de erros (apenas para desenvolvimento)
if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // Em produção, não exibir erros
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Configuração de timezone
date_default_timezone_set('America/Bahia');

// Configuração de sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Configurações globais do sistema
 */
define('SYSTEM_NAME', 'Sistema de Gerenciamento SENAI');
define('MAX_FILE_SIZE', 5242880); // 5MB em bytes
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');

// Criar diretório de uploads se não existir
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

?>