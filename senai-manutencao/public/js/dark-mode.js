/*
===============================================================================
SISTEMA DE GERENCIAMENTO DE TI E MANUTENÇÃO - SENAI ALAGOINHAS
Dark Mode - Sistema de Tema Escuro
===============================================================================
*/

const DarkMode = {
    // Configurações
    config: {
        storageKey: 'senai_theme',
        defaultTheme: 'light',
        transitionDuration: 300
    },

    // Estado atual
    currentTheme: null,

    /**
     * Inicializar Dark Mode
     */
    init() {
        // Carregar tema salvo ou usar padrão
        this.currentTheme = this.getStoredTheme() || this.getSystemTheme() || this.config.defaultTheme;
        
        // Aplicar tema
        this.applyTheme(this.currentTheme, false);
        
        // Configurar listeners
        this.setupListeners();
        
        // Criar toggle button se não existir
        this.createToggleButton();
        
        console.log('✅ Dark Mode inicializado:', this.currentTheme);
    },

    /**
     * Obter tema salvo no localStorage
     */
    getStoredTheme() {
        return localStorage.getItem(this.config.storageKey);
    },

    /**
     * Obter tema do sistema operacional
     */
    getSystemTheme() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    },

    /**
     * Salvar tema no localStorage
     */
    saveTheme(theme) {
        localStorage.setItem(this.config.storageKey, theme);
    },

    /**
     * Aplicar tema
     */
    applyTheme(theme, animate = true) {
        // Adicionar classe de transição
        if (animate) {
            document.documentElement.classList.add('theme-transition');
        }

        // Aplicar tema
        document.documentElement.setAttribute('data-theme', theme);
        this.currentTheme = theme;

        // Salvar preferência
        this.saveTheme(theme);

        // Atualizar UI
        this.updateToggleButton();
        this.updateMetaThemeColor();

        // Remover classe de transição
        if (animate) {
            setTimeout(() => {
                document.documentElement.classList.remove('theme-transition');
            }, this.config.transitionDuration);
        }

        // Disparar evento customizado
        this.dispatchThemeChangeEvent(theme);
    },

    /**
     * Alternar tema
     */
    toggle() {
        const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme(newTheme, true);
        
        // Feedback visual
        this.showToast(`Tema ${newTheme === 'dark' ? 'escuro' : 'claro'} ativado`);
    },

    /**
     * Configurar listeners
     */
    setupListeners() {
        // Detectar mudança de preferência do sistema
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (!this.getStoredTheme()) {
                    this.applyTheme(e.matches ? 'dark' : 'light', true);
                }
            });
        }

        // Listener para atalho de teclado (Ctrl/Cmd + Shift + D)
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'D') {
                e.preventDefault();
                this.toggle();
            }
        });
    },

    /**
     * Criar botão de toggle
     */
    createToggleButton() {
        // Verificar se já existe
        if (document.getElementById('dark-mode-toggle')) {
            return;
        }

        // Criar botão
        const button = document.createElement('button');
        button.id = 'dark-mode-toggle';
        button.className = 'dark-mode-toggle';
        button.setAttribute('aria-label', 'Alternar tema');
        button.setAttribute('title', 'Alternar tema (Ctrl+Shift+D)');
        
        // Ícones
        button.innerHTML = `
            <span class="icon-light">☀️</span>
            <span class="icon-dark">🌙</span>
        `;

        // Evento de clique
        button.addEventListener('click', () => this.toggle());

        // Adicionar ao DOM
        document.body.appendChild(button);

        // Adicionar estilos
        this.addToggleStyles();
    },

    /**
     * Adicionar estilos do botão de toggle
     */
    addToggleStyles() {
        if (document.getElementById('dark-mode-toggle-styles')) {
            return;
        }

        const style = document.createElement('style');
        style.id = 'dark-mode-toggle-styles';
        style.textContent = `
            .dark-mode-toggle {
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                border: none;
                background: var(--primary-color);
                color: white;
                font-size: 24px;
                cursor: pointer;
                box-shadow: var(--shadow-lg);
                transition: all 0.3s ease;
                z-index: 1000;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .dark-mode-toggle:hover {
                transform: scale(1.1);
                box-shadow: var(--shadow-xl);
            }

            .dark-mode-toggle:active {
                transform: scale(0.95);
            }

            .dark-mode-toggle .icon-light,
            .dark-mode-toggle .icon-dark {
                position: absolute;
                transition: opacity 0.3s ease, transform 0.3s ease;
            }

            [data-theme="light"] .dark-mode-toggle .icon-light {
                opacity: 1;
                transform: rotate(0deg) scale(1);
            }

            [data-theme="light"] .dark-mode-toggle .icon-dark {
                opacity: 0;
                transform: rotate(180deg) scale(0);
            }

            [data-theme="dark"] .dark-mode-toggle .icon-light {
                opacity: 0;
                transform: rotate(-180deg) scale(0);
            }

            [data-theme="dark"] .dark-mode-toggle .icon-dark {
                opacity: 1;
                transform: rotate(0deg) scale(1);
            }

            .theme-transition,
            .theme-transition *,
            .theme-transition *::before,
            .theme-transition *::after {
                transition: background-color 0.3s ease, 
                            color 0.3s ease, 
                            border-color 0.3s ease,
                            box-shadow 0.3s ease !important;
            }

            @media (max-width: 767px) {
                .dark-mode-toggle {
                    bottom: 15px;
                    right: 15px;
                    width: 45px;
                    height: 45px;
                    font-size: 20px;
                }
            }

            @media print {
                .dark-mode-toggle {
                    display: none !important;
                }
            }
        `;
        
        document.head.appendChild(style);
    },

    /**
     * Atualizar botão de toggle
     */
    updateToggleButton() {
        const button = document.getElementById('dark-mode-toggle');
        if (button) {
            button.setAttribute('aria-label', 
                this.currentTheme === 'dark' ? 'Ativar tema claro' : 'Ativar tema escuro'
            );
        }
    },

    /**
     * Atualizar meta theme-color para mobile
     */
    updateMetaThemeColor() {
        let metaThemeColor = document.querySelector('meta[name="theme-color"]');
        
        if (!metaThemeColor) {
            metaThemeColor = document.createElement('meta');
            metaThemeColor.name = 'theme-color';
            document.head.appendChild(metaThemeColor);
        }

        const color = this.currentTheme === 'dark' ? '#0F172A' : '#003C78';
        metaThemeColor.content = color;
    },

    /**
     * Disparar evento de mudança de tema
     */
    dispatchThemeChangeEvent(theme) {
        const event = new CustomEvent('themechange', {
            detail: { theme }
        });
        document.dispatchEvent(event);
    },

    /**
     * Mostrar toast de feedback
     */
    showToast(message) {
        // Verificar se existe função de toast global
        if (typeof window.showToast === 'function') {
            window.showToast(message, 'info');
            return;
        }

        // Criar toast simples
        const toast = document.createElement('div');
        toast.className = 'dark-mode-toast';
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 80px;
            right: 20px;
            background: var(--primary-color);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: var(--shadow-lg);
            z-index: 9999;
            animation: slideInRight 0.3s ease;
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    },

    /**
     * Obter tema atual
     */
    getTheme() {
        return this.currentTheme;
    },

    /**
     * Verificar se está em modo escuro
     */
    isDark() {
        return this.currentTheme === 'dark';
    },

    /**
     * Forçar tema específico
     */
    setTheme(theme) {
        if (theme === 'light' || theme === 'dark') {
            this.applyTheme(theme, true);
        }
    }
};

// Adicionar animações CSS
const animations = document.createElement('style');
animations.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(animations);

// Inicializar quando o DOM estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => DarkMode.init());
} else {
    DarkMode.init();
}

// Exportar para uso global
window.DarkMode = DarkMode;

// Listener para mudanças de tema
document.addEventListener('themechange', (e) => {
    console.log('🎨 Tema alterado para:', e.detail.theme);
});

/*
===============================================================================
EXEMPLO DE USO:
===============================================================================

// 1. Alternar tema
DarkMode.toggle();

// 2. Definir tema específico
DarkMode.setTheme('dark');
DarkMode.setTheme('light');

// 3. Obter tema atual
const currentTheme = DarkMode.getTheme();

// 4. Verificar se está em modo escuro
if (DarkMode.isDark()) {
    console.log('Modo escuro ativo');
}

// 5. Escutar mudanças de tema
document.addEventListener('themechange', (e) => {
    console.log('Novo tema:', e.detail.theme);
    // Fazer algo quando o tema mudar
});

===============================================================================
*/
