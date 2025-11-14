<?php

/**
 * Model User - Gerenciamento de Usuários
 * Sistema de Gerenciamento de TI e Manutenção - SENAI Alagoinhas
 */

require_once __DIR__ . '/../config/db.php';

class User
{

    private $id;
    private $nome;
    private $matricula;
    private $cargo;
    private $setor_id;
    private $tipo_usuario;
    private $senha_hash;
    private $ativo;

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
     * @param array $data
     */
    public function fill($data)
    {
        $this->id = $data['id_usuario'] ?? null;
        $this->nome = $data['nome'] ?? null;
        $this->matricula = $data['matricula'] ?? null;
        $this->cargo = $data['cargo'] ?? null;
        $this->setor_id = $data['setor_id'] ?? null;
        $this->tipo_usuario = $data['tipo_usuario'] ?? 'solicitante';
        $this->senha_hash = $data['senha_hash'] ?? null;
        $this->ativo = $data['ativo'] ?? true;
    }

    /**
     * Busca usuário por ID
     * @param int $id
     * @return User|null
     */
    public static function find($id)
    {
        try {
            $sql = "SELECT u.*, s.nome_setor 
                    FROM usuarios u 
                    LEFT JOIN setores s ON u.setor_id = s.id_setor 
                    WHERE u.id_usuario = ?";
            $stmt = Database::execute($sql, [$id]);
            $data = $stmt->fetch();

            return $data ? new User($data) : null;
        } catch (Exception $e) {
            error_log("Erro ao buscar usuário: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca usuário por matrícula
     * @param string $matricula
     * @return User|null
     */
    public static function findByMatricula($matricula)
    {
        try {
            $sql = "SELECT u.*, s.nome_setor 
                    FROM usuarios u 
                    LEFT JOIN setores s ON u.setor_id = s.id_setor 
                    WHERE u.matricula = ? AND u.ativo = 1";
            $stmt = Database::execute($sql, [$matricula]);
            $data = $stmt->fetch();

            return $data ? new User($data) : null;
        } catch (Exception $e) {
            error_log("Erro ao buscar usuário por matrícula: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lista todos os usuários
     * @param array $filters Filtros opcionais
     * @param int $limit Limite de registros
     * @param int $offset Offset para paginação
     * @return array
     */
    public static function getAll($filters = [], $limit = 50, $offset = 0)
    {
        try {
            $where = ['1 = 1'];
            $params = [];

            if (!empty($filters['tipo_usuario'])) {
                $where[] = "u.tipo_usuario = ?";
                $params[] = $filters['tipo_usuario'];
            }

            if (isset($filters['ativo']) && $filters['ativo'] !== '') {
                $where[] = "u.ativo = ?";
                $params[] = (int)$filters['ativo'];
            }

            if (!empty($filters['setor_id'])) {
                $where[] = "u.setor_id = ?";
                $params[] = $filters['setor_id'];
            }

            if (!empty($filters['search'])) {
                $where[] = "(u.nome LIKE ? OR u.matricula LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $whereClause = implode(' AND ', $where);

            $sql = "SELECT u.*, s.nome_setor 
                    FROM usuarios u 
                    LEFT JOIN setores s ON u.setor_id = s.id_setor 
                    WHERE $whereClause 
                    ORDER BY u.nome
                    LIMIT ? OFFSET ?";

            $params[] = $limit;
            $params[] = $offset;

            $stmt = Database::execute($sql, $params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao listar usuários: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta o total de usuários com filtros
     * @param array $filters
     * @return int
     */
    public static function count($filters = [])
    {
        try {
            $where = ['1 = 1'];
            $params = [];

            if (!empty($filters['tipo_usuario'])) {
                $where[] = "u.tipo_usuario = ?";
                $params[] = $filters['tipo_usuario'];
            }

            if (isset($filters['ativo']) && $filters['ativo'] !== '') {
                $where[] = "u.ativo = ?";
                $params[] = (int)$filters['ativo'];
            }

            if (!empty($filters['setor_id'])) {
                $where[] = "u.setor_id = ?";
                $params[] = $filters['setor_id'];
            }

            if (!empty($filters['search'])) {
                $where[] = "(u.nome LIKE ? OR u.matricula LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $whereClause = implode(' AND ', $where);

            $sql = "SELECT COUNT(*) as total FROM usuarios u WHERE $whereClause";

            $stmt = Database::execute($sql, $params);
            $result = $stmt->fetch();

            return (int)$result['total'];
        } catch (Exception $e) {
            error_log("Erro ao contar usuários: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Retorna estatísticas agregadas de usuários
     * @param array $filters
     * @return array
     */
    public static function getStats($filters = [])
    {
        try {
            $where = ['1 = 1'];
            $params = [];

            if (!empty($filters['tipo_usuario'])) {
                $where[] = "u.tipo_usuario = ?";
                $params[] = $filters['tipo_usuario'];
            }

            if (isset($filters['ativo']) && $filters['ativo'] !== '') {
                $where[] = "u.ativo = ?";
                $params[] = (int)$filters['ativo'];
            }

            if (!empty($filters['setor_id'])) {
                $where[] = "u.setor_id = ?";
                $params[] = $filters['setor_id'];
            }

            if (!empty($filters['search'])) {
                $where[] = "(u.nome LIKE ? OR u.matricula LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $whereClause = implode(' AND ', $where);

            $sql = "
                SELECT
                    COUNT(*) AS total,
                    COUNT(CASE WHEN u.ativo = 1 THEN 1 END) AS ativos,
                    COUNT(CASE WHEN u.ativo = 0 THEN 1 END) AS inativos,
                    COUNT(CASE WHEN u.tipo_usuario = 'solicitante' THEN 1 END) AS solicitantes,
                    COUNT(CASE WHEN u.tipo_usuario = 'admin' THEN 1 END) AS admins,
                    COUNT(CASE 
                        WHEN YEAR(u.data_criacao) = YEAR(CURDATE()) 
                        AND MONTH(u.data_criacao) = MONTH(CURDATE())
                    THEN 1 END) AS novos_mes
                FROM usuarios u
                WHERE $whereClause
            ";

            $stmt = Database::execute($sql, $params);
            $result = $stmt->fetch() ?: [];

            return [
                'total' => (int)($result['total'] ?? 0),
                'ativos' => (int)($result['ativos'] ?? 0),
                'inativos' => (int)($result['inativos'] ?? 0),
                'solicitantes' => (int)($result['solicitantes'] ?? 0),
                'admins' => (int)($result['admins'] ?? 0),
                'novos_mes' => (int)($result['novos_mes'] ?? 0)
            ];
        } catch (Exception $e) {
            error_log("Erro ao obter estatísticas de usuários: " . $e->getMessage());
            return [
                'total' => 0,
                'ativos' => 0,
                'inativos' => 0,
                'solicitantes' => 0,
                'admins' => 0,
                'novos_mes' => 0
            ];
        }
    }

    /**
     * Verifica se matrícula já existe
     * @param string $matricula
     * @param int $excludeId ID para excluir da verificação
     * @return bool
     */
    public static function existsByMatricula($matricula, $excludeId = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE matricula = ?";
            $params = [$matricula];

            if ($excludeId) {
                $sql .= " AND id_usuario != ?";
                $params[] = $excludeId;
            }

            $stmt = Database::execute($sql, $params);
            $result = $stmt->fetch();

            return (int)$result['total'] > 0;
        } catch (Exception $e) {
            error_log("Erro ao verificar matrícula: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lista usuários ativos para select
     * @param string $tipo Tipo de usuário (opcional)
     * @return array
     */
    public static function getActiveForSelect($tipo = null)
    {
        try {
            $where = "u.ativo = 1";
            $params = [];

            if ($tipo) {
                $where .= " AND u.tipo_usuario = ?";
                $params[] = $tipo;
            }

            $sql = "SELECT u.id_usuario, u.nome, u.matricula, u.cargo
                    FROM usuarios u 
                    WHERE $where 
                    ORDER BY u.nome";

            $stmt = Database::execute($sql, $params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao buscar usuários ativos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista responsáveis técnicos (admins) agrupados por setor
     * @return array
     */
    public static function getResponsaveisPorSetor()
    {
        try {
            $sql = "SELECT u.id_usuario, u.nome, u.cargo, u.setor_id, s.nome_setor
                    FROM usuarios u
                    LEFT JOIN setores s ON u.setor_id = s.id_setor
                    WHERE u.ativo = 1 AND u.tipo_usuario = 'admin'
                    ORDER BY s.nome_setor, u.nome";

            $stmt = Database::execute($sql);
            $rows = $stmt->fetchAll();

            $grouped = [];

            foreach ($rows as $row) {
                $setorId = $row['setor_id'] ?? 0;

                if (!isset($grouped[$setorId])) {
                    $grouped[$setorId] = [
                        'setor_id' => $row['setor_id'],
                        'setor_nome' => $row['nome_setor'] ?? 'Não definido',
                        'responsaveis' => []
                    ];
                }

                $grouped[$setorId]['responsaveis'][] = [
                    'id_usuario' => (int)$row['id_usuario'],
                    'nome' => $row['nome'],
                    'cargo' => $row['cargo']
                ];
            }

            return $grouped;
        } catch (Exception $e) {
            error_log("Erro ao buscar responsáveis por setor: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Salva o usuário (create ou update)
     * @return bool
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
            error_log("Erro ao salvar usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cria novo usuário
     * @return bool
     */
    private function create()
    {
        $sql = "INSERT INTO usuarios (nome, matricula, cargo, setor_id, tipo_usuario, senha_hash, ativo) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $this->nome,
            $this->matricula,
            $this->cargo ?? '', // Corrigido: usa string vazia se cargo for null
            $this->setor_id,
            $this->tipo_usuario,
            $this->senha_hash,
            $this->ativo ?? 1 // Corrigido: usa 1 (ativo) se não definido
        ];

        $stmt = Database::execute($sql, $params);
        $this->id = Database::getConnection()->lastInsertId();

        return $stmt->rowCount() > 0;
    }


    /**
     * Atualiza usuário existente
     * @return bool
     */
    private function update()
    {
        $sql = "UPDATE usuarios 
                SET nome = ?, cargo = ?, setor_id = ?, tipo_usuario = ?, ativo = ? 
                WHERE id_usuario = ?";

        $params = [
            $this->nome,
            $this->cargo,
            $this->setor_id,
            $this->tipo_usuario,
            $this->ativo ? 1 : 0,
            $this->id
        ];

        $stmt = Database::execute($sql, $params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Atualiza senha do usuário
     * @param string $novaSenha
     * @return bool
     */
    public function updatePassword($novaSenha)
    {
        try {
            $hash = password_hash($novaSenha, PASSWORD_DEFAULT);

            $sql = "UPDATE usuarios SET senha_hash = ? WHERE id_usuario = ?";
            $stmt = Database::execute($sql, [$hash, $this->id]);

            $this->senha_hash = $hash;
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erro ao atualizar senha: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Desativa usuário (soft delete)
     * @return bool
     */
    public function deactivate()
    {
        try {
            $sql = "UPDATE usuarios SET ativo = 0 WHERE id_usuario = ?";
            $stmt = Database::execute($sql, [$this->id]);

            $this->ativo = false;
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erro ao desativar usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Valida dados do usuário
     * @return array Erros encontrados
     */
    public function validate()
    {
        $errors = [];

        if (empty($this->nome)) {
            $errors['nome'] = 'Nome é obrigatório';
        } elseif (strlen($this->nome) < 3) {
            $errors['nome'] = 'Nome deve ter pelo menos 3 caracteres';
        }

        if (empty($this->matricula)) {
            $errors['matricula'] = 'Matrícula é obrigatória';
        } else {
            // Verificar se matrícula já existe (exceto para o próprio usuário)
            $existing = self::findByMatricula($this->matricula);
            if ($existing && $existing->getId() != $this->id) {
                $errors['matricula'] = 'Esta matrícula já está em uso';
            }
        }

        if (!in_array($this->tipo_usuario, ['admin', 'solicitante'])) {
            $errors['tipo_usuario'] = 'Tipo de usuário inválido';
        }

        return $errors;
    }

    /**
     * Getters
     */
    public function getId()
    {
        return $this->id;
    }
    public function getNome()
    {
        return $this->nome;
    }
    public function getMatricula()
    {
        return $this->matricula;
    }
    public function getCargo()
    {
        return $this->cargo;
    }
    public function getSetorId()
    {
        return $this->setor_id;
    }
    public function getTipoUsuario()
    {
        return $this->tipo_usuario;
    }
    public function isAtivo()
    {
        return $this->ativo;
    }

    /**
     * Setters
     */
    public function setNome($nome)
    {
        $this->nome = sanitize($nome);
    }
    public function setMatricula($matricula)
    {
        $this->matricula = sanitize($matricula);
    }
    public function setCargo($cargo)
    {
        $this->cargo = sanitize($cargo);
    }
    public function setSetorId($setor_id)
    {
        $this->setor_id = $setor_id;
    }
    public function setTipoUsuario($tipo)
    {
        $this->tipo_usuario = $tipo;
    }
    public function setAtivo($ativo)
    {
        $this->ativo = $ativo;
    }

    /**
     * Converte para array
     * @return array
     */
    public function toArray()
    {
        return [
            'id_usuario' => $this->id,
            'nome' => $this->nome,
            'matricula' => $this->matricula,
            'cargo' => $this->cargo,
            'setor_id' => $this->setor_id,
            'tipo_usuario' => $this->tipo_usuario,
            'ativo' => $this->ativo
        ];
    }

    /**
     * Ativar/Desativar usuário (método estático)
     * @param int $id
     * @param bool $ativo
     * @return bool
     */
    public static function toggleActive($id, $ativo)
    {
        try {
            $sql = "UPDATE usuarios SET ativo = ? WHERE id_usuario = ?";
            $stmt = Database::execute($sql, [$ativo ? 1 : 0, $id]);

            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erro ao alterar status do usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar senha do usuário (método estático)
     * @param int $id
     * @param string $hash
     * @return bool
     */
    public static function updatePasswordById($id, $hash)
    {
        try {
            $sql = "UPDATE usuarios SET senha_hash = ? WHERE id_usuario = ?";
            $stmt = Database::execute($sql, [$hash, $id]);

            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erro ao atualizar senha: " . $e->getMessage());
            return false;
        }
    }
}
