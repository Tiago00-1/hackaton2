<?php

/**
 * Model Type - Gerenciamento de Tipos de Solicitação
 * Sistema de Gerenciamento de TI e Manutenção - SENAI Alagoinhas
 */

require_once __DIR__ . '/../config/db.php';

class Type
{

    private $id;
    private $nome_tipo;
    private $descricao;
    private $data_criacao;

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
        $this->id = $data['id_tipo'] ?? null;
        $this->nome_tipo = $data['nome_tipo'] ?? null;
        $this->descricao = $data['descricao'] ?? null;
        $this->data_criacao = $data['data_criacao'] ?? null;
    }

    /**
     * Busca tipo por ID
     */
    public static function find($id)
    {
        try {
            $sql = "SELECT * FROM tipos_solicitacao WHERE id_tipo = ?";
            $stmt = Database::execute($sql, [$id]);
            $data = $stmt->fetch();

            return $data ? new Type($data) : null;
        } catch (Exception $e) {
            error_log("Erro ao buscar tipo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lista todos os tipos
     */
    public static function getAll()
    {
        try {
            $sql = "SELECT * FROM tipos_solicitacao ORDER BY nome_tipo";
            $stmt = Database::execute($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao listar tipos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista tipos ativos
     */
    public static function getActive()
    {
        return self::getAll(); // Todos os tipos são considerados ativos
    }

    /**
     * Lista tipos para select
     */
    public static function getForSelect()
    {
        try {
            $sql = "SELECT id_tipo, nome_tipo FROM tipos_solicitacao ORDER BY nome_tipo";
            $stmt = Database::execute($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao buscar tipos para select: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta solicitações por tipo
     */
    public function countRequests($status = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM solicitacoes WHERE tipo_id = ?";
            $params = [$this->id];

            if ($status) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }

            $stmt = Database::execute($sql, $params);
            $result = $stmt->fetch();

            return (int)$result['total'];
        } catch (Exception $e) {
            error_log("Erro ao contar solicitações do tipo: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Salva o tipo
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
            error_log("Erro ao salvar tipo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cria novo tipo
     */
    private function create()
    {
        $sql = "INSERT INTO tipos_solicitacao (nome_tipo, descricao) VALUES (?, ?)";

        $params = [
            $this->nome_tipo,
            $this->descricao
        ];

        $stmt = Database::execute($sql, $params);
        $this->id = Database::getConnection()->lastInsertId();

        return $stmt->rowCount() > 0;
    }

    /**
     * Atualiza tipo existente
     */
    private function update()
    {
        $sql = "UPDATE tipos_solicitacao SET nome_tipo = ?, descricao = ? WHERE id_tipo = ?";

        $params = [
            $this->nome_tipo,
            $this->descricao,
            $this->id
        ];

        $stmt = Database::execute($sql, $params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Valida dados do tipo
     */
    public function validate()
    {
        $errors = [];

        if (empty($this->nome_tipo)) {
            $errors['nome_tipo'] = 'Nome do tipo é obrigatório';
        } elseif (strlen($this->nome_tipo) < 3) {
            $errors['nome_tipo'] = 'Nome do tipo deve ter pelo menos 3 caracteres';
        }

        return $errors;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }
    public function getNomeTipo()
    {
        return $this->nome_tipo;
    }
    public function getDescricao()
    {
        return $this->descricao;
    }
    public function getDataCriacao()
    {
        return $this->data_criacao;
    }

    // Setters
    public function setNomeTipo($nome)
    {
        $this->nome_tipo = sanitize($nome);
    }
    public function setDescricao($descricao)
    {
        $this->descricao = sanitize($descricao);
    }

    /**
     * Converte para array
     */
    public function toArray()
    {
        return [
            'id_tipo' => $this->id,
            'nome_tipo' => $this->nome_tipo,
            'descricao' => $this->descricao,
            'data_criacao' => $this->data_criacao
        ];
    }
}
