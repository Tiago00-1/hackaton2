<?php

/**
 * Página Principal - Sistema de Gerenciamento SENAI Alagoinhas
 * Ponto de entrada do sistema com tela de seleção de acesso
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/utils/auth.php';

// Verificar se já está logado e redirecionar
if (isLoggedIn()) {
    redirectByUserType();
}

// Processar mensagens de URL
$message = '';
$messageType = 'info';

if (isset($_GET['logout'])) {
    $message = 'Logout realizado com sucesso!';
    $messageType = 'success';
} elseif (isset($_GET['timeout'])) {
    $message = 'Sua sessão expirou. Faça login novamente.';
    $messageType = 'warning';
} elseif (isset($_GET['error'])) {
    $message = 'Ocorreu um erro. Tente novamente.';
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gerenciamento - SENAI Alagoinhas</title>
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="public/css/components.css">
    <link rel="stylesheet" href="public/css/responsive.css">
    <link rel="stylesheet" href="public/css/alerts.css">
    <link rel="icon" type="image/x-icon" href="public/images/favicon.ico">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #003C78 0%, #0066CC 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
        }

        .hero-container {
            max-width: 1200px;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        .hero-content {
            text-align: center;
            color: white;
            margin-bottom: 60px;
        }

        .hero-logo {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        .hero-title {
            font-size: 48px;
            font-weight: 900;
            margin-bottom: 16px;
            letter-spacing: -1px;
            text-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .hero-subtitle {
            font-size: 24px;
            font-weight: 300;
            margin-bottom: 12px;
            opacity: 0.95;
        }

        .hero-description {
            font-size: 16px;
            opacity: 0.85;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .access-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 800px;
            margin: 0 auto;
        }

        .access-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .access-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #003C78, #FF6600);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s ease;
        }

        .access-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 50px rgba(0, 60, 120, 0.2);
        }

        .access-card:hover::before {
            transform: scaleX(1);
        }

        .access-icon {
            font-size: 64px;
            margin-bottom: 20px;
            display: inline-block;
            animation: float 3s ease-in-out infinite;
        }

        .access-card:nth-child(2) .access-icon {
            animation-delay: 1s;
        }

        .access-title {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 12px;
        }

        .access-role {
            font-size: 16px;
            font-weight: 600;
            color: #FF6600;
            margin-bottom: 8px;
        }

        .access-description {
            font-size: 14px;
            color: #6B7280;
            line-height: 1.6;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .btn-back {
            background: linear-gradient(135deg, #6B7280 0%, #4B5563 100%);
            color: white;
            font-weight: 600;
            padding: 14px 24px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            background: linear-gradient(135deg, #4B5563 0%, #374151 100%);
        }

        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 12px 16px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 20px;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .theme-toggle:hover {
            transform: scale(1.1) rotate(15deg);
            background: rgba(255, 255, 255, 0.3);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        /* Dark Mode */
        [data-theme="dark"] .hero-section {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        }

        [data-theme="dark"] .access-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        [data-theme="dark"] .access-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
        }

        [data-theme="dark"] .access-title {
            color: #ffffff;
        }

        [data-theme="dark"] .access-description {
            color: #9CA3AF;
        }

        [data-theme="dark"] .form-control {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        [data-theme="dark"] .form-label {
            color: #E5E7EB;
        }

        [data-theme="dark"] .login-form {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }

        [data-theme="dark"] .form-text small {
            color: #9CA3AF;
        }

        [data-theme="dark"] .btn-primary {
            background: linear-gradient(135deg, #0066CC 0%, #003C78 100%);
        }

        .login-form {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            margin: 0 auto;
            animation: slideInUp 0.5s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 32px;
            }

            .hero-subtitle {
                font-size: 18px;
            }

            .access-cards {
                grid-template-columns: 1fr;
            }

            .theme-toggle {
                top: 10px;
                right: 10px;
                padding: 10px 14px;
                font-size: 18px;
            }
        }
    </style>
</head>

<body>
    <!-- Botão de Tema Fixo -->
    <button class="theme-toggle" id="theme-toggle-btn" title="Alternar tema">
        🌙
    </button>

    <div class="hero-section fade-in">
        <div class="hero-container">
            <!-- Hero Content -->
            <div class="hero-content">
                <div class="hero-logo">
                    <svg width="80" height="80" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="100" height="100" rx="10" fill="#003C78" />
                        <text x="50" y="35" text-anchor="middle" fill="white" font-size="14" font-weight="bold">SENAI</text>
                        <text x="50" y="75" text-anchor="middle" fill="#FF6600" font-size="8">ALAGOINHAS</text>
                    </svg>
                </div>
                <h1 class="hero-title">Sistema de Gerenciamento</h1>
                <p class="hero-subtitle">Solicitações de TI e Manutenção</p>
                <p class="hero-description">
                    Plataforma integrada para registro, acompanhamento e resolução de solicitações de manutenção e suporte técnico no SENAI Alagoinhas
                </p>
            </div>

            <!-- Mensagens -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>" id="message-alert" style="max-width: 600px; margin: 0 auto 30px;">
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <!-- Opções de Acesso -->
            <div class="access-cards" id="access-options">
                <div class="access-card" data-user-type="solicitante">
                    <div class="access-icon">👤</div>
                    <h3 class="access-title">Solicitante</h3>
                    <p class="access-role">Professor ou Funcionário</p>
                    <p class="access-description">
                        Crie e acompanhe suas solicitações de manutenção e suporte técnico em tempo real
                    </p>
                </div>

                <div class="access-card" data-user-type="admin">
                    <div class="access-icon">⚙️</div>
                    <h3 class="access-title">Administrador</h3>
                    <p class="access-role">Gerenciar Sistema</p>
                    <p class="access-description">
                        Gerencie solicitações, visualize relatórios e administre o sistema completo
                    </p>
                </div>
            </div>

            <!-- Formulário Solicitante -->
            <form id="form-solicitante" class="login-form" style="display: none;">
                <?php echo csrfField(); ?>

                <div class="form-group">
                    <label class="form-label" for="solicitante-nome">Nome Completo</label>
                    <input
                        type="text"
                        id="solicitante-nome"
                        name="nome"
                        class="form-control"
                        placeholder="Digite seu nome completo"
                        data-validate="required|minLength:3"
                        required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="solicitante-matricula">Matrícula</label>
                    <input
                        type="text"
                        id="solicitante-matricula"
                        name="matricula"
                        class="form-control"
                        placeholder="Digite sua matrícula"
                        data-validate="required"
                        required>
                </div>

                <div class="form-text">
                    <small>💡 Se você ainda não possui cadastro, um será criado automaticamente</small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary w-100">
                        <span>Acessar Sistema</span>
                    </button>
                </div>

                <div class="form-group">
                    <button type="button" class="btn btn-back" onclick="resetForms()">
                        ← Voltar
                    </button>
                </div>
            </form>

            <!-- Formulário Administrador -->
            <form id="form-admin" class="login-form" style="display: none;">
                <?php echo csrfField(); ?>

                <div class="form-group">
                    <label class="form-label" for="admin-matricula">Matrícula</label>
                    <input
                        type="text"
                        id="admin-matricula"
                        name="matricula"
                        class="form-control"
                        placeholder="Digite sua matrícula de administrador"
                        data-validate="required"
                        required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="admin-senha">Senha</label>
                    <input
                        type="password"
                        id="admin-senha"
                        name="senha"
                        class="form-control"
                        placeholder="Digite sua senha"
                        data-validate="required|minLength:4"
                        required>
                </div>

                <div class="form-text">
                    <small>🔒 Acesso restrito para administradores do sistema</small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary w-100">
                        <span>Entrar como Admin</span>
                    </button>
                </div>

                <div class="form-group">
                    <button type="button" class="btn btn-back" onclick="resetForms()">
                        ← Voltar
                    </button>
                </div>
            </form>

            <!-- Rodapé -->
            <div style="text-align: center; margin-top: 60px; color: rgba(255, 255, 255, 0.8); font-size: 14px;">
                <p>
                    © 2025 SENAI Alagoinhas - Hackathon 2025<br>
                    <small style="opacity: 0.7;">Sistema de Gerenciamento de TI e Manutenção Interna</small>
                </p>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div id="loading-modal" class="modal">
        <div class="modal-dialog" style="max-width: 300px;">
            <div class="modal-body text-center">
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Processando...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
    <script src="public/js/email-service.js"></script>
    <script src="public/js/dark-mode.js"></script>
    <script src="public/js/main.js"></script>
    <script src="public/js/advanced.js"></script>
    <script>
        // Estado da aplicação de login
        let currentUserType = null;

        // Inicializar página
        document.addEventListener('DOMContentLoaded', function() {
            setupLoginPage();

            // Auto-ocultar mensagem após 5 segundos
            const messageAlert = document.getElementById('message-alert');
            if (messageAlert) {
                setTimeout(() => {
                    messageAlert.style.animation = 'fadeOut 0.5s ease-out';
                    setTimeout(() => messageAlert.remove(), 500);
                }, 5000);
            }
        });

        function setupLoginPage() {
            // Event listeners para seleção de tipo de usuário
            document.querySelectorAll('.access-card').forEach(card => {
                card.addEventListener('click', function() {
                    const userType = this.dataset.userType;
                    selectUserType(userType);
                });
            });

            // Event listeners para formulários
            const formSolicitante = document.getElementById('form-solicitante');
            const formAdmin = document.getElementById('form-admin');

            if (formSolicitante) {
                formSolicitante.addEventListener('submit', handleSolicitanteLogin);
            }

            if (formAdmin) {
                formAdmin.addEventListener('submit', handleAdminLogin);
            }

            // Aplicar tema salvo
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);

            // Atualizar botão de tema
            const themeBtn = document.getElementById('theme-toggle-btn');
            if (themeBtn) {
                themeBtn.textContent = savedTheme === 'dark' ? '☀️' : '🌙';
                themeBtn.addEventListener('click', toggleTheme);
            }
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);

            // Atualizar emoji do botão
            const btn = document.getElementById('theme-toggle-btn');
            if (btn) {
                btn.textContent = newTheme === 'dark' ? '☀️' : '🌙';
            }

            AppUtils.Toast.success(`Tema ${newTheme === 'dark' ? 'escuro' : 'claro'} ativado!`);
        }

        function selectUserType(userType) {
            currentUserType = userType;

            // Ocultar opções
            const options = document.getElementById('access-options');
            options.style.display = 'none';

            // Mostrar formulário correspondente
            const form = document.getElementById(`form-${userType}`);
            form.style.display = 'block';
            form.classList.add('fade-in');

            // Focar no primeiro campo
            setTimeout(() => {
                const firstInput = form.querySelector('input');
                if (firstInput) firstInput.focus();
            }, 100);
        }

        function resetForms() {
            // Ocultar formulários
            document.querySelectorAll('.login-form').forEach(form => {
                form.style.display = 'none';
                form.reset();
                AppUtils.FormValidator.clearErrors(form);
            });

            // Mostrar opções
            const options = document.getElementById('access-options');
            options.style.display = 'grid';
            options.classList.add('fade-in');

            currentUserType = null;
        }

        async function handleSolicitanteLogin(e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);

            // Validar formulário
            const errors = AppUtils.FormValidator.validate(form);
            if (Object.keys(errors).length > 0) {
                AppUtils.FormValidator.showErrors(form, errors);
                AppUtils.Toast.error('Por favor, corrija os erros no formulário.');
                return;
            }

            try {
                showLoading(true);

                const response = await AppUtils.Ajax.postForm(
                    'controllers/AuthController.php?action=login_solicitante',
                    formData
                );

                showLoading(false);

                if (response.success) {
                    AppUtils.Toast.success(response.message);

                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 1500);
                } else {
                    AppUtils.Toast.error(response.message);

                    // Focar no primeiro campo com erro
                    setTimeout(() => {
                        const firstInput = form.querySelector('input');
                        if (firstInput) firstInput.focus();
                    }, 100);
                }
            } catch (error) {
                showLoading(false);
                console.error('Erro no login:', error);
                AppUtils.Toast.error('Erro de conexão. Tente novamente.');
            }
        }

        async function handleAdminLogin(e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);

            console.log('🔐 Tentando login admin...');
            console.log('Matrícula:', formData.get('matricula'));

            // Validar formulário
            const errors = AppUtils.FormValidator.validate(form);
            if (Object.keys(errors).length > 0) {
                AppUtils.FormValidator.showErrors(form, errors);
                AppUtils.Toast.error('Por favor, corrija os erros no formulário.');
                return;
            }

            try {
                showLoading(true);

                const response = await AppUtils.Ajax.postForm(
                    'controllers/AuthController.php?action=login_admin',
                    formData
                );

                showLoading(false);

                if (response.success) {
                    AppUtils.Toast.success(response.message);

                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 1500);
                } else {
                    AppUtils.Toast.error(response.message);

                    // Limpar senha e focar
                    const senhaInput = form.querySelector('input[name="senha"]');
                    if (senhaInput) {
                        senhaInput.value = '';
                        senhaInput.focus();
                    }
                }
            } catch (error) {
                showLoading(false);
                console.error('Erro no login:', error);
                AppUtils.Toast.error('Erro de conexão. Tente novamente.');
            }
        }

        function showLoading(show) {
            const modal = document.getElementById('loading-modal');
            if (show) {
                modal.classList.add('show');
            } else {
                modal.classList.remove('show');
            }
        }

        // Atalhos de teclado
        document.addEventListener('keydown', function(e) {
            // ESC para voltar
            if (e.key === 'Escape' && currentUserType) {
                resetForms();
            }

            // Enter para continuar se não estiver em um formulário
            if (e.key === 'Enter' && !currentUserType) {
                const firstOption = document.querySelector('.login-option');
                if (firstOption) firstOption.click();
            }
        });

        // Informações do sistema para debug (apenas em desenvolvimento)
        console.log('🚀 Sistema SENAI Alagoinhas - Hackathon 2025');
        console.log('📋 Funcionalidades:');
        console.log('   • Autenticação dupla (Solicitante/Admin)');
        console.log('   • Gerenciamento de solicitações');
        console.log('   • Dashboard administrativo');
        console.log('   • Relatórios e estatísticas');
        console.log('   • Interface responsiva');
        console.log('   • Dark Mode');
        console.log('💡 Acesso Admin padrão: admin / 1234');
    </script>
</body>

</html>