<?php
/**
 * ReportController - Controlador de Relatórios
 * Sistema de Gerenciamento de TI e Manutenção - SENAI Alagoinhas
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/auth.php';
require_once __DIR__ . '/../models/Request.php';

class ReportController {
    
    /**
     * Gerar relatório geral de solicitações
     */
    public static function getGeneralReport() {
        try {
            // Verificar se é admin
            if (!isAdmin()) {
                throw new Exception('Acesso não autorizado');
            }
            
            // Parâmetros de filtro
            $filters = [
                'data_inicio' => sanitize($_GET['data_inicio'] ?? ''),
                'data_fim' => sanitize($_GET['data_fim'] ?? ''),
                'setor_id' => (int)($_GET['setor_id'] ?? 0),
                'tipo_id' => (int)($_GET['tipo_id'] ?? 0),
                'status' => sanitize($_GET['status'] ?? ''),
                'prioridade' => sanitize($_GET['prioridade'] ?? '')
            ];
            
            // Remover filtros vazios
            $filters = array_filter($filters, function($value) {
                return $value !== '' && $value !== 0;
            });
            
            // Se não há filtro de data, usar último mês
            if (empty($filters['data_inicio'])) {
                $filters['data_inicio'] = date('Y-m-d', strtotime('-30 days'));
            }
            if (empty($filters['data_fim'])) {
                $filters['data_fim'] = date('Y-m-d');
            }
            
            // Obter dados do relatório
            $reportData = self::generateReportData($filters);
            
            return [
                'success' => true,
                'report_data' => $reportData,
                'filters' => $filters,
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao gerar relatório: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao gerar relatório'
            ];
        }
    }
    
    /**
     * Gerar dados do relatório
     */
    private static function generateReportData($filters) {
        // Estatísticas gerais
        $generalStats = self::getGeneralReportStats($filters);
        
        // Distribuição por setor
        $sectorDistribution = self::getSectorDistribution($filters);
        
        // Distribuição por tipo
        $typeDistribution = self::getTypeDistribution($filters);
        
        // Distribuição por status
        $statusDistribution = self::getStatusDistribution($filters);
        
        // Distribuição por prioridade
        $priorityDistribution = self::getPriorityDistribution($filters);
        
        // Tempo médio de resolução
        $avgResolutionTime = self::getAverageResolutionTime($filters);
        
        // Performance por período
        $periodPerformance = self::getPeriodPerformance($filters);
        
        // Lista detalhada de solicitações
        $detailedRequests = Request::getForReport($filters, 1000); // Limit 1000 para relatório
        
        return [
            'general_stats' => $generalStats,
            'sector_distribution' => $sectorDistribution,
            'type_distribution' => $typeDistribution,
            'status_distribution' => $statusDistribution,
            'priority_distribution' => $priorityDistribution,
            'avg_resolution_time' => $avgResolutionTime,
            'period_performance' => $periodPerformance,
            'detailed_requests' => $detailedRequests
        ];
    }
    
    /**
     * Estatísticas gerais do relatório
     */
    private static function getGeneralReportStats($filters) {
        $whereClause = self::buildWhereClause($filters);
        
        $sql = "
            SELECT 
                COUNT(*) as total_solicitacoes,
                COUNT(CASE WHEN status = 'Aberta' THEN 1 END) as abertas,
                COUNT(CASE WHEN status = 'Em andamento' THEN 1 END) as em_andamento,
                COUNT(CASE WHEN status = 'Concluída' THEN 1 END) as concluidas,
                COUNT(CASE WHEN prioridade = 'Urgente' THEN 1 END) as urgentes,
                COUNT(CASE WHEN prioridade = 'Média' THEN 1 END) as medias,
                COUNT(CASE WHEN prioridade = 'Baixa' THEN 1 END) as baixas,
                ROUND(
                    (COUNT(CASE WHEN status = 'Concluída' THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0)), 2
                ) as taxa_conclusao
            FROM solicitacoes s
            {$whereClause['sql']}
        ";
        
        $stmt = Database::execute($sql, $whereClause['params']);
        return $stmt->fetch();
    }
    
    /**
     * Distribuição por setor
     */
    private static function getSectorDistribution($filters) {
        $whereClause = self::buildWhereClause($filters);
        
        $sql = "
            SELECT 
                st.nome_setor,
                COUNT(s.id_solicitacao) as total,
                COUNT(CASE WHEN s.status = 'Concluída' THEN 1 END) as concluidas,
                ROUND(
                    (COUNT(CASE WHEN s.status = 'Concluída' THEN 1 END) * 100.0 / NULLIF(COUNT(s.id_solicitacao), 0)), 2
                ) as taxa_conclusao
            FROM setores st
            LEFT JOIN solicitacoes s ON st.id_setor = s.setor_id
            {$whereClause['sql']}
            GROUP BY st.id_setor, st.nome_setor
            HAVING total > 0
            ORDER BY total DESC
        ";
        
        $stmt = Database::execute($sql, $whereClause['params']);
        return $stmt->fetchAll();
    }
    
    /**
     * Distribuição por tipo
     */
    private static function getTypeDistribution($filters) {
        $whereClause = self::buildWhereClause($filters);
        
        $sql = "
            SELECT 
                t.nome_tipo,
                COUNT(s.id_solicitacao) as total,
                COUNT(CASE WHEN s.status = 'Concluída' THEN 1 END) as concluidas,
                ROUND(
                    (COUNT(CASE WHEN s.status = 'Concluída' THEN 1 END) * 100.0 / NULLIF(COUNT(s.id_solicitacao), 0)), 2
                ) as taxa_conclusao
            FROM tipos_solicitacao t
            LEFT JOIN solicitacoes s ON t.id_tipo = s.tipo_id
            {$whereClause['sql']}
            GROUP BY t.id_tipo, t.nome_tipo
            HAVING total > 0
            ORDER BY total DESC
        ";
        
        $stmt = Database::execute($sql, $whereClause['params']);
        return $stmt->fetchAll();
    }
    
    /**
     * Distribuição por status
     */
    private static function getStatusDistribution($filters) {
        $whereClause = self::buildWhereClause($filters);
        
        $sql = "
            SELECT 
                status,
                COUNT(*) as total,
                ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM solicitacoes s2 {$whereClause['sql']})), 2) as percentual
            FROM solicitacoes s
            {$whereClause['sql']}
            GROUP BY status
            ORDER BY total DESC
        ";
        
        $stmt = Database::execute($sql, array_merge($whereClause['params'], $whereClause['params']));
        return $stmt->fetchAll();
    }
    
    /**
     * Distribuição por prioridade
     */
    private static function getPriorityDistribution($filters) {
        $whereClause = self::buildWhereClause($filters);
        
        $sql = "
            SELECT 
                prioridade,
                COUNT(*) as total,
                ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM solicitacoes s2 {$whereClause['sql']})), 2) as percentual
            FROM solicitacoes s
            {$whereClause['sql']}
            GROUP BY prioridade
            ORDER BY 
                CASE prioridade 
                    WHEN 'Urgente' THEN 1
                    WHEN 'Média' THEN 2
                    WHEN 'Baixa' THEN 3
                END
        ";
        
        $stmt = Database::execute($sql, array_merge($whereClause['params'], $whereClause['params']));
        return $stmt->fetchAll();
    }
    
    /**
     * Tempo médio de resolução
     */
    private static function getAverageResolutionTime($filters) {
        $whereClause = self::buildWhereClause($filters);
        $whereClause['sql'] .= " AND s.status = 'Concluída' AND s.data_conclusao IS NOT NULL";
        
        $sql = "
            SELECT 
                st.nome_setor,
                t.nome_tipo,
                COUNT(*) as total_concluidas,
                AVG(TIMESTAMPDIFF(HOUR, s.data_abertura, s.data_conclusao)) as tempo_medio_horas,
                MIN(TIMESTAMPDIFF(HOUR, s.data_abertura, s.data_conclusao)) as tempo_minimo_horas,
                MAX(TIMESTAMPDIFF(HOUR, s.data_abertura, s.data_conclusao)) as tempo_maximo_horas
            FROM solicitacoes s
            JOIN setores st ON s.setor_id = st.id_setor
            JOIN tipos_solicitacao t ON s.tipo_id = t.id_tipo
            {$whereClause['sql']}
            GROUP BY s.setor_id, st.nome_setor, s.tipo_id, t.nome_tipo
            HAVING total_concluidas > 0
            ORDER BY tempo_medio_horas DESC
        ";
        
        $stmt = Database::execute($sql, $whereClause['params']);
        return $stmt->fetchAll();
    }
    
    /**
     * Performance por período (diário)
     */
    private static function getPeriodPerformance($filters) {
        $whereClause = self::buildWhereClause($filters);
        
        $sql = "
            SELECT 
                DATE(data_abertura) as data,
                COUNT(*) as abertas,
                COUNT(CASE WHEN status = 'Concluída' THEN 1 END) as concluidas,
                COUNT(CASE WHEN prioridade = 'Urgente' THEN 1 END) as urgentes
            FROM solicitacoes s
            {$whereClause['sql']}
            GROUP BY DATE(data_abertura)
            ORDER BY data
        ";
        
        $stmt = Database::execute($sql, $whereClause['params']);
        return $stmt->fetchAll();
    }
    
    /**
     * Construir cláusula WHERE dinâmica
     */
    private static function buildWhereClause($filters) {
        $conditions = [];
        $params = [];
        
        if (!empty($filters['data_inicio'])) {
            $conditions[] = "DATE(s.data_abertura) >= ?";
            $params[] = $filters['data_inicio'];
        }
        
        if (!empty($filters['data_fim'])) {
            $conditions[] = "DATE(s.data_abertura) <= ?";
            $params[] = $filters['data_fim'];
        }
        
        if (!empty($filters['setor_id'])) {
            $conditions[] = "s.setor_id = ?";
            $params[] = $filters['setor_id'];
        }
        
        if (!empty($filters['tipo_id'])) {
            $conditions[] = "s.tipo_id = ?";
            $params[] = $filters['tipo_id'];
        }
        
        if (!empty($filters['status'])) {
            $conditions[] = "s.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['prioridade'])) {
            $conditions[] = "s.prioridade = ?";
            $params[] = $filters['prioridade'];
        }
        
        $sql = '';
        if (!empty($conditions)) {
            $sql = "WHERE " . implode(' AND ', $conditions);
        }
        
        return ['sql' => $sql, 'params' => $params];
    }
    
    /**
     * Exportar relatório para CSV
     */
    public static function exportCSV() {
        try {
            // Verificar se é admin
            if (!isAdmin()) {
                throw new Exception('Acesso não autorizado');
            }
            
            // Obter filtros
            $filters = [
                'data_inicio' => sanitize($_GET['data_inicio'] ?? ''),
                'data_fim' => sanitize($_GET['data_fim'] ?? ''),
                'setor_id' => (int)($_GET['setor_id'] ?? 0),
                'tipo_id' => (int)($_GET['tipo_id'] ?? 0),
                'status' => sanitize($_GET['status'] ?? ''),
                'prioridade' => sanitize($_GET['prioridade'] ?? '')
            ];
            
            // Remover filtros vazios
            $filters = array_filter($filters, function($value) {
                return $value !== '' && $value !== 0;
            });
            
            // Obter dados
            $requests = Request::getForReport($filters, 5000); // Limit 5000 para CSV
            
            // Configurar headers para download
            $filename = 'relatorio_solicitacoes_' . date('Y-m-d_H-i-s') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Abrir saída
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalhos
            fputcsv($output, [
                'ID',
                'Data Abertura',
                'Solicitante',
                'Matrícula',
                'Local',
                'Descrição',
                'Setor',
                'Tipo',
                'Prioridade',
                'Status',
                'Data Conclusão',
                'Tempo Resolução (horas)',
                'Último Comentário'
            ], ';');
            
            // Dados
            foreach ($requests as $request) {
                $tempoResolucao = '';
                if ($request['data_conclusao']) {
                    $inicio = new DateTime($request['data_abertura']);
                    $fim = new DateTime($request['data_conclusao']);
                    $diff = $inicio->diff($fim);
                    $tempoResolucao = ($diff->days * 24) + $diff->h + ($diff->i / 60);
                }
                
                fputcsv($output, [
                    $request['id_solicitacao'],
                    formatDate($request['data_abertura']),
                    $request['solicitante_nome'],
                    $request['solicitante_matricula'],
                    $request['local'],
                    $request['descricao'],
                    $request['setor_nome'],
                    $request['tipo_nome'],
                    $request['prioridade'],
                    $request['status'],
                    formatDate($request['data_conclusao']),
                    $tempoResolucao,
                    $request['ultimo_comentario'] ?? ''
                ], ';');
            }
            
            fclose($output);
            
            // Log da ação
            $currentUser = getCurrentUser();
            error_log("Relatório CSV exportado por {$currentUser['nome']} - $filename");
            
            exit;
            
        } catch (Exception $e) {
            error_log("Erro ao exportar CSV: " . $e->getMessage());
            header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=export_failed");
            exit;
        }
    }
    
    /**
     * Gerar relatório em JSON para gráficos
     */
    public static function getChartsData() {
        try {
            // Verificar se é admin
            if (!isAdmin()) {
                throw new Exception('Acesso não autorizado');
            }
            
            $period = sanitize($_GET['period'] ?? '30'); // 7, 30, 90 dias
            $validPeriods = ['7', '30', '90'];
            
            if (!in_array($period, $validPeriods)) {
                $period = '30';
            }
            
            // Dados para gráfico de linha (solicitações por dia)
            $timelineData = self::getTimelineData($period);
            
            // Dados para gráfico de pizza (distribuição por setor)
            $sectorPieData = self::getSectorPieData($period);
            
            // Dados para gráfico de barras (status por setor)
            $statusBarData = self::getStatusBarData($period);
            
            // Dados para gráfico de prioridade
            $priorityData = self::getPriorityChartData($period);
            
            return [
                'success' => true,
                'timeline_data' => $timelineData,
                'sector_pie_data' => $sectorPieData,
                'status_bar_data' => $statusBarData,
                'priority_data' => $priorityData,
                'period' => $period
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao obter dados dos gráficos: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar dados dos gráficos'
            ];
        }
    }
    
    /**
     * Dados para gráfico de linha temporal
     */
    private static function getTimelineData($period) {
        $sql = "
            SELECT 
                DATE(data_abertura) as data,
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'Concluída' THEN 1 END) as concluidas
            FROM solicitacoes
            WHERE data_abertura >= DATE_SUB(CURDATE(), INTERVAL $period DAY)
            GROUP BY DATE(data_abertura)
            ORDER BY data
        ";
        
        $stmt = Database::execute($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Dados para gráfico de pizza por setor
     */
    private static function getSectorPieData($period) {
        $sql = "
            SELECT 
                st.nome_setor as label,
                COUNT(s.id_solicitacao) as value
            FROM setores st
            LEFT JOIN solicitacoes s ON st.id_setor = s.setor_id
            WHERE s.data_abertura >= DATE_SUB(CURDATE(), INTERVAL $period DAY)
            GROUP BY st.id_setor, st.nome_setor
            HAVING value > 0
            ORDER BY value DESC
        ";
        
        $stmt = Database::execute($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Dados para gráfico de barras (status por setor)
     */
    private static function getStatusBarData($period) {
        $sql = "
            SELECT 
                st.nome_setor,
                COUNT(CASE WHEN s.status = 'Aberta' THEN 1 END) as abertas,
                COUNT(CASE WHEN s.status = 'Em andamento' THEN 1 END) as em_andamento,
                COUNT(CASE WHEN s.status = 'Concluída' THEN 1 END) as concluidas
            FROM setores st
            LEFT JOIN solicitacoes s ON st.id_setor = s.setor_id
            WHERE s.data_abertura >= DATE_SUB(CURDATE(), INTERVAL $period DAY)
            GROUP BY st.id_setor, st.nome_setor
            HAVING (abertas + em_andamento + concluidas) > 0
            ORDER BY (abertas + em_andamento + concluidas) DESC
        ";
        
        $stmt = Database::execute($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Dados para gráfico de prioridade
     */
    private static function getPriorityChartData($period) {
        $sql = "
            SELECT 
                prioridade as label,
                COUNT(*) as value,
                COUNT(CASE WHEN status = 'Concluída' THEN 1 END) as concluidas
            FROM solicitacoes
            WHERE data_abertura >= DATE_SUB(CURDATE(), INTERVAL $period DAY)
            GROUP BY prioridade
            ORDER BY 
                CASE prioridade 
                    WHEN 'Urgente' THEN 1
                    WHEN 'Média' THEN 2
                    WHEN 'Baixa' THEN 3
                END
        ";
        
        $stmt = Database::execute($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Processar requisições AJAX
     */
    public static function handleAjax() {
        $action = $_GET['action'] ?? '';
        $response = [];
        
        switch ($action) {
            case 'general':
                $response = self::getGeneralReport();
                break;
                
            case 'charts':
                $response = self::getChartsData();
                break;
                
            case 'export_csv':
                self::exportCSV(); // Já faz output e exit
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
    ReportController::handleAjax();
}

