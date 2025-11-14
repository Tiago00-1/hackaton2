<?php

/**
 * Model Sector - Gerenciamento de Setores
 * Sistema de Gerenciamento de TI e Manutenção - SENAI Alagoinhas
 */

require_once __DIR__ . '/../config/db.php';

class Sector
{

    private $id;
    private $nome_setor;
    private $descricao;
    private $ativo;
    private $data_criacao;
    private $data_atualizacao;

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
        $this->id = $data['id_setor'] ?? null;
        $this->nome_setor = $data['nome_setor'] ?? null;
        $this->descricao = $data['descricao'] ?? null;
        $this->ativo = $data['ativo'] ?? true;
        $this->data_criacao = $data['data_criacao'] ?? null;
        $this->data_atualizacao = $data['data_atualizacao'] ?? null;
    }

    /**
     * Busca setor por ID
     */
    public static function find($id)
    {
        try {
            $sql = "SELECT * FROM setores WHERE id_setor = ?";
            $stmt = Database::execute($sql, [$id]);
            $data = $stmt->fetch();

            return $data ? new Sector($data) : null;
        } catch (Exception $e) {
            error_log("Erro ao buscar setor: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lista todos os setores
     */
    public static function getAll($activeOnly = false)
    {
        try {
            $sql = "SELECT * FROM setores";
            $params = [];

            if ($activeOnly) {
                $sql .= " WHERE ativo = 1";
            }

            $sql .= " ORDER BY nome_setor";

            $stmt = Database::execute($sql, $params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao listar setores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista setores ativos
     */
    public static function getActive()
    {
        return self::getAll(true);
    }

    /**
     * Lista setores para select
     */
    public static function getForSelect()
    {
        try {
            $sql = "SELECT id_setor, nome_setor FROM setores WHERE ativo = 1 ORDER BY nome_setor";
            $stmt = Database::execute($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao buscar setores para select: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta solicitações por setor
     */
    public function countRequests($status = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM solicitacoes WHERE setor_id = ?";
            $params = [$this->id];

            if ($status) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }

            $stmt = Database::execute($sql, $params);
            $result = $stmt->fetch();

            return (int)$result['total'];
        } catch (Exception $e) {
            error_log("Erro ao contar solicitações do setor: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Salva o setor
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
            error_log("Erro ao salvar setor: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cria novo setor
     */
    private function create()
    {
        $sql = "INSERT INTO setores (nome_setor, descricao, ativo) VALUES (?, ?, ?)";

        $params = [
            $this->nome_setor,
            $this->descricao,
            $this->ativo ? 1 : 0
        ];

        $stmt = Database::execute($sql, $params);
        $this->id = Database::getConnection()->lastInsertId();

        return $stmt->rowCount() > 0;
    }

    /**
     * Atualiza setor existente
     */
    private function update()
    {
        $sql = "UPDATE setores SET nome_setor = ?, descricao = ?, ativo = ? WHERE id_setor = ?";

        $params = [
            $this->nome_setor,
            $this->descricao,
            $this->ativo ? 1 : 0,
            $this->id
        ];

        $stmt = Database::execute($sql, $params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Valida dados do setor
     */
    public function validate()
    {
        $errors = [];

        if (empty($this->nome_setor)) {
            $errors['nome_setor'] = 'Nome do setor é obrigatório';
        } elseif (strlen($this->nome_setor) < 3) {
            $errors['nome_setor'] = 'Nome do setor deve ter pelo menos 3 caracteres';
        }

        return $errors;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }
    public function getNomeSetor()
    {
        return $this->nome_setor;
    }
    public function getDescricao()
    {
        return $this->descricao;
    }
    public function isAtivo()
    {
        return $this->ativo;
    }
    public function getDataCriacao()
    {
        return $this->data_criacao;
    }
    public function getDataAtualizacao()
    {
        return $this->data_atualizacao;
    }

    // Setters
    public function setNomeSetor($nome)
    {
        $this->nome_setor = sanitize($nome);
    }
    public function setDescricao($descricao)
    {
        $this->descricao = sanitize($descricao);
    }
    public function setAtivo($ativo)
    {
        $this->ativo = $ativo;
    }

    /**
     * Converte para array
     */
    public function toArray()
    {
        return [
            'id_setor' => $this->id,
            'nome_setor' => $this->nome_setor,
            'descricao' => $this->descricao,
            'ativo' => $this->ativo,
            'data_criacao' => $this->data_criacao,
            'data_atualizacao' => $this->data_atualizacao
        ];
    }
}
