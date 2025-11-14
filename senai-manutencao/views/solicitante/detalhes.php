<?php

/**
 * Detalhes de Solicitação
 * Sistema SENAI Alagoinhas
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/auth.php';
require_once __DIR__ . '/../../models/Request.php';

// Verificar se está logado
Auth::requireLogin();

$currentUser = getCurrentUser();
$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: minhas_solicitacoes.php');
    exit;
}

// Buscar solicitação com detalhes completos
$request = Request::findWithDetails($id);

// Verificar permissão: solicitante pode ver suas próprias, admin pode ver todas
$isAdmin = ($currentUser['tipo_usuario'] === 'admin');
$isOwner = ($request['solicitante_matricula'] === $currentUser['matricula']);

if (!$request || (!$isOwner && !$isAdmin)) {
    header('Location: minhas_solicitacoes.php?error=not_found');
    exit;
}

// Buscar movimentações
$movements = Request::getMovements($id);

// Processar ações do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Adicionar comentário do solicitante
    if ($_POST['action'] === 'adicionar_comentario') {
        $comentario = trim($_POST['comentario'] ?? '');

        if (!empty($comentario)) {
            try {
                $sql = "INSERT INTO movimentacoes (solicitacao_id, usuario_id, status_antigo, status_novo, comentario, data_movimentacao) 
                        VALUES (?, ?, ?, ?, ?, NOW())";
                Database::execute($sql, [
                    $id,
                    $currentUser['id'],
                    $request['status'],
                    $request['status'],
                    $comentario
                ]);

                // Atualizar data de última atualização da solicitação
                Database::execute("UPDATE solicitacoes SET data_atualizacao = NOW() WHERE id_solicitacao = ?", [$id]);

                header('Location: detalhes.php?id=' . $id . '&comment_sent=1');
                exit;
            } catch (Exception $e) {
                $error = 'Erro ao enviar comentário: ' . $e->getMessage();
            }
        } else {
            $error = 'O comentário não pode estar vazio';
        }
    }

    // Processar avaliação
    if ($_POST['action'] === 'avaliar' && $request['status'] === 'Concluída') {
        $rating = (int) ($_POST['rating'] ?? 0);
        $feedback = trim($_POST['feedback'] ?? '');

        if ($rating >= 1 && $rating <= 5) {
            try {
                // Atualizar avaliação
                $sql = "UPDATE solicitacoes SET avaliacao = ?, feedback_solicitante = ?, data_avaliacao = NOW() WHERE id_solicitacao = ?";
                $stmt = Database::execute($sql, [$rating, $feedback, $id]);

                // Log da ação
                error_log("Solicitação #{$id} avaliada com nota {$rating} pelo usuário {$currentUser['matricula']}");

                header('Location: detalhes.php?id=' . $id . '&rated=1');
                exit;
            } catch (Exception $e) {
                $error = 'Erro ao salvar avaliação';
            }
        } else {
            $error = 'Nota inválida';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação #<?php echo $request['id_solicitacao']; ?> - SENAI Alagoinhas</title>
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
            <a href="minhas_solicitacoes.php" class="nav-item">
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
            <!-- Cabeçalho -->
            <div class="d-flex justify-between align-center mb-4">
                <div>
                    <h1>Solicitação #<?php echo $request['id_solicitacao']; ?></h1>
                    <p class="text-secondary"><?php echo htmlspecialchars($request['local']); ?></p>
                </div>

                <a href="minhas_solicitacoes.php" class="btn btn-outline">
                    ← Voltar
                </a>
            </div>

            <!-- Alertas -->
            <?php if (isset($_GET['rated'])): ?>
                <div class="alert alert-success mb-4">
                    <strong>✅ Obrigado!</strong> Sua avaliação foi registrada com sucesso.
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger mb-4">
                    <strong>❌ Erro!</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Detalhes Principais -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-between align-center">
                            <h3>Detalhes da Solicitação</h3>
                            <div class="d-flex gap-2">
                                <span class="status-badge <?php echo getStatusClass($request['status']); ?>">
                                    <?php echo $request['status']; ?>
                                </span>
                                <span class="priority-badge prioridade-<?php echo strtolower(str_replace('ê', 'e', $request['prioridade'])); ?>">
                                    <?php echo $request['prioridade']; ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h4>📍 Local</h4>
                                    <p><?php echo htmlspecialchars($request['local']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h4>🏷️ Tipo</h4>
                                    <p><?php echo htmlspecialchars($request['nome_tipo']); ?></p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h4>📝 Descrição</h4>
                                <div class="bg-light p-3 rounded">
                                    <?php echo nl2br(htmlspecialchars($request['descricao'])); ?>
                                </div>
                            </div>

                            <?php if ($request['anexo_path']): ?>
                                <div class="mb-4">
                                    <h4>📎 Anexo</h4>
                                    <a href="../../<?php echo htmlspecialchars($request['anexo_path']); ?>"
                                        target="_blank"
                                        class="btn btn-outline btn-sm">
                                        📄 Ver Anexo
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if ($request['solucao']): ?>
                                <div class="mb-4">
                                    <h4>✅ Solução</h4>
                                    <div class="bg-success-light p-3 rounded">
                                        <?php echo nl2br(htmlspecialchars($request['solucao'])); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($request['responsavel_matricula']): ?>
                                <div class="mb-4">
                                    <h4>👨‍🔧 Responsável</h4>
                                    <p>
                                        <strong><?php echo htmlspecialchars($request['responsavel_nome']); ?></strong><br>
                                        <small class="text-secondary">Matrícula: <?php echo htmlspecialchars($request['responsavel_matricula']); ?></small>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Mensagens de Feedback -->
                    <?php if (isset($_GET['comment_sent'])): ?>
                        <div class="alert alert-success mb-4">
                            ✅ Comentário enviado com sucesso!
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger mb-4">
                            ❌ <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Histórico de Movimentações / Chat -->
                    <?php if (!empty($movements)): ?>
                        <div class="card mt-4">
                            <div class="card-header">
                                <h3>💬 Histórico e Comunicações</h3>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <?php foreach (array_reverse($movements) as $index => $movement): ?>
                                        <?php
                                        $isComment = ($movement['status_antigo'] === $movement['status_novo'] && !empty($movement['comentario']));
                                        $isFromCurrentUser = ($movement['usuario_matricula'] === $currentUser['matricula']);
                                        ?>
                                        <div class="timeline-item <?php echo $index === 0 ? 'timeline-item-latest' : ''; ?> <?php echo $isComment ? 'timeline-comment' : ''; ?> <?php echo $isFromCurrentUser ? 'timeline-from-me' : 'timeline-from-admin'; ?>">
                                            <div class="timeline-marker">
                                                <?php
                                                if ($isComment) {
                                                    echo $isFromCurrentUser ? '💬' : '👨‍💼';
                                                } else {
                                                    switch ($movement['status_novo']) {
                                                        case 'Aberta':
                                                            echo '🆕';
                                                            break;
                                                        case 'Em andamento':
                                                            echo '⏳';
                                                            break;
                                                        case 'Concluída':
                                                            echo '✅';
                                                            break;
                                                        default:
                                                            echo '📝';
                                                            break;
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="timeline-header">
                                                    <strong>
                                                        <?php
                                                        if ($isComment) {
                                                            echo $isFromCurrentUser ? '💬 Você comentou:' : '👨‍💼 Resposta do Administrador:';
                                                        } else {
                                                            echo htmlspecialchars($movement['acao']);
                                                        }
                                                        ?>
                                                    </strong>
                                                    <span class="timeline-date"><?php echo formatDate($movement['data_movimento']); ?></span>
                                                </div>
                                                <?php if ($movement['observacoes'] || $movement['comentario']): ?>
                                                    <p class="timeline-description">
                                                        <?php echo nl2br(htmlspecialchars($movement['observacoes'] ?: $movement['comentario'])); ?>
                                                    </p>
                                                <?php endif; ?>
                                                <small class="text-secondary">
                                                    Por: <?php echo htmlspecialchars($movement['usuario_nome'] ?? $movement['usuario_matricula']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Formulário de Novo Comentário -->
                    <?php if ($request['status'] !== 'Concluída'): ?>
                        <div class="card mt-4">
                            <div class="card-header">
                                <h3>💬 Adicionar Comentário</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="solicitante-comment-form">
                                    <input type="hidden" name="action" value="adicionar_comentario">

                                    <div class="form-group">
                                        <label for="comentario" class="form-label">Sua mensagem:</label>
                                        <textarea
                                            name="comentario"
                                            id="comentario"
                                            class="form-control"
                                            rows="4"
                                            required
                                            placeholder="Digite sua mensagem ou dúvida para o administrador..."><?php echo isset($_POST['comentario']) ? htmlspecialchars($_POST['comentario']) : ''; ?></textarea>
                                        <small class="text-secondary">Use este espaço para enviar informações adicionais ou tirar dúvidas sobre sua solicitação.</small>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            📤 Enviar Comentário
                                        </button>
                                        <button type="button" class="btn btn-outline" onclick="document.getElementById('solicitante-comment-form').reset();">
                                            🗑️ Limpar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mt-4">
                            ℹ️ Esta solicitação está concluída. Não é possível adicionar mais comentários.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar de Informações -->
                <div class="col-md-4">
                    <!-- Status Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3>ℹ️ Informações</h3>
                        </div>
                        <div class="card-body">
                            <div class="info-item">
                                <span class="info-label">ID:</span>
                                <span class="info-value">#<?php echo $request['id_solicitacao']; ?></span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Data de Abertura:</span>
                                <span class="info-value"><?php echo formatDate($request['data_abertura']); ?></span>
                            </div>

                            <?php if ($request['data_conclusao']): ?>
                                <div class="info-item">
                                    <span class="info-label">Data de Conclusão:</span>
                                    <span class="info-value"><?php echo formatDate($request['data_conclusao']); ?></span>
                                </div>

                                <?php
                                $prazo_dias = ceil((strtotime($request['data_conclusao']) - strtotime($request['data_abertura'])) / (60 * 60 * 24));
                                ?>
                                <div class="info-item">
                                    <span class="info-label">Tempo de Atendimento:</span>
                                    <span class="info-value"><?php echo $prazo_dias; ?> dia(s)</span>
                                </div>
                            <?php endif; ?>

                            <div class="info-item">
                                <span class="info-label">Última Atualização:</span>
                                <span class="info-value"><?php echo formatDate($request['data_ultima_atualizacao'] ?? $request['data_abertura']); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Avaliação -->
                    <?php if ($request['status'] === 'Concluída'): ?>
                        <div class="card">
                            <div class="card-header">
                                <h3>⭐ Avaliação</h3>
                            </div>
                            <div class="card-body">
                                <?php if ($request['avaliacao']): ?>
                                    <div class="text-center">
                                        <div class="rating-display">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star <?php echo $i <= $request['avaliacao'] ? 'star-filled' : ''; ?>">★</span>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="mt-2">Nota: <?php echo $request['avaliacao']; ?>/5</p>

                                        <?php if ($request['feedback_solicitante']): ?>
                                            <div class="bg-light p-3 rounded mt-3">
                                                <strong>Seu comentário:</strong><br>
                                                <?php echo nl2br(htmlspecialchars($request['feedback_solicitante'])); ?>
                                            </div>
                                        <?php endif; ?>

                                        <small class="text-secondary">
                                            Avaliado em <?php echo formatDate($request['data_avaliacao']); ?>
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="avaliar">

                                        <div class="form-group">
                                            <label class="form-label">Como você avalia o atendimento?</label>
                                            <div class="rating-input">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" required>
                                                    <label for="star<?php echo $i; ?>" class="star">★</label>
                                                <?php endfor; ?>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="feedback" class="form-label">Comentários (opcional)</label>
                                            <textarea name="feedback"
                                                id="feedback"
                                                class="form-control"
                                                rows="3"
                                                placeholder="Deixe seu comentário sobre o atendimento..."></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100">
                                            ⭐ Avaliar Atendimento
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Ações -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>⚡ Ações</h3>
                        </div>
                        <div class="card-body">
                            <a href="criar.php" class="btn btn-primary w-100 mb-2">
                                ➕ Nova Solicitação
                            </a>

                            <a href="minhas_solicitacoes.php" class="btn btn-outline w-100 mb-2">
                                📋 Minhas Solicitações
                            </a>

                            <button onclick="window.print()" class="btn btn-secondary w-100">
                                🖨️ Imprimir
                            </button>
                        </div>
                    </div>
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
            // Rating interativo
            const ratingInputs = document.querySelectorAll('.rating-input input[type="radio"]');
            const ratingLabels = document.querySelectorAll('.rating-input .star');

            ratingLabels.forEach((label, index) => {
                label.addEventListener('mouseover', function() {
                    // Highlight até a estrela atual
                    ratingLabels.forEach((l, i) => {
                        l.style.color = i <= index ? '#ffc107' : '#ddd';
                    });
                });

                label.addEventListener('mouseout', function() {
                    // Voltar ao estado baseado na seleção
                    const checkedInput = document.querySelector('.rating-input input[type="radio"]:checked');
                    const checkedIndex = checkedInput ? Array.from(ratingInputs).indexOf(checkedInput) : -1;

                    ratingLabels.forEach((l, i) => {
                        l.style.color = i <= checkedIndex ? '#ffc107' : '#ddd';
                    });
                });

                label.addEventListener('click', function() {
                    // Atualizar visual após seleção
                    setTimeout(() => {
                        const checkedInput = document.querySelector('.rating-input input[type="radio"]:checked');
                        const checkedIndex = checkedInput ? Array.from(ratingInputs).indexOf(checkedInput) : -1;

                        ratingLabels.forEach((l, i) => {
                            l.style.color = i <= checkedIndex ? '#ffc107' : '#ddd';
                        });
                    }, 10);
                });
            });

            // Auto-refresh a cada minuto se a solicitação não estiver concluída
            <?php if ($request['status'] !== 'Concluída'): ?>
                setInterval(function() {
                    if (document.visibilityState === 'visible') {
                        // Verificar se há atualizações
                        fetch('../../controllers/RequestController.php?action=checkUpdates&id=<?php echo $request["id_solicitacao"]; ?>')
                            .then(response => response.json())
                            .then(data => {
                                if (data.hasUpdates) {
                                    // Mostrar notificação de atualização
                                    AppUtils.Toast.show('Esta solicitação foi atualizada. Recarregue a página.', 'info', 5000);

                                    // Adicionar botão de recarga
                                    const reloadBtn = document.createElement('button');
                                    reloadBtn.textContent = '🔄 Recarregar';
                                    reloadBtn.className = 'btn btn-sm btn-primary';
                                    reloadBtn.onclick = () => window.location.reload();

                                    const toast = document.querySelector('.toast:last-child');
                                    if (toast) {
                                        toast.appendChild(reloadBtn);
                                    }
                                }
                            })
                            .catch(() => {
                                // Silenciar erros de rede
                            });
                    }
                }, 60000); // 1 minuto
            <?php endif; ?>
        });

        // Estilos para impressão
        const printStyles = `
            <style media="print">
                .header, .sidebar, .btn { display: none !important; }
                .content { margin-left: 0 !important; }
                .card { border: 1px solid #ddd !important; }
                .timeline-item { break-inside: avoid; }
                @page { margin: 1in; }
            </style>
        `;
        document.head.insertAdjacentHTML('beforeend', printStyles);
    </script>
</body>

</html>