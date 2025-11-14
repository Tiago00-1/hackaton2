<?php
/**
 * Controller de Exportação - Versão Otimizada
 * Sistema de Gerenciamento de TI e Manutenção - SENAI Alagoinhas
 * 
 * Exporta relatórios em formato PDF e CSV
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/auth.php';

class ExportController
{
    /**
     * Exportar solicitações para CSV
     */
    public static function exportarCSV()
    {
        Auth::requireLogin();
        Auth::requireAdmin();

        try {
            // Obter filtros da query string
            $filtros = self::obterFiltros();
            
            // Buscar dados
            $solicitacoes = self::buscarSolicitacoes($filtros);
            
            // Configurar headers para download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="relatorio_solicitacoes_' . date('Y-m-d_His') . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Abrir output stream
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalhos
            $headers = [
                'ID',
                'Data Abertura',
                'Solicitante',
                'Matrícula',
                'Cargo',
                'Setor',
                'Tipo',
                'Local',
                'Descrição',
                'Prioridade',
                'Status',
                'Responsável',
                'Curso',
                'Data Conclusão',
                'Tempo Resolução (h)',
                'Avaliação',
                'Comentário Admin',
                'Solução'
            ];
            
            fputcsv($output, $headers, ';');
            
            // Dados
            foreach ($solicitacoes as $sol) {
                $row = [
                    $sol['id_solicitacao'],
                    date('d/m/Y H:i', strtotime($sol['data_abertura'])),
                    $sol['solicitante_nome'],
                    $sol['solicitante_matricula'],
                    $sol['solicitante_cargo'] ?? '-',
                    $sol['setor_responsavel'],
                    $sol['tipo_solicitacao'],
                    $sol['local'],
                    self::limparTexto($sol['descricao']),
                    $sol['prioridade'],
                    $sol['status'],
                    $sol['responsavel_nome'] ?? '-',
                    $sol['curso'] ?? '-',
                    $sol['data_conclusao'] ? date('d/m/Y H:i', strtotime($sol['data_conclusao'])) : '-',
                    $sol['tempo_resolucao_horas'] ?? '-',
                    $sol['avaliacao'] ?? '-',
                    self::limparTexto($sol['comentario_admin'] ?? '-'),
                    self::limparTexto($sol['solucao'] ?? '-')
                ];
                
                fputcsv($output, $row, ';');
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            error_log("Erro ao exportar CSV: " . $e->getMessage());
            http_response_code(500);
            echo "Erro ao gerar exportação CSV";
            exit;
        }
    }

    /**
     * Exportar solicitações para PDF
     */
    public static function exportarPDF()
    {
        Auth::requireLogin();
        Auth::requireAdmin();

        try {
            // Obter filtros
            $filtros = self::obterFiltros();
            
            // Buscar dados
            $solicitacoes = self::buscarSolicitacoes($filtros);
            $estatisticas = self::calcularEstatisticas($solicitacoes);
            
            // Gerar HTML para PDF
            $html = self::gerarHTMLRelatorio($solicitacoes, $estatisticas, $filtros);
            
            // Configurar headers
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="relatorio_senai_' . date('Y-m-d_His') . '.pdf"');
            
            // Gerar PDF usando biblioteca nativa do PHP ou HTML2PDF
            self::gerarPDFNativo($html);
            
        } catch (Exception $e) {
            error_log("Erro ao exportar PDF: " . $e->getMessage());
            http_response_code(500);
            echo "Erro ao gerar exportação PDF";
            exit;
        }
    }

    /**
     * Buscar solicitações com filtros
     */
    private static function buscarSolicitacoes($filtros)
    {
        $where = ['1 = 1'];
        $params = [];

        // Filtro por período
        if (!empty($filtros['data_inicio'])) {
            $where[] = "DATE(s.data_abertura) >= ?";
            $params[] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where[] = "DATE(s.data_abertura) <= ?";
            $params[] = $filtros['data_fim'];
        }

        // Filtro por tipo
        if (!empty($filtros['tipo_id'])) {
            $where[] = "s.tipo_id = ?";
            $params[] = $filtros['tipo_id'];
        }

        // Filtro por setor
        if (!empty($filtros['setor_id'])) {
            $where[] = "s.setor_id = ?";
            $params[] = $filtros['setor_id'];
        }

        // Filtro por status
        if (!empty($filtros['status'])) {
            $where[] = "s.status = ?";
            $params[] = $filtros['status'];
        }

        // Filtro por prioridade
        if (!empty($filtros['prioridade'])) {
            $where[] = "s.prioridade = ?";
            $params[] = $filtros['prioridade'];
        }

        // Filtro por local
        if (!empty($filtros['local'])) {
            $where[] = "s.local LIKE ?";
            $params[] = '%' . $filtros['local'] . '%';
        }

        // Filtro por curso
        if (!empty($filtros['curso'])) {
            $where[] = "s.curso LIKE ?";
            $params[] = '%' . $filtros['curso'] . '%';
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT 
                    s.*,
                    u.nome as solicitante_nome,
                    u.matricula as solicitante_matricula,
                    u.cargo as solicitante_cargo,
                    u.email as solicitante_email,
                    ts.nome_tipo as tipo_solicitacao,
                    st.nome_setor as setor_responsavel,
                    r.nome as responsavel_nome
                FROM solicitacoes s
                INNER JOIN usuarios u ON s.solicitante_id = u.id_usuario
                INNER JOIN tipos_solicitacao ts ON s.tipo_id = ts.id_tipo
                INNER JOIN setores st ON s.setor_id = st.id_setor
                LEFT JOIN usuarios r ON s.responsavel_id = r.id_usuario
                WHERE $whereClause
                ORDER BY s.data_abertura DESC";

        $stmt = Database::execute($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Calcular estatísticas do relatório
     */
    private static function calcularEstatisticas($solicitacoes)
    {
        $stats = [
            'total' => count($solicitacoes),
            'abertas' => 0,
            'em_andamento' => 0,
            'concluidas' => 0,
            'urgentes' => 0,
            'medias' => 0,
            'baixas' => 0,
            'tempo_medio' => 0,
            'avaliacao_media' => 0
        ];

        $tempos = [];
        $avaliacoes = [];

        foreach ($solicitacoes as $sol) {
            // Contar por status
            switch ($sol['status']) {
                case 'Aberta':
                    $stats['abertas']++;
                    break;
                case 'Em andamento':
                    $stats['em_andamento']++;
                    break;
                case 'Concluída':
                    $stats['concluidas']++;
                    break;
            }

            // Contar por prioridade
            switch ($sol['prioridade']) {
                case 'Urgente':
                    $stats['urgentes']++;
                    break;
                case 'Média':
                    $stats['medias']++;
                    break;
                case 'Baixa':
                    $stats['baixas']++;
                    break;
            }

            // Coletar tempos de resolução
            if ($sol['tempo_resolucao_horas']) {
                $tempos[] = $sol['tempo_resolucao_horas'];
            }

            // Coletar avaliações
            if ($sol['avaliacao']) {
                $avaliacoes[] = $sol['avaliacao'];
            }
        }

        // Calcular médias
        if (count($tempos) > 0) {
            $stats['tempo_medio'] = round(array_sum($tempos) / count($tempos), 1);
        }

        if (count($avaliacoes) > 0) {
            $stats['avaliacao_media'] = round(array_sum($avaliacoes) / count($avaliacoes), 1);
        }

        return $stats;
    }

    /**
     * Gerar HTML do relatório
     */
    private static function gerarHTMLRelatorio($solicitacoes, $stats, $filtros)
    {
        $dataGeracao = date('d/m/Y H:i:s');
        $periodo = '';
        
        if (!empty($filtros['data_inicio']) || !empty($filtros['data_fim'])) {
            $inicio = !empty($filtros['data_inicio']) ? date('d/m/Y', strtotime($filtros['data_inicio'])) : 'Início';
            $fim = !empty($filtros['data_fim']) ? date('d/m/Y', strtotime($filtros['data_fim'])) : 'Hoje';
            $periodo = "Período: $inicio a $fim";
        }

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <title>Relatório de Solicitações - SENAI Alagoinhas</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    font-size: 10pt;
                    line-height: 1.4;
                    color: #333;
                    margin: 20px;
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 3px solid #003C78;
                    padding-bottom: 20px;
                }
                .header h1 {
                    color: #003C78;
                    margin: 0;
                    font-size: 24pt;
                }
                .header h2 {
                    color: #FF6600;
                    margin: 5px 0;
                    font-size: 16pt;
                }
                .info {
                    margin-bottom: 20px;
                    font-size: 9pt;
                    color: #666;
                }
                .stats {
                    background: #f5f5f5;
                    padding: 15px;
                    margin-bottom: 20px;
                    border-radius: 5px;
                }
                .stats-grid {
                    display: grid;
                    grid-template-columns: repeat(4, 1fr);
                    gap: 10px;
                }
                .stat-box {
                    background: white;
                    padding: 10px;
                    border-left: 3px solid #003C78;
                    text-align: center;
                }
                .stat-box .label {
                    font-size: 8pt;
                    color: #666;
                    text-transform: uppercase;
                }
                .stat-box .value {
                    font-size: 18pt;
                    font-weight: bold;
                    color: #003C78;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                    font-size: 8pt;
                }
                th {
                    background: #003C78;
                    color: white;
                    padding: 8px 5px;
                    text-align: left;
                    font-weight: bold;
                }
                td {
                    padding: 6px 5px;
                    border-bottom: 1px solid #ddd;
                }
                tr:nth-child(even) {
                    background: #f9f9f9;
                }
                .status {
                    padding: 3px 8px;
                    border-radius: 3px;
                    font-size: 7pt;
                    font-weight: bold;
                    display: inline-block;
                }
                .status-aberta { background: #FEE; color: #C00; }
                .status-andamento { background: #FFE; color: #C60; }
                .status-concluida { background: #EFE; color: #0A0; }
                .prioridade-urgente { color: #C00; font-weight: bold; }
                .prioridade-media { color: #C60; }
                .prioridade-baixa { color: #666; }
                .footer {
                    margin-top: 30px;
                    padding-top: 10px;
                    border-top: 1px solid #ddd;
                    text-align: center;
                    font-size: 8pt;
                    color: #999;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>SENAI ALAGOINHAS</h1>
                <h2>Relatório de Solicitações de TI e Manutenção</h2>
            </div>

            <div class="info">
                <strong>Data de Geração:</strong> <?= $dataGeracao ?><br>
                <?php if ($periodo): ?>
                <strong><?= $periodo ?></strong>
                <?php endif; ?>
            </div>

            <div class="stats">
                <h3 style="margin-top: 0; color: #003C78;">Estatísticas Gerais</h3>
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="label">Total</div>
                        <div class="value"><?= $stats['total'] ?></div>
                    </div>
                    <div class="stat-box">
                        <div class="label">Abertas</div>
                        <div class="value" style="color: #C00;"><?= $stats['abertas'] ?></div>
                    </div>
                    <div class="stat-box">
                        <div class="label">Em Andamento</div>
                        <div class="value" style="color: #C60;"><?= $stats['em_andamento'] ?></div>
                    </div>
                    <div class="stat-box">
                        <div class="label">Concluídas</div>
                        <div class="value" style="color: #0A0;"><?= $stats['concluidas'] ?></div>
                    </div>
                    <div class="stat-box">
                        <div class="label">Urgentes</div>
                        <div class="value" style="color: #C00;"><?= $stats['urgentes'] ?></div>
                    </div>
                    <div class="stat-box">
                        <div class="label">Médias</div>
                        <div class="value"><?= $stats['medias'] ?></div>
                    </div>
                    <div class="stat-box">
                        <div class="label">Tempo Médio (h)</div>
                        <div class="value"><?= $stats['tempo_medio'] ?></div>
                    </div>
                    <div class="stat-box">
                        <div class="label">Avaliação Média</div>
                        <div class="value"><?= $stats['avaliacao_media'] ?></div>
                    </div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data</th>
                        <th>Solicitante</th>
                        <th>Tipo</th>
                        <th>Local</th>
                        <th>Descrição</th>
                        <th>Prior.</th>
                        <th>Status</th>
                        <th>Responsável</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitacoes as $sol): ?>
                    <tr>
                        <td><?= $sol['id_solicitacao'] ?></td>
                        <td><?= date('d/m/Y', strtotime($sol['data_abertura'])) ?></td>
                        <td><?= htmlspecialchars($sol['solicitante_nome']) ?></td>
                        <td><?= htmlspecialchars($sol['tipo_solicitacao']) ?></td>
                        <td><?= htmlspecialchars($sol['local']) ?></td>
                        <td><?= htmlspecialchars(substr($sol['descricao'], 0, 80)) . (strlen($sol['descricao']) > 80 ? '...' : '') ?></td>
                        <td class="prioridade-<?= strtolower($sol['prioridade']) ?>"><?= $sol['prioridade'] ?></td>
                        <td>
                            <span class="status status-<?= strtolower(str_replace(' ', '', $sol['status'])) ?>">
                                <?= $sol['status'] ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($sol['responsavel_nome'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="footer">
                <p>Sistema de Gerenciamento de TI e Manutenção - SENAI Alagoinhas</p>
                <p>Relatório gerado automaticamente em <?= $dataGeracao ?></p>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Gerar PDF nativo usando DomPDF ou similar
     */
    private static function gerarPDFNativo($html)
    {
        // Se tiver DomPDF instalado, usar:
        // require_once 'dompdf/autoload.inc.php';
        // $dompdf = new \Dompdf\Dompdf();
        // $dompdf->loadHtml($html);
        // $dompdf->setPaper('A4', 'landscape');
        // $dompdf->render();
        // echo $dompdf->output();

        // Alternativa: Salvar HTML e converter com wkhtmltopdf
        // ou simplesmente retornar o HTML para impressão
        
        echo $html;
    }

    /**
     * Obter filtros da query string
     */
    private static function obterFiltros()
    {
        return [
            'data_inicio' => $_GET['data_inicio'] ?? null,
            'data_fim' => $_GET['data_fim'] ?? null,
            'tipo_id' => $_GET['tipo_id'] ?? null,
            'setor_id' => $_GET['setor_id'] ?? null,
            'status' => $_GET['status'] ?? null,
            'prioridade' => $_GET['prioridade'] ?? null,
            'local' => $_GET['local'] ?? null,
            'curso' => $_GET['curso'] ?? null
        ];
    }

    /**
     * Limpar texto para CSV
     */
    private static function limparTexto($texto)
    {
        if (empty($texto)) return '';
        
        // Remover quebras de linha
        $texto = str_replace(["\r\n", "\r", "\n"], ' ', $texto);
        
        // Remover múltiplos espaços
        $texto = preg_replace('/\s+/', ' ', $texto);
        
        return trim($texto);
    }
}

// Processar requisição
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'csv':
            ExportController::exportarCSV();
            break;
        case 'pdf':
            ExportController::exportarPDF();
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação inválida']);
    }
}
?>
