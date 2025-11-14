<?php

/**
 * Relatórios e Analytics
 * Sistema SENAI Alagoinhas - Hackathon 2025
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/auth.php';
require_once __DIR__ . '/../../models/Request.php';
require_once __DIR__ . '/../../models/Type.php';
require_once __DIR__ . '/../../models/Sector.php';

// Verificar se é admin
Auth::requireAdmin();

$currentUser = getCurrentUser();

// Parâmetros do relatório
$params = [
    'data_inicio' => $_GET['data_inicio'] ?? date('Y-m-01'), // Primeiro dia do mês atual
    'data_fim' => $_GET['data_fim'] ?? date('Y-m-d'),
    'tipo_id' => $_GET['tipo_id'] ?? '',
    'setor_id' => $_GET['setor_id'] ?? '',
    'status' => $_GET['status'] ?? '',
    'prioridade' => $_GET['prioridade'] ?? ''
];

// Processar exportação
if (isset($_GET['export'])) {
    $format = $_GET['export']; // csv, pdf
    
    // Redirecionar para ExportController com parâmetros
    $exportParams = array_merge($params, ['action' => $format]);
    $url = "../../controllers/ExportController.php?" . http_build_query($exportParams);
    
    header("Location: $url");
    exit;
}

// Buscar dados do relatório
// Gerar relatório usando Request::all com filtros
$requests = Request::all($params, 1000, 0);
$reportData = [
    'requests' => $requests,
    'total' => Request::count($params),
    'filters' => $params
];
$tipos = Type::getActive();
$setores = Sector::getActive();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - SENAI Alagoinhas</title>
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
            <span>Sistema de Gerenciamento - Relatórios</span>
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
            <a href="relatorios.php" class="nav-item active">
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
                    <h1>Relatórios e Analytics</h1>
                    <p class="text-secondary">
                        Período: <?php echo formatDate($params['data_inicio']); ?> até <?php echo formatDate($params['data_fim']); ?>
                    </p>
                </div>

                <div class="d-flex gap-2">
                    <a href="?<?php echo http_build_query(array_merge($params, ['export' => 'csv'])); ?>"
                        class="btn btn-outline">
                        📊 Exportar CSV
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($params, ['export' => 'pdf'])); ?>"
                        class="btn btn-secondary">
                        📄 Exportar PDF
                    </a>
                    <button onclick="window.print()" class="btn btn-primary">
                        🖨️ Imprimir
                    </button>
                </div>
            </div>

            <!-- Filtros do Relatório -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3>🔍 Filtros do Relatório</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Data Início</label>
                                <input type="date"
                                    name="data_inicio"
                                    class="form-control"
                                    value="<?php echo $params['data_inicio']; ?>"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Data Fim</label>
                                <input type="date"
                                    name="data_fim"
                                    class="form-control"
                                    value="<?php echo $params['data_fim']; ?>"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Tipo</label>
                                <select name="tipo_id" class="form-control">
                                    <option value="">Todos</option>
                                    <?php foreach ($tipos as $tipo): ?>
                                        <option value="<?php echo $tipo['id_tipo']; ?>"
                                            <?php echo $params['tipo_id'] == $tipo['id_tipo'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tipo['nome_tipo']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="">Todos</option>
                                    <option value="Aberta" <?php echo $params['status'] === 'Aberta' ? 'selected' : ''; ?>>Aberta</option>
                                    <option value="Em andamento" <?php echo $params['status'] === 'Em andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                    <option value="Concluída" <?php echo $params['status'] === 'Concluída' ? 'selected' : ''; ?>>Concluída</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                📈 Gerar Relatório
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resumo Executivo -->
            <div class="stats-grid mb-4">
                <div class="stat-card stat-card-primary">
                    <div class="stat-card-content">
                        <h3><?php echo $reportData['resumo']['total']; ?></h3>
                        <p>Total no Período</p>
                        <small>
                            <?php
                            $variacao = $reportData['resumo']['variacao_periodo_anterior'];
                            echo $variacao > 0 ? "↗️ +{$variacao}%" : ($variacao < 0 ? "↘️ {$variacao}%" : "→ 0%");
                            ?> vs período anterior
                        </small>
                    </div>
                    <div class="stat-card-icon">📊</div>
                </div>

                <div class="stat-card stat-card-success">
                    <div class="stat-card-content">
                        <h3><?php echo $reportData['resumo']['concluidas']; ?></h3>
                        <p>Concluídas</p>
                        <small>
                            <?php echo round(($reportData['resumo']['concluidas'] / max($reportData['resumo']['total'], 1)) * 100, 1); ?>% de taxa de conclusão
                        </small>
                    </div>
                    <div class="stat-card-icon">✅</div>
                </div>

                <div class="stat-card stat-card-info">
                    <div class="stat-card-content">
                        <h3><?php echo $reportData['resumo']['tempo_medio']; ?></h3>
                        <p>Tempo Médio (dias)</p>
                        <small>Para conclusão das solicitações</small>
                    </div>
                    <div class="stat-card-icon">⏱️</div>
                </div>

                <div class="stat-card stat-card-warning">
                    <div class="stat-card-content">
                        <h3><?php echo $reportData['resumo']['urgentes']; ?></h3>
                        <p>Urgentes</p>
                        <small>
                            <?php echo round(($reportData['resumo']['urgentes'] / max($reportData['resumo']['total'], 1)) * 100, 1); ?>% do total
                        </small>
                    </div>
                    <div class="stat-card-icon">🚨</div>
                </div>
            </div>

            <div class="row">
                <!-- Gráfico por Status -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>📊 Distribuição por Status</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="statusChart" width="400" height="300"></canvas>

                            <div class="mt-3">
                                <div class="row text-center">
                                    <?php foreach ($reportData['por_status'] as $status): ?>
                                        <div class="col-4">
                                            <div class="stat-mini">
                                                <h4><?php echo $status['total']; ?></h4>
                                                <p><?php echo $status['status']; ?></p>
                                                <small><?php echo round(($status['total'] / max($reportData['resumo']['total'], 1)) * 100, 1); ?>%</small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfico por Tipo -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>🏷️ Solicitações por Tipo</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="tipoChart" width="400" height="300"></canvas>

                            <div class="mt-3 ranking-tipos">
                                <?php foreach (array_slice($reportData['por_tipo'], 0, 5) as $index => $tipo): ?>
                                    <div class="ranking-item">
                                        <span class="ranking-position"><?php echo $index + 1; ?>°</span>
                                        <span class="ranking-name"><?php echo htmlspecialchars($tipo['nome_tipo']); ?></span>
                                        <span class="ranking-value"><?php echo $tipo['total']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Evolução Temporal -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3>📈 Evolução no Período</h3>
                </div>
                <div class="card-body">
                    <canvas id="evolucaoChart" width="800" height="400"></canvas>
                </div>
            </div>

            <!-- Tabelas Detalhadas -->
            <div class="row mt-4">
                <!-- Ranking por Setor -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>🏢 Ranking por Setor</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-container">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Posição</th>
                                            <th>Setor</th>
                                            <th>Total</th>
                                            <th>% Conclusão</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reportData['por_setor'] as $index => $setor): ?>
                                            <tr>
                                                <td>
                                                    <span class="ranking-badge"><?php echo $index + 1; ?>°</span>
                                                </td>
                                                <td><?php echo htmlspecialchars($setor['nome_setor']); ?></td>
                                                <td><strong><?php echo $setor['total']; ?></strong></td>
                                                <td>
                                                    <?php
                                                    $percentual = $setor['total'] > 0 ? round(($setor['concluidas'] / $setor['total']) * 100, 1) : 0;
                                                    $cor_classe = $percentual >= 80 ? 'text-success' : ($percentual >= 60 ? 'text-warning' : 'text-danger');
                                                    ?>
                                                    <span class="<?php echo $cor_classe; ?>">
                                                        <?php echo $percentual; ?>%
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Solicitantes Mais Ativos -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>👤 Solicitantes Mais Ativos</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-container">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Posição</th>
                                            <th>Nome</th>
                                            <th>Matrícula</th>
                                            <th>Solicitações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reportData['solicitantes_ativos'] as $index => $solicitante): ?>
                                            <tr>
                                                <td>
                                                    <span class="ranking-badge"><?php echo $index + 1; ?>°</span>
                                                </td>
                                                <td><?php echo htmlspecialchars($solicitante['nome']); ?></td>
                                                <td><?php echo htmlspecialchars($solicitante['matricula']); ?></td>
                                                <td><strong><?php echo $solicitante['total']; ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Análise de Performance -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3>⚡ Análise de Performance</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="performance-metric">
                                <h4>🎯 SLA Cumprido</h4>
                                <div class="performance-value text-success">
                                    <?php echo $reportData['performance']['sla_cumprido']; ?>%
                                </div>
                                <p>Das solicitações foram atendidas no prazo esperado</p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="performance-metric">
                                <h4>⚡ Tempo Resposta</h4>
                                <div class="performance-value text-info">
                                    <?php echo $reportData['performance']['tempo_primeira_resposta']; ?>h
                                </div>
                                <p>Tempo médio para primeira resposta</p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="performance-metric">
                                <h4>⭐ Satisfação</h4>
                                <div class="performance-value text-warning">
                                    <?php echo $reportData['performance']['satisfacao_media']; ?>/5
                                </div>
                                <p>Nota média de satisfação dos usuários</p>
                            </div>
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
        // Dados dos gráficos vindos do PHP
        const reportData = <?php echo json_encode($reportData); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de status
            drawPieChart('statusChart', reportData.por_status.map(item => ({
                nome_tipo: item.status,
                total: item.total
            })), 'Distribuição por Status');

            // Gráfico de tipos
            drawPieChart('tipoChart', reportData.por_tipo, 'Solicitações por Tipo');

            // Gráfico de evolução
            drawLineChart('evolucaoChart', reportData.evolucao_temporal, 'Evolução no Período');
        });

        // Função para desenhar gráfico de pizza
        function drawPieChart(canvasId, data, title) {
            const canvas = document.getElementById(canvasId);
            const ctx = canvas.getContext('2d');
            const centerX = canvas.width / 2;
            const centerY = (canvas.height / 2) + 20;
            const radius = Math.min(centerX, centerY) - 60;

            const colors = ['#003C78', '#FF6600', '#28a745', '#ffc107', '#dc3545', '#6c757d'];
            let total = data.reduce((sum, item) => sum + parseInt(item.total), 0);
            let currentAngle = 0;

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (total === 0) {
                ctx.fillStyle = '#666';
                ctx.font = '14px Arial';
                ctx.textAlign = 'center';
                ctx.fillText('Nenhum dado encontrado', centerX, centerY);
                return;
            }

            // Desenhar fatias
            data.forEach((item, index) => {
                const sliceAngle = (parseInt(item.total) / total) * 2 * Math.PI;

                ctx.beginPath();
                ctx.moveTo(centerX, centerY);
                ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + sliceAngle);
                ctx.closePath();
                ctx.fillStyle = colors[index % colors.length];
                ctx.fill();
                ctx.strokeStyle = '#fff';
                ctx.lineWidth = 2;
                ctx.stroke();

                // Labels nas fatias (se a fatia for grande o suficiente)
                if (sliceAngle > 0.2) {
                    const labelAngle = currentAngle + sliceAngle / 2;
                    const labelX = centerX + Math.cos(labelAngle) * (radius * 0.7);
                    const labelY = centerY + Math.sin(labelAngle) * (radius * 0.7);

                    ctx.fillStyle = '#fff';
                    ctx.font = 'bold 12px Arial';
                    ctx.textAlign = 'center';
                    ctx.fillText(item.total, labelX, labelY);
                }

                currentAngle += sliceAngle;
            });

            // Título
            ctx.fillStyle = '#333';
            ctx.font = 'bold 16px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(title, centerX, 20);
        }

        // Função para desenhar gráfico de linha
        function drawLineChart(canvasId, data, title) {
            const canvas = document.getElementById(canvasId);
            const ctx = canvas.getContext('2d');
            const padding = 60;
            const chartWidth = canvas.width - (padding * 2);
            const chartHeight = canvas.height - (padding * 2);

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (data.length === 0) {
                ctx.fillStyle = '#666';
                ctx.font = '14px Arial';
                ctx.textAlign = 'center';
                ctx.fillText('Nenhum dado encontrado', canvas.width / 2, canvas.height / 2);
                return;
            }

            const maxValue = Math.max(...data.map(item => parseInt(item.total)), 1);
            const stepX = chartWidth / Math.max(data.length - 1, 1);
            const stepY = chartHeight / maxValue;

            // Desenhar grade
            ctx.strokeStyle = '#f0f0f0';
            ctx.lineWidth = 1;

            // Linhas horizontais
            for (let i = 0; i <= 5; i++) {
                const y = padding + (chartHeight / 5) * i;
                ctx.beginPath();
                ctx.moveTo(padding, y);
                ctx.lineTo(canvas.width - padding, y);
                ctx.stroke();

                // Labels do eixo Y
                const value = Math.round(maxValue - (maxValue / 5) * i);
                ctx.fillStyle = '#666';
                ctx.font = '12px Arial';
                ctx.textAlign = 'right';
                ctx.fillText(value, padding - 10, y + 4);
            }

            // Eixos
            ctx.strokeStyle = '#ddd';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(padding, padding);
            ctx.lineTo(padding, canvas.height - padding);
            ctx.lineTo(canvas.width - padding, canvas.height - padding);
            ctx.stroke();

            // Área sob a curva
            ctx.fillStyle = 'rgba(0, 60, 120, 0.1)';
            ctx.beginPath();
            ctx.moveTo(padding, canvas.height - padding);

            data.forEach((item, index) => {
                const x = padding + (index * stepX);
                const y = canvas.height - padding - (parseInt(item.total) * stepY);

                if (index === 0) {
                    ctx.lineTo(x, y);
                } else {
                    ctx.lineTo(x, y);
                }
            });

            ctx.lineTo(padding + ((data.length - 1) * stepX), canvas.height - padding);
            ctx.closePath();
            ctx.fill();

            // Linha principal
            ctx.strokeStyle = '#003C78';
            ctx.lineWidth = 3;
            ctx.beginPath();

            data.forEach((item, index) => {
                const x = padding + (index * stepX);
                const y = canvas.height - padding - (parseInt(item.total) * stepY);

                if (index === 0) {
                    ctx.moveTo(x, y);
                } else {
                    ctx.lineTo(x, y);
                }

                // Pontos
                ctx.save();
                ctx.fillStyle = '#FF6600';
                ctx.beginPath();
                ctx.arc(x, y, 5, 0, 2 * Math.PI);
                ctx.fill();
                ctx.restore();

                // Labels no eixo X
                ctx.fillStyle = '#666';
                ctx.font = '11px Arial';
                ctx.textAlign = 'center';
                ctx.fillText(
                    item.periodo || item.data?.substr(5) || `${index + 1}`,
                    x,
                    canvas.height - padding + 20
                );

                // Valores
                ctx.fillStyle = '#333';
                ctx.font = 'bold 12px Arial';
                ctx.fillText(item.total, x, y - 10);
            });

            ctx.stroke();

            // Título
            ctx.fillStyle = '#333';
            ctx.font = 'bold 18px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(title, canvas.width / 2, 25);
        }

        // Estilos para impressão
        const printStyles = `
            <style media="print">
                .sidebar, .btn, .form-group { display: none !important; }
                .content { margin-left: 0 !important; }
                .card { break-inside: avoid; margin-bottom: 20px; }
                @page { margin: 0.5in; size: A4; }
                .stats-grid { display: flex !important; }
            </style>
        `;
        document.head.insertAdjacentHTML('beforeend', printStyles);
    </script>
</body>

</html>