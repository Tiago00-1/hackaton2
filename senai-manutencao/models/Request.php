<?php

/**
 * Model Request - Gerenciamento de Solicitações
 * Sistema de Gerenciamento de TI e Manutenção - SENAI Alagoinhas
 */

require_once __DIR__ . '/../config/db.php';

class Request
{

    private $id;
    private $solicitante_id;
    private $tipo_id;
    private $setor_id;
    private $local;
    private $descricao;
    private $prioridade;
    private $curso;
    private $caminho_imagem;
    private $status;
    private $comentario_admin;
    private $responsavel_id;
    private $data_abertura;
    private $data_atualizacao;
    private $data_conclusao;

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
        $this->id = $data['id_solicitacao'] ?? null;
        $this->solicitante_id = $data['solicitante_id'] ?? null;
        $this->tipo_id = $data['tipo_id'] ?? null;
        $this->setor_id = $data['setor_id'] ?? null;
        $this->local = $data['local'] ?? null;
        $this->descricao = $data['descricao'] ?? null;
        $this->prioridade = $data['prioridade'] ?? 'Média';
        $this->curso = $data['curso'] ?? null;
        $this->caminho_imagem = $data['caminho_imagem'] ?? null;
        $this->status = $data['status'] ?? 'Aberta';
        $this->comentario_admin = $data['comentario_admin'] ?? null;
        $this->responsavel_id = $data['responsavel_id'] ?? null;
        $this->data_abertura = $data['data_abertura'] ?? null;
        $this->data_atualizacao = $data['data_atualizacao'] ?? null;
        $this->data_conclusao = $data['data_conclusao'] ?? null;
    }

    /**
     * Busca solicitação por ID
     */
    public static function find($id)
    {
        try {
            $sql = "SELECT * FROM solicitacoes WHERE id_solicitacao = ?";
            $stmt = Database::execute($sql, [$id]);
            $data = $stmt->fetch();

            return $data ? new Request($data) : null;
        } catch (Exception $e) {
            error_log("Erro ao buscar solicitação: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca solicitação com detalhes completos
     */
    public static function findWithDetails($id)
    {
        try {
            $sql = "
                SELECT s.*, 
                       u.nome as solicitante_nome,
                       u.matricula as solicitante_matricula,
                       u.cargo as solicitante_cargo,
                       ts.nome_tipo,
                       st.nome_setor
                FROM solicitacoes s
                JOIN usuarios u ON s.solicitante_id = u.id_usuario
                JOIN tipos_solicitacao ts ON s.tipo_id = ts.id_tipo
                JOIN setores st ON s.setor_id = st.id_setor
                WHERE s.id_solicitacao = ?
            ";

            $stmt = Database::execute($sql, [$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Erro ao buscar detalhes da solicitação: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lista todas as solicitações com filtros
     */
    public static function getAll($filters = [], $limit = 20, $offset = 0)
    {
        try {
            $where = ['1 = 1'];
            $params = [];

            if (!empty($filters['solicitante_id'])) {
                $where[] = "s.solicitante_id = ?";
                $params[] = $filters['solicitante_id'];
            }

            if (!empty($filters['status'])) {
                $where[] = "s.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['prioridade'])) {
                $where[] = "s.prioridade = ?";
                $params[] = $filters['prioridade'];
            }

            if (!empty($filters['tipo_id'])) {
                $where[] = "s.tipo_id = ?";
                $params[] = $filters['tipo_id'];
            }

            if (!empty($filters['setor_id'])) {
                $where[] = "s.setor_id = ?";
                $params[] = $filters['setor_id'];
            }

            if (!empty($filters['data_inicio'])) {
                $where[] = "DATE(s.data_abertura) >= ?";
                $params[] = $filters['data_inicio'];
            }

            if (!empty($filters['data_fim'])) {
                $where[] = "DATE(s.data_abertura) <= ?";
                $params[] = $filters['data_fim'];
            }

            $whereClause = implode(' AND ', $where);

            $sql = "
                SELECT s.*, 
                       u.nome as solicitante_nome,
                       u.matricula as solicitante_matricula,
                       ts.nome_tipo,
                       st.nome_setor
                FROM solicitacoes s
                JOIN usuarios u ON s.solicitante_id = u.id_usuario
                JOIN tipos_solicitacao ts ON s.tipo_id = ts.id_tipo
                JOIN setores st ON s.setor_id = st.id_setor
                WHERE $whereClause
                ORDER BY s.data_abertura DESC
                LIMIT ? OFFSET ?
            ";

            $params[] = $limit;
            $params[] = $offset;

            $stmt = Database::execute($sql, $params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao listar solicitações: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Alias para getAll() - mantido para compatibilidade
     */
    public static function all($filters = [], $limit = 20, $offset = 0)
    {
        return self::getAll($filters, $limit, $offset);
    }

    /**
     * Conta solicitações com filtros
     */
    public static function count($filters = [])
    {
        try {
            $where = ['1 = 1'];
            $params = [];

            if (!empty($filters['solicitante_id'])) {
                $where[] = "solicitante_id = ?";
                $params[] = $filters['solicitante_id'];
            }

            if (!empty($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['prioridade'])) {
                $where[] = "prioridade = ?";
                $params[] = $filters['prioridade'];
            }

            if (!empty($filters['tipo_id'])) {
                $where[] = "tipo_id = ?";
                $params[] = $filters['tipo_id'];
            }

            if (!empty($filters['setor_id'])) {
                $where[] = "setor_id = ?";
                $params[] = $filters['setor_id'];
            }

            if (!empty($filters['data_inicio'])) {
                $where[] = "DATE(data_abertura) >= ?";
                $params[] = $filters['data_inicio'];
            }

            if (!empty($filters['data_fim'])) {
                $where[] = "DATE(data_abertura) <= ?";
                $params[] = $filters['data_fim'];
            }

            $whereClause = implode(' AND ', $where);

            $sql = "SELECT COUNT(*) as total FROM solicitacoes WHERE $whereClause";

            $stmt = Database::execute($sql, $params);
            $result = $stmt->fetch();

            return (int)$result['total'];
        } catch (Exception $e) {
            error_log("Erro ao contar solicitações: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtém solicitações recentes
     */
    public static function getRecent($limit = 10)
    {
        try {
            $sql = "
                SELECT s.*, 
                       u.nome as solicitante_nome,
                       ts.nome_tipo,
                       st.nome_setor
                FROM solicitacoes s
                JOIN usuarios u ON s.solicitante_id = u.id_usuario
                JOIN tipos_solicitacao ts ON s.tipo_id = ts.id_tipo
                JOIN setores st ON s.setor_id = st.id_setor
                ORDER BY s.data_abertura DESC
                LIMIT ?
            ";

            $stmt = Database::execute($sql, [$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao buscar solicitações recentes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém solicitações para relatório
     */
    public static function getForReport($filters = [], $limit = 1000)
    {
        try {
            $where = ['1 = 1'];
            $params = [];

            if (!empty($filters['data_inicio'])) {
                $where[] = "DATE(s.data_abertura) >= ?";
                $params[] = $filters['data_inicio'];
            }

            if (!empty($filters['data_fim'])) {
                $where[] = "DATE(s.data_abertura) <= ?";
                $params[] = $filters['data_fim'];
            }

            if (!empty($filters['setor_id'])) {
                $where[] = "s.setor_id = ?";
                $params[] = $filters['setor_id'];
            }

            if (!empty($filters['tipo_id'])) {
                $where[] = "s.tipo_id = ?";
                $params[] = $filters['tipo_id'];
            }

            if (!empty($filters['status'])) {
                $where[] = "s.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['prioridade'])) {
                $where[] = "s.prioridade = ?";
                $params[] = $filters['prioridade'];
            }

            $whereClause = implode(' AND ', $where);

            $sql = "
                SELECT s.*, 
                       u.nome as solicitante_nome,
                       u.matricula as solicitante_matricula,
                       ts.nome_tipo,
                       st.nome_setor as setor_nome,
                       (SELECT comentario FROM movimentacoes 
                        WHERE solicitacao_id = s.id_solicitacao 
                        ORDER BY data_movimentacao DESC LIMIT 1) as ultimo_comentario
                FROM solicitacoes s
                JOIN usuarios u ON s.solicitante_id = u.id_usuario
                JOIN tipos_solicitacao ts ON s.tipo_id = ts.id_tipo
                JOIN setores st ON s.setor_id = st.id_setor
                WHERE $whereClause
                ORDER BY s.data_abertura DESC
                LIMIT ?
            ";

            $params[] = $limit;

            $stmt = Database::execute($sql, $params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao buscar solicitações para relatório: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém movimentações de uma solicitação
     */
    public static function getMovements($requestId)
    {
        try {
            $sql = "
                SELECT m.*, 
                       u.nome as usuario_nome,
                       u.matricula as usuario_matricula,
                       CONCAT(
                           CASE 
                               WHEN m.status_antigo IS NULL THEN 'Solicitação criada'
                               WHEN m.status_antigo = m.status_novo THEN 'Comentário adicionado'
                               ELSE CONCAT('Status alterado de ', m.status_antigo, ' para ', m.status_novo)
                           END
                       ) as acao,
                       COALESCE(m.comentario, '') as observacoes,
                       m.status_novo as status,
                       m.data_movimentacao as data_movimento
                FROM movimentacoes m
                JOIN usuarios u ON m.usuario_id = u.id_usuario
                WHERE m.solicitacao_id = ?
                ORDER BY m.data_movimentacao DESC
            ";

            $stmt = Database::execute($sql, [$requestId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao buscar movimentações: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Salva a solicitação
     */
    public function save()
    {
        try {
            if ($this->id) {
                return $this->update();
            } else {
                return $this->create();
            }
        } catch (Exception $e) {
            error_log("Erro ao salvar solicitação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cria nova solicitação
     */
    private function create()
    {
        $sql = "
            INSERT INTO solicitacoes 
            (solicitante_id, tipo_id, setor_id, local, descricao, prioridade, curso, caminho_imagem, status, responsavel_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $params = [
            $this->solicitante_id,
            $this->tipo_id,
            $this->setor_id,
            $this->local,
            $this->descricao,
            $this->prioridade,
            $this->curso,
            $this->caminho_imagem,
            $this->status,
            $this->responsavel_id
        ];

        $stmt = Database::execute($sql, $params);
        $this->id = Database::getConnection()->lastInsertId();

        return $stmt->rowCount() > 0;
    }

    /**
     * Atualiza solicitação existente
     */
    private function update()
    {
        $sql = "
            UPDATE solicitacoes 
            SET local = ?, descricao = ?, prioridade = ?, curso = ?, status = ?, comentario_admin = ?, responsavel_id = ?
            WHERE id_solicitacao = ?
        ";

        $params = [
            $this->local,
            $this->descricao,
            $this->prioridade,
            $this->curso,
            $this->status,
            $this->comentario_admin,
            $this->responsavel_id,
            $this->id
        ];

        $stmt = Database::execute($sql, $params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Atualiza status da solicitação
     */
    public function updateStatus($newStatus, $comentario = '', $userId = null)
    {
        try {
            Database::beginTransaction();

            $oldStatus = $this->status;

            // Atualizar status na solicitação
            $sql = "UPDATE solicitacoes SET status = ?, comentario_admin = ?";
            $params = [$newStatus, $comentario];

            if ($newStatus === 'Concluída') {
                $sql .= ", data_conclusao = CURRENT_TIMESTAMP";
            }

            $sql .= " WHERE id_solicitacao = ?";
            $params[] = $this->id;

            Database::execute($sql, $params);

            // Registrar movimentação
            if ($userId) {
                $movSql = "
                    INSERT INTO movimentacoes 
                    (solicitacao_id, usuario_id, status_antigo, status_novo, comentario)
                    VALUES (?, ?, ?, ?, ?)
                ";

                Database::execute($movSql, [
                    $this->id,
                    $userId,
                    $oldStatus,
                    $newStatus,
                    $comentario
                ]);
            }

            Database::commit();

            $this->status = $newStatus;
            $this->comentario_admin = $comentario;

            return true;
        } catch (Exception $e) {
            Database::rollback();
            error_log("Erro ao atualizar status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Adiciona comentário à solicitação
     */
    public function addComment($comentario, $userId)
    {
        try {
            // Registrar movimentação
            $sql = "
                INSERT INTO movimentacoes 
                (solicitacao_id, usuario_id, status_antigo, status_novo, comentario)
                VALUES (?, ?, ?, ?, ?)
            ";

            $stmt = Database::execute($sql, [
                $this->id,
                $userId,
                $this->status,
                $this->status,
                $comentario
            ]);

            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erro ao adicionar comentário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui a solicitação
     */
    public function delete()
    {
        try {
            if ($this->status !== 'Aberta') {
                throw new Exception('Só é possível excluir solicitações com status Aberta');
            }

            // Excluir imagem se existir
            if ($this->caminho_imagem && file_exists(UPLOAD_PATH . $this->caminho_imagem)) {
                unlink(UPLOAD_PATH . $this->caminho_imagem);
            }

            // Excluir do banco (movimentações são excluídas em cascata)
            $sql = "DELETE FROM solicitacoes WHERE id_solicitacao = ?";
            $stmt = Database::execute($sql, [$this->id]);

            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erro ao excluir solicitação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Valida dados da solicitação
     */
    public function validate()
    {
        $errors = [];

        if (empty($this->solicitante_id)) {
            $errors['solicitante_id'] = 'Solicitante é obrigatório';
        }

        if (empty($this->tipo_id)) {
            $errors['tipo_id'] = 'Tipo de solicitação é obrigatório';
        }

        if (empty($this->setor_id)) {
            $errors['setor_id'] = 'Setor responsável é obrigatório';
        }

        if (empty($this->local)) {
            $errors['local'] = 'Local é obrigatório';
        }

        if (empty($this->descricao)) {
            $errors['descricao'] = 'Descrição é obrigatória';
        } elseif (strlen($this->descricao) < 10) {
            $errors['descricao'] = 'Descrição deve ter pelo menos 10 caracteres';
        }

        if (empty($this->curso)) {
            $errors['curso'] = 'Curso/Turma é obrigatório';
        }

        if (!in_array($this->prioridade, ['Baixa', 'Média', 'Urgente'])) {
            $errors['prioridade'] = 'Prioridade inválida';
        }

        if (!in_array($this->status, ['Aberta', 'Em andamento', 'Concluída'])) {
            $errors['status'] = 'Status inválido';
        }

        if (empty($this->responsavel_id)) {
            $errors['responsavel_id'] = 'Responsável técnico é obrigatório';
        }

        return $errors;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }
    public function getSolicitanteId()
    {
        return $this->solicitante_id;
    }
    public function getTipoId()
    {
        return $this->tipo_id;
    }
    public function getSetorId()
    {
        return $this->setor_id;
    }
    public function getLocal()
    {
        return $this->local;
    }
    public function getDescricao()
    {
        return $this->descricao;
    }
    public function getPrioridade()
    {
        return $this->prioridade;
    }
    public function getCurso()
    {
        return $this->curso;
    }
    public function getCaminhoImagem()
    {
        return $this->caminho_imagem;
    }
    public function getStatus()
    {
        return $this->status;
    }
    public function getComentarioAdmin()
    {
        return $this->comentario_admin;
    }
    public function getDataAbertura()
    {
        return $this->data_abertura;
    }
    public function getDataConclusao()
    {
        return $this->data_conclusao;
    }
    public function getResponsavelId()
    {
        return $this->responsavel_id;
    }

    // Setters
    public function setLocal($local)
    {
        $this->local = sanitize($local);
    }
    public function setDescricao($descricao)
    {
        $this->descricao = sanitize($descricao);
    }
    public function setPrioridade($prioridade)
    {
        $this->prioridade = $prioridade;
    }
    public function setCurso($curso)
    {
        $this->curso = sanitize($curso);
    }
    public function setStatus($status)
    {
        $this->status = $status;
    }
    public function setComentarioAdmin($comentario)
    {
        $this->comentario_admin = sanitize($comentario);
    }
    public function setResponsavelId($responsavelId)
    {
        $this->responsavel_id = $responsavelId ? (int)$responsavelId : null;
    }

    /**
     * Converte para array
     */
    public function toArray()
    {
        return [
            'id_solicitacao' => $this->id,
            'solicitante_id' => $this->solicitante_id,
            'tipo_id' => $this->tipo_id,
            'setor_id' => $this->setor_id,
            'local' => $this->local,
            'descricao' => $this->descricao,
            'prioridade' => $this->prioridade,
            'curso' => $this->curso,
            'caminho_imagem' => $this->caminho_imagem,
            'status' => $this->status,
            'comentario_admin' => $this->comentario_admin,
            'data_abertura' => $this->data_abertura,
            'data_conclusao' => $this->data_conclusao,
            'responsavel_id' => $this->responsavel_id
        ];
    }
}
