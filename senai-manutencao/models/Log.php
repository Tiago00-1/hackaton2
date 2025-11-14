<?php

/**
 * Model Log - Gerenciamento de Logs do Sistema
 * Sistema de Gerenciamento de TI e Manutenção - SENAI Alagoinhas
 */

require_once __DIR__ . '/../config/db.php';

class Log
{

    private $id;
    private $solicitacao_id;
    private $usuario_id;
    private $status_antigo;
    private $status_novo;
    private $comentario;
    private $data_movimentacao;

    /**
     * Construtor da classe
     */
    public function __construct($data = [])
    {
        if (!empty($data)) {
            $this->fill($data);
        }
    }

    /**
     * Preenche o objeto com dados
     */
    public function fill($data)
    {
        $this->id = $data['id_mov'] ?? null;
        $this->solicitacao_id = $data['solicitacao_id'] ?? null;
        $this->usuario_id = $data['usuario_id'] ?? null;
        $this->status_antigo = $data['status_antigo'] ?? null;
        $this->status_novo = $data['status_novo'] ?? null;
        $this->comentario = $data['comentario'] ?? null;
        $this->data_movimentacao = $data['data_movimentacao'] ?? null;
    }

    /**
     * Busca log por ID
     */
    public static function find($id)
    {
        try {
            $sql = "SELECT * FROM movimentacoes WHERE id_mov = ?";
            $stmt = Database::execute($sql, [$id]);
            $data = $stmt->fetch();

            return $data ? new Log($data) : null;
        } catch (Exception $e) {
            error_log("Erro ao buscar log: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lista logs de uma solicitação
     */
    public static function getBySolicitacao($solicitacaoId)
    {
        try {
            $sql = "
                SELECT m.*, u.nome as usuario_nome
                FROM movimentacoes m
                JOIN usuarios u ON m.usuario_id = u.id_usuario
                WHERE m.solicitacao_id = ?
                ORDER BY m.data_movimentacao DESC
            ";

            $stmt = Database::execute($sql, [$solicitacaoId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao buscar logs da solicitação: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista logs de um usuário
     */
    public static function getByUsuario($usuarioId, $limit = 50)
    {
        try {
            $sql = "
                SELECT m.*, s.local, s.descricao
                FROM movimentacoes m
                JOIN solicitacoes s ON m.solicitacao_id = s.id_solicitacao
                WHERE m.usuario_id = ?
                ORDER BY m.data_movimentacao DESC
                LIMIT ?
            ";

            $stmt = Database::execute($sql, [$usuarioId, $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao buscar logs do usuário: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista logs recentes do sistema
     */
    public static function getRecent($limit = 100)
    {
        try {
            $sql = "
                SELECT m.*, 
                       u.nome as usuario_nome,
                       s.id_solicitacao,
                       s.local,
                       sol_user.nome as solicitante_nome
                FROM movimentacoes m
                JOIN usuarios u ON m.usuario_id = u.id_usuario
                JOIN solicitacoes s ON m.solicitacao_id = s.id_solicitacao
                JOIN usuarios sol_user ON s.solicitante_id = sol_user.id_usuario
                ORDER BY m.data_movimentacao DESC
                LIMIT ?
            ";

            $stmt = Database::execute($sql, [$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao buscar logs recentes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Registra nova movimentação
     */
    public static function registrar($solicitacaoId, $usuarioId, $statusAntigo, $statusNovo, $comentario = '')
    {
        try {
            $sql = "
                INSERT INTO movimentacoes 
                (solicitacao_id, usuario_id, status_antigo, status_novo, comentario)
                VALUES (?, ?, ?, ?, ?)
            ";

            $stmt = Database::execute($sql, [
                $solicitacaoId,
                $usuarioId,
                $statusAntigo,
                $statusNovo,
                $comentario
            ]);

            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erro ao registrar log: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registra log geral do sistema (tabela logs)
     * @param array $data
     * @return bool
     */
    public static function create(array $data)
    {
        try {
            if (!class_exists('Debug')) {
                require_once __DIR__ . '/../utils/debug.php';
            }
            $usuarioId = $data['usuario_id'] ?? null;

            // Verificar se tabela de logs existe; se não existir, evitar erro 500
            if (!self::ensureLogsTableExists()) {
                Debug::log('Logs table missing and could not be created', ['data' => $data]);
                return false;
            }

            if (!$usuarioId && !empty($data['usuario_matricula'])) {
                $stmt = Database::execute(
                    "SELECT id_usuario FROM usuarios WHERE matricula = ? LIMIT 1",
                    [$data['usuario_matricula']]
                );
                $usuarioId = $stmt->fetchColumn() ?: null;
            }

            $sql = "
                INSERT INTO logs
                    (usuario_id, acao, tabela_afetada, registro_id, descricao, ip_address, user_agent)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?)
            ";

            $stmt = Database::execute($sql, [
                $usuarioId,
                $data['acao'] ?? 'Ação não especificada',
                $data['tabela_afetada'] ?? null,
                $data['registro_id'] ?? null,
                $data['detalhes'] ?? ($data['descricao'] ?? null),
                $data['ip'] ?? $data['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? null),
                $data['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? null)
            ]);

            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erro ao registrar log geral: " . $e->getMessage());
            if (class_exists('Debug')) {
                Debug::log('Log::create exception', ['error' => $e->getMessage(), 'data' => $data]);
            }
            return false;
        }
    }

    /**
     * Verifica se a tabela de logs existe e tenta criá-la se necessário
     * @return bool
     */
    private static function ensureLogsTableExists()
    {
        try {
            $sql = "SHOW TABLES LIKE 'logs'";
            $stmt = Database::execute($sql);
            if ($stmt->fetchColumn()) {
                return true;
            }

            $createSql = "
                CREATE TABLE IF NOT EXISTS logs (
                    id_log INT AUTO_INCREMENT PRIMARY KEY,
                    usuario_id INT NULL,
                    acao VARCHAR(100) NOT NULL,
                    tabela_afetada VARCHAR(50) NULL,
                    registro_id INT NULL,
                    descricao TEXT,
                    ip_address VARCHAR(45) NULL,
                    user_agent TEXT NULL,
                    data_log TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario) ON DELETE SET NULL ON UPDATE CASCADE,
                    INDEX idx_log_usuario (usuario_id),
                    INDEX idx_log_acao (acao),
                    INDEX idx_log_data (data_log),
                    INDEX idx_log_tabela (tabela_afetada)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";

            Database::execute($createSql);
            return true;
        } catch (Exception $e) {
            error_log("Erro ao garantir existencia da tabela de logs: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Estatísticas de atividades
     */
    public static function getActivityStats($periodo = 30)
    {
        try {
            $sql = "
                SELECT 
                    DATE(data_movimentacao) as data,
                    COUNT(*) as total_atividades,
                    COUNT(DISTINCT solicitacao_id) as solicitacoes_movimentadas,
                    COUNT(DISTINCT usuario_id) as usuarios_ativos
                FROM movimentacoes
                WHERE data_movimentacao >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(data_movimentacao)
                ORDER BY data DESC
            ";

            $stmt = Database::execute($sql, [$periodo]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao buscar estatísticas de atividade: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Usuários mais ativos
     */
    public static function getMostActiveUsers($periodo = 30, $limit = 10)
    {
        try {
            $sql = "
                SELECT 
                    u.nome,
                    u.matricula,
                    u.tipo_usuario,
                    COUNT(m.id_mov) as total_atividades,
                    COUNT(DISTINCT m.solicitacao_id) as solicitacoes_trabalhadas
                FROM movimentacoes m
                JOIN usuarios u ON m.usuario_id = u.id_usuario
                WHERE m.data_movimentacao >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY u.id_usuario, u.nome, u.matricula, u.tipo_usuario
                ORDER BY total_atividades DESC
                LIMIT ?
            ";

            $stmt = Database::execute($sql, [$periodo, $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao buscar usuários mais ativos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Limpar logs antigos
     */
    public static function cleanup($diasParaManter = 365)
    {
        try {
            $sql = "DELETE FROM movimentacoes WHERE data_movimentacao < DATE_SUB(CURDATE(), INTERVAL ? DAY)";
            $stmt = Database::execute($sql, [$diasParaManter]);

            $deletedCount = $stmt->rowCount();
            error_log("Log cleanup: $deletedCount registros antigos removidos");

            return $deletedCount;
        } catch (Exception $e) {
            error_log("Erro ao limpar logs antigos: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Salva o log
     */
    public function save()
    {
        try {
            $sql = "
                INSERT INTO movimentacoes 
                (solicitacao_id, usuario_id, status_antigo, status_novo, comentario)
                VALUES (?, ?, ?, ?, ?)
            ";

            $stmt = Database::execute($sql, [
                $this->solicitacao_id,
                $this->usuario_id,
                $this->status_antigo,
                $this->status_novo,
                $this->comentario
            ]);

            $this->id = Database::getConnection()->lastInsertId();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erro ao salvar log: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Valida dados do log
     */
    public function validate()
    {
        $errors = [];

        if (empty($this->solicitacao_id)) {
            $errors['solicitacao_id'] = 'Solicitação é obrigatória';
        }

        if (empty($this->usuario_id)) {
            $errors['usuario_id'] = 'Usuário é obrigatório';
        }

        if (empty($this->status_novo)) {
            $errors['status_novo'] = 'Status novo é obrigatório';
        }

        return $errors;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }
    public function getSolicitacaoId()
    {
        return $this->solicitacao_id;
    }
    public function getUsuarioId()
    {
        return $this->usuario_id;
    }
    public function getStatusAntigo()
    {
        return $this->status_antigo;
    }
    public function getStatusNovo()
    {
        return $this->status_novo;
    }
    public function getComentario()
    {
        return $this->comentario;
    }
    public function getDataMovimentacao()
    {
        return $this->data_movimentacao;
    }

    // Setters
    public function setSolicitacaoId($id)
    {
        $this->solicitacao_id = $id;
    }
    public function setUsuarioId($id)
    {
        $this->usuario_id = $id;
    }
    public function setStatusAntigo($status)
    {
        $this->status_antigo = $status;
    }
    public function setStatusNovo($status)
    {
        $this->status_novo = $status;
    }
    public function setComentario($comentario)
    {
        $this->comentario = sanitize($comentario);
    }

    /**
     * Converte para array
     */
    public function toArray()
    {
        return [
            'id_mov' => $this->id,
            'solicitacao_id' => $this->solicitacao_id,
            'usuario_id' => $this->usuario_id,
            'status_antigo' => $this->status_antigo,
            'status_novo' => $this->status_novo,
            'comentario' => $this->comentario,
            'data_movimentacao' => $this->data_movimentacao
        ];
    }
}
