<?php

/**
 * Listagem de Solicitações do Usuário
 * Sistema SENAI Alagoinhas
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/auth.php';
require_once __DIR__ . '/../../models/Request.php';
require_once __DIR__ . '/../../models/Type.php';
require_once __DIR__ . '/../../models/Sector.php';

// Verificar se está logado
Auth::requireLogin();

$currentUser = getCurrentUser();

// Paginação
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$page = (int) ($_GET['page'] ?? 1);
$filters = [
    'status' => $_GET['status'] ?? '',
    'prioridade' => $_GET['prioridade'] ?? '',
    'tipo_id' => $_GET['tipo_id'] ?? ''
];

// Buscar dados
$filters['solicitante_id'] = $currentUser['id'];
$requests = Request::all($filters, $limit, ($page - 1) * $limit);
$total = Request::count($filters);
$requestData = [
    'requests' => $requests,
    'total' => $total,
    'page' => $page,
    'pages' => ceil($total / $limit)
];
$tipos = Type::getActive();
$setores = Sector::getActive();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Solicitações - SENAI Alagoinhas</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/components.css">
    <link rel="stylesheet" href="../../public/css/responsive.css">
    <link rel="stylesheet" href="../../public/css/alerts.css">
</head>

<body data-logged-in="true">
    <!-- Header -->
    <header class="header">
        <div class="header-brand">
            <svg width="40" height="40" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="100" height="100" rx="10" fill="white" />
                <text x="50" y="35" text-anchor="middle" fill="#003C78" font-size="14" font-weight="bold">SENAI</text>
                <text x="50" y="75" text-anchor="middle" fill="#FF6600" font-size="8">ALAGOINHAS</text>
            </svg>
            <span>Sistema de Gerenciamento</span>
        </div>

        <nav class="header-nav">
            <div class="header-user">
                <div class="user-info">
                    <div class="user-name" data-user-info="nome"><?php echo htmlspecialchars($currentUser['nome']); ?></div>
                    <div class="user-role">Solicitante - <?php echo htmlspecialchars($currentUser['matricula']); ?></div>
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
            <a href="minhas_solicitacoes.php" class="nav-item active">
                <span>📋</span>
                Minhas Solicitações
            </a>
            <a href="criar.php" class="nav-item">
                <span>➕</span>
                Nova Solicitação
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="content">
        <div class="container-fluid">
            <!-- Cabeçalho da Página -->
            <div class="d-flex justify-between align-center mb-4">
                <div>
                    <h1>Minhas Solicitações</h1>
                    <p class="text-secondary">Acompanhe suas solicitações de TI e manutenção</p>
                </div>

                <a href="criar.php" class="btn btn-primary">
                    ➕ Nova Solicitação
                </a>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Filtros</h3>
                </div>
                <div class="card-body">
                    <form id="filter-form" method="GET" class="d-flex gap-2 flex-wrap">
                        <div class="form-group">
                            <select name="status" class="form-control">
                                <option value="">Todos os Status</option>
                                <option value="Aberta" <?php echo $filters['status'] === 'Aberta' ? 'selected' : ''; ?>>Aberta</option>
                                <option value="Em andamento" <?php echo $filters['status'] === 'Em andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                <option value="Concluída" <?php echo $filters['status'] === 'Concluída' ? 'selected' : ''; ?>>Concluída</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <select name="prioridade" class="form-control">
                                <option value="">Todas Prioridades</option>
                                <option value="Baixa" <?php echo $filters['prioridade'] === 'Baixa' ? 'selected' : ''; ?>>Baixa</option>
                                <option value="Média" <?php echo $filters['prioridade'] === 'Média' ? 'selected' : ''; ?>>Média</option>
                                <option value="Urgente" <?php echo $filters['prioridade'] === 'Urgente' ? 'selected' : ''; ?>>Urgente</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <select name="tipo_id" class="form-control">
                                <option value="">Todos os Tipos</option>
                                <?php foreach ($tipos as $tipo): ?>
                                    <option value="<?php echo $tipo['id_tipo']; ?>" <?php echo $filters['tipo_id'] == $tipo['id_tipo'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tipo['nome_tipo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-secondary">Filtrar</button>
                        <a href="minhas_solicitacoes.php" class="btn btn-outline">Limpar</a>
                    </form>
                </div>
            </div>

            <!-- Lista de Solicitações -->
            <?php if (empty($requestData['requests'])): ?>
                <div class="card">
                    <div class="card-body text-center">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
                        <h3>Nenhuma solicitação encontrada</h3>
                        <p class="text-secondary mb-4">
                            <?php if (array_filter($filters)): ?>
                                Nenhuma solicitação corresponde aos filtros selecionados.
                            <?php else: ?>
                                Você ainda não possui solicitações cadastradas.
                            <?php endif; ?>
                        </p>
                        <a href="criar.php" class="btn btn-primary">
                            ➕ Criar primeira solicitação
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Local</th>
                                <th>Tipo</th>
                                <th>Prioridade</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requestData['requests'] as $request): ?>
                                <tr>
                                    <td>#<?php echo $request['id_solicitacao']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($request['local']); ?></strong>
                                        <br>
                                        <small class="text-secondary">
                                            <?php echo htmlspecialchars(substr($request['descricao'], 0, 50)); ?>...
                                        </small>
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
                                        <span class="status-badge <?php echo getStatusClass($request['status']); ?>">
                                            <?php echo $request['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo formatDate($request['data_abertura']); ?>
                                    </td>
                                    <td class="table-actions">
                                        <a href="detalhes.php?id=<?php echo $request['id_solicitacao']; ?>"
                                            class="btn btn-sm btn-primary">
                                            👁️ Ver
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <?php if ($requestData['pages'] > 1): ?>
                    <div id="pagination-container"></div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Resumo -->
            <div class="stats-grid mt-4">
                <div class="stat-card stat-card-primary">
                    <div class="stat-card-content">
                        <h3><?php echo $requestData['total']; ?></h3>
                        <p>Total de Solicitações</p>
                    </div>
                    <div class="stat-card-icon">📊</div>
                </div>

                <div class="stat-card stat-card-success">
                    <div class="stat-card-content">
                        <h3><?php echo count(array_filter($requestData['requests'], fn($r) => $r['status'] === 'Concluída')); ?></h3>
                        <p>Concluídas</p>
                    </div>
                    <div class="stat-card-icon">✅</div>
                </div>

                <div class="stat-card stat-card-warning">
                    <div class="stat-card-content">
                        <h3><?php echo count(array_filter($requestData['requests'], fn($r) => $r['status'] === 'Em andamento')); ?></h3>
                        <p>Em Andamento</p>
                    </div>
                    <div class="stat-card-icon">⏳</div>
                </div>

                <div class="stat-card stat-card-danger">
                    <div class="stat-card-content">
                        <h3><?php echo count(array_filter($requestData['requests'], fn($r) => $r['prioridade'] === 'Urgente')); ?></h3>
                        <p>Urgentes</p>
                    </div>
                    <div class="stat-card-icon">🚨</div>
                </div>
            </div>
        </div>
    </main>

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

            // Auto-submit do formulário de filtros
            document.getElementById('filter-form').addEventListener('change', function() {
                this.submit();
            });

            // Atualização automática a cada 30 segundos
            setInterval(function() {
                if (document.visibilityState === 'visible') {
                    window.location.reload();
                }
            }, 30000);
        });
    </script>
</body>

</html>