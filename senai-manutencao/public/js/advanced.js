/*
===============================================================================
SISTEMA DE GERENCIAMENTO DE TI E MANUTENÇÃO - SENAI ALAGOINHAS
JavaScript Avançado - Validações e Notificações
===============================================================================
*/

// ===== SISTEMA DE TOAST NOTIFICATIONS =====
class ToastSystem {
    constructor() {
        this.container = this.createContainer();
    }

    createContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                display: flex;
                flex-direction: column;
                gap: 10px;
            `;
            document.body.appendChild(container);
        }
        return container;
    }

    show(message, type = 'info', duration = 5000) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };

        const colors = {
            success: '#10B981',
            error: '#EF4444',
            warning: '#F59E0B',
            info: '#3B82F6'
        };

        toast.style.cssText = `
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            background: white;
            border-left: 4px solid ${colors[type]};
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            min-width: 300px;
            max-width: 500px;
            animation: slideInRight 0.3s ease-out;
            cursor: pointer;
        `;

        toast.innerHTML = `
            <div style="width: 24px; height: 24px; border-radius: 50%; background: ${colors[type]}; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                ${icons[type]}
            </div>
            <div style="flex: 1; color: #1F2937; font-size: 14px;">
                ${message}
            </div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #9CA3AF; cursor: pointer; font-size: 20px; padding: 0; width: 24px; height: 24px; flex-shrink: 0;">
                ×
            </button>
        `;

        this.container.appendChild(toast);

        // Auto remover
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }
        }, duration);

        // Remover ao clicar
        toast.addEventListener('click', (e) => {
            if (e.target.tagName !== 'BUTTON') {
                toast.remove();
            }
        });
    }

    success(message, duration = 5000) {
        this.show(message, 'success', duration);
    }

    error(message, duration = 6000) {
        this.show(message, 'error', duration);
    }

    warning(message, duration = 5000) {
        this.show(message, 'warning', duration);
    }

    info(message, duration = 4000) {
        this.show(message, 'info', duration);
    }
}

// Instância global
window.toast = new ToastSystem();

// ===== SISTEMA DE VALIDAÇÃO =====
const ValidationRules = {
    required: (value) => value.trim() !== '',
    email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
    minLength: (value, length) => value.length >= parseInt(length),
    maxLength: (value, length) => value.length <= parseInt(length),
    numeric: (value) => /^\d+$/.test(value),
    alphanumeric: (value) => /^[a-zA-Z0-9]+$/.test(value),
    phone: (value) => /^\(\d{2}\)\s?\d{4,5}-?\d{4}$/.test(value),
    min: (value, min) => parseFloat(value) >= parseFloat(min),
    max: (value, max) => parseFloat(value) <= parseFloat(max)
};

const ValidationMessages = {
    required: 'Este campo é obrigatório',
    email: 'E-mail inválido',
    minLength: 'Mínimo de {0} caracteres',
    maxLength: 'Máximo de {0} caracteres',
    numeric: 'Apenas números são permitidos',
    alphanumeric: 'Apenas letras e números são permitidos',
    phone: 'Telefone inválido',
    min: 'Valor mínimo: {0}',
    max: 'Valor máximo: {0}'
};

class FormValidator {
    constructor(form) {
        this.form = form;
        this.fields = form.querySelectorAll('[data-validate]');
        this.init();
    }

    init() {
        this.fields.forEach(field => {
            field.addEventListener('blur', () => this.validateField(field));
            field.addEventListener('input', () => {
                if (field.classList.contains('is-invalid')) {
                    this.validateField(field);
                }
            });
        });

        this.form.addEventListener('submit', (e) => {
            if (!this.validateAll()) {
                e.preventDefault();
                toast.error('Por favor, corrija os erros no formulário');

                // Focar no primeiro campo inválido
                const firstInvalid = this.form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }

    validateField(field) {
        const rules = field.dataset.validate.split('|');
        const value = field.value;
        let isValid = true;
        let errorMessage = '';

        for (let rule of rules) {
            const [ruleName, ruleValue] = rule.split(':');

            if (ValidationRules[ruleName]) {
                if (!ValidationRules[ruleName](value, ruleValue)) {
                    isValid = false;
                    errorMessage = ValidationMessages[ruleName];
                    if (ruleValue) {
                        errorMessage = errorMessage.replace('{0}', ruleValue);
                    }
                    break;
                }
            }
        }

        this.updateFieldUI(field, isValid, errorMessage);
        return isValid;
    }

    updateFieldUI(field, isValid, errorMessage) {
        const formGroup = field.closest('.form-group') || field.parentElement;

        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            this.removeError(formGroup);
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            this.showError(formGroup, errorMessage);
        }
    }

    showError(formGroup, message) {
        this.removeError(formGroup);
        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error';
        errorDiv.style.cssText = 'color: #EF4444; font-size: 12px; margin-top: 4px;';
        errorDiv.textContent = message;
        formGroup.appendChild(errorDiv);
    }

    removeError(formGroup) {
        const existingError = formGroup.querySelector('.form-error');
        if (existingError) {
            existingError.remove();
        }
    }

    validateAll() {
        let allValid = true;
        this.fields.forEach(field => {
            if (!this.validateField(field)) {
                allValid = false;
            }
        });
        return allValid;
    }
}

// ===== MÁSCARAS DE INPUT =====
function applyInputMasks() {
    // Máscara de telefone
    document.querySelectorAll('[data-mask="phone"]').forEach(input => {
        input.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
                value = value.replace(/(\d)(\d{4})$/, '$1-$2');
            }
            e.target.value = value;
        });
    });

    // Máscara de matrícula
    document.querySelectorAll('[data-mask="matricula"]').forEach(input => {
        input.addEventListener('input', function (e) {
            e.target.value = e.target.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        });
    });

    // Máscara de data
    document.querySelectorAll('[data-mask="date"]').forEach(input => {
        input.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 8) {
                value = value.replace(/^(\d{2})(\d)/g, '$1/$2');
                value = value.replace(/(\d{2})(\d)/g, '$1/$2');
            }
            e.target.value = value.slice(0, 10);
        });
    });

    // Máscara de CPF
    document.querySelectorAll('[data-mask="cpf"]').forEach(input => {
        input.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            }
            e.target.value = value;
        });
    });
}

// ===== LOADING OVERLAY =====
class LoadingOverlay {
    constructor() {
        this.overlay = null;
    }

    show(message = 'Carregando...') {
        this.hide(); // Remove qualquer overlay existente

        this.overlay = document.createElement('div');
        this.overlay.className = 'loading-overlay';
        this.overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            animation: fadeIn 0.2s ease-out;
        `;

        this.overlay.innerHTML = `
            <div style="background: white; padding: 30px 40px; border-radius: 12px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.3);">
                <div style="width: 50px; height: 50px; border: 4px solid #E5E7EB; border-top-color: #003C78; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
                <div style="color: #1F2937; font-size: 16px; font-weight: 500;">${message}</div>
            </div>
        `;

        document.body.appendChild(this.overlay);
        document.body.style.overflow = 'hidden';
    }

    hide() {
        if (this.overlay) {
            this.overlay.style.animation = 'fadeOut 0.2s ease-out';
            setTimeout(() => {
                if (this.overlay && this.overlay.parentElement) {
                    this.overlay.remove();
                    document.body.style.overflow = '';
                }
            }, 200);
        }
    }
}

window.loading = new LoadingOverlay();

// ===== CONFIRMAÇÃO DE AÇÕES =====
function confirmAction(message, onConfirm, onCancel = null) {
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 99999;
        animation: fadeIn 0.2s ease-out;
    `;

    overlay.innerHTML = `
        <div style="background: white; padding: 30px; border-radius: 12px; max-width: 400px; width: 90%; box-shadow: 0 20px 50px rgba(0,0,0,0.3); animation: slideInRight 0.3s ease-out;">
            <h3 style="color: #1F2937; margin: 0 0 15px 0; font-size: 18px;">Confirmação</h3>
            <p style="color: #6B7280; margin: 0 0 25px 0; font-size: 14px; line-height: 1.6;">${message}</p>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button class="btn-cancel" style="padding: 10px 20px; border: 1px solid #E5E7EB; background: white; color: #6B7280; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;">
                    Cancelar
                </button>
                <button class="btn-confirm" style="padding: 10px 20px; border: none; background: #EF4444; color: white; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;">
                    Confirmar
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    overlay.querySelector('.btn-confirm').addEventListener('click', () => {
        overlay.remove();
        if (onConfirm) onConfirm();
    });

    overlay.querySelector('.btn-cancel').addEventListener('click', () => {
        overlay.remove();
        if (onCancel) onCancel();
    });

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.remove();
            if (onCancel) onCancel();
        }
    });
}

window.confirmAction = confirmAction;

// ===== ANIMAÇÕES DE SCROLL =====
function observeAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });

    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
}

// ===== CONTADOR ANIMADO =====
function animateCounter(element, target, duration = 2000) {
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;

    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

window.animateCounter = animateCounter;

// ===== ADICIONAR ESTILOS DE ANIMAÇÃO =====
if (!document.getElementById('advanced-animations')) {
    const style = document.createElement('style');
    style.id = 'advanced-animations';
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        @keyframes slideOutRight {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(30px); }
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .form-control.is-invalid {
            border-color: #EF4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }
        
        .form-control.is-valid {
            border-color: #10B981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
        }
    `;
    document.head.appendChild(style);
}

// ===== INICIALIZAÇÃO =====
document.addEventListener('DOMContentLoaded', function () {
    console.log('%c🚀 Sistema SENAI Alagoinhas - Módulos Avançados Carregados', 'color: #003C78; font-size: 14px; font-weight: bold');

    // Aplicar máscaras
    applyInputMasks();

    // Inicializar validadores
    document.querySelectorAll('form[data-validate="true"]').forEach(form => {
        new FormValidator(form);
    });

    // Observar animações
    observeAnimations();

    // Adicionar smooth scroll para links âncora
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '#!') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });
});
