<?php

/**
 * Gestão de Usuários
 * Sistema SENAI Alagoinhas - Hackathon 2025
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/auth.php';
require_once __DIR__ . '/../../utils/debug.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Sector.php';
require_once __DIR__ . '/../../models/Log.php';

// Verificar se é admin
Auth::requireAdmin();

$currentUser = getCurrentUser();

// Processar ações
$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create_user':
            $userData = [
                'nome' => trim($_POST['nome'] ?? ''),
                'matricula' => trim($_POST['matricula'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'cargo' => trim($_POST['cargo'] ?? ''), // Adicionado campo cargo
                'setor_id' => (int) ($_POST['setor_id'] ?? 0),
                'tipo_usuario' => $_POST['tipo_usuario'] ?? 'solicitante',
                'senha_hash' => $_POST['tipo_usuario'] === 'admin' ? password_hash($_POST['senha'] ?? '123456', PASSWORD_DEFAULT) : null
            ];


            // Validações
            if (empty($userData['nome'])) $errors[] = 'Nome é obrigatório';
            if (empty($userData['matricula'])) $errors[] = 'Matrícula é obrigatória';
            if ($userData['setor_id'] <= 0) $errors[] = 'Setor é obrigatório';

            // Verificar se matrícula já existe
            if (User::findByMatricula($userData['matricula'])) {
                $errors[] = 'Matrícula já existe no sistema';
            }

            if (empty($errors)) {
                try {
                    Debug::log('Iniciando criação de usuário', ['data' => $userData, 'admin' => $currentUser['matricula']]);
                    $user = new User();
                    foreach ($userData as $key => $value) {
                        $user->$key = $value;
                    }

                    if ($user->save()) {
                        Debug::log('Usuário criado com sucesso', ['novo_usuario' => $user->getId()]);
                        Log::create([
                            'usuario_id' => $currentUser['id'],
                            'usuario_matricula' => $currentUser['matricula'],
                            'acao' => 'Criação de Usuário',
                            'tabela_afetada' => 'usuarios',
                            'registro_id' => $user->getId(),
                            'detalhes' => "Usuário {$userData['matricula']} - {$userData['nome']} criado",
                            'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
                        ]);

                        $message = 'Usuário criado com sucesso!';
                    } else {
                        Debug::log('Falha ao salvar usuário', ['data' => $userData]);
                        $errors[] = 'Erro ao criar usuário';
                    }
                } catch (Exception $e) {
                    Debug::log('Exceção ao criar usuário', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    $errors[] = 'Erro interno: ' . $e->getMessage();
                }
            }
            break;

        case 'toggle_user':
            $id = (int) $_POST['id'];
            $ativo = (bool) $_POST['ativo'];

            if (User::toggleActive($id, $ativo)) {
                Debug::log('Status de usuário alterado', ['id' => $id, 'ativo' => $ativo, 'admin' => $currentUser['matricula']]);
                Log::create([
                    'usuario_id' => $currentUser['id'],
                    'usuario_matricula' => $currentUser['matricula'],
                    'acao' => 'Alteração de Usuário',
                    'tabela_afetada' => 'usuarios',
                    'registro_id' => $id,
                    'detalhes' => "Usuário ID {$id} " . ($ativo ? 'ativado' : 'desativado'),
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
                ]);

                $message = 'Usuário ' . ($ativo ? 'ativado' : 'desativado') . ' com sucesso!';
            } else {
                $errors[] = 'Erro ao alterar usuário';
            }
            break;

        case 'reset_password':
            $id = (int) $_POST['id'];
            $new_password = 'senai' . rand(1000, 9999);
            $hash = password_hash($new_password, PASSWORD_DEFAULT);

            if (User::updatePasswordById($id, $hash)) {
                Debug::log('Senha resetada para usuário', ['id' => $id, 'admin' => $currentUser['matricula']]);
                Log::create([
                    'usuario_id' => $currentUser['id'],
                    'usuario_matricula' => $currentUser['matricula'],
                    'acao' => 'Reset de Senha',
                    'tabela_afetada' => 'usuarios',
                    'registro_id' => $id,
                    'detalhes' => "Senha resetada para usuário ID {$id}",
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
                ]);

                $message = "Senha resetada! Nova senha: {$new_password}";
            } else {
                $errors[] = 'Erro ao resetar senha';
            }
            break;
    }
}

// Buscar dados
$filters = [
    'setor_id' => $_GET['setor_id'] ?? '',
    'tipo_usuario' => $_GET['tipo_usuario'] ?? '',
    'ativo' => $_GET['ativo'] ?? '',
    'search' => $_GET['search'] ?? ''
];

$users = User::getAll($filters, 50, 0);
$setores = Sector::getActive();
$stats = User::getStats($filters);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Usuários - SENAI Alagoinhas</title>
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
            <span>Sistema de Gerenciamento - Usuários</span>
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
            <a href="solicitacoes.php" class="nav-item">
                <span>📋</span>
                Todas Solicitações
            </a>
            <a href="relatorios.php" class="nav-item">
                <span>📈</span>
                Relatórios
            </a>
            <a href="usuarios.php" class="nav-item active">
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
                    <h1>Gestão de Usuários</h1>
                    <p class="text-secondary"><?php echo count($users); ?> usuários encontrados</p>
                </div>

                <button onclick="AppUtils.Modal.show('create-user-modal')" class="btn btn-primary">
                    👤 Novo Usuário
                </button>
            </div>

            <!-- Alertas -->
            <?php if ($message): ?>
                <div class="alert alert-success mb-4">
                    <strong>✅ Sucesso!</strong> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger mb-4">
                    <strong>❌ Erro!</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Estatísticas -->
            <div class="stats-grid mb-4">
                <div class="stat-card stat-card-primary">
                    <div class="stat-card-content">
                        <h3><?php echo $stats['total']; ?></h3>
                        <p>Total de Usuários</p>
                        <small><?php echo $stats['ativos']; ?> ativos</small>
                    </div>
                    <div class="stat-card-icon">👥</div>
                </div>

                <div class="stat-card stat-card-info">
                    <div class="stat-card-content">
                        <h3><?php echo $stats['solicitantes']; ?></h3>
                        <p>Solicitantes</p>
                        <small>Professores e funcionários</small>
                    </div>
                    <div class="stat-card-icon">👨‍🏫</div>
                </div>

                <div class="stat-card stat-card-warning">
                    <div class="stat-card-content">
                        <h3><?php echo $stats['admins']; ?></h3>
                        <p>Administradores</p>
                        <small>Gestores do sistema</small>
                    </div>
                    <div class="stat-card-icon">👨‍💻</div>
                </div>

                <div class="stat-card stat-card-success">
                    <div class="stat-card-content">
                        <h3><?php echo $stats['novos_mes']; ?></h3>
                        <p>Novos este Mês</p>
                        <small>Cadastrados recentemente</small>
                    </div>
                    <div class="stat-card-icon">🆕</div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3>🔍 Filtros</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Busca</label>
                                <input type="text"
                                    name="search"
                                    class="form-control"
                                    placeholder="Nome ou matrícula..."
                                    value="<?php echo htmlspecialchars($filters['search']); ?>">
                            </div>
                        </div>

                        <div class="col-md-2">
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

                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Tipo</label>
                                <select name="tipo_usuario" class="form-control">
                                    <option value="">Todos</option>
                                    <option value="solicitante" <?php echo $filters['tipo_usuario'] === 'solicitante' ? 'selected' : ''; ?>>Solicitante</option>
                                    <option value="admin" <?php echo $filters['tipo_usuario'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="ativo" class="form-control">
                                    <option value="">Todos</option>
                                    <option value="1" <?php echo $filters['ativo'] === '1' ? 'selected' : ''; ?>>Ativo</option>
                                    <option value="0" <?php echo $filters['ativo'] === '0' ? 'selected' : ''; ?>>Inativo</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                🔍 Filtrar
                            </button>
                            <a href="usuarios.php" class="btn btn-outline">
                                🗑️ Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Usuários -->
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Matrícula</th>
                            <th>Email</th>
                            <th>Setor</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr id="user-<?php echo $user['id_usuario']; ?>">
                                <td><strong>#<?php echo $user['id_usuario']; ?></strong></td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($user['nome']); ?></strong>
                                        <?php if ($user['id_usuario'] == $currentUser['id_usuario']): ?>
                                            <span class="badge badge-info badge-sm">Você</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['matricula']); ?></td>
                                <td>
                                    <?php if ($user['email']): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>">
                                            <?php echo htmlspecialchars($user['email']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['setor_nome'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge <?php echo $user['tipo_usuario'] === 'admin' ? 'badge-warning' : 'badge-secondary'; ?>">
                                        <?php echo $user['tipo_usuario'] === 'admin' ? '👨‍💻 Admin' : '👤 Solicitante'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $user['ativo'] ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo $user['ativo'] ? '✅ Ativo' : '❌ Inativo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo formatDate($user['data_criacao']); ?></strong>
                                        <?php if ($user['data_atualizacao'] !== $user['data_criacao']): ?>
                                            <br>
                                            <small class="text-secondary">
                                                Atualizado: <?php echo formatDate($user['data_atualizacao']); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="table-actions">
                                    <div class="btn-group">
                                        <?php if ($user['id_usuario'] != $currentUser['id_usuario']): ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Confirmar alteração de status?')">
                                                <input type="hidden" name="action" value="toggle_user">
                                                <input type="hidden" name="id" value="<?php echo $user['id_usuario']; ?>">
                                                <input type="hidden" name="ativo" value="<?php echo $user['ativo'] ? '0' : '1'; ?>">
                                                <button type="submit"
                                                    class="btn btn-sm <?php echo $user['ativo'] ? 'btn-warning' : 'btn-success'; ?>"
                                                    title="<?php echo $user['ativo'] ? 'Desativar' : 'Ativar'; ?>">
                                                    <?php echo $user['ativo'] ? '⏸️' : '▶️'; ?>
                                                </button>
                                            </form>

                                            <?php if ($user['tipo_usuario'] === 'admin'): ?>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Resetar senha? Uma nova senha será gerada.')">
                                                    <input type="hidden" name="action" value="reset_password">
                                                    <input type="hidden" name="id" value="<?php echo $user['id_usuario']; ?>">
                                                    <button type="submit"
                                                        class="btn btn-sm btn-secondary"
                                                        title="Resetar senha">
                                                        🔑
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted small">Você</span>
                                        <?php endif; ?>

                                        <button onclick="viewUserLogs(<?php echo $user['id_usuario']; ?>)"
                                            class="btn btn-sm btn-outline"
                                            title="Ver logs">
                                            📋
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (empty($users)): ?>
                <div class="card">
                    <div class="card-body text-center">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">👥</div>
                        <h3>Nenhum usuário encontrado</h3>
                        <p class="text-secondary">Ajuste os filtros ou cadastre um novo usuário.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal Novo Usuário -->
    <div id="create-user-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>👤 Novo Usuário</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="user-form">
                    <input type="hidden" name="action" value="create_user">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nome" class="form-label required">Nome Completo</label>
                                <input type="text"
                                    id="nome"
                                    name="nome"
                                    class="form-control"
                                    placeholder="Nome completo do usuário"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="matricula" class="form-label required">Matrícula</label>
                                <input type="text"
                                    id="matricula"
                                    name="matricula"
                                    class="form-control"
                                    placeholder="Matrícula única"
                                    required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-label">Email</label>
                                <input type="email"
                                    id="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="email@senai.edu.br">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="setor_id" class="form-label required">Setor</label>
                                <select id="setor_id" name="setor_id" class="form-control" required>
                                    <option value="">Selecione o setor</option>
                                    <?php foreach ($setores as $setor): ?>
                                        <option value="<?php echo $setor['id_setor']; ?>">
                                            <?php echo htmlspecialchars($setor['nome_setor']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipo_usuario" class="form-label required">Tipo de Usuário</label>
                                <select id="tipo_usuario" name="tipo_usuario" class="form-control" required>
                                    <option value="solicitante">👤 Solicitante (Professor/Funcionário)</option>
                                    <option value="admin">👨‍💻 Administrador</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6" id="senha-group" style="display: none;">
                            <div class="form-group">
                                <label for="senha" class="form-label">Senha Inicial</label>
                                <input type="password"
                                    id="senha"
                                    name="senha"
                                    class="form-control"
                                    placeholder="Senha para administrador"
                                    value="123456">
                                <div class="form-text">Deixe em branco para usar a senha padrão: 123456</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-end mt-4">
                        <button type="button" class="btn btn-outline modal-close">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            👤 Criar Usuário
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Logs do Usuário -->
    <div id="user-logs-modal" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h3>📋 Logs do Usuário</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div id="user-logs-content">
                    <div class="text-center">
                        <div class="spinner"></div>
                        <p>Carregando logs...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
    <script src="../../public/js/email-service.js"></script>
    <script src="../../public/js/dark-mode.js"></script>
    <script src="../../public/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle campo senha baseado no tipo de usuário
            document.getElementById('tipo_usuario').addEventListener('change', function() {
                const senhaGroup = document.getElementById('senha-group');
                if (this.value === 'admin') {
                    senhaGroup.style.display = 'block';
                    document.getElementById('senha').required = false; // Opcional, tem valor padrão
                } else {
                    senhaGroup.style.display = 'none';
                    document.getElementById('senha').required = false;
                }
            });

            // Validação do formulário
            document.getElementById('user-form').addEventListener('submit', function(e) {
                e.preventDefault();

                // Validações básicas
                const nome = document.getElementById('nome').value.trim();
                const matricula = document.getElementById('matricula').value.trim();
                const setor = document.getElementById('setor_id').value;

                if (!nome || !matricula || !setor) {
                    AppUtils.Toast.show('Por favor, preencha todos os campos obrigatórios', 'error');
                    return;
                }

                if (matricula.length < 3) {
                    AppUtils.Toast.show('Matrícula deve ter pelo menos 3 caracteres', 'error');
                    return;
                }

                // Submit do formulário
                this.submit();
            });

            // Auto-submit filtros
            const filterForm = document.querySelector('form[method="GET"]');
            filterForm.addEventListener('change', function(e) {
                if (e.target.name !== 'search') {
                    setTimeout(() => this.submit(), 500);
                }
            });

            // Busca em tempo real
            let searchTimeout;
            document.querySelector('input[name="search"]').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filterForm.submit();
                }, 1000);
            });
        });

        // Função para ver logs do usuário
        function viewUserLogs(userId) {
            AppUtils.Modal.show('user-logs-modal');

            // Carregar logs via AJAX
            fetch(`../../controllers/AdminController.php?action=user_logs&user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('user-logs-content');

                    if (data.success && data.logs.length > 0) {
                        let html = '<div class="table-container"><table class="table table-sm"><thead><tr>';
                        html += '<th>Data</th><th>Ação</th><th>Detalhes</th><th>IP</th></tr></thead><tbody>';

                        data.logs.forEach(log => {
                            html += `<tr>
                                <td>${formatDateTime(log.data_log)}</td>
                                <td><strong>${log.acao}</strong></td>
                                <td>${log.detalhes}</td>
                                <td><small>${log.ip}</small></td>
                            </tr>`;
                        });

                        html += '</tbody></table></div>';
                        content.innerHTML = html;
                    } else {
                        content.innerHTML = `
                            <div class="text-center">
                                <div style="font-size: 2rem; margin-bottom: 1rem;">📋</div>
                                <h4>Nenhum log encontrado</h4>
                                <p class="text-secondary">Este usuário ainda não possui atividades registradas.</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    document.getElementById('user-logs-content').innerHTML = `
                        <div class="alert alert-danger">
                            Erro ao carregar logs: ${error.message}
                        </div>
                    `;
                });
        }

        // Função auxiliar para formatar data/hora
        function formatDateTime(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleString('pt-BR');
        }
    </script>
</body>

</html>