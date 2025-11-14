/*
===============================================================================
SISTEMA DE GERENCIAMENTO DE TI E MANUTENÇÃO - SENAI ALAGOINHAS
===============================================================================
Script de criação do banco de dados completo - VERSÃO OTIMIZADA
Desenvolvido para o Hackathon SENAI Alagoinhas 2025

INSTRUÇÕES DE INSTALAÇÃO:
1. Abra o phpMyAdmin no XAMPP (http://localhost/phpmyadmin)
2. Crie um novo banco de dados chamado: senai_manutencao
3. Selecione o banco criado e importe este arquivo SQL
4. Configure as credenciais no arquivo config/db.php

USUÁRIO ADMINISTRADOR PADRÃO:
- Matrícula: admin
- Senha: admin123

PORTA MYSQL: 3306 (padrão XAMPP)

===============================================================================
*/

-- Remover banco se existir (apenas para desenvolvimento)
DROP DATABASE IF EXISTS senai_manutencao;

-- Criação do banco de dados
CREATE DATABASE senai_manutencao 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE senai_manutencao;

-- ===========================
-- 1. TABELA SETORES
-- ===========================
CREATE TABLE setores (
    id_setor INT AUTO_INCREMENT PRIMARY KEY,
    nome_setor VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    email_setor VARCHAR(150) NULL,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setor_ativo (ativo),
    INDEX idx_setor_nome (nome_setor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================
-- 2. TABELA TIPOS_SOLICITACAO
-- ===========================
CREATE TABLE tipos_solicitacao (
    id_tipo INT AUTO_INCREMENT PRIMARY KEY,
    nome_tipo VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    cor_identificacao VARCHAR(7) DEFAULT '#003C78',
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo_ativo (ativo),
    INDEX idx_tipo_nome (nome_tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================
-- 3. TABELA USUARIOS
-- ===========================
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    matricula VARCHAR(20) NOT NULL UNIQUE,
    cargo VARCHAR(100) NOT NULL,
    email VARCHAR(150) NULL,
    setor_id INT NOT NULL,
    tipo_usuario ENUM('admin', 'solicitante') NOT NULL DEFAULT 'solicitante',
    senha_hash VARCHAR(255) NULL,
    ativo BOOLEAN DEFAULT TRUE,
    ultimo_acesso TIMESTAMP NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (setor_id) REFERENCES setores(id_setor) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_usuario_tipo (tipo_usuario),
    INDEX idx_usuario_matricula (matricula),
    INDEX idx_usuario_ativo (ativo),
    INDEX idx_usuario_setor (setor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================
-- 4. TABELA SOLICITACOES
-- ===========================
CREATE TABLE solicitacoes (
    id_solicitacao INT AUTO_INCREMENT PRIMARY KEY,
    solicitante_id INT NOT NULL,
    tipo_id INT NOT NULL,
    setor_id INT NOT NULL,
    local VARCHAR(200) NOT NULL,
    descricao TEXT NOT NULL,
    prioridade ENUM('Baixa', 'Média', 'Urgente') NOT NULL DEFAULT 'Média',
    curso VARCHAR(150) NULL,
    caminho_imagem VARCHAR(255) NULL,
    status ENUM('Aberta', 'Em andamento', 'Concluída', 'Cancelada') NOT NULL DEFAULT 'Aberta',
    comentario_admin TEXT NULL,
    solucao TEXT NULL,
    responsavel_id INT NULL,
    avaliacao TINYINT NULL CHECK (avaliacao BETWEEN 1 AND 5),
    feedback_solicitante TEXT NULL,
    data_abertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    data_conclusao TIMESTAMP NULL,
    tempo_resolucao_horas INT NULL,
    FOREIGN KEY (solicitante_id) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (tipo_id) REFERENCES tipos_solicitacao(id_tipo) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (setor_id) REFERENCES setores(id_setor) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id_usuario) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_solicitacao_status (status),
    INDEX idx_solicitacao_prioridade (prioridade),
    INDEX idx_solicitacao_tipo (tipo_id),
    INDEX idx_solicitacao_setor (setor_id),
    INDEX idx_solicitacao_responsavel (responsavel_id),
    INDEX idx_solicitacao_solicitante (solicitante_id),
    INDEX idx_solicitacao_data_abertura (data_abertura),
    INDEX idx_solicitacao_data_conclusao (data_conclusao),
    INDEX idx_solicitacao_curso (curso),
    INDEX idx_solicitacao_local (local)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================
-- 5. TABELA MOVIMENTACOES
-- ===========================
CREATE TABLE movimentacoes (
    id_mov INT AUTO_INCREMENT PRIMARY KEY,
    solicitacao_id INT NOT NULL,
    usuario_id INT NOT NULL,
    status_antigo ENUM('Aberta', 'Em andamento', 'Concluída', 'Cancelada') NULL,
    status_novo ENUM('Aberta', 'Em andamento', 'Concluída', 'Cancelada') NOT NULL,
    comentario TEXT,
    ip_address VARCHAR(45) NULL,
    data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes(id_solicitacao) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_mov_solicitacao (solicitacao_id),
    INDEX idx_mov_usuario (usuario_id),
    INDEX idx_mov_data (data_movimentacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================
-- 6. TABELA LOGS (AUDITORIA)
-- ===========================
CREATE TABLE logs (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================
-- 7. TABELA NOTIFICACOES
-- ===========================
CREATE TABLE notificacoes (
    id_notificacao INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    solicitacao_id INT NULL,
    titulo VARCHAR(200) NOT NULL,
    mensagem TEXT NOT NULL,
    tipo ENUM('info', 'sucesso', 'alerta', 'erro') DEFAULT 'info',
    lida BOOLEAN DEFAULT FALSE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_leitura TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes(id_solicitacao) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_notif_usuario (usuario_id),
    INDEX idx_notif_lida (lida),
    INDEX idx_notif_data (data_criacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================
-- INSERÇÃO DE DADOS BÁSICOS
-- ===========================

-- Inserindo setores
INSERT INTO setores (nome_setor, descricao, email_setor) VALUES 
('Tecnologia da Informação', 'Responsável pelo suporte técnico, manutenção de equipamentos e infraestrutura de TI', 'ti@senai-alagoinhas.edu.br'),
('Manutenção Predial', 'Responsável pela manutenção da infraestrutura física do prédio', 'manutencao@senai-alagoinhas.edu.br'),
('Manutenção Elétrica', 'Responsável pela manutenção do sistema elétrico', 'eletrica@senai-alagoinhas.edu.br'),
('Limpeza e Conservação', 'Responsável pela limpeza e conservação das instalações', 'limpeza@senai-alagoinhas.edu.br'),
('Segurança', 'Responsável pela segurança das instalações', 'seguranca@senai-alagoinhas.edu.br'),
('Coordenação Pedagógica', 'Coordenação de cursos e atividades pedagógicas', 'pedagogico@senai-alagoinhas.edu.br');

-- Inserindo tipos de solicitação
INSERT INTO tipos_solicitacao (nome_tipo, descricao, cor_identificacao) VALUES 
('Suporte de TI', 'Problemas com computadores, internet, sistemas e equipamentos de informática', '#0066CC'),
('Manutenção Predial', 'Problemas estruturais, vazamentos, portas, janelas e instalações', '#FF6600'),
('Manutenção Elétrica', 'Problemas elétricos, iluminação, tomadas e quadros elétricos', '#FFCC00'),
('Limpeza', 'Problemas relacionados à limpeza e conservação', '#00CC66'),
('Segurança', 'Problemas de segurança, controle de acesso e vigilância', '#CC0000'),
('Equipamentos', 'Manutenção e reparo de equipamentos diversos', '#9933CC'),
('Climatização', 'Problemas com ar-condicionado e ventilação', '#00CCCC'),
('Outros', 'Outras solicitações não categorizadas acima', '#666666');

-- Inserindo usuário administrador padrão (senha: admin123)
INSERT INTO usuarios (nome, matricula, cargo, email, setor_id, tipo_usuario, senha_hash) VALUES 
('Administrador do Sistema', 'admin', 'Administrador Geral', 'admin@senai-alagoinhas.edu.br', 1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Inserindo usuários administradores de setores
INSERT INTO usuarios (nome, matricula, cargo, email, setor_id, tipo_usuario, senha_hash) VALUES 
('Carlos Eduardo Santos', 'admin_ti', 'Coordenador de TI', 'carlos.santos@senai-alagoinhas.edu.br', 1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Roberto Silva Lima', 'admin_manut', 'Supervisor de Manutenção', 'roberto.lima@senai-alagoinhas.edu.br', 2, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Inserindo usuários solicitantes (professores e funcionários)
INSERT INTO usuarios (nome, matricula, cargo, email, setor_id, tipo_usuario) VALUES 
('Prof. João Silva Santos', '2024001', 'Professor de Informática', 'joao.silva@senai-alagoinhas.edu.br', 1, 'solicitante'),
('Prof. Maria Santos Oliveira', '2024002', 'Professora de Eletrônica', 'maria.santos@senai-alagoinhas.edu.br', 1, 'solicitante'),
('Carlos Oliveira Costa', '2024003', 'Técnico em Laboratório', 'carlos.oliveira@senai-alagoinhas.edu.br', 1, 'solicitante'),
('Ana Paula Costa Lima', '2024004', 'Coordenadora Pedagógica', 'ana.costa@senai-alagoinhas.edu.br', 6, 'solicitante'),
('Prof. Pedro Henrique Souza', '2024005', 'Professor de Mecânica', 'pedro.souza@senai-alagoinhas.edu.br', 1, 'solicitante'),
('Juliana Ferreira Alves', '2024006', 'Secretária Acadêmica', 'juliana.alves@senai-alagoinhas.edu.br', 6, 'solicitante'),
('Prof. Fernanda Lima Santos', '2024007', 'Professora de Edificações', 'fernanda.lima@senai-alagoinhas.edu.br', 1, 'solicitante'),
('Ricardo Almeida Pereira', '2024008', 'Técnico em Eletrotécnica', 'ricardo.pereira@senai-alagoinhas.edu.br', 3, 'solicitante');

-- Inserindo solicitações de exemplo
INSERT INTO solicitacoes (solicitante_id, tipo_id, setor_id, local, descricao, prioridade, status, curso, responsavel_id) VALUES 
(4, 1, 1, 'Laboratório de Informática 1', 'Computador 05 não está ligando. Ao pressionar o botão power, nenhum LED acende e não há sinal de vida. Problema pode ser na fonte de alimentação ou na placa-mãe.', 'Média', 'Aberta', 'Técnico em Informática', NULL),
(5, 2, 2, 'Sala 201 - Bloco A', 'Vazamento no teto da sala, causando goteira sobre as carteiras dos alunos. O problema se agrava em dias de chuva, impossibilitando o uso da sala.', 'Urgente', 'Em andamento', 'Técnico em Edificações', 2),
(6, 3, 3, 'Corredor Principal - 2º Andar', 'Lâmpadas queimadas no corredor principal, deixando o ambiente escuro e perigoso para circulação dos alunos no período noturno.', 'Média', 'Aberta', NULL, NULL),
(4, 1, 1, 'Laboratório de Redes', 'Internet instável no laboratório, com quedas frequentes de conexão. Afetando as aulas práticas de configuração de redes e impossibilitando acesso aos servidores remotos.', 'Urgente', 'Aberta', 'Técnico em Redes de Computadores', NULL),
(7, 7, 3, 'Sala 105 - Bloco B', 'Ar-condicionado não está funcionando. Equipamento liga mas não resfria o ambiente, tornando as aulas muito desconfortáveis no período da tarde.', 'Média', 'Aberta', 'Técnico em Automação Industrial', NULL),
(8, 6, 2, 'Oficina de Mecânica', 'Torno mecânico apresentando ruídos anormais e vibração excessiva durante operação. Necessita revisão urgente por questões de segurança.', 'Urgente', 'Em andamento', 'Técnico em Mecânica', 3),
(5, 4, 4, 'Banheiro Masculino - Térreo', 'Banheiro necessita limpeza profunda. Pias entupidas e cheiro desagradável. Situação crítica de higiene.', 'Média', 'Concluída', NULL, NULL),
(6, 5, 5, 'Portaria Principal', 'Câmera de segurança da entrada principal está com imagem tremida e sem foco. Comprometendo o monitoramento do acesso.', 'Baixa', 'Aberta', NULL, NULL);

-- Inserindo movimentações de exemplo
INSERT INTO movimentacoes (solicitacao_id, usuario_id, status_antigo, status_novo, comentario, ip_address) VALUES 
(1, 4, NULL, 'Aberta', 'Solicitação criada pelo sistema.', '127.0.0.1'),
(2, 5, NULL, 'Aberta', 'Solicitação criada pelo sistema.', '127.0.0.1'),
(2, 2, 'Aberta', 'Em andamento', 'Solicitação recebida. Técnico designado para verificar o vazamento. Previsão de início dos reparos: amanhã pela manhã.', '127.0.0.1'),
(3, 6, NULL, 'Aberta', 'Solicitação criada pelo sistema.', '127.0.0.1'),
(4, 4, NULL, 'Aberta', 'Solicitação criada pelo sistema.', '127.0.0.1'),
(5, 7, NULL, 'Aberta', 'Solicitação criada pelo sistema.', '127.0.0.1'),
(6, 8, NULL, 'Aberta', 'Solicitação criada pelo sistema.', '127.0.0.1'),
(6, 3, 'Aberta', 'Em andamento', 'Técnico avaliou o equipamento. Necessário substituir rolamentos. Peças solicitadas ao fornecedor.', '127.0.0.1'),
(7, 5, NULL, 'Aberta', 'Solicitação criada pelo sistema.', '127.0.0.1'),
(7, 1, 'Aberta', 'Em andamento', 'Equipe de limpeza acionada.', '127.0.0.1'),
(7, 1, 'Em andamento', 'Concluída', 'Limpeza profunda realizada. Problema de entupimento resolvido. Banheiro liberado para uso.', '127.0.0.1'),
(8, 6, NULL, 'Aberta', 'Solicitação criada pelo sistema.', '127.0.0.1');

-- Inserindo logs de exemplo
INSERT INTO logs (usuario_id, acao, tabela_afetada, registro_id, descricao, ip_address) VALUES 
(1, 'LOGIN', 'usuarios', 1, 'Administrador realizou login no sistema', '127.0.0.1'),
(2, 'LOGIN', 'usuarios', 2, 'Coordenador de TI realizou login no sistema', '127.0.0.1'),
(4, 'CREATE', 'solicitacoes', 1, 'Nova solicitação criada: Computador 05 não está ligando', '127.0.0.1'),
(2, 'UPDATE', 'solicitacoes', 2, 'Status alterado de Aberta para Em andamento', '127.0.0.1');

-- ===========================
-- VIEWS PARA RELATÓRIOS
-- ===========================

-- View para dashboard administrativo
CREATE VIEW vw_dashboard AS
SELECT 
    COUNT(CASE WHEN status = 'Aberta' THEN 1 END) as total_abertas,
    COUNT(CASE WHEN status = 'Em andamento' THEN 1 END) as total_em_andamento,
    COUNT(CASE WHEN status = 'Concluída' THEN 1 END) as total_concluidas,
    COUNT(CASE WHEN status = 'Cancelada' THEN 1 END) as total_canceladas,
    COUNT(CASE WHEN prioridade = 'Urgente' THEN 1 END) as total_urgentes,
    COUNT(CASE WHEN prioridade = 'Média' THEN 1 END) as total_medias,
    COUNT(CASE WHEN prioridade = 'Baixa' THEN 1 END) as total_baixas,
    COUNT(*) as total_geral,
    AVG(CASE WHEN tempo_resolucao_horas IS NOT NULL THEN tempo_resolucao_horas END) as tempo_medio_resolucao
FROM solicitacoes 
WHERE DATE(data_abertura) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY);

-- View para solicitações completas
CREATE VIEW vw_solicitacoes_completas AS
SELECT 
    s.id_solicitacao,
    s.local,
    s.descricao,
    s.prioridade,
    s.status,
    s.curso,
    s.data_abertura,
    s.data_conclusao,
    s.tempo_resolucao_horas,
    s.avaliacao,
    s.feedback_solicitante,
    u.nome as solicitante_nome,
    u.matricula as solicitante_matricula,
    u.cargo as solicitante_cargo,
    u.email as solicitante_email,
    ts.nome_tipo as tipo_solicitacao,
    ts.cor_identificacao as tipo_cor,
    st.nome_setor as setor_responsavel,
    st.email_setor as setor_email,
    r.nome as responsavel_nome,
    r.matricula as responsavel_matricula,
    s.comentario_admin,
    s.solucao
FROM solicitacoes s
INNER JOIN usuarios u ON s.solicitante_id = u.id_usuario
INNER JOIN tipos_solicitacao ts ON s.tipo_id = ts.id_tipo  
INNER JOIN setores st ON s.setor_id = st.id_setor
LEFT JOIN usuarios r ON s.responsavel_id = r.id_usuario;

-- View para estatísticas por setor
CREATE VIEW vw_estatisticas_setor AS
SELECT 
    st.nome_setor,
    COUNT(*) as total_solicitacoes,
    COUNT(CASE WHEN s.status = 'Aberta' THEN 1 END) as abertas,
    COUNT(CASE WHEN s.status = 'Em andamento' THEN 1 END) as em_andamento,
    COUNT(CASE WHEN s.status = 'Concluída' THEN 1 END) as concluidas,
    AVG(CASE WHEN s.tempo_resolucao_horas IS NOT NULL THEN s.tempo_resolucao_horas END) as tempo_medio_resolucao,
    AVG(s.avaliacao) as avaliacao_media
FROM solicitacoes s
INNER JOIN setores st ON s.setor_id = st.id_setor
GROUP BY st.id_setor, st.nome_setor;

-- View para estatísticas por tipo
CREATE VIEW vw_estatisticas_tipo AS
SELECT 
    ts.nome_tipo,
    ts.cor_identificacao,
    COUNT(*) as total_solicitacoes,
    COUNT(CASE WHEN s.prioridade = 'Urgente' THEN 1 END) as urgentes,
    COUNT(CASE WHEN s.prioridade = 'Média' THEN 1 END) as medias,
    COUNT(CASE WHEN s.prioridade = 'Baixa' THEN 1 END) as baixas,
    AVG(CASE WHEN s.tempo_resolucao_horas IS NOT NULL THEN s.tempo_resolucao_horas END) as tempo_medio_resolucao
FROM solicitacoes s
INNER JOIN tipos_solicitacao ts ON s.tipo_id = ts.id_tipo
GROUP BY ts.id_tipo, ts.nome_tipo, ts.cor_identificacao;

-- ===========================
-- TRIGGERS PARA AUDITORIA
-- ===========================

-- Trigger para calcular tempo de resolução
DELIMITER $$
CREATE TRIGGER tr_calcular_tempo_resolucao
BEFORE UPDATE ON solicitacoes
FOR EACH ROW
BEGIN
    IF NEW.status = 'Concluída' AND OLD.status != 'Concluída' THEN
        SET NEW.data_conclusao = CURRENT_TIMESTAMP;
        SET NEW.tempo_resolucao_horas = TIMESTAMPDIFF(HOUR, NEW.data_abertura, NEW.data_conclusao);
    END IF;
END$$
DELIMITER ;

-- Trigger para registrar movimentações automaticamente
DELIMITER $$
CREATE TRIGGER tr_registrar_movimentacao
AFTER UPDATE ON solicitacoes
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO movimentacoes (solicitacao_id, usuario_id, status_antigo, status_novo, comentario)
        VALUES (NEW.id_solicitacao, COALESCE(NEW.responsavel_id, 1), OLD.status, NEW.status, 'Alteração automática de status via sistema');
    END IF;
END$$
DELIMITER ;

-- Trigger para criar notificação ao solicitante
DELIMITER $$
CREATE TRIGGER tr_notificar_solicitante
AFTER UPDATE ON solicitacoes
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO notificacoes (usuario_id, solicitacao_id, titulo, mensagem, tipo)
        VALUES (
            NEW.solicitante_id, 
            NEW.id_solicitacao,
            CONCAT('Solicitação #', NEW.id_solicitacao, ' - Status Atualizado'),
            CONCAT('O status da sua solicitação foi alterado de "', OLD.status, '" para "', NEW.status, '"'),
            CASE 
                WHEN NEW.status = 'Concluída' THEN 'sucesso'
                WHEN NEW.status = 'Em andamento' THEN 'info'
                ELSE 'alerta'
            END
        );
    END IF;
END$$
DELIMITER ;

-- ===========================
-- PROCEDIMENTOS ARMAZENADOS
-- ===========================

-- Procedure para relatório por período
DELIMITER $$
CREATE PROCEDURE sp_relatorio_periodo(IN data_inicio DATE, IN data_fim DATE)
BEGIN
    SELECT 
        st.nome_setor,
        ts.nome_tipo,
        s.prioridade,
        COUNT(*) as quantidade,
        COUNT(CASE WHEN s.status = 'Concluída' THEN 1 END) as concluidas,
        COUNT(CASE WHEN s.status = 'Em andamento' THEN 1 END) as em_andamento,
        COUNT(CASE WHEN s.status = 'Aberta' THEN 1 END) as abertas,
        AVG(CASE 
            WHEN s.tempo_resolucao_horas IS NOT NULL 
            THEN s.tempo_resolucao_horas
            ELSE NULL 
        END) as tempo_medio_resolucao_horas,
        AVG(s.avaliacao) as avaliacao_media
    FROM solicitacoes s
    INNER JOIN setores st ON s.setor_id = st.id_setor
    INNER JOIN tipos_solicitacao ts ON s.tipo_id = ts.id_tipo
    WHERE DATE(s.data_abertura) BETWEEN data_inicio AND data_fim
    GROUP BY st.nome_setor, ts.nome_tipo, s.prioridade
    ORDER BY st.nome_setor, quantidade DESC;
END$$
DELIMITER ;

-- Procedure para estatísticas gerais
DELIMITER $$
CREATE PROCEDURE sp_estatisticas_gerais()
BEGIN
    SELECT 
        'Total de Solicitações' as metrica,
        COUNT(*) as valor
    FROM solicitacoes
    UNION ALL
    SELECT 
        'Solicitações Abertas' as metrica,
        COUNT(*) as valor
    FROM solicitacoes WHERE status = 'Aberta'
    UNION ALL
    SELECT 
        'Solicitações Em Andamento' as metrica,
        COUNT(*) as valor
    FROM solicitacoes WHERE status = 'Em andamento'
    UNION ALL
    SELECT 
        'Solicitações Concluídas' as metrica,
        COUNT(*) as valor
    FROM solicitacoes WHERE status = 'Concluída'
    UNION ALL
    SELECT 
        'Tempo Médio de Resolução (horas)' as metrica,
        ROUND(AVG(tempo_resolucao_horas), 2) as valor
    FROM solicitacoes WHERE tempo_resolucao_horas IS NOT NULL
    UNION ALL
    SELECT 
        'Avaliação Média' as metrica,
        ROUND(AVG(avaliacao), 2) as valor
    FROM solicitacoes WHERE avaliacao IS NOT NULL;
END$$
DELIMITER ;

-- ===========================
-- CONFIGURAÇÕES FINAIS
-- ===========================

-- Definir charset padrão
ALTER DATABASE senai_manutencao CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Mensagem de conclusão
SELECT 
    '✅ Banco de dados criado com sucesso!' as status,
    'admin / admin123' as credenciais_admin,
    '3306' as porta_mysql,
    'Sistema pronto para uso no XAMPP!' as resultado;

-- ===========================
-- FIM DO SCRIPT
-- ===========================
