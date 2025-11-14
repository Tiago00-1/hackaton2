<?php

/**
 * AuthController - Controlador de Autenticação
 * Sistema de Gerenciamento de TI e Manutenção - SENAI Alagoinhas
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/auth.php';
require_once __DIR__ . '/../models/User.php';

class AuthController
{

    /**
     * Processa login de administrador
     */
    public static function loginAdmin()
    {
        $response = ['success' => false, 'message' => '', 'redirect' => ''];

        try {
            // Verificar se já está logado
            if (isLoggedIn() && isAdmin()) {
                $response['success'] = true;
                $response['redirect'] = '/senai-manutencao/views/admin/dashboard.php';
                return $response;
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $response['message'] = 'Método inválido';
                return $response;
            }

            // Validar CSRF
            if (!validateCSRF()) {
                $response['message'] = 'Token de segurança inválido';
                return $response;
            }

            $matricula = sanitize($_POST['matricula'] ?? '');
            $senha = $_POST['senha'] ?? '';

            // Validar campos
            if (empty($matricula) || empty($senha)) {
                $response['message'] = 'Matrícula e senha são obrigatórios';
                return $response;
            }

            // Autenticar
            $user = Auth::authenticateAdmin($matricula, $senha);

            if ($user) {
                Auth::login($user);
                $response['success'] = true;
                $response['message'] = 'Login realizado com sucesso!';
                $response['redirect'] = '/senai-manutencao/views/admin/dashboard.php';

                // Log da ação
                error_log("Login admin realizado: {$user['nome']} ({$user['matricula']})");
            } else {
                $response['message'] = 'Credenciais inválidas';

                // Log da tentativa inválida
                error_log("Tentativa de login admin inválida: $matricula");
            }
        } catch (Exception $e) {
            error_log("Erro no login admin: " . $e->getMessage());
            $response['message'] = 'Erro interno do servidor';
        }

        return $response;
    }

    /**
     * Processa login de solicitante
     */
    public static function loginSolicitante()
    {
        $response = ['success' => false, 'message' => '', 'redirect' => ''];

        try {
            // Verificar se já está logado
            if (isLoggedIn() && !isAdmin()) {
                $response['success'] = true;
                $response['redirect'] = '/senai-manutencao/views/solicitante/minhas_solicitacoes.php';
                return $response;
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $response['message'] = 'Método inválido';
                return $response;
            }

            // Validar CSRF
            if (!validateCSRF()) {
                $response['message'] = 'Token de segurança inválido';
                return $response;
            }

            $matricula = sanitize($_POST['matricula'] ?? '');
            $nome = sanitize($_POST['nome'] ?? '');

            // Validar campos
            if (empty($matricula) || empty($nome)) {
                $response['message'] = 'Matrícula e nome são obrigatórios';
                return $response;
            }

            if (strlen($nome) < 3) {
                $response['message'] = 'Nome deve ter pelo menos 3 caracteres';
                return $response;
            }

            // Autenticar
            $user = Auth::authenticateSolicitante($matricula, $nome);

            if ($user) {
                Auth::login($user);
                $response['success'] = true;
                $response['message'] = 'Acesso realizado com sucesso!';
                $response['redirect'] = '/senai-manutencao/views/solicitante/minhas_solicitacoes.php';

                // Log da ação
                error_log("Login solicitante: {$user['nome']} ({$user['matricula']})");
            } else {
                $response['message'] = 'Dados não conferem ou erro na criação do usuário';

                // Log da tentativa
                error_log("Tentativa de login solicitante falhada: $matricula - $nome");
            }
        } catch (Exception $e) {
            error_log("Erro no login solicitante: " . $e->getMessage());
            $response['message'] = 'Erro interno do servidor';
        }

        return $response;
    }

    /**
     * Processa logout
     */
    public static function logout()
    {
        try {
            $userName = $_SESSION['user_name'] ?? 'Desconhecido';

            Auth::logout();

            // Log da ação
            error_log("Logout realizado: $userName");

            // Redirecionar para página inicial
            header("Location: /senai-manutencao/?logout=success");
            exit;
        } catch (Exception $e) {
            error_log("Erro no logout: " . $e->getMessage());
            header("Location: /senai-manutencao/?error=logout_failed");
            exit;
        }
    }

    /**
     * Verifica status da sessão (AJAX)
     */
    public static function checkSession()
    {
        header('Content-Type: application/json');

        $response = [
            'logged_in' => isLoggedIn(),
            'user_type' => null,
            'user_name' => null,
            'session_time_left' => 0
        ];

        if (isLoggedIn()) {
            $user = getCurrentUser();
            $response['user_type'] = $user['tipo'];
            $response['user_name'] = $user['nome'];

            // Calcular tempo restante da sessão (2 horas)
            $loginTime = $_SESSION['login_time'] ?? time();
            $sessionTimeout = 7200; // 2 horas em segundos
            $timeElapsed = time() - $loginTime;
            $response['session_time_left'] = max(0, $sessionTimeout - $timeElapsed);
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Renovar sessão (AJAX)
     */
    public static function renewSession()
    {
        header('Content-Type: application/json');

        $response = ['success' => false, 'message' => ''];

        if (isLoggedIn()) {
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();

            $response['success'] = true;
            $response['message'] = 'Sessão renovada com sucesso';

            error_log("Sessão renovada: " . $_SESSION['user_name']);
        } else {
            $response['message'] = 'Usuário não está logado';
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Atualizar perfil do usuário
     */
    public static function updateProfile()
    {
        $response = ['success' => false, 'message' => ''];

        try {
            // Verificar se está logado
            if (!isLoggedIn()) {
                $response['message'] = 'Acesso não autorizado';
                return $response;
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $response['message'] = 'Método inválido';
                return $response;
            }

            // Validar CSRF
            if (!validateCSRF()) {
                $response['message'] = 'Token de segurança inválido';
                return $response;
            }

            $currentUser = getCurrentUser();
            $user = User::find($currentUser['id']);

            if (!$user) {
                $response['message'] = 'Usuário não encontrado';
                return $response;
            }

            // Atualizar dados permitidos
            $nome = sanitize($_POST['nome'] ?? '');
            $cargo = sanitize($_POST['cargo'] ?? '');

            if (!empty($nome)) {
                $user->setNome($nome);
            }

            if (!empty($cargo)) {
                $user->setCargo($cargo);
            }

            // Validar dados
            $errors = $user->validate();
            if (!empty($errors)) {
                $response['message'] = implode(', ', $errors);
                return $response;
            }

            // Salvar alterações
            if ($user->save()) {
                // Atualizar sessão
                $_SESSION['user_name'] = $user->getNome();
                $_SESSION['user_cargo'] = $user->getCargo();

                $response['success'] = true;
                $response['message'] = 'Perfil atualizado com sucesso!';

                error_log("Perfil atualizado: " . $user->getNome());
            } else {
                $response['message'] = 'Erro ao atualizar perfil';
            }
        } catch (Exception $e) {
            error_log("Erro ao atualizar perfil: " . $e->getMessage());
            $response['message'] = 'Erro interno do servidor';
        }

        return $response;
    }

    /**
     * Alterar senha (apenas para admins)
     */
    public static function changePassword()
    {
        $response = ['success' => false, 'message' => ''];

        try {
            // Verificar se é admin
            if (!isAdmin()) {
                $response['message'] = 'Acesso não autorizado';
                return $response;
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $response['message'] = 'Método inválido';
                return $response;
            }

            // Validar CSRF
            if (!validateCSRF()) {
                $response['message'] = 'Token de segurança inválido';
                return $response;
            }

            $senhaAtual = $_POST['senha_atual'] ?? '';
            $novaSenha = $_POST['nova_senha'] ?? '';
            $confirmarSenha = $_POST['confirmar_senha'] ?? '';

            // Validar campos
            if (empty($senhaAtual) || empty($novaSenha) || empty($confirmarSenha)) {
                $response['message'] = 'Todos os campos são obrigatórios';
                return $response;
            }

            if ($novaSenha !== $confirmarSenha) {
                $response['message'] = 'Nova senha e confirmação não conferem';
                return $response;
            }

            if (strlen($novaSenha) < 4) {
                $response['message'] = 'Nova senha deve ter pelo menos 4 caracteres';
                return $response;
            }

            $currentUser = getCurrentUser();
            $user = User::find($currentUser['id']);

            if (!$user) {
                $response['message'] = 'Usuário não encontrado';
                return $response;
            }

            // Verificar senha atual
            $userAuth = Auth::authenticateAdmin($user->getMatricula(), $senhaAtual);
            if (!$userAuth) {
                $response['message'] = 'Senha atual incorreta';
                return $response;
            }

            // Atualizar senha
            if ($user->updatePassword($novaSenha)) {
                $response['success'] = true;
                $response['message'] = 'Senha alterada com sucesso!';

                error_log("Senha alterada: " . $user->getNome());
            } else {
                $response['message'] = 'Erro ao alterar senha';
            }
        } catch (Exception $e) {
            error_log("Erro ao alterar senha: " . $e->getMessage());
            $response['message'] = 'Erro interno do servidor';
        }

        return $response;
    }

    /**
     * Processar requisições AJAX
     */
    public static function handleAjax()
    {
        $action = $_GET['action'] ?? '';
        $response = [];

        switch ($action) {
            case 'login_admin':
                $response = self::loginAdmin();
                break;

            case 'login_solicitante':
                $response = self::loginSolicitante();
                break;

            case 'logout':
                self::logout(); // Já faz redirect e exit
                break;

            case 'check_session':
                self::checkSession(); // Já faz echo e exit
                break;

            case 'renew_session':
                self::renewSession(); // Já faz echo e exit
                break;

            case 'update_profile':
                $response = self::updateProfile();
                break;

            case 'change_password':
                $response = self::changePassword();
                break;

            default:
                $response = ['success' => false, 'message' => 'Ação inválida'];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// Processar requisições se este arquivo foi chamado diretamente
if (isset($_GET['action'])) {
    AuthController::handleAjax();
}
