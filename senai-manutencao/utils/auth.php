<?php
/**
 * Sistema de Autenticação e Autorização
 * 
 * Este arquivo contém funções para autenticação, autorização e gerenciamento de sessões
 */

require_once __DIR__ . '/../config/db.php';

/**
 * Classe responsável pela autenticação e autorização do sistema
 */
class Auth {
    
    /**
     * Inicia uma sessão de usuário
     * @param array $userData Dados do usuário
     */
    public static function login($userData) {
        $_SESSION['user_id'] = $userData['id_usuario'];
        $_SESSION['user_name'] = $userData['nome'];
        $_SESSION['user_matricula'] = $userData['matricula'];
        $_SESSION['user_cargo'] = $userData['cargo'];
        $_SESSION['user_type'] = $userData['tipo_usuario'];
        $_SESSION['user_setor'] = $userData['setor_id'];
        $_SESSION['login_time'] = time();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // Regenerar ID da sessão por segurança
        session_regenerate_id(true);
    }
    
    /**
     * Finaliza a sessão do usuário
     */
    public static function logout() {
        // Limpar todas as variáveis de sessão
        $_SESSION = array();
        
        // Destruir a sessão
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }
    
    /**
     * Verifica se o usuário está logado
     * @return bool
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Verifica se o usuário é administrador
     * @return bool
     */
    public static function isAdmin() {
        return self::isLoggedIn() && $_SESSION['user_type'] === 'admin';
    }
    
    /**
     * Verifica se o usuário é solicitante
     * @return bool
     */
    public static function isSolicitante() {
        return self::isLoggedIn() && $_SESSION['user_type'] === 'solicitante';
    }
    
    /**
     * Obtém dados do usuário logado
     * @return array|null
     */
    public static function getUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'nome' => $_SESSION['user_name'],
            'matricula' => $_SESSION['user_matricula'],
            'cargo' => $_SESSION['user_cargo'],
            'tipo' => $_SESSION['user_type'],
            'setor' => $_SESSION['user_setor']
        ];
    }
    
    /**
     * Autentica administrador com usuário e senha
     * @param string $matricula
     * @param string $senha
     * @return array|false Dados do usuário ou false
     */
    public static function authenticateAdmin($matricula, $senha) {
        try {
            $sql = "SELECT * FROM usuarios WHERE matricula = ? AND tipo_usuario = 'admin' AND ativo = 1";
            $stmt = Database::execute($sql, [$matricula]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($senha, $user['senha_hash'])) {
                return $user;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Erro na autenticação admin: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Autentica solicitante apenas com matrícula e nome
     * @param string $matricula
     * @param string $nome
     * @return array|false Dados do usuário ou false
     */
    public static function authenticateSolicitante($matricula, $nome) {
        try {
            // Buscar usuário existente
            $sql = "SELECT * FROM usuarios WHERE matricula = ? AND ativo = 1";
            $stmt = Database::execute($sql, [$matricula]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Usuário existe, verificar se o nome confere (flexível)
                if (stripos($user['nome'], $nome) !== false || stripos($nome, $user['nome']) !== false) {
                    return $user;
                }
            } else {
                // Usuário não existe, criar novo solicitante
                return self::createSolicitante($matricula, $nome);
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Erro na autenticação solicitante: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cria um novo usuário solicitante
     * @param string $matricula
     * @param string $nome
     * @return array|false
     */
    private static function createSolicitante($matricula, $nome) {
        try {
            $sql = "INSERT INTO usuarios (nome, matricula, cargo, setor_id, tipo_usuario) 
                    VALUES (?, ?, 'Não informado', 1, 'solicitante')";
            
            Database::execute($sql, [sanitize($nome), sanitize($matricula)]);
            
            // Buscar o usuário recém-criado
            $sql = "SELECT * FROM usuarios WHERE matricula = ?";
            $stmt = Database::execute($sql, [$matricula]);
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("Erro ao criar solicitante: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Gera token CSRF
     * @return string
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Valida token CSRF
     * @param string $token
     * @return bool
     */
    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Middleware para páginas que requerem login
     * @param string $redirectTo URL de redirecionamento se não logado
     */
    public static function requireLogin($redirectTo = '/senai-manutencao/') {
        if (!self::isLoggedIn()) {
            header("Location: $redirectTo");
            exit;
        }
        
        // Verificar timeout da sessão (2 horas)
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 7200) {
            self::logout();
            header("Location: $redirectTo?timeout=1");
            exit;
        }
        
        // Atualizar último acesso
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Middleware para páginas que requerem privilégios de admin
     * @param string $redirectTo URL de redirecionamento se não autorizado
     */
    public static function requireAdmin($redirectTo = '/senai-manutencao/') {
        self::requireLogin($redirectTo);
        
        if (!self::isAdmin()) {
            header("Location: $redirectTo?error=unauthorized");
            exit;
        }
    }
    
    /**
     * Verifica se o usuário pode acessar uma solicitação específica
     * @param int $solicitacaoId
     * @return bool
     */
    public static function canAccessRequest($solicitacaoId) {
        if (self::isAdmin()) {
            return true; // Admin pode acessar todas
        }
        
        if (!self::isLoggedIn()) {
            return false;
        }
        
        try {
            $sql = "SELECT solicitante_id FROM solicitacoes WHERE id_solicitacao = ?";
            $stmt = Database::execute($sql, [$solicitacaoId]);
            $request = $stmt->fetch();
            
            return $request && $request['solicitante_id'] == $_SESSION['user_id'];
        } catch (Exception $e) {
            return false;
        }
    }
}

/**
 * Funções auxiliares globais
 */

/**
 * Verifica se está logado
 * @return bool
 */
function isLoggedIn() {
    return Auth::isLoggedIn();
}

/**
 * Verifica se é admin
 * @return bool
 */
function isAdmin() {
    return Auth::isAdmin();
}

/**
 * Obtém usuário atual
 * @return array|null
 */
function getCurrentUser() {
    return Auth::getUser();
}

/**
 * Gera campo CSRF oculto para formulários
 * @return string
 */
function csrfField() {
    $token = Auth::generateCSRFToken();
    return "<input type='hidden' name='csrf_token' value='$token'>";
}

/**
 * Valida token CSRF do POST
 * @return bool
 */
function validateCSRF() {
    return isset($_POST['csrf_token']) && Auth::validateCSRFToken($_POST['csrf_token']);
}

/**
 * Redireciona com base no tipo de usuário
 */
function redirectByUserType() {
    if (!isLoggedIn()) {
        return;
    }
    
    $baseUrl = '/senai-manutencao';
    
    if (isAdmin()) {
        header("Location: $baseUrl/views/admin/dashboard.php");
    } else {
        header("Location: $baseUrl/views/solicitante/minhas_solicitacoes.php");
    }
    exit;
}

/**
 * Formata data para exibição
 * @param string $date
 * @return string
 */
function formatDate($date) {
    if (empty($date)) return '-';
    $datetime = new DateTime($date);
    return $datetime->format('d/m/Y H:i');
}

/**
 * Formata data simples
 * @param string $date
 * @return string
 */
function formatDateOnly($date) {
    if (empty($date)) return '-';
    $datetime = new DateTime($date);
    return $datetime->format('d/m/Y');
}

/**
 * Obtém classe CSS para status
 * @param string $status
 * @return string
 */
function getStatusClass($status) {
    switch ($status) {
        case 'Aberta': return 'status-aberta';
        case 'Em andamento': return 'status-andamento';
        case 'Concluída': return 'status-concluida';
        default: return '';
    }
}

/**
 * Obtém classe CSS para prioridade
 * @param string $prioridade
 * @return string
 */
function getPrioridadeClass($prioridade) {
    switch ($prioridade) {
        case 'Baixa': return 'prioridade-baixa';
        case 'Média': return 'prioridade-media';
        case 'Urgente': return 'prioridade-urgente';
        default: return '';
    }
}

?>