/*
===============================================================================
SISTEMA DE GERENCIAMENTO DE TI E MANUTENÇÃO - SENAI ALAGOINHAS
Serviço de Envio de Email via EmailJS
===============================================================================
*/

/**
 * Serviço de Email usando EmailJS
 * Permite envio de emails sem necessidade de backend
 * 
 * CONFIGURAÇÃO:
 * 1. Criar conta em https://www.emailjs.com/
 * 2. Configurar serviço de email (Gmail, Outlook, etc)
 * 3. Criar templates de email
 * 4. Substituir as chaves abaixo pelas suas credenciais
 */

const EmailService = {
    // Configurações do EmailJS (SUBSTITUIR PELAS SUAS CHAVES)
    config: {
        serviceId: 'service_senai',      // ID do serviço EmailJS
        publicKey: 'YOUR_PUBLIC_KEY',     // Chave pública EmailJS
        templates: {
            novaSolicitacao: 'template_nova_solicitacao',
            solicitacaoConcluida: 'template_solicitacao_concluida',
            statusAtualizado: 'template_status_atualizado'
        }
    },

    // Inicializar EmailJS
    init() {
        if (typeof emailjs === 'undefined') {
            console.warn('EmailJS não carregado. Carregando biblioteca...');
            this.loadEmailJS();
        } else {
            emailjs.init(this.config.publicKey);
            console.log('✅ EmailJS inicializado com sucesso');
        }
    },

    // Carregar biblioteca EmailJS dinamicamente
    loadEmailJS() {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js';
        script.onload = () => {
            emailjs.init(this.config.publicKey);
            console.log('✅ EmailJS carregado e inicializado');
        };
        script.onerror = () => {
            console.error('❌ Erro ao carregar EmailJS');
        };
        document.head.appendChild(script);
    },

    /**
     * Enviar email de nova solicitação para o setor responsável
     * @param {Object} dados - Dados da solicitação
     */
    async enviarNovaSolicitacao(dados) {
        const templateParams = {
            to_email: dados.setor_email || 'ti@senai-alagoinhas.edu.br',
            to_name: dados.setor_nome || 'Setor Responsável',
            from_name: dados.solicitante_nome,
            from_matricula: dados.solicitante_matricula,
            from_cargo: dados.solicitante_cargo || 'Não informado',
            solicitacao_id: dados.id_solicitacao,
            tipo_solicitacao: dados.tipo_solicitacao,
            local: dados.local,
            descricao: dados.descricao,
            prioridade: dados.prioridade,
            data_abertura: new Date().toLocaleString('pt-BR'),
            link_sistema: window.location.origin + '/senai-manutencao'
        };

        try {
            const response = await emailjs.send(
                this.config.serviceId,
                this.config.templates.novaSolicitacao,
                templateParams
            );
            
            console.log('✅ Email de nova solicitação enviado:', response);
            return { success: true, response };
        } catch (error) {
            console.error('❌ Erro ao enviar email de nova solicitação:', error);
            return { success: false, error };
        }
    },

    /**
     * Enviar email de solicitação concluída para o solicitante
     * @param {Object} dados - Dados da solicitação
     */
    async enviarSolicitacaoConcluida(dados) {
        const templateParams = {
            to_email: dados.solicitante_email || 'usuario@senai-alagoinhas.edu.br',
            to_name: dados.solicitante_nome,
            solicitacao_id: dados.id_solicitacao,
            tipo_solicitacao: dados.tipo_solicitacao,
            local: dados.local,
            descricao: dados.descricao,
            solucao: dados.solucao || 'Solicitação atendida com sucesso',
            responsavel_nome: dados.responsavel_nome || 'Equipe SENAI',
            data_conclusao: new Date().toLocaleString('pt-BR'),
            link_sistema: window.location.origin + '/senai-manutencao',
            link_avaliacao: window.location.origin + '/senai-manutencao/views/solicitante/detalhes.php?id=' + dados.id_solicitacao
        };

        try {
            const response = await emailjs.send(
                this.config.serviceId,
                this.config.templates.solicitacaoConcluida,
                templateParams
            );
            
            console.log('✅ Email de conclusão enviado:', response);
            return { success: true, response };
        } catch (error) {
            console.error('❌ Erro ao enviar email de conclusão:', error);
            return { success: false, error };
        }
    },

    /**
     * Enviar email de atualização de status
     * @param {Object} dados - Dados da solicitação
     */
    async enviarStatusAtualizado(dados) {
        const templateParams = {
            to_email: dados.solicitante_email || 'usuario@senai-alagoinhas.edu.br',
            to_name: dados.solicitante_nome,
            solicitacao_id: dados.id_solicitacao,
            status_antigo: dados.status_antigo,
            status_novo: dados.status_novo,
            comentario: dados.comentario || 'Status atualizado',
            responsavel_nome: dados.responsavel_nome || 'Equipe SENAI',
            data_atualizacao: new Date().toLocaleString('pt-BR'),
            link_sistema: window.location.origin + '/senai-manutencao'
        };

        try {
            const response = await emailjs.send(
                this.config.serviceId,
                this.config.templates.statusAtualizado,
                templateParams
            );
            
            console.log('✅ Email de atualização enviado:', response);
            return { success: true, response };
        } catch (error) {
            console.error('❌ Erro ao enviar email de atualização:', error);
            return { success: false, error };
        }
    },

    /**
     * Enviar email personalizado
     * @param {string} templateId - ID do template
     * @param {Object} params - Parâmetros do template
     */
    async enviarEmail(templateId, params) {
        try {
            const response = await emailjs.send(
                this.config.serviceId,
                templateId,
                params
            );
            
            console.log('✅ Email enviado:', response);
            return { success: true, response };
        } catch (error) {
            console.error('❌ Erro ao enviar email:', error);
            return { success: false, error };
        }
    },

    /**
     * Verificar se EmailJS está configurado
     */
    isConfigured() {
        return this.config.publicKey !== 'YOUR_PUBLIC_KEY' && 
               this.config.serviceId !== 'service_senai';
    },

    /**
     * Mostrar instruções de configuração
     */
    showConfigInstructions() {
        console.group('📧 Configuração do EmailJS');
        console.log('Para ativar o envio de emails, siga os passos:');
        console.log('1. Acesse: https://www.emailjs.com/');
        console.log('2. Crie uma conta gratuita');
        console.log('3. Configure um serviço de email (Gmail, Outlook, etc)');
        console.log('4. Crie os templates de email necessários');
        console.log('5. Copie suas credenciais e atualize o arquivo email-service.js');
        console.log('');
        console.log('Templates necessários:');
        console.log('- template_nova_solicitacao: Email para setor quando nova solicitação criada');
        console.log('- template_solicitacao_concluida: Email para solicitante quando concluída');
        console.log('- template_status_atualizado: Email para solicitante quando status muda');
        console.groupEnd();
    }
};

// Inicializar quando o DOM estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        EmailService.init();
        if (!EmailService.isConfigured()) {
            EmailService.showConfigInstructions();
        }
    });
} else {
    EmailService.init();
    if (!EmailService.isConfigured()) {
        EmailService.showConfigInstructions();
    }
}

// Exportar para uso global
window.EmailService = EmailService;

/*
===============================================================================
EXEMPLO DE USO:
===============================================================================

// 1. Enviar email ao criar nova solicitação
EmailService.enviarNovaSolicitacao({
    setor_email: 'ti@senai-alagoinhas.edu.br',
    setor_nome: 'Tecnologia da Informação',
    solicitante_nome: 'João Silva',
    solicitante_matricula: '2024001',
    solicitante_cargo: 'Professor',
    id_solicitacao: 123,
    tipo_solicitacao: 'Suporte de TI',
    local: 'Laboratório 1',
    descricao: 'Computador não liga',
    prioridade: 'Urgente'
});

// 2. Enviar email ao concluir solicitação
EmailService.enviarSolicitacaoConcluida({
    solicitante_email: 'joao@senai.edu.br',
    solicitante_nome: 'João Silva',
    id_solicitacao: 123,
    tipo_solicitacao: 'Suporte de TI',
    local: 'Laboratório 1',
    descricao: 'Computador não liga',
    solucao: 'Fonte de alimentação substituída',
    responsavel_nome: 'Carlos Eduardo'
});

// 3. Enviar email ao atualizar status
EmailService.enviarStatusAtualizado({
    solicitante_email: 'joao@senai.edu.br',
    solicitante_nome: 'João Silva',
    id_solicitacao: 123,
    status_antigo: 'Aberta',
    status_novo: 'Em andamento',
    comentario: 'Técnico designado para atendimento',
    responsavel_nome: 'Carlos Eduardo'
});

===============================================================================
*/
