<?php

/**
 * RequestController - Controlador de Solicitações
 * Sistema de Gerenciamento de TI e Manutenção - SENAI Alagoinhas
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/auth.php';
require_once __DIR__ . '/../models/Request.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Sector.php';
require_once __DIR__ . '/../models/Type.php';

class RequestController
{

    /**
     * Criar nova solicitação
     */
    public static function create()
    {
        $response = ['success' => false, 'message' => '', 'redirect' => ''];

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

            // Coletar dados
            $data = [
                'solicitante_id' => $currentUser['id'],
                'tipo_id' => (int)($_POST['tipo_id'] ?? 0),
                'setor_id' => (int)($_POST['setor_id'] ?? 0),
                'local' => sanitize($_POST['local'] ?? ''),
                'descricao' => sanitize($_POST['descricao'] ?? ''),
                'prioridade' => sanitize($_POST['prioridade'] ?? 'Média'),
                'curso' => sanitize($_POST['curso'] ?? ''),
                'responsavel_id' => (int)($_POST['responsavel_id'] ?? 0),
                'status' => 'Aberta'
            ];

            // Validar campos obrigatórios
            if (
                empty($data['tipo_id']) || empty($data['setor_id']) ||
                empty($data['local']) || empty($data['descricao']) ||
                empty($data['curso']) || empty($data['responsavel_id'])
            ) {
                $response['message'] = 'Todos os campos obrigatórios devem ser preenchidos';
                return $response;
            }

            // Validar se responsável pertence ao setor selecionado
            if (!self::isResponsavelDoSetor($data['responsavel_id'], $data['setor_id'])) {
                $response['message'] = 'Responsável selecionado inválido para o setor informado';
                return $response;
            }

            // Processar upload de imagem
            $imagePath = null;
            $uploadField = null;
            if (isset($_FILES['anexo']) && $_FILES['anexo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadField = 'anexo';
            } elseif (isset($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadField = 'imagem';
            }

            if ($uploadField && $_FILES[$uploadField]['error'] === UPLOAD_ERR_OK) {
                $imagePath = self::handleImageUpload($_FILES[$uploadField]);
                if ($imagePath === false) {
                    $response['message'] = 'Erro no upload da imagem. Verifique o formato e tamanho';
                    return $response;
                }
            }

            $data['caminho_imagem'] = $imagePath;

            // Criar solicitação
            $request = new Request($data);

            // Validar dados
            $errors = $request->validate();
            if (!empty($errors)) {
                $response['message'] = implode(', ', $errors);
                return $response;
            }

            // Salvar
            if ($request->save()) {
                $response['success'] = true;
                $response['message'] = 'Solicitação criada com sucesso!';
                $response['redirect'] = '/senai-manutencao/views/solicitante/minhas_solicitacoes.php';
                $response['request_id'] = $request->getId();
                $response['send_email'] = true; // Flag para enviar email via JavaScript

                // Log da ação
                error_log("Solicitação criada: #{$request->getId()} por {$currentUser['nome']}");
            } else {
                $response['message'] = 'Erro ao criar solicitação';
            }
        } catch (Exception $e) {
            error_log("Erro ao criar solicitação: " . $e->getMessage());
            $response['message'] = 'Erro interno do servidor';
        }

        return $response;
    }

    /**
     * Listar solicitações do usuário logado
     */
    public static function getUserRequests()
    {
        try {
            // Verificar se está logado
            if (!isLoggedIn()) {
                throw new Exception('Acesso não autorizado');
            }

            $currentUser = getCurrentUser();
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = 10;
            $offset = ($page - 1) * $limit;

            // Filtros
            $filters = [
                'status' => sanitize($_GET['status'] ?? ''),
                'prioridade' => sanitize($_GET['prioridade'] ?? ''),
                'tipo_id' => (int)($_GET['tipo_id'] ?? 0),
                'data_inicio' => sanitize($_GET['data_inicio'] ?? ''),
                'data_fim' => sanitize($_GET['data_fim'] ?? '')
            ];

            // Remover filtros vazios
            $filters = array_filter($filters);

            if (isAdmin()) {
                // Admin vê todas as solicitações
                $requests = Request::getAll($filters, $limit, $offset);
                $total = Request::count($filters);
            } else {
                // Usuário vê apenas suas solicitações
                $filters['solicitante_id'] = $currentUser['id'];
                $requests = Request::getAll($filters, $limit, $offset);
                $total = Request::count($filters);
            }

            $totalPages = ceil($total / $limit);

            return [
                'success' => true,
                'requests' => $requests,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_records' => $total,
                    'per_page' => $limit
                ],
                'filters' => $filters
            ];
        } catch (Exception $e) {
            error_log("Erro ao listar solicitações: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar solicitações',
                'requests' => [],
                'pagination' => []
            ];
        }
    }

    /**
     * Obter detalhes de uma solicitação
     */
    public static function getDetails($id)
    {
        try {
            // Verificar se está logado
            if (!isLoggedIn()) {
                throw new Exception('Acesso não autorizado');
            }

            $request = Request::findWithDetails($id);

            if (!$request) {
                throw new Exception('Solicitação não encontrada');
            }

            // Verificar permissões
            if (!Auth::canAccessRequest($id)) {
                throw new Exception('Acesso não autorizado a esta solicitação');
            }

            // Obter histórico de movimentações
            $movements = Request::getMovements($id);

            return [
                'success' => true,
                'request' => $request,
                'movements' => $movements
            ];
        } catch (Exception $e) {
            error_log("Erro ao obter detalhes da solicitação: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Atualizar status de uma solicitação (apenas admin)
     */
    public static function updateStatus()
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

            $requestId = (int)($_POST['request_id'] ?? 0);
            $newStatus = sanitize($_POST['status'] ?? '');
            $comentario = sanitize($_POST['comentario'] ?? '');

            // Validar campos
            if (empty($requestId) || empty($newStatus)) {
                $response['message'] = 'ID da solicitação e status são obrigatórios';
                return $response;
            }

            $validStatuses = ['Aberta', 'Em andamento', 'Concluída'];
            if (!in_array($newStatus, $validStatuses)) {
                $response['message'] = 'Status inválido';
                return $response;
            }

            // Buscar solicitação
            $request = Request::find($requestId);
            if (!$request) {
                $response['message'] = 'Solicitação não encontrada';
                return $response;
            }

            $oldStatus = $request->getStatus();

            // Validar transição de status
            if (!self::isValidStatusTransition($oldStatus, $newStatus)) {
                $response['message'] = 'Transição de status inválida';
                return $response;
            }

            $currentUser = getCurrentUser();

            // Atualizar status
            if ($request->updateStatus($newStatus, $comentario, $currentUser['id'])) {
                $response['success'] = true;
                $response['message'] = 'Status atualizado com sucesso!';
                $response['send_email'] = true; // Flag para enviar email via JavaScript
                $response['old_status'] = $oldStatus;
                $response['new_status'] = $newStatus;

                error_log("Status atualizado: Solicitação #{$requestId} de '{$oldStatus}' para '{$newStatus}' por {$currentUser['nome']}");
            } else {
                $response['message'] = 'Erro ao atualizar status';
            }
        } catch (Exception $e) {
            error_log("Erro ao atualizar status: " . $e->getMessage());
            $response['message'] = 'Erro interno do servidor';
        }

        return $response;
    }

    /**
     * Adicionar comentário a uma solicitação (apenas admin)
     */
    public static function addComment()
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

            $requestId = (int)($_POST['request_id'] ?? 0);
            $comentario = sanitize($_POST['comentario'] ?? '');

            // Validar campos
            if (empty($requestId) || empty($comentario)) {
                $response['message'] = 'ID da solicitação e comentário são obrigatórios';
                return $response;
            }

            // Buscar solicitação
            $request = Request::find($requestId);
            if (!$request) {
                $response['message'] = 'Solicitação não encontrada';
                return $response;
            }

            $currentUser = getCurrentUser();

            // Adicionar comentário
            if ($request->addComment($comentario, $currentUser['id'])) {
                $response['success'] = true;
                $response['message'] = 'Comentário adicionado com sucesso!';

                error_log("Comentário adicionado à solicitação #{$requestId} por {$currentUser['nome']}");
            } else {
                $response['message'] = 'Erro ao adicionar comentário';
            }
        } catch (Exception $e) {
            error_log("Erro ao adicionar comentário: " . $e->getMessage());
            $response['message'] = 'Erro interno do servidor';
        }

        return $response;
    }

    /**
     * Excluir solicitação (apenas o próprio solicitante e apenas se status = Aberta)
     */
    public static function delete()
    {
        $response = ['success' => false, 'message' => ''];

        try {
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

            $requestId = (int)($_POST['request_id'] ?? 0);

            if (empty($requestId)) {
                $response['message'] = 'ID da solicitação é obrigatório';
                return $response;
            }

            // Buscar solicitação
            $request = Request::find($requestId);
            if (!$request) {
                $response['message'] = 'Solicitação não encontrada';
                return $response;
            }

            $currentUser = getCurrentUser();

            // Verificar permissões
            if (!isAdmin() && $request->getSolicitanteId() != $currentUser['id']) {
                $response['message'] = 'Você não tem permissão para excluir esta solicitação';
                return $response;
            }

            // Verificar se pode excluir (apenas se status = Aberta)
            if ($request->getStatus() !== 'Aberta') {
                $response['message'] = 'Não é possível excluir solicitações que já estão em andamento ou concluídas';
                return $response;
            }

            // Excluir
            if ($request->delete()) {
                $response['success'] = true;
                $response['message'] = 'Solicitação excluída com sucesso!';

                error_log("Solicitação #{$requestId} excluída por {$currentUser['nome']}");
            } else {
                $response['message'] = 'Erro ao excluir solicitação';
            }
        } catch (Exception $e) {
            error_log("Erro ao excluir solicitação: " . $e->getMessage());
            $response['message'] = 'Erro interno do servidor';
        }

        return $response;
    }

    /**
     * Verifica se o responsável pertence ao setor informado
     */
    private static function isResponsavelDoSetor($responsavelId, $setorId)
    {
        if (empty($responsavelId) || empty($setorId)) {
            return false;
        }

        $responsavel = User::find($responsavelId);

        if (!$responsavel || $responsavel->getTipoUsuario() !== 'admin') {
            return false;
        }

        $setorResponsavel = $responsavel->getSetorId();

        // Se o responsável não tiver setor definido, consideramos válido (responsável geral)
        if (empty($setorResponsavel)) {
            return true;
        }

        return (int)$setorResponsavel === (int)$setorId;
    }

    /**
     * Processar upload de imagem
     */
    private static function handleImageUpload($file)
    {
        try {
            // Verificar se há erro no upload
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return false;
            }

            // Verificar tamanho (2MB máximo)
            if ($file['size'] > MAX_FILE_SIZE) {
                return false;
            }

            // Verificar tipo MIME
            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!in_array($file['type'], $allowedMimes)) {
                return false;
            }

            // Verificar extensão
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ALLOWED_EXTENSIONS)) {
                return false;
            }

            // Gerar nome único
            $fileName = 'solicitacao_' . time() . '_' . uniqid() . '.' . $ext;
            $uploadPath = UPLOAD_PATH . $fileName;

            // Criar diretório se não existir
            if (!is_dir(UPLOAD_PATH)) {
                mkdir(UPLOAD_PATH, 0755, true);
            }

            // Mover arquivo
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                return $fileName; // Retorna apenas o nome do arquivo
            }

            return false;
        } catch (Exception $e) {
            error_log("Erro no upload de imagem: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validar transição de status
     */
    private static function isValidStatusTransition($oldStatus, $newStatus)
    {
        $validTransitions = [
            'Aberta' => ['Em andamento', 'Concluída'],
            'Em andamento' => ['Concluída', 'Aberta'], // Permite voltar para aberta
            'Concluída' => [] // Não permite sair de concluída
        ];

        return $oldStatus === $newStatus ||
            (isset($validTransitions[$oldStatus]) &&
                in_array($newStatus, $validTransitions[$oldStatus]));
    }

    /**
     * Obter dados para formulário de nova solicitação
     */
    public static function getFormData()
    {
        try {
            $sectors = Sector::getActive();
            $types = Type::getActive();

            return [
                'success' => true,
                'sectors' => $sectors,
                'types' => $types
            ];
        } catch (Exception $e) {
            error_log("Erro ao obter dados do formulário: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar dados do formulário'
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
            case 'create':
                $response = self::create();
                break;

            case 'list':
                $response = self::getUserRequests();
                break;

            case 'details':
                $id = (int)($_GET['id'] ?? 0);
                $response = self::getDetails($id);
                break;

            case 'update_status':
                $response = self::updateStatus();
                break;

            case 'add_comment':
                $response = self::addComment();
                break;

            case 'delete':
                $response = self::delete();
                break;
            case 'add_comment':
                $response = self::addComment();
                break;

            case 'get_movements':
                $id = (int)($_GET['id'] ?? 0);
                if ($id > 0) {
                    try {
                        $sql = "
                            SELECT m.*, 
                                   u.nome as usuario_nome,
                                   u.matricula as usuario_matricula,
                                   u.tipo_usuario as usuario_tipo,
                                   m.comentario,
                                   m.status_antigo,
                                   m.status_novo,
                                   m.data_movimentacao as data_movimento
                            FROM movimentacoes m
                            JOIN usuarios u ON m.usuario_id = u.id_usuario
                            WHERE m.solicitacao_id = ?
                            ORDER BY m.data_movimentacao ASC
                        ";
                        $stmt = Database::execute($sql, [$id]);
                        $movements = $stmt->fetchAll();
                        $response = ['success' => true, 'movements' => $movements];
                    } catch (Exception $e) {
                        error_log("Erro ao buscar movimentações: " . $e->getMessage());
                        $response = ['success' => false, 'message' => 'Erro ao buscar mensagens'];
                    }
                } else {
                    $response = ['success' => false, 'message' => 'ID inválido'];
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
    RequestController::handleAjax();
}
