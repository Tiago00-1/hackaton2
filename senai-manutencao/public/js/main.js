/*
===============================================================================
SISTEMA DE GERENCIAMENTO DE TI E MANUTENÇÃO - SENAI ALAGOINHAS
JavaScript Principal - Funcionalidades Interativas
===============================================================================
*/

// ===== CONFIGURAÇÕES GLOBAIS =====
const CONFIG = {
    baseUrl: '/senai-manutencao',
    apiUrl: '/senai-manutencao/controllers',
    sessionTimeout: 7200, // 2 horas em segundos
    toastDuration: 5000,
    animationDuration: 300
};

// ===== UTILITÁRIOS GLOBAIS =====
const Utils = {
    // Formatar data para exibição
    formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleString('pt-BR');
    },

    // Formatar data simples
    formatDateOnly(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR');
    },

    // Sanitizar HTML
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    // Debounce para otimizar eventos
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // Gerar ID único
    generateId() {
        return 'id_' + Math.random().toString(36).substr(2, 9);
    },

    // Validar email
    isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    // Validar campos obrigatórios
    validateRequired(value) {
        return value && value.toString().trim().length > 0;
    }
};

// ===== GERENCIADOR DE ESTADO =====
const AppState = {
    user: null,
    theme: localStorage.getItem('theme') || 'light',
    sidebarOpen: false,

    setUser(userData) {
        this.user = userData;
        this.updateUI();
    },

    setTheme(theme) {
        this.theme = theme;
        localStorage.setItem('theme', theme);
        document.documentElement.setAttribute('data-theme', theme);
        this.updateUI();
    },

    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.toggle('show', this.sidebarOpen);
        }
    },

    updateUI() {
        // Atualizar informações do usuário na interface
        const userElements = document.querySelectorAll('[data-user-info]');
        userElements.forEach(el => {
            const info = el.getAttribute('data-user-info');
            if (this.user && this.user[info]) {
                el.textContent = this.user[info];
            }
        });

        // Atualizar botão de tema
        const themeToggle = document.querySelector('.theme-toggle');
        if (themeToggle) {
            themeToggle.innerHTML = this.theme === 'dark' ? '☀️' : '🌙';
            themeToggle.title = this.theme === 'dark' ? 'Modo Claro' : 'Modo Escuro';
        }
    }
};

// ===== SISTEMA DE NOTIFICAÇÕES =====
const Toast = {
    container: null,

    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    },

    show(message, type = 'info', duration = CONFIG.toastDuration) {
        this.init();

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <div class="toast-icon">${this.getIcon(type)}</div>
                <div class="toast-message">${Utils.escapeHtml(message)}</div>
                <button class="toast-close" onclick="Toast.close(this.parentElement)">×</button>
            </div>
        `;

        this.container.appendChild(toast);

        // Animar entrada
        setTimeout(() => toast.classList.add('show'), 100);

        // Auto-remover
        setTimeout(() => this.close(toast), duration);

        return toast;
    },

    getIcon(type) {
        const icons = {
            success: '✓',
            error: '✗',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    },

    close(toast) {
        if (toast && toast.parentElement) {
            toast.classList.remove('show');
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.parentElement.removeChild(toast);
                }
            }, CONFIG.animationDuration);
        }
    },

    success(message) { return this.show(message, 'success'); },
    error(message) { return this.show(message, 'error'); },
    warning(message) { return this.show(message, 'warning'); },
    info(message) { return this.show(message, 'info'); }
};

// ===== SISTEMA DE MODAIS =====
const Modal = {
    current: null,

    show(modalId, data = {}) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        this.current = modal;

        // Preencher dados se fornecidos
        this.populateData(modal, data);

        modal.classList.add('show');
        document.body.style.overflow = 'hidden';

        // Focar no primeiro input
        setTimeout(() => {
            const firstInput = modal.querySelector('input, select, textarea');
            if (firstInput) firstInput.focus();
        }, CONFIG.animationDuration);
    },

    hide(modal = null) {
        const targetModal = modal || this.current;
        if (!targetModal) return;

        targetModal.classList.remove('show');
        document.body.style.overflow = '';

        setTimeout(() => {
            this.resetForm(targetModal);
        }, CONFIG.animationDuration);

        if (targetModal === this.current) {
            this.current = null;
        }
    },

    populateData(modal, data) {
        Object.keys(data).forEach(key => {
            const element = modal.querySelector(`[name="${key}"]`);
            if (element) {
                if (element.type === 'checkbox') {
                    element.checked = data[key];
                } else {
                    element.value = data[key];
                }
            }
        });
    },

    resetForm(modal) {
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            this.clearErrors(form);
        }
    },

    clearErrors(form) {
        const errorElements = form.querySelectorAll('.form-error');
        errorElements.forEach(el => el.remove());

        const invalidInputs = form.querySelectorAll('.error');
        invalidInputs.forEach(input => input.classList.remove('error'));
    }
};

// ===== VALIDAÇÃO DE FORMULÁRIOS =====
class FormValidator {
    constructor(form, schema = {}, options = {}) {
        this.form = form;
        this.schema = schema;
        this.options = options;
    }

    validate() {
        if (!this.form) {
            return true;
        }

        const errors = this.validateSchema();

        if (Object.keys(errors).length > 0) {
            FormValidator.showErrors(this.form, errors);
            return false;
        }

        FormValidator.clearErrors(this.form);
        return true;
    }

    validateSchema() {
        const errors = {};

        Object.entries(this.schema).forEach(([fieldName, rules]) => {
            const field = this.form.querySelector(`[name="${fieldName}"]`) || this.form.querySelector(`#${fieldName}`);
            if (!field) {
                return;
            }

            const value = FormValidator.getFieldValue(field);

            Object.entries(rules).some(([ruleName, ruleValue]) => {
                if (ruleValue === false || ruleValue === null || typeof ruleValue === 'undefined') {
                    return false;
                }

                const ruleFunc = FormValidator.rules[ruleName];
                if (!ruleFunc) {
                    return false;
                }

                const result = ruleFunc(value, ruleValue);
                if (result !== true) {
                    errors[fieldName] = typeof result === 'string' ? result : 'Campo inválido';
                    return true;
                }

                return false;
            });
        });

        return errors;
    }

    static getFieldValue(field) {
        if (!field) {
            return '';
        }

        if (field.type === 'checkbox') {
            return field.checked ? (field.value || 'on') : '';
        }

        if (field.type === 'radio') {
            const radios = field.form ? field.form.querySelectorAll(`input[name="${field.name}"]`) : [];
            const checked = Array.from(radios).find(radio => radio.checked);
            return checked ? checked.value : '';
        }

        return (field.value || '').trim();
    }

    static validate(form) {
        const errors = {};
        const inputs = form.querySelectorAll('[data-validate]');

        inputs.forEach(input => {
            const definition = input.getAttribute('data-validate');
            if (!definition) {
                return;
            }

            const rules = definition.split('|');
            const fieldName = input.name || input.id;
            const value = FormValidator.getFieldValue(input);

            for (let rule of rules) {
                const [ruleName, ...params] = rule.split(':');
                const ruleFunc = FormValidator.rules[ruleName];

                if (ruleFunc) {
                    const result = ruleFunc(value, ...params);
                    if (result !== true) {
                        errors[fieldName] = result;
                        break;
                    }
                }
            }
        });

        return errors;
    }

    static showErrors(form, errors) {
        FormValidator.clearErrors(form);

        Object.keys(errors).forEach(fieldName => {
            const input = form.querySelector(`[name="${fieldName}"]`) || form.querySelector(`#${fieldName}`);
            if (input) {
                input.classList.add('error');

                const errorEl = document.createElement('div');
                errorEl.className = 'form-error';
                errorEl.textContent = errors[fieldName];

                const parent = input.parentNode || input.closest('.form-group') || input;
                parent.appendChild(errorEl);
            }
        });
    }

    static clearErrors(form) {
        Modal.clearErrors(form);
    }
}

FormValidator.rules = {
    required: (value) => Utils.validateRequired(value) || 'Campo obrigatório',
    email: (value) => !value || Utils.isValidEmail(value) || 'E-mail inválido',
    minLength: (value, min) => !value || value.length >= Number(min) || `Mínimo ${min} caracteres`,
    maxLength: (value, max) => !value || value.length <= Number(max) || `Máximo ${max} caracteres`,
    numeric: (value) => !value || !isNaN(value) || 'Deve ser um número',
    match: (value, matchValue) => value === matchValue || 'Campos não coincidem'
};

// ===== REQUISIÇÕES AJAX =====
const Ajax = {
    async request(url, options = {}) {
        const defaults = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        const config = { ...defaults, ...options };

        try {
            const response = await fetch(url, config);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return await response.json();
            } else {
                return await response.text();
            }
        } catch (error) {
            console.error('Ajax request failed:', error);
            throw error;
        }
    },

    async get(url, params = {}) {
        const searchParams = new URLSearchParams(params);
        const fullUrl = `${url}${searchParams.toString() ? '?' + searchParams.toString() : ''}`;
        return this.request(fullUrl);
    },

    async post(url, data = {}) {
        return this.request(url, {
            method: 'POST',
            body: data instanceof FormData ? data : JSON.stringify(data)
        });
    },

    async postForm(url, formData) {
        return this.request(url, {
            method: 'POST',
            headers: {},
            body: formData
        });
    }
};

// ===== SISTEMA DE SESSÃO =====
const Session = {
    checkInterval: null,

    init() {
        this.startSessionCheck();
        this.bindEvents();
    },

    startSessionCheck() {
        // Verificar sessão a cada 5 minutos
        this.checkInterval = setInterval(() => {
            this.checkSession();
        }, 300000);
    },

    async checkSession() {
        try {
            const response = await Ajax.get(`${CONFIG.apiUrl}/AuthController.php?action=check_session`);

            if (!response.logged_in) {
                this.handleSessionExpired();
            } else {
                AppState.setUser(response);

                // Avisar se sessão está próxima do fim (últimos 10 minutos)
                if (response.session_time_left < 600) {
                    this.showSessionWarning(response.session_time_left);
                }
            }
        } catch (error) {
            console.error('Erro ao verificar sessão:', error);
        }
    },

    async renewSession() {
        try {
            const response = await Ajax.get(`${CONFIG.apiUrl}/AuthController.php?action=renew_session`);

            if (response.success) {
                Toast.success('Sessão renovada');
                this.hideSessionWarning();
            }
        } catch (error) {
            Toast.error('Erro ao renovar sessão');
        }
    },

    showSessionWarning(timeLeft) {
        const minutes = Math.floor(timeLeft / 60);

        if (!document.querySelector('.session-warning')) {
            const warning = document.createElement('div');
            warning.className = 'alert alert-warning session-warning';
            warning.innerHTML = `
                <span>Sua sessão expira em ${minutes} minutos.</span>
                <button onclick="Session.renewSession()" class="btn btn-sm btn-primary">Renovar</button>
            `;

            document.body.insertBefore(warning, document.body.firstChild);
        }
    },

    hideSessionWarning() {
        const warning = document.querySelector('.session-warning');
        if (warning) {
            warning.remove();
        }
    },

    handleSessionExpired() {
        clearInterval(this.checkInterval);
        Toast.error('Sua sessão expirou. Você será redirecionado para o login.');

        setTimeout(() => {
            window.location.href = CONFIG.baseUrl;
        }, 3000);
    },

    bindEvents() {
        // Renovar sessão em atividades do usuário
        const events = ['click', 'keypress', 'scroll', 'mousemove'];
        let lastActivity = Date.now();

        events.forEach(event => {
            document.addEventListener(event, () => {
                const now = Date.now();
                if (now - lastActivity > 60000) { // A cada minuto de atividade
                    lastActivity = now;
                    // Renovar sessão silenciosamente em atividade
                }
            }, { passive: true });
        });
    }
};

// ===== GRÁFICOS SIMPLES =====
const Charts = {
    createPieChart(canvas, data, options = {}) {
        const ctx = canvas.getContext('2d');
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const radius = Math.min(centerX, centerY) - 20;

        let total = data.reduce((sum, item) => sum + item.value, 0);
        let currentAngle = -Math.PI / 2;

        // Cores padrão
        const colors = options.colors || ['#003C78', '#0066CC', '#FF6600', '#28A745', '#FFC107', '#DC3545'];

        data.forEach((item, index) => {
            const sliceAngle = (item.value / total) * 2 * Math.PI;

            // Desenhar fatia
            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + sliceAngle);
            ctx.closePath();
            ctx.fillStyle = colors[index % colors.length];
            ctx.fill();

            // Desenhar borda
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = 2;
            ctx.stroke();

            currentAngle += sliceAngle;
        });

        // Legenda
        if (options.showLegend !== false) {
            this.drawLegend(ctx, data, colors, canvas.width - 150, 20);
        }
    },

    createBarChart(canvas, data, options = {}) {
        const ctx = canvas.getContext('2d');
        const padding = 40;
        const chartWidth = canvas.width - (padding * 2);
        const chartHeight = canvas.height - (padding * 2);

        const maxValue = Math.max(...data.map(item => item.value));
        const barWidth = chartWidth / data.length - 10;

        data.forEach((item, index) => {
            const barHeight = (item.value / maxValue) * chartHeight;
            const x = padding + (index * (barWidth + 10));
            const y = canvas.height - padding - barHeight;

            // Desenhar barra
            ctx.fillStyle = options.colors?.[index] || '#003C78';
            ctx.fillRect(x, y, barWidth, barHeight);

            // Label
            ctx.fillStyle = '#333';
            ctx.font = '12px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(item.label, x + barWidth / 2, canvas.height - 10);
            ctx.fillText(item.value, x + barWidth / 2, y - 5);
        });
    },

    drawLegend(ctx, data, colors, x, y) {
        ctx.font = '12px Arial';
        ctx.textAlign = 'left';

        data.forEach((item, index) => {
            const legendY = y + (index * 25);

            // Quadrado colorido
            ctx.fillStyle = colors[index % colors.length];
            ctx.fillRect(x, legendY, 15, 15);

            // Texto
            ctx.fillStyle = '#333';
            ctx.fillText(`${item.label} (${item.value})`, x + 20, legendY + 12);
        });
    }
};

// ===== MANIPULAÇÃO DE ARQUIVOS =====
const FileHandler = {
    setupFileUpload(inputElement, options = {}) {
        const maxSize = options.maxSize || 2 * 1024 * 1024; // 2MB
        const allowedTypes = options.allowedTypes || ['image/jpeg', 'image/png', 'image/jpg'];

        inputElement.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;

            // Validar tamanho
            if (file.size > maxSize) {
                Toast.error('Arquivo muito grande. Máximo 2MB.');
                e.target.value = '';
                return;
            }

            // Validar tipo
            if (!allowedTypes.includes(file.type)) {
                Toast.error('Tipo de arquivo não permitido. Use JPG, JPEG ou PNG.');
                e.target.value = '';
                return;
            }

            // Preview se for imagem
            if (file.type.startsWith('image/') && options.previewElement) {
                this.showImagePreview(file, options.previewElement);
            }

            if (options.onFileSelect) {
                options.onFileSelect(file);
            }
        });
    },

    showImagePreview(file, previewElement) {
        const reader = new FileReader();
        reader.onload = (e) => {
            previewElement.src = e.target.result;
            previewElement.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
};

// ===== FILTROS E PESQUISA =====
const Filter = {
    debounceTimeout: null,

    setupSearch(inputElement, callback, delay = 500) {
        inputElement.addEventListener('input', (e) => {
            clearTimeout(this.debounceTimeout);
            this.debounceTimeout = setTimeout(() => {
                callback(e.target.value);
            }, delay);
        });
    },

    setupDateRangeFilter(startElement, endElement, callback) {
        const validate = () => {
            const start = startElement.value;
            const end = endElement.value;

            if (start && end && new Date(start) > new Date(end)) {
                Toast.warning('Data inicial não pode ser maior que data final');
                return false;
            }

            callback(start, end);
            return true;
        };

        startElement.addEventListener('change', validate);
        endElement.addEventListener('change', validate);
    }
};

// ===== PAGINAÇÃO =====
const Pagination = {
    create(container, currentPage, totalPages, onPageChange) {
        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<div class="pagination">';

        // Botão anterior
        const prevDisabled = currentPage <= 1 ? 'disabled' : '';
        html += `<button class="pagination-btn" ${prevDisabled} onclick="Pagination.goToPage(${currentPage - 1}, ${totalPages})">« Anterior</button>`;

        // Páginas
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            html += `<button class="pagination-btn" onclick="Pagination.goToPage(1, ${totalPages})">1</button>`;
            if (startPage > 2) {
                html += '<span class="pagination-ellipsis">...</span>';
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === currentPage ? 'active' : '';
            html += `<button class="pagination-btn ${activeClass}" onclick="Pagination.goToPage(${i}, ${totalPages})">${i}</button>`;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += '<span class="pagination-ellipsis">...</span>';
            }
            html += `<button class="pagination-btn" onclick="Pagination.goToPage(${totalPages}, ${totalPages})">${totalPages}</button>`;
        }

        // Botão próximo
        const nextDisabled = currentPage >= totalPages ? 'disabled' : '';
        html += `<button class="pagination-btn" ${nextDisabled} onclick="Pagination.goToPage(${currentPage + 1}, ${totalPages})">Próximo »</button>`;

        html += '</div>';

        container.innerHTML = html;

        // Salvar callback
        this.currentCallback = onPageChange;
    },

    goToPage(page, totalPages) {
        if (page < 1 || page > totalPages) return;
        if (this.currentCallback) {
            this.currentCallback(page);
        }
    }
};

// ===== LOADING/SPINNER =====
const Loading = {
    show(element = document.body) {
        const existing = element.querySelector('.loading-overlay');
        if (existing) return;

        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = `
            <div class="loading">
                <div class="spinner"></div>
                <span>Carregando...</span>
            </div>
        `;

        element.appendChild(overlay);
    },

    hide(element = document.body) {
        const overlay = element.querySelector('.loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    }
};

// ===== INICIALIZAÇÃO =====
document.addEventListener('DOMContentLoaded', () => {
    // Aplicar tema salvo
    AppState.setTheme(AppState.theme);

    // Inicializar sistema de sessão se estiver logado
    if (document.querySelector('[data-logged-in="true"]')) {
        Session.init();
    }

    // Event listeners globais
    setupGlobalEvents();

    // Inicializar componentes específicos da página
    initializePageComponents();
});

function setupGlobalEvents() {
    // Toggle tema
    document.addEventListener('click', (e) => {
        if (e.target.matches('.theme-toggle, .theme-toggle *')) {
            e.preventDefault();
            const newTheme = AppState.theme === 'light' ? 'dark' : 'light';
            AppState.setTheme(newTheme);
        }
    });

    // Toggle sidebar (mobile)
    document.addEventListener('click', (e) => {
        if (e.target.matches('.sidebar-toggle, .sidebar-toggle *')) {
            e.preventDefault();
            AppState.toggleSidebar();
        }
    });

    // Fechar modais clicando fora
    document.addEventListener('click', (e) => {
        if (e.target.matches('.modal')) {
            Modal.hide(e.target);
        }
    });

    // Fechar modais com ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && Modal.current) {
            Modal.hide();
        }
    });

    // Confirmar saída se houver alterações não salvas
    let hasUnsavedChanges = false;
    document.addEventListener('input', (e) => {
        if (e.target.matches('input, textarea, select')) {
            hasUnsavedChanges = true;
        }
    });

    document.addEventListener('submit', () => {
        hasUnsavedChanges = false;
    });

    window.addEventListener('beforeunload', (e) => {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
}

function initializePageComponents() {
    // Inicializar tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(initTooltip);

    // Inicializar dropdowns
    const dropdowns = document.querySelectorAll('[data-dropdown]');
    dropdowns.forEach(initDropdown);

    // Inicializar validação automática de formulários
    const forms = document.querySelectorAll('form[data-validate-auto]');
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const errors = FormValidator.validate(form);

            if (Object.keys(errors).length === 0) {
                // Submeter formulário se válido
                form.submit();
            } else {
                FormValidator.showErrors(form, errors);
                Toast.error('Por favor, corrija os erros no formulário.');
            }
        });
    });

    // Inicializar upload de arquivos
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    fileInputs.forEach(input => {
        const previewElement = document.querySelector(input.dataset.preview);
        FileHandler.setupFileUpload(input, {
            previewElement: previewElement
        });
    });
}

function initTooltip(element) {
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = element.dataset.tooltip;

    element.addEventListener('mouseenter', () => {
        document.body.appendChild(tooltip);
        // Posicionar tooltip...
    });

    element.addEventListener('mouseleave', () => {
        if (tooltip.parentElement) {
            tooltip.parentElement.removeChild(tooltip);
        }
    });
}

function initDropdown(element) {
    const toggle = element.querySelector('[data-dropdown-toggle]');
    const menu = element.querySelector('[data-dropdown-menu]');

    if (!toggle || !menu) return;

    toggle.addEventListener('click', (e) => {
        e.preventDefault();
        menu.classList.toggle('show');
    });

    document.addEventListener('click', (e) => {
        if (!element.contains(e.target)) {
            menu.classList.remove('show');
        }
    });
}

// ===== MENU MOBILE =====
const MobileMenu = {
    init() {
        // Criar botão de menu se estiver em mobile
        if (window.innerWidth <= 768 && document.querySelector('.sidebar')) {
            this.setupMobileMenu();
        }

        // Reconfigurar ao redimensionar
        window.addEventListener('resize', Utils.debounce(() => {
            if (window.innerWidth <= 768 && document.querySelector('.sidebar')) {
                this.setupMobileMenu();
            } else {
                this.removeMobileMenu();
            }
        }, 250));
    },

    setupMobileMenu() {
        const sidebar = document.querySelector('.sidebar');
        if (!sidebar) return;

        // Adicionar evento ao pseudo-elemento
        document.addEventListener('click', (e) => {
            const rect = {
                top: 10,
                left: 10,
                right: 50,
                bottom: 50
            };

            if (e.clientX >= rect.left && e.clientX <= rect.right &&
                e.clientY >= rect.top && e.clientY <= rect.bottom) {
                this.toggleSidebar();
            }
        });

        // Fechar ao clicar fora
        document.addEventListener('click', (e) => {
            if (sidebar.classList.contains('active') &&
                !sidebar.contains(e.target) &&
                !(e.clientX >= 10 && e.clientX <= 50 && e.clientY >= 10 && e.clientY <= 50)) {
                sidebar.classList.remove('active');
            }
        });

        // Fechar ao clicar em um link
        const navLinks = sidebar.querySelectorAll('.nav-item');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                sidebar.classList.remove('active');
            });
        });
    },

    toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.toggle('active');
        }
    },

    removeMobileMenu() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.remove('active');
        }
    }
};

// Exportar para uso global
window.AppUtils = {
    Utils,
    AppState,
    Toast,
    Modal,
    FormValidator,
    Ajax,
    Session,
    Charts,
    FileHandler,
    Filter,
    Pagination,
    Loading,
    MobileMenu,
    CONFIG
};

// Inicializar menu mobile
document.addEventListener('DOMContentLoaded', () => {
    MobileMenu.init();
});