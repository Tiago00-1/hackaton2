<?php

/**
 * AdminController - Controlador Administrativo
 * Sistema de Gerenciamento de TI e Manutenção - SENAI Alagoinhas
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/auth.php';
require_once __DIR__ . '/../models/Request.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Sector.php';
require_once __DIR__ . '/../models/Type.php';

class AdminController
{

    /**
     * Obter dados do dashboard
     */
    public static function getDashboardData()
    {
        try {
            // Verificar se é admin
            if (!isAdmin()) {
                throw new Exception('Acesso não autorizado');
            }

            // Estatísticas gerais
            $stats = self::getGeneralStats();

            // Solicitações recentes
            $recentRequests = Request::getRecent(10);

            // Estatísticas por setor
            $sectorStats = self::getSectorStats();

            // Estatísticas por tipo
            $typeStats = self::getTypeStats();

            // Gráfico de tendências (últimos 30 dias)
            $trendData = self::getTrendData();

            // Gráfico de tendências mensais (últimos 6 meses)
            $trendDataMonthly = self::getTrendDataMonthly();

            return [
                'success' => true,
                'stats' => $stats,
                'recent_requests' => $recentRequests,
                'sector_stats' => $sectorStats,
                'type_stats' => $typeStats,
                'trend_data' => $trendData,
                'trend_data_monthly' => $trendDataMonthly
            ];
        } catch (Exception $e) {
            error_log("Erro ao obter dados do dashboard: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar dados do dashboard'
            ];
        }
    }

    /**
     * Obter estatísticas gerais
     */
    private static function getGeneralStats()
    {
        $sql = "
            SELECT 
                COUNT(*) as total_solicitacoes,
                COUNT(CASE WHEN status = 'Aberta' THEN 1 END) as abertas,
                COUNT(CASE WHEN status = 'Em andamento' THEN 1 END) as em_andamento,
                COUNT(CASE WHEN status = 'Concluída' THEN 1 END) as concluidas,
                COUNT(CASE WHEN prioridade = 'Urgente' THEN 1 END) as urgentes,
                COUNT(CASE WHEN prioridade = 'Média' THEN 1 END) as medias,
                COUNT(CASE WHEN prioridade = 'Baixa' THEN 1 END) as baixas,
                COUNT(CASE WHEN DATE(data_abertura) = CURDATE() THEN 1 END) as hoje,
                COUNT(CASE WHEN DATE(data_abertura) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as esta_semana,
                COUNT(CASE WHEN DATE(data_abertura) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as este_mes
            FROM solicitacoes
        ";

        $stmt = Database::execute($sql);
        return $stmt->fetch();
    }

    /**
     * Obter estatísticas por setor
     */
    private static function getSectorStats()
    {
        $sql = "
            SELECT 
                s.nome_setor,
                COUNT(sol.id_solicitacao) as total,
                COUNT(CASE WHEN sol.status = 'Aberta' THEN 1 END) as abertas,
                COUNT(CASE WHEN sol.status = 'Em andamento' THEN 1 END) as em_andamento,
                COUNT(CASE WHEN sol.status = 'Concluída' THEN 1 END) as concluidas,
                AVG(CASE 
                    WHEN sol.data_conclusao IS NOT NULL 
                    THEN TIMESTAMPDIFF(HOUR, sol.data_abertura, sol.data_conclusao) 
                    ELSE NULL 
                END) as tempo_medio_resolucao
            FROM setores s
            LEFT JOIN solicitacoes sol ON s.id_setor = sol.setor_id
            WHERE s.ativo = 1
            GROUP BY s.id_setor, s.nome_setor
            ORDER BY total DESC
        ";

        $stmt = Database::execute($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obter estatísticas por tipo
     */
    private static function getTypeStats()
    {
        $sql = "
            SELECT 
                t.nome_tipo,
                COUNT(sol.id_solicitacao) as total,
                COUNT(CASE WHEN sol.status = 'Concluída' THEN 1 END) as concluidas,
                ROUND((COUNT(CASE WHEN sol.status = 'Concluída' THEN 1 END) * 100.0 / NULLIF(COUNT(sol.id_solicitacao), 0)), 1) as taxa_conclusao
            FROM tipos_solicitacao t
            LEFT JOIN solicitacoes sol ON t.id_tipo = sol.tipo_id
            GROUP BY t.id_tipo, t.nome_tipo
            HAVING total > 0
            ORDER BY total DESC
            LIMIT 10
        ";

        $stmt = Database::execute($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obter dados de tendência (últimos 30 dias)
     */
    private static function getTrendData()
    {
        $sql = "
            SELECT 
                DATE(data_abertura) as data,
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'Concluída' THEN 1 END) as concluidas
            FROM solicitacoes
            WHERE data_abertura >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY DATE(data_abertura)
            ORDER BY data
        ";

        $stmt = Database::execute($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obter dados de tendência mensal (últimos 6 meses)
     */
    private static function getTrendDataMonthly()
    {
        $sql = "
            SELECT 
                DATE_FORMAT(data_abertura, '%Y-%m') as mes,
                DATE_FORMAT(data_abertura, '%b/%Y') as mes_formatado,
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'Concluída' THEN 1 END) as concluidas,
                COUNT(CASE WHEN status = 'Aberta' THEN 1 END) as abertas,
                COUNT(CASE WHEN status = 'Em andamento' THEN 1 END) as em_andamento
            FROM solicitacoes
            WHERE data_abertura >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(data_abertura, '%Y-%m'), mes_formatado
            ORDER BY mes
        ";

        $stmt = Database::execute($sql);
        return $stmt->fetchAll();
    }

    /**
     * Listar todos os usuários (apenas admin)
     */
    public static function getUsers()
    {
        try {
            // Verificar se é admin
            if (!isAdmin()) {
                throw new Exception('Acesso não autorizado');
            }

            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = 15;
            $offset = ($page - 1) * $limit;

            // Filtros
            $filters = [
                'tipo_usuario' => sanitize($_GET['tipo_usuario'] ?? ''),
                'ativo' => $_GET['ativo'] ?? '',
                'setor_id' => (int)($_GET['setor_id'] ?? 0),
                'search' => sanitize($_GET['search'] ?? '')
            ];

            // Remover filtros vazios
            $filters = array_filter($filters, function ($value) {
                return $value !== '' && $value !== 0;
            });

            $users = User::getAll($filters, $limit, $offset);
            $total = User::count($filters);
            $totalPages = ceil($total / $limit);

            return [
                'success' => true,
                'users' => $users,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_records' => $total,
                    'per_page' => $limit
                ],
                'filters' => $filters
            ];
        } catch (Exception $e) {
            error_log("Erro ao listar usuários: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar usuários'
            ];
        }
    }

    /**
     * Criar novo usuário administrador
     */
    public static function createUser()
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

            $data = [
                'nome' => sanitize($_POST['nome'] ?? ''),
                'matricula' => sanitize($_POST['matricula'] ?? ''),
                'cargo' => sanitize($_POST['cargo'] ?? ''),
                'setor_id' => (int)($_POST['setor_id'] ?? 0),
                'tipo_usuario' => sanitize($_POST['tipo_usuario'] ?? 'solicitante'),
                'senha' => $_POST['senha'] ?? ''
            ];

            // Validar campos obrigatórios
            if (empty($data['nome']) || empty($data['matricula'])) {
                $response['message'] = 'Nome e matrícula são obrigatórios';
                return $response;
            }

            // Se for admin, senha é obrigatória
            if ($data['tipo_usuario'] === 'admin' && empty($data['senha'])) {
                $response['message'] = 'Senha é obrigatória para administradores';
                return $response;
            }

            // Verificar se matrícula já existe
            if (User::existsByMatricula($data['matricula'])) {
                $response['message'] = 'Já existe um usuário com esta matrícula';
                return $response;
            }

            // Criar usuário
            $user = new User($data);

            // Validar dados
            $errors = $user->validate();
            if (!empty($errors)) {
                $response['message'] = implode(', ', $errors);
                return $response;
            }

            // Salvar
            if ($user->save()) {
                $response['success'] = true;
                $response['message'] = 'Usuário criado com sucesso!';

                $currentUser = getCurrentUser();
                error_log("Usuário criado: {$data['nome']} ({$data['matricula']}) por {$currentUser['nome']}");
            } else {
                $response['message'] = 'Erro ao criar usuário';
            }
        } catch (Exception $e) {
            error_log("Erro ao criar usuário: " . $e->getMessage());
            $response['message'] = 'Erro interno do servidor';
        }

        return $response;
    }

    /**
     * Atualizar usuário
     */
    public static function updateUser()
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

            $userId = (int)($_POST['user_id'] ?? 0);
            $data = [
                'nome' => sanitize($_POST['nome'] ?? ''),
                'cargo' => sanitize($_POST['cargo'] ?? ''),
                'setor_id' => (int)($_POST['setor_id'] ?? 0),
                'ativo' => (bool)($_POST['ativo'] ?? true)
            ];

            // Validar campos
            if (empty($userId) || empty($data['nome'])) {
                $response['message'] = 'ID do usuário e nome são obrigatórios';
                return $response;
            }

            // Buscar usuário
            $user = User::find($userId);
            if (!$user) {
                $response['message'] = 'Usuário não encontrado';
                return $response;
            }

            // Atualizar dados
            $user->setNome($data['nome']);
            $user->setCargo($data['cargo']);
            $user->setSetorId($data['setor_id']);
            $user->setAtivo($data['ativo']);

            // Validar dados
            $errors = $user->validate();
            if (!empty($errors)) {
                $response['message'] = implode(', ', $errors);
                return $response;
            }

            // Salvar alterações
            if ($user->save()) {
                $response['success'] = true;
                $response['message'] = 'Usuário atualizado com sucesso!';

                $currentUser = getCurrentUser();
                error_log("Usuário atualizado: {$user->getNome()} por {$currentUser['nome']}");
            } else {
                $response['message'] = 'Erro ao atualizar usuário';
            }
        } catch (Exception $e) {
            error_log("Erro ao atualizar usuário: " . $e->getMessage());
            $response['message'] = 'Erro interno do servidor';
        }

        return $response;
    }

    /**
     * Desativar/ativar usuário
     */
    public static function toggleUserStatus()
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

            $userId = (int)($_POST['user_id'] ?? 0);

            if (empty($userId)) {
                $response['message'] = 'ID do usuário é obrigatório';
                return $response;
            }

            // Não permitir desativar a si mesmo
            $currentUser = getCurrentUser();
            if ($userId == $currentUser['id']) {
                $response['message'] = 'Você não pode desativar sua própria conta';
                return $response;
            }

            // Buscar usuário
            $user = User::find($userId);
            if (!$user) {
                $response['message'] = 'Usuário não encontrado';
                return $response;
            }

            // Alternar status
            $newStatus = !$user->isAtivo();
            $user->setAtivo($newStatus);

            if ($user->save()) {
                $response['success'] = true;
                $response['message'] = $newStatus ? 'Usuário ativado com sucesso!' : 'Usuário desativado com sucesso!';
                $response['new_status'] = $newStatus;

                error_log("Status do usuário alterado: {$user->getNome()} - " . ($newStatus ? 'Ativo' : 'Inativo') . " por {$currentUser['nome']}");
            } else {
                $response['message'] = 'Erro ao alterar status do usuário';
            }
        } catch (Exception $e) {
            error_log("Erro ao alterar status do usuário: " . $e->getMessage());
            $response['message'] = 'Erro interno do servidor';
        }

        return $response;
    }

    /**
     * Obter configurações do sistema
     */
    public static function getSystemSettings()
    {
        try {
            // Verificar se é admin
            if (!isAdmin()) {
                throw new Exception('Acesso não autorizado');
            }

            // Obter setores ativos
            $sectors = Sector::getActive();

            // Obter tipos de solicitação ativos
            $types = Type::getActive();

            // Estatísticas do sistema
            $systemStats = [
                'total_users' => User::count(),
                'total_requests' => Request::count(),
                'active_sectors' => count($sectors),
                'active_types' => count($types)
            ];

            return [
                'success' => true,
                'sectors' => $sectors,
                'types' => $types,
                'system_stats' => $systemStats
            ];
        } catch (Exception $e) {
            error_log("Erro ao obter configurações do sistema: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar configurações'
            ];
        }
    }

    /**
     * Processar requisições AJAX
     */
    public static function handleAjax()
    {
        $action = $_GET['action'] ?? '';
        $response = [];

        switch ($action) {
            case 'dashboard':
                $response = self::getDashboardData();
                break;

            case 'users':
                $response = self::getUsers();
                break;

            case 'create_user':
                $response = self::createUser();
                break;

            case 'update_user':
                $response = self::updateUser();
                break;

            case 'toggle_user_status':
                $response = self::toggleUserStatus();
                break;

            case 'system_settings':
                $response = self::getSystemSettings();
                break;

            case 'user_logs':
                $userId = (int)($_GET['user_id'] ?? 0);
                if ($userId > 0) {
                    try {
                        require_once __DIR__ . '/../models/Log.php';
                        $sql = "SELECT * FROM logs WHERE usuario_id = ? ORDER BY data_hora DESC LIMIT 50";
                        $stmt = Database::execute($sql, [$userId]);
                        $logs = $stmt->fetchAll();
                        $response = ['success' => true, 'logs' => $logs];
                    } catch (Exception $e) {
                        error_log("Erro ao buscar logs: " . $e->getMessage());
                        $response = ['success' => false, 'message' => 'Erro ao buscar logs'];
                    }
                } else {
                    $response = ['success' => false, 'message' => 'ID de usuário inválido'];
                }
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
    AdminController::handleAjax();
}
