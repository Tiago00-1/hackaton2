<?php

/**
 * Dashboard Administrativo
 *
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/auth.php';
require_once __DIR__ . '/../../controllers/AdminController.php';

// Verificar se é admin
Auth::requireAdmin();

$currentUser = getCurrentUser();

// Buscar dados do dashboard
$dashboardDataRaw = AdminController::getDashboardData();

// Mapear dados para o formato esperado pela view
$stats = $dashboardDataRaw['stats'] ?? [];
$dashboardData = [
    'total_solicitacoes' => $stats['total'] ?? 0,
    'solicitacoes_mes' => $stats['mes'] ?? 0,
    'solicitacoes_abertas' => $stats['abertas'] ?? 0,
    'solicitacoes_andamento' => $stats['em_andamento'] ?? 0,
    'solicitacoes_concluidas' => $stats['concluidas'] ?? 0,
    'concluidas_mes' => $stats['concluidas_mes'] ?? 0,
    'urgentes' => $dashboardDataRaw['urgent_requests'] ?? [],
    'atividade_recente' => $dashboardDataRaw['recent_requests'] ?? [],
    'resumo_setores' => $dashboardDataRaw['requests_by_sector'] ?? []
];

// Preparar dados dos gráficos
$chartData = [
    'tipos' => array_map(function($item) {
        return [
            'nome_tipo' => $item['nome_tipo'] ?? $item['nome'] ?? '',
            'total' => $item['quantidade'] ?? $item['total'] ?? 0
        ];
    }, $dashboardDataRaw['requests_by_type'] ?? []),
    'evolucao' => array_map(function($item) {
        return [
            'mes' => $item['data'] ?? $item['mes'] ?? date('Y-m'),
            'total' => $item['quantidade'] ?? $item['total'] ?? 0
        ];
    }, $dashboardDataRaw['trend_data'] ?? [])
];
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrativo - SENAI Alagoinhas</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/components.css">
    <link rel="stylesheet" href="../../public/css/responsive.css">
    <link rel="stylesheet" href="../../public/css/alerts.css">
    <link rel="stylesheet" href="../../public/css/dashboard-enhanced.css">
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
            <span>Sistema de Gerenciamento - Administrativo</span>
        </div>

        <nav class="header-nav">
            <div class="header-user">
                <div class="user-info">
                    <div class="user-name" data-user-info="nome"><?php echo htmlspecialchars($currentUser['nome']); ?></div>
                    <div class="user-role">Administrador - <?php echo htmlspecialchars($currentUser['matricula']); ?></div>
                </div>
            </div>


            <a href="../../controllers/AuthController.php?action=logout" class="btn btn-outline btn-sm">
                Sair
            </a>
        </nav>
    </header>

    <!-- Sidebar -->
    <aside class="sidebar">
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item active">
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
            <a href="usuarios.php" class="nav-item">
                <span>👥</span>
                Usuários
            </a>
            <a href="../../index.php" class="nav-item">
                <span>🏠</span>
                Página Inicial
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="content">
        <div class="container-fluid">
            <!-- Cabeçalho -->
            <div class="d-flex justify-between align-center mb-4">
                <div>
                    <h1>Dashboard Administrativo</h1>
                    <p class="text-secondary">Visão geral do sistema - <?php echo date('d/m/Y H:i'); ?></p>
                </div>

                <div class="d-flex gap-2">
                    <button onclick="refreshDashboard()" class="btn btn-outline">
                        🔄 Atualizar
                    </button>
                    <a href="relatorios.php" class="btn btn-primary">
                        📈 Ver Relatórios
                    </a>
                </div>
            </div>

            <!-- Cards de Estatísticas -->
            <div class="stats-grid mb-4">
                <div class="stat-card stat-card-primary">
                    <div class="stat-card-content">
                        <h3><?php echo $dashboardData['total_solicitacoes']; ?></h3>
                        <p>Total de Solicitações</p>
                        <small>
                            <?php echo $dashboardData['solicitacoes_mes']; ?> este mês
                        </small>
                    </div>
                    <div class="stat-card-icon">📊</div>
                </div>

                <div class="stat-card stat-card-warning">
                    <div class="stat-card-content">
                        <h3><?php echo $dashboardData['solicitacoes_abertas']; ?></h3>
                        <p>Abertas</p>
                        <small>Aguardando atendimento</small>
                    </div>
                    <div class="stat-card-icon">🆕</div>
                </div>

                <div class="stat-card stat-card-info">
                    <div class="stat-card-content">
                        <h3><?php echo $dashboardData['solicitacoes_andamento']; ?></h3>
                        <p>Em Andamento</p>
                        <small>Sendo processadas</small>
                    </div>
                    <div class="stat-card-icon">⏳</div>
                </div>

                <div class="stat-card stat-card-success">
                    <div class="stat-card-content">
                        <h3><?php echo $dashboardData['solicitacoes_concluidas']; ?></h3>
                        <p>Concluídas</p>
                        <small>
                            <?php echo $dashboardData['concluidas_mes']; ?> este mês
                        </small>
                    </div>
                    <div class="stat-card-icon">✅</div>
                </div>
            </div>

            <div class="row">
                <!-- Solicitações Urgentes -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-between align-center">
                            <h3>🚨 Solicitações Urgentes</h3>
                            <span class="badge badge-danger"><?php echo count($dashboardData['urgentes']); ?></span>
                        </div>
                        <div class="card-body">
                            <?php if (empty($dashboardData['urgentes'])): ?>
                                <div class="text-center py-4">
                                    <div style="font-size: 2rem; margin-bottom: 1rem;">✨</div>
                                    <p class="text-secondary">Nenhuma solicitação urgente!</p>
                                </div>
                            <?php else: ?>
                                <div class="table-container">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Local</th>
                                                <th>Tipo</th>
                                                <th>Data</th>
                                                <th>Ação</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($dashboardData['urgentes'], 0, 5) as $urgente): ?>
                                                <tr>
                                                    <td>#<?php echo $urgente['id_solicitacao']; ?></td>
                                                    <td><?php echo htmlspecialchars(substr($urgente['local'], 0, 20)); ?></td>
                                                    <td><?php echo htmlspecialchars($urgente['nome_tipo']); ?></td>
                                                    <td><?php echo formatDate($urgente['data_abertura']); ?></td>
                                                    <td>
                                                        <a href="solicitacoes.php?id=<?php echo $urgente['id_solicitacao']; ?>"
                                                            class="btn btn-sm btn-danger">
                                                            👁️
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if (count($dashboardData['urgentes']) > 5): ?>
                                    <div class="text-center mt-2">
                                        <a href="solicitacoes.php?prioridade=Urgente" class="btn btn-sm btn-outline">
                                            Ver todas (<?php echo count($dashboardData['urgentes']); ?>)
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Atividade Recente -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>📝 Atividade Recente</h3>
                        </div>
                        <div class="card-body">
                            <div class="timeline timeline-sm">
                                <?php foreach ($dashboardData['atividade_recente'] as $atividade): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-marker">
                                            <?php
                                            switch ($atividade['acao']) {
                                                case 'Criação de Solicitação':
                                                    echo '🆕';
                                                    break;
                                                case 'Atualização de Status':
                                                    echo '🔄';
                                                    break;
                                                case 'Conclusão':
                                                    echo '✅';
                                                    break;
                                                default:
                                                    echo '📝';
                                                    break;
                                            }
                                            ?>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="timeline-header">
                                                <strong><?php echo htmlspecialchars($atividade['acao']); ?></strong>
                                                <span class="timeline-date"><?php echo formatDate($atividade['data_log']); ?></span>
                                            </div>
                                            <p class="timeline-description">
                                                <?php echo htmlspecialchars(substr($atividade['detalhes'], 0, 80)); ?>...
                                            </p>
                                            <small class="text-secondary">
                                                Por: <?php echo htmlspecialchars($atividade['usuario_nome'] ?? $atividade['usuario_matricula']); ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>📊 Solicitações por Tipo</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="tiposChart" width="400" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>📈 Evolução Mensal</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="evolucaoChart" width="400" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumo por Setores -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3>🏢 Resumo por Setores</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Setor</th>
                                    <th>Total</th>
                                    <th>Abertas</th>
                                    <th>Andamento</th>
                                    <th>Concluídas</th>
                                    <th>% Conclusão</th>
                                    <th>Tempo Médio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dashboardData['resumo_setores'] as $setor): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($setor['nome_setor']); ?></strong></td>
                                        <td><?php echo $setor['total']; ?></td>
                                        <td>
                                            <span class="badge badge-warning"><?php echo $setor['abertas']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?php echo $setor['andamento']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-success"><?php echo $setor['concluidas']; ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $percentual = $setor['total'] > 0 ? round(($setor['concluidas'] / $setor['total']) * 100, 1) : 0;
                                            $cor_classe = $percentual >= 80 ? 'text-success' : ($percentual >= 60 ? 'text-warning' : 'text-danger');
                                            ?>
                                            <span class="<?php echo $cor_classe; ?>">
                                                <?php echo $percentual; ?>%
                                            </span>
                                        </td>
                                        <td><?php echo $setor['tempo_medio'] ?? '-'; ?> dias</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
    <script src="../../public/js/email-service.js"></script>
    <script src="../../public/js/dark-mode.js"></script>
    <script src="../../public/js/main.js"></script>
    <script src="../../public/js/advanced.js"></script>
    <script>
        // Dados dos gráficos vindos do PHP
        const chartData = <?php echo json_encode($chartData); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            // Configurações globais do Chart.js
            Chart.defaults.font.family = 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            Chart.defaults.color = '#6B7280';

            // Gráfico de tipos (Doughnut)
            createDoughnutChart('tiposChart', chartData.tipos);

            // Gráfico de evolução (Linha)
            createLineChart('evolucaoChart', chartData.evolucao);

            // Animar contadores
            animateCounters();

            // Auto-refresh a cada 5 minutos
            setInterval(refreshDashboard, 5 * 60 * 1000);
        });

        function animateCounters() {
            document.querySelectorAll('[data-counter]').forEach(el => {
                const target = parseInt(el.dataset.counter);
                animateCounter(el, target);
            });
        }

        function refreshDashboard() {
            toast.info('Atualizando dashboard...');
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }

        function createDoughnutChart(canvasId, data) {
            const ctx = document.getElementById(canvasId);
            if (!ctx) return;

            const labels = data.map(item => item.nome_tipo);
            const values = data.map(item => parseInt(item.total));

            const colors = [
                '#003C78', '#FF6600', '#10B981', '#F59E0B',
                '#EF4444', '#3B82F6', '#8B5CF6', '#EC4899'
            ];

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        function createLineChart(canvasId, data) {
            const ctx = document.getElementById(canvasId);
            if (!ctx) return;

            const labels = data.map(item => {
                const [year, month] = item.mes.split('-');
                return new Date(year, month - 1).toLocaleDateString('pt-BR', {
                    month: 'short',
                    year: 'numeric'
                });
            });
            const values = data.map(item => parseInt(item.total));

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Solicitações',
                        data: values,
                        borderColor: '#003C78',
                        backgroundColor: 'rgba(0, 60, 120, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#FF6600',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    return `Total: ${context.parsed.y} solicitações`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        }

        // Função para desenhar gráfico de pizza simples (sem bibliotecas externas)
        function drawPieChart(canvasId, data, title) {
            const canvas = document.getElementById(canvasId);
            const ctx = canvas.getContext('2d');
            const centerX = canvas.width / 2;
            const centerY = canvas.height / 2;
            const radius = Math.min(centerX, centerY) - 40;

            // Cores para o gráfico
            const colors = ['#003C78', '#FF6600', '#28a745', '#ffc107', '#dc3545', '#6c757d'];

            let total = data.reduce((sum, item) => sum + parseInt(item.total), 0);
            let currentAngle = 0;

            // Limpar canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);

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

                // Legenda
                const legendY = 20 + (index * 20);
                ctx.fillStyle = colors[index % colors.length];
                ctx.fillRect(10, legendY, 15, 15);
                ctx.fillStyle = '#333';
                ctx.font = '12px Arial';
                ctx.fillText(`${item.nome_tipo}: ${item.total}`, 30, legendY + 12);

                currentAngle += sliceAngle;
            });

            // Título
            ctx.fillStyle = '#333';
            ctx.font = 'bold 16px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(title, centerX, 20);
        }

        // Função para desenhar gráfico de linha simples
        function drawLineChart(canvasId, data, title) {
            const canvas = document.getElementById(canvasId);
            const ctx = canvas.getContext('2d');
            const padding = 40;
            const chartWidth = canvas.width - (padding * 2);
            const chartHeight = canvas.height - (padding * 2);

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (data.length === 0) {
                ctx.fillStyle = '#666';
                ctx.font = '14px Arial';
                ctx.textAlign = 'center';
                ctx.fillText('Sem dados suficientes', canvas.width / 2, canvas.height / 2);
                return;
            }

            const maxValue = Math.max(...data.map(item => parseInt(item.total)));
            const stepX = chartWidth / (data.length - 1);
            const stepY = chartHeight / maxValue;

            // Desenhar eixos
            ctx.strokeStyle = '#ddd';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(padding, padding);
            ctx.lineTo(padding, canvas.height - padding);
            ctx.lineTo(canvas.width - padding, canvas.height - padding);
            ctx.stroke();

            // Desenhar linha
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
                ctx.arc(x, y, 4, 0, 2 * Math.PI);
                ctx.fill();
                ctx.restore();

                // Labels no eixo X
                ctx.fillStyle = '#666';
                ctx.font = '10px Arial';
                ctx.textAlign = 'center';
                ctx.fillText(item.mes.substr(5), x, canvas.height - 15);

                // Valores
                ctx.fillText(item.total, x, y - 10);
            });

            ctx.stroke();

            // Título
            ctx.fillStyle = '#333';
            ctx.font = 'bold 16px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(title, canvas.width / 2, 20);
        }
    </script>
</body>

</html>