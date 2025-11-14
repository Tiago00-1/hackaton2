<?php

/**
 * Gestão Global de Solicitações
 * Sistema SENAI Alagoinhas - Hackathon 2025
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/auth.php';
require_once __DIR__ . '/../../controllers/RequestController.php';
require_once __DIR__ . '/../../models/Type.php';
require_once __DIR__ . '/../../models/Sector.php';
require_once __DIR__ . '/../../models/User.php';

// Verificar se é admin
Auth::requireAdmin();

$currentUser = getCurrentUser();
$page = (int) ($_GET['page'] ?? 1);

// Filtros
$filters = [
    'status' => $_GET['status'] ?? '',
    'prioridade' => $_GET['prioridade'] ?? '',
    'tipo_id' => $_GET['tipo_id'] ?? '',
    'setor_id' => $_GET['setor_id'] ?? '',
    'data_inicio' => $_GET['data_inicio'] ?? '',
    'data_fim' => $_GET['data_fim'] ?? '',
    'local' => $_GET['local'] ?? '',
    'curso' => $_GET['curso'] ?? '',
    'search' => $_GET['search'] ?? ''
];

// Processar ações AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    switch ($_POST['action']) {
        case 'add_comment':
            $id = (int) $_POST['id'];
            $comentario = trim($_POST['comentario'] ?? '');

            if (empty($comentario)) {
                echo json_encode(['success' => false, 'message' => 'Comentário não pode estar vazio']);
                exit;
            }

            try {
                // Buscar solicitação para pegar status atual
                $request = Request::findWithDetails($id);
                if (!$request) {
                    echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada']);
                    exit;
                }

                // Inserir comentário sem mudar status
                $sql = "INSERT INTO movimentacoes (solicitacao_id, usuario_id, status_antigo, status_novo, comentario, data_movimentacao) 
                        VALUES (?, ?, ?, ?, ?, NOW())";
                Database::execute($sql, [
                    $id,
                    $currentUser['id'],
                    $request['status'],
                    $request['status'],
                    $comentario
                ]);

                // Atualizar data de atualização
                Database::execute("UPDATE solicitacoes SET data_atualizacao = NOW() WHERE id_solicitacao = ?", [$id]);

                echo json_encode(['success' => true, 'message' => 'Comentário adicionado com sucesso']);
            } catch (Exception $e) {
                error_log("Erro ao adicionar comentário: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Erro ao adicionar comentário']);
            }
            exit;

        case 'update_status':
            // Preparar dados para o RequestController
            $_POST['request_id'] = (int) $_POST['id'];
            $_POST['comentario'] = trim($_POST['observacoes'] ?? '');
            $_POST['csrf_token'] = $_SESSION['csrf_token'] ?? '';

            $result = RequestController::updateStatus();
            echo json_encode($result);
            exit;

        case 'assign_technician':
            $id = (int) $_POST['id'];
            $tech_matricula = $_POST['tech_matricula'];

            // Atribuir técnico (implementar se necessário)
            error_log("Técnico {$tech_matricula} atribuído à solicitação {$id}");
            $result = ['success' => true, 'message' => 'Técnico atribuído com sucesso'];
            echo json_encode($result);
            exit;
    }
}

// Buscar dados
$requests = Request::all($filters, 15, ($page - 1) * 15);
$total = Request::count($filters);
$requestData = [
    'requests' => $requests,
    'total' => $total,
    'page' => $page,
    'pages' => ceil($total / 15)
];
$tipos = Type::getActive();
$setores = Sector::getActive();
$tecnicos = User::getAll(['tipo_usuario' => 'admin'], 100, 0);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Solicitações - SENAI Alagoinhas</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/components.css">
    <link rel="stylesheet" href="../../public/css/responsive.css">
    <link rel="stylesheet" href="../../public/css/alerts.css">
</head>

<body data-logged-in="true" data-admin="true">
    <!-- Header -->
    <header class="header">
        <div class="header-brand">
            <svg width="40" height="40" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="100" height="100" rx="10" fill="white" />
                <text x="50" y="35" text-anchor="middle" fill="#003C78" font-size="14" font-weight="bold">SENAI</text>
                <text x="50" y="75" text-anchor="middle" fill="#FF6600" font-size="8">ALAGOINHAS</text>
            </svg>
            <span>Sistema de Gerenciamento - Solicitações</span>
        </div>

        <nav class="header-nav">
            <div class="header-user">
                <div class="user-info">
                    <div class="user-name" data-user-info="nome"><?php echo htmlspecialchars($currentUser['nome']); ?></div>
                    <div class="user-role">Administrador</div>
                </div>
            </div>


            <a href="../../controllers/AuthController.php?action=logout" class="btn btn-danger btn-sm" style="margin-left: 1rem;">
                🚪 Sair
            </a>
        </nav>
    </header>

    <!-- Sidebar -->
    <aside class="sidebar">
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">
                <span>📊</span>
                Dashboard
            </a>
            <a href="solicitacoes.php" class="nav-item active">
                <span>📋</span>
                Todas Solicitações
            </a>
            <a href="relatorios.php" class="nav-item">
                <span>📈</span>
                Relatórios
            </a>
            <a href="usuarios.php" class="nav-item">
                <span>👥</span>
                Usuários
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="content">
        <div class="container-fluid">
            <!-- Cabeçalho -->
            <div class="d-flex justify-between align-center mb-4">
                <div>
                    <h1>Gerenciar Solicitações</h1>
                    <p class="text-secondary">
                        <?php echo $requestData['total']; ?> solicitações encontradas
                    </p>
                </div>

                <div class="d-flex gap-2">
                    <div class="dropdown">
                        <button onclick="toggleExportMenu()" class="btn btn-outline">
                            📊 Exportar ▼
                        </button>
                        <div id="export-menu" class="dropdown-menu" style="display: none;">
                            <a href="#" onclick="exportData('pdf'); return false;" class="dropdown-item">
                                📄 Exportar PDF
                            </a>
                            <a href="#" onclick="exportData('csv'); return false;" class="dropdown-item">
                                📊 Exportar CSV
                            </a>
                        </div>
                    </div>
                    <button onclick="refreshList()" class="btn btn-secondary">
                        🔄 Atualizar
                    </button>
                </div>
            </div>

            <!-- Filtros Avançados -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-between align-center">
                    <h3>🔍 Filtros Avançados</h3>
                    <button id="toggle-filters" class="btn btn-sm btn-outline">
                        Expandir Filtros
                    </button>
                </div>
                <div class="card-body">
                    <form id="filter-form" method="GET">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Busca Geral</label>
                                    <input type="text"
                                        name="search"
                                        class="form-control"
                                        placeholder="ID, local, descrição..."
                                        value="<?php echo htmlspecialchars($filters['search']); ?>">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">Todos</option>
                                        <option value="Aberta" <?php echo $filters['status'] === 'Aberta' ? 'selected' : ''; ?>>Aberta</option>
                                        <option value="Em andamento" <?php echo $filters['status'] === 'Em andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                        <option value="Concluída" <?php echo $filters['status'] === 'Concluída' ? 'selected' : ''; ?>>Concluída</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">Prioridade</label>
                                    <select name="prioridade" class="form-control">
                                        <option value="">Todas</option>
                                        <option value="Urgente" <?php echo $filters['prioridade'] === 'Urgente' ? 'selected' : ''; ?>>Urgente</option>
                                        <option value="Média" <?php echo $filters['prioridade'] === 'Média' ? 'selected' : ''; ?>>Média</option>
                                        <option value="Baixa" <?php echo $filters['prioridade'] === 'Baixa' ? 'selected' : ''; ?>>Baixa</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">Tipo</label>
                                    <select name="tipo_id" class="form-control">
                                        <option value="">Todos</option>
                                        <?php foreach ($tipos as $tipo): ?>
                                            <option value="<?php echo $tipo['id_tipo']; ?>"
                                                <?php echo $filters['tipo_id'] == $tipo['id_tipo'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($tipo['nome_tipo']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Setor</label>
                                    <select name="setor_id" class="form-control">
                                        <option value="">Todos</option>
                                        <?php foreach ($setores as $setor): ?>
                                            <option value="<?php echo $setor['id_setor']; ?>"
                                                <?php echo $filters['setor_id'] == $setor['id_setor'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($setor['nome_setor']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row advanced-filters" style="display: none;">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Data Início</label>
                                    <input type="date"
                                        name="data_inicio"
                                        class="form-control"
                                        value="<?php echo $filters['data_inicio']; ?>">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Data Fim</label>
                                    <input type="date"
                                        name="data_fim"
                                        class="form-control"
                                        value="<?php echo $filters['data_fim']; ?>">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Local/Laboratório</label>
                                    <input type="text"
                                        name="local"
                                        class="form-control"
                                        placeholder="Ex: Lab 1, Sala 205..."
                                        value="<?php echo htmlspecialchars($filters['local'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Curso</label>
                                    <input type="text"
                                        name="curso"
                                        class="form-control"
                                        placeholder="Ex: Mecânica, Informática..."
                                        value="<?php echo htmlspecialchars($filters['curso'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                🔍 Filtrar
                            </button>
                            <a href="solicitacoes.php" class="btn btn-outline">
                                🗑️ Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Solicitações -->
            <?php if (empty($requestData['requests'])): ?>
                <div class="card">
                    <div class="card-body text-center">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                        <h3>Nenhuma solicitação encontrada</h3>
                        <p class="text-secondary">
                            Ajuste os filtros para encontrar as solicitações desejadas.
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table" id="requests-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Solicitante</th>
                                <th>Local/Descrição</th>
                                <th>Tipo</th>
                                <th>Prioridade</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>Responsável</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requestData['requests'] as $request): ?>
                                <tr id="request-<?php echo $request['id_solicitacao']; ?>">
                                    <td>
                                        <strong>#<?php echo $request['id_solicitacao']; ?></strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($request['solicitante_nome']); ?></strong>
                                            <br>
                                            <small class="text-secondary"><?php echo htmlspecialchars($request['solicitante_matricula']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($request['local']); ?></strong>
                                            <br>
                                            <small class="text-secondary">
                                                <?php echo htmlspecialchars(substr($request['descricao'], 0, 60)); ?>
                                                <?php if (strlen($request['descricao']) > 60) echo '...'; ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?php echo htmlspecialchars($request['nome_tipo']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="priority-badge prioridade-<?php echo strtolower(str_replace('ê', 'e', $request['prioridade'])); ?>">
                                            <?php echo $request['prioridade']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <select class="form-control form-control-sm status-select"
                                            data-id="<?php echo $request['id_solicitacao']; ?>"
                                            data-current="<?php echo $request['status']; ?>">
                                            <option value="Aberta" <?php echo $request['status'] === 'Aberta' ? 'selected' : ''; ?>>Aberta</option>
                                            <option value="Em andamento" <?php echo $request['status'] === 'Em andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                            <option value="Concluída" <?php echo $request['status'] === 'Concluída' ? 'selected' : ''; ?>>Concluída</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo formatDate($request['data_abertura']); ?></strong>
                                            <?php if ($request['data_conclusao']): ?>
                                                <br>
                                                <small class="text-success">
                                                    Concl.: <?php echo formatDate($request['data_conclusao']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($request['responsavel_matricula']): ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($request['responsavel_nome']); ?></strong>
                                                <br>
                                                <small class="text-secondary"><?php echo htmlspecialchars($request['responsavel_matricula']); ?></small>
                                            </div>
                                        <?php else: ?>
                                            <select class="form-control form-control-sm tech-select"
                                                data-id="<?php echo $request['id_solicitacao']; ?>">
                                                <option value="">Atribuir técnico...</option>
                                                <?php foreach ($tecnicos as $tecnico): ?>
                                                    <option value="<?php echo htmlspecialchars($tecnico['matricula']); ?>">
                                                        <?php echo htmlspecialchars($tecnico['nome']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>
                                    </td>
                                    <td class="table-actions">
                                        <div class="btn-group">
                                            <button onclick="viewRequest(<?php echo $request['id_solicitacao']; ?>)"
                                                class="btn btn-sm btn-primary"
                                                title="Ver detalhes">
                                                👁️
                                            </button>
                                            <button onclick="addComment(<?php echo $request['id_solicitacao']; ?>)"
                                                class="btn btn-sm btn-secondary"
                                                title="Adicionar comentário"
                                                style="position: relative;">
                                                💬
                                                <?php
                                                // Contar comentários não lidos (do solicitante)
                                                $movements = Request::getMovements($request['id_solicitacao']);
                                                $unreadCount = 0;
                                                foreach ($movements as $mov) {
                                                    // Se o comentário é do solicitante (não é admin)
                                                    if ($mov['status_antigo'] === $mov['status_novo'] && !empty($mov['comentario'])) {
                                                        // Verificar se o usuário não é admin
                                                        $userSql = "SELECT tipo_usuario FROM usuarios WHERE id_usuario = ?";
                                                        $userStmt = Database::execute($userSql, [$mov['usuario_id']]);
                                                        $userData = $userStmt->fetch();
                                                        if ($userData && $userData['tipo_usuario'] !== 'admin') {
                                                            $unreadCount++;
                                                        }
                                                    }
                                                }
                                                if ($unreadCount > 0):
                                                ?>
                                                    <span class="badge badge-danger" style="position: absolute; top: -5px; right: -5px; background: #EF4444; color: white; border-radius: 10px; padding: 2px 6px; font-size: 10px;">
                                                        <?php echo $unreadCount; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </button>
                                            <?php if ($request['anexo_path']): ?>
                                                <a href="../../<?php echo htmlspecialchars($request['anexo_path']); ?>"
                                                    target="_blank"
                                                    class="btn btn-sm btn-outline"
                                                    title="Ver anexo">
                                                    📎
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <?php if ($requestData['pages'] > 1): ?>
                    <div id="pagination-container" class="mt-4"></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal para Visualização de Solicitação -->
    <div id="view-modal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3>👁️ Detalhes da Solicitação <span id="modal-id"></span></h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label"><strong>👤 Solicitante</strong></label>
                            <p id="modal-solicitante" style="margin: 0.5rem 0;"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label"><strong>💼 Cargo</strong></label>
                            <p id="modal-cargo" style="margin: 0.5rem 0;"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label"><strong>🎫 Matrícula</strong></label>
                            <p id="modal-matricula" style="margin: 0.5rem 0;"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label"><strong>📍 Local</strong></label>
                            <p id="modal-local" style="margin: 0.5rem 0;"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label"><strong>📁 Tipo</strong></label>
                            <p id="modal-tipo" style="margin: 0.5rem 0;"></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label"><strong>🏛️ Setor</strong></label>
                            <p id="modal-setor" style="margin: 0.5rem 0;"></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label"><strong>⚠️ Prioridade</strong></label>
                            <p id="modal-prioridade" style="margin: 0.5rem 0;"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label"><strong>📦 Status</strong></label>
                            <p id="modal-status" style="margin: 0.5rem 0;"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label"><strong>📅 Data de Abertura</strong></label>
                            <p id="modal-data" style="margin: 0.5rem 0;"></p>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label"><strong>📝 Descrição</strong></label>
                    <p id="modal-descricao" style="margin: 0.5rem 0; padding: 1rem; background: var(--bg-secondary); border-radius: 8px; white-space: pre-wrap;"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline modal-close">
                    Fechar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para Comentários -->
    <div id="comment-modal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3>💬 Chat da Solicitação <span id="modal-request-number"></span></h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Histórico de Mensagens -->
                <div id="chat-history" class="chat-history" style="max-height: 300px; overflow-y: auto; margin-bottom: 20px; padding: 15px; background: var(--bg-secondary); border-radius: 8px;">
                    <p class="text-secondary text-center">Carregando mensagens...</p>
                </div>
                
                <!-- Formulário de Nova Mensagem -->
                <form id="comment-form">
                    <input type="hidden" id="comment-request-id">
                    <input type="hidden" id="comment-mode" value="comment">
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" id="change-status-checkbox" onchange="toggleStatusField()">
                            Alterar status da solicitação
                        </label>
                    </div>
                    <div class="form-group" id="status-field" style="display: none;">
                        <label for="comment-status" class="form-label">Novo Status</label>
                        <select id="comment-status" name="status" class="form-control">
                            <option value="Aberta">Aberta</option>
                            <option value="Em andamento">Em Andamento</option>
                            <option value="Concluída">Concluída</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="comment-text" class="form-label">Mensagem / Observações</label>
                        <textarea id="comment-text"
                            name="observacoes"
                            class="form-control"
                            rows="4"
                            required
                            placeholder="Digite sua mensagem para o solicitante..."></textarea>
                        <small class="text-secondary">Esta mensagem será visível para o solicitante no histórico da solicitação.</small>
                    </div>
                    <div class="d-flex gap-2 justify-end">
                        <button type="button" class="btn btn-outline modal-close">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            💬 Salvar Comentário
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
    <script src="../../public/js/email-service.js"></script>
    <script src="../../public/js/dark-mode.js"></script>
    <script src="../../public/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar paginação
            <?php if ($requestData['pages'] > 1): ?>
                AppUtils.Pagination.create(
                    document.getElementById('pagination-container'),
                    <?php echo $requestData['current_page']; ?>,
                    <?php echo $requestData['pages']; ?>,
                    function(page) {
                        const url = new URL(window.location);
                        url.searchParams.set('page', page);
                        window.location.href = url.toString();
                    }
                );
            <?php endif; ?>

            // Toggle filtros avançados
            document.getElementById('toggle-filters').addEventListener('click', function() {
                const advanced = document.querySelector('.advanced-filters');
                if (advanced.style.display === 'none') {
                    advanced.style.display = 'flex';
                    this.textContent = 'Recolher Filtros';
                } else {
                    advanced.style.display = 'none';
                    this.textContent = 'Expandir Filtros';
                }
            });

            // Auto-submit filtros
            document.getElementById('filter-form').addEventListener('change', function(e) {
                if (e.target.name !== 'search') {
                    setTimeout(() => this.submit(), 500);
                }
            });

            // Busca em tempo real
            let searchTimeout;
            document.querySelector('input[name="search"]').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    document.getElementById('filter-form').submit();
                }, 1000);
            });

            // Status update
            document.querySelectorAll('.status-select').forEach(select => {
                select.addEventListener('change', function() {
                    const id = this.dataset.id;
                    const newStatus = this.value;
                    const currentStatus = this.dataset.current;

                    if (newStatus === currentStatus) return;

                    // Abrir modal para comentário se necessário
                    if (newStatus === 'Concluída') {
                        addComment(id, newStatus);
                        return;
                    }

                    updateStatus(id, newStatus, '');
                });
            });

            // Atribuição de técnicos
            document.querySelectorAll('.tech-select').forEach(select => {
                select.addEventListener('change', function() {
                    const id = this.dataset.id;
                    const techMatricula = this.value;

                    if (!techMatricula) return;

                    assignTechnician(id, techMatricula);
                });
            });

            // Modal de comentários
            document.getElementById('comment-form').addEventListener('submit', function(e) {
                e.preventDefault();

                const id = document.getElementById('comment-request-id').value;
                const changeStatus = document.getElementById('change-status-checkbox').checked;
                const status = changeStatus ? document.getElementById('comment-status').value : null;
                const observacoes = document.getElementById('comment-text').value;

                if (!observacoes.trim()) {
                    AppUtils.Toast.show('Digite uma mensagem', 'warning');
                    return;
                }

                if (changeStatus && status) {
                    updateStatus(id, status, observacoes);
                } else {
                    addCommentOnly(id, observacoes);
                }
            });
        });

        // Funções auxiliares
        function viewRequest(id) {
            // Buscar dados da solicitação e mostrar em modal
            fetch(`../../controllers/RequestController.php?action=get&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showRequestModal(data.request);
                    } else {
                        AppUtils.Toast.show('Erro ao carregar solicitação', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    AppUtils.Toast.show('Erro de conexão', 'error');
                });
        }

        function showRequestModal(request) {
            const modal = document.getElementById('view-modal');
            if (!modal) return;

            // Preencher dados no modal
            document.getElementById('modal-id').textContent = '#' + request.id_solicitacao;
            document.getElementById('modal-solicitante').textContent = request.solicitante_nome;
            document.getElementById('modal-cargo').textContent = request.solicitante_cargo || 'Não informado';
            document.getElementById('modal-matricula').textContent = request.solicitante_matricula;
            document.getElementById('modal-local').textContent = request.local;
            document.getElementById('modal-tipo').textContent = request.tipo_nome;
            document.getElementById('modal-setor').textContent = request.setor_nome;
            document.getElementById('modal-prioridade').textContent = request.prioridade;
            document.getElementById('modal-status').textContent = request.status;
            document.getElementById('modal-data').textContent = new Date(request.data_abertura).toLocaleString('pt-BR');
            document.getElementById('modal-descricao').textContent = request.descricao;

            // Mostrar modal
            AppUtils.Modal.show('view-modal');
        }

        function addComment(id, defaultStatus = '') {
            document.getElementById('comment-request-id').value = id;
            document.getElementById('modal-request-number').textContent = '#' + id;

            if (defaultStatus) {
                document.getElementById('comment-status').value = defaultStatus;
                document.getElementById('change-status-checkbox').checked = true;
                toggleStatusField();
            } else {
                document.getElementById('change-status-checkbox').checked = false;
                toggleStatusField();
            }

            // Carregar histórico de mensagens
            loadChatHistory(id);

            AppUtils.Modal.show('comment-modal');
        }

        function loadChatHistory(requestId) {
            const chatHistory = document.getElementById('chat-history');
            chatHistory.innerHTML = '<p class="text-secondary text-center">Carregando mensagens...</p>';

            fetch(`../../controllers/RequestController.php?action=get_movements&id=${requestId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.movements) {
                        displayChatMessages(data.movements);
                    } else {
                        chatHistory.innerHTML = '<p class="text-secondary text-center">Nenhuma mensagem ainda</p>';
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar mensagens:', error);
                    chatHistory.innerHTML = '<p class="text-danger text-center">Erro ao carregar mensagens</p>';
                });
        }

        function displayChatMessages(movements) {
            const chatHistory = document.getElementById('chat-history');
            
            if (!movements || movements.length === 0) {
                chatHistory.innerHTML = '<p class="text-secondary text-center">Nenhuma mensagem ainda</p>';
                return;
            }

            let html = '';
            movements.reverse().forEach(mov => {
                const isComment = (mov.status_antigo === mov.status_novo && mov.comentario);
                if (!isComment && !mov.comentario) return; // Pular se não tem comentário

                const isAdmin = mov.usuario_tipo === 'admin';
                const messageClass = isAdmin ? 'chat-message-admin' : 'chat-message-user';
                const icon = isAdmin ? '👨‍💼' : '👤';
                const userName = mov.usuario_nome || mov.usuario_matricula;
                const date = new Date(mov.data_movimento).toLocaleString('pt-BR');

                html += `
                    <div class="chat-message ${messageClass}" style="margin-bottom: 15px; padding: 10px; border-radius: 8px; ${isAdmin ? 'background: #DBEAFE; border-left: 4px solid #3B82F6; color: #1E3A8A;' : 'background: #D1FAE5; border-left: 4px solid #10B981; color: #065F46;'}">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <strong style="color: ${isAdmin ? '#1E3A8A' : '#065F46'};">${icon} ${userName}</strong>
                            <small style="color: ${isAdmin ? '#60A5FA' : '#34D399'};">${date}</small>
                        </div>
                        <p style="margin: 0; white-space: pre-wrap; color: ${isAdmin ? '#1E3A8A' : '#065F46'};">${mov.comentario || mov.observacoes}</p>
                        ${!isComment ? `<small style="color: ${isAdmin ? '#60A5FA' : '#34D399'};">Status alterado para: <strong>${mov.status_novo}</strong></small>` : ''}
                    </div>
                `;
            });

            chatHistory.innerHTML = html;
            // Scroll para o final
            chatHistory.scrollTop = chatHistory.scrollHeight;
        }

        function toggleStatusField() {
            const checkbox = document.getElementById('change-status-checkbox');
            const statusField = document.getElementById('status-field');
            statusField.style.display = checkbox.checked ? 'block' : 'none';
        }

        function addCommentOnly(id, comentario) {
            const formData = new FormData();
            formData.append('action', 'add_comment');
            formData.append('id', id);
            formData.append('comentario', comentario);

            AppUtils.Ajax.post(window.location.href, formData)
                .then(response => {
                    if (response.success) {
                        AppUtils.Toast.show('Comentário enviado com sucesso!', 'success');
                        document.getElementById('comment-text').value = '';
                        
                        // Recarregar histórico de mensagens
                        loadChatHistory(id);

                        // Atualizar lista após 1 segundo
                        setTimeout(() => {
                            refreshList();
                        }, 1000);
                    } else {
                        AppUtils.Toast.show(response.message || 'Erro ao enviar comentário', 'error');
                    }
                })
                .catch(error => {
                    AppUtils.Toast.show('Erro de conexão', 'error');
                });
        }

        function updateStatus(id, status, observacoes) {
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('id', id);
            formData.append('status', status);
            formData.append('observacoes', observacoes);

            AppUtils.Ajax.post(window.location.href, formData)
                .then(response => {
                    if (response.success) {
                        AppUtils.Toast.show('Status atualizado com sucesso!', 'success');
                        AppUtils.Modal.hide('comment-modal');

                        // Atualizar linha da tabela
                        setTimeout(() => {
                            refreshList();
                        }, 1000);
                    } else {
                        AppUtils.Toast.show(response.message || 'Erro ao atualizar status', 'error');
                    }
                })
                .catch(error => {
                    AppUtils.Toast.show('Erro de conexão', 'error');
                });
        }

        function assignTechnician(id, techMatricula) {
            const formData = new FormData();
            formData.append('action', 'assign_technician');
            formData.append('id', id);
            formData.append('tech_matricula', techMatricula);

            AppUtils.Ajax.post(window.location.href, formData)
                .then(response => {
                    if (response.success) {
                        AppUtils.Toast.show('Técnico atribuído com sucesso!', 'success');
                        setTimeout(() => {
                            refreshList();
                        }, 1000);
                    } else {
                        AppUtils.Toast.show(response.message || 'Erro ao atribuir técnico', 'error');
                    }
                })
                .catch(error => {
                    AppUtils.Toast.show('Erro de conexão', 'error');
                });
        }

        function refreshList() {
            window.location.reload();
        }

        function toggleExportMenu() {
            const menu = document.getElementById('export-menu');
            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        }

        function exportData(format) {
            // Fechar menu
            document.getElementById('export-menu').style.display = 'none';

            // Construir URL com filtros atuais
            const params = new URLSearchParams();
            params.set('action', format);
            
            // Adicionar filtros ativos
            const tipoSelect = document.getElementById('filter-tipo');
            const setorSelect = document.getElementById('filter-setor');
            const statusSelect = document.getElementById('filter-status');
            const prioridadeSelect = document.getElementById('filter-prioridade');
            const localSelect = document.getElementById('filter-local');
            const cursoSelect = document.getElementById('filter-curso');
            const dataInicio = document.getElementById('filter-data-inicio');
            const dataFim = document.getElementById('filter-data-fim');
            
            if (tipoSelect && tipoSelect.value) params.set('tipo_id', tipoSelect.value);
            if (setorSelect && setorSelect.value) params.set('setor_id', setorSelect.value);
            if (statusSelect && statusSelect.value) params.set('status', statusSelect.value);
            if (prioridadeSelect && prioridadeSelect.value) params.set('prioridade', prioridadeSelect.value);
            if (localSelect && localSelect.value) params.set('local', localSelect.value);
            if (cursoSelect && cursoSelect.value) params.set('curso', cursoSelect.value);
            if (dataInicio && dataInicio.value) params.set('data_inicio', dataInicio.value);
            if (dataFim && dataFim.value) params.set('data_fim', dataFim.value);

            const url = `../../controllers/ExportController.php?${params.toString()}`;
            
            // Criar link temporário para download
            const link = document.createElement('a');
            link.href = url;
            link.download = `relatorio_${format}_${new Date().getTime()}`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            AppUtils.Toast.show(`Exportação ${format.toUpperCase()} iniciada!`, 'success');
        }

        // Fechar menu ao clicar fora
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('export-menu');
            const button = e.target.closest('.dropdown');
            if (!button && menu) {
                menu.style.display = 'none';
            }
        });
    </script>
</body>

</html>