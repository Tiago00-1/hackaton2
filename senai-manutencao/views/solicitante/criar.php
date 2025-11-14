<?php

/**
 * Formulário para Criar Nova Solicitação
 * Sistema SENAI Alagoinhas
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/auth.php';
require_once __DIR__ . '/../../models/Type.php';
require_once __DIR__ . '/../../models/Sector.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Request.php';

// Verificar se está logado
Auth::requireLogin();

$currentUser = getCurrentUser();
$tipos = Type::getActive();
$setores = Sector::getActive();
$responsaveisPorSetor = User::getResponsaveisPorSetor();

$formData = [
    'local' => '',
    'descricao' => '',
    'prioridade' => 'Média',
    'tipo_id' => '',
    'setor_id' => (int)($currentUser['setor'] ?? 0),
    'curso' => '',
    'responsavel_id' => ''
];

// Processar formulário se enviado
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF()) {
        $errors[] = 'Sessão expirada. Atualize a página e tente novamente.';
    }

    $formData['local'] = sanitize($_POST['local'] ?? '');
    $formData['descricao'] = sanitize($_POST['descricao'] ?? '');
    $formData['prioridade'] = sanitize($_POST['prioridade'] ?? 'Média');
    $formData['tipo_id'] = (int)($_POST['tipo_id'] ?? 0);
    $formData['setor_id'] = (int)($_POST['setor_id'] ?? 0);
    $formData['curso'] = sanitize($_POST['curso'] ?? '');
    $formData['responsavel_id'] = (int)($_POST['responsavel_id'] ?? 0);

    // Processar upload de arquivo (opcional)
    $anexo_path = null;
    if (empty($errors) && !empty($_FILES['anexo']['tmp_name'])) {
        $upload_dir = __DIR__ . '/../../uploads/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['anexo']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'];

        if (in_array($file_extension, $allowed_extensions, true)) {
            if ($_FILES['anexo']['size'] <= 5 * 1024 * 1024) {
                $file_name = 'anexo_' . uniqid() . '.' . $file_extension;
                $full_path = $upload_dir . $file_name;

                if (!move_uploaded_file($_FILES['anexo']['tmp_name'], $full_path)) {
                    $errors[] = 'Erro ao fazer upload do arquivo';
                } else {
                    $anexo_path = 'uploads/' . $file_name;
                }
            } else {
                $errors[] = 'Arquivo muito grande (máximo 5MB)';
            }
        } else {
            $errors[] = 'Tipo de arquivo não permitido';
        }
    }

    $request = null;

    if (empty($errors)) {
        $requestPayload = [
            'solicitante_id' => $currentUser['id'],
            'setor_id' => $formData['setor_id'],
            'tipo_id' => $formData['tipo_id'],
            'local' => $formData['local'],
            'descricao' => $formData['descricao'],
            'prioridade' => $formData['prioridade'],
            'curso' => $formData['curso'],
            'responsavel_id' => $formData['responsavel_id'],
            'caminho_imagem' => $anexo_path,
            'status' => 'Aberta'
        ];

        $request = new Request($requestPayload);

        $validationErrors = $request->validate();
        if (!empty($validationErrors)) {
            $errors = array_merge($errors, array_values($validationErrors));
        }

        if ($formData['responsavel_id']) {
            $responsavelSelecionado = User::find($formData['responsavel_id']);
            if (!$responsavelSelecionado || ($responsavelSelecionado->getSetorId() && (int)$responsavelSelecionado->getSetorId() !== $formData['setor_id'])) {
                $errors[] = 'O responsável selecionado não pertence ao setor escolhido.';
            }
        }
    }

    if (empty($errors) && $request instanceof Request) {
        try {
            if ($request->save()) {
                $success = true;

                error_log("Solicitação criada: ID {$request->getId()}, Local: {$formData['local']}, Usuário: {$currentUser['matricula']}");

                header('Location: minhas_solicitacoes.php?created=' . $request->getId());
                exit;
            }

            $errors[] = 'Erro ao salvar solicitação. Tente novamente.';
        } catch (Exception $e) {
            $errors[] = 'Erro interno do sistema. Contate o administrador.';
            error_log('Erro ao criar solicitação: ' . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Solicitação - SENAI Alagoinhas</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/components.css">
    <link rel="stylesheet" href="../../public/css/responsive.css">
    <link rel="stylesheet" href="../../public/css/alerts.css">
</head>

<body data-logged-in="true">
    <!-- Header -->
    <header class="header">
        <div class="header-brand">
            <svg width="40" height="40" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="100" height="100" rx="10" fill="white" />
                <text x="50" y="35" text-anchor="middle" fill="#003C78" font-size="14" font-weight="bold">SENAI</text>
                <text x="50" y="75" text-anchor="middle" fill="#FF6600" font-size="8">ALAGOINHAS</text>
            </svg>
            <span>Sistema de Gerenciamento</span>
        </div>

        <nav class="header-nav">
            <div class="header-user">
                <div class="user-info">
                    <div class="user-name" data-user-info="nome"><?php echo htmlspecialchars($currentUser['nome']); ?></div>
                    <div class="user-role">Solicitante - <?php echo htmlspecialchars($currentUser['matricula']); ?></div>
                </div>
            </div>


            <a href="../../controllers/AuthController.php?action=logout" class="btn btn-danger btn-sm" style="margin-left: 1rem;">
                🚪 Sair
            </a>
        </nav>
    </header>

    <!-- Sidebar -->
    <aside class="sidebar">
        <nav class="sidebar-nav">
            <a href="minhas_solicitacoes.php" class="nav-item">
                <span>📋</span>
                Minhas Solicitações
            </a>
            <a href="criar.php" class="nav-item active">
                <span>➕</span>
                Nova Solicitação
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="content">
        <div class="container">
            <!-- Cabeçalho -->
            <div class="mb-4">
                <h1>Nova Solicitação</h1>
                <p class="text-secondary">Preencha o formulário abaixo para criar uma nova solicitação de TI ou manutenção</p>
            </div>

            <!-- Alertas -->
            <?php if ($success): ?>
                <div class="alert alert-success mb-4">
                    <strong>✅ Sucesso!</strong> Sua solicitação foi criada com sucesso.
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger mb-4">
                    <strong>❌ Erro!</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Formulário -->
            <form id="request-form" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <?php echo csrfField(); ?>
                <div class="row">
                    <!-- Informações Básicas -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3>Informações da Solicitação</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="local" class="form-label required">Local</label>
                                            <input type="text"
                                                id="local"
                                                name="local"
                                                class="form-control"
                                                placeholder="Ex: Laboratório 1, Sala 205, Oficina..."
                                                value="<?php echo htmlspecialchars($formData['local']); ?>"
                                                required>
                                            <div class="invalid-feedback">Local é obrigatório</div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tipo_id" class="form-label required">Tipo de Solicitação</label>
                                            <select id="tipo_id" name="tipo_id" class="form-control" required>
                                                <option value="">Selecione o tipo</option>
                                                <?php foreach ($tipos as $tipo): ?>
                                                    <option value="<?php echo $tipo['id_tipo']; ?>"
                                                        <?php echo ((int)$formData['tipo_id'] === (int)$tipo['id_tipo']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($tipo['nome_tipo']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">Tipo de solicitação é obrigatório</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="setor_id" class="form-label required">Setor Responsável</label>
                                            <select id="setor_id" name="setor_id" class="form-control" required>
                                                <option value="">Selecione o setor</option>
                                                <?php foreach ($setores as $setor): ?>
                                                    <option value="<?php echo $setor['id_setor']; ?>"
                                                        <?php echo ((int)$formData['setor_id'] === (int)$setor['id_setor']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($setor['nome_setor']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">Informe o setor responsável pelo atendimento</div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="curso" class="form-label required">Curso / Turma Impactada</label>
                                            <input type="text"
                                                id="curso"
                                                name="curso"
                                                class="form-control"
                                                placeholder="Ex: Informática Industrial, Eletrotécnica..."
                                                value="<?php echo htmlspecialchars($formData['curso']); ?>"
                                                required>
                                            <div class="form-text">Ajuda a direcionar a manutenção com prioridade correta.</div>
                                            <div class="invalid-feedback">Informe o curso ou turma afetada</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="descricao" class="form-label required">Descrição do Problema</label>
                                    <textarea id="descricao"
                                        name="descricao"
                                        class="form-control"
                                        rows="6"
                                        placeholder="Descreva detalhadamente o problema ou serviço solicitado..."
                                        required><?php echo htmlspecialchars($formData['descricao']); ?></textarea>
                                    <div class="form-text">Mínimo de 10 caracteres. Seja específico para facilitar o atendimento.</div>
                                    <div class="invalid-feedback">Descrição é obrigatória (mínimo 10 caracteres)</div>
                                </div>

                                <div class="form-group">
                                    <label for="anexo" class="form-label">Anexo (Opcional)</label>
                                    <input type="file"
                                        id="anexo"
                                        name="anexo"
                                        class="form-control"
                                        accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt">
                                    <div class="form-text">
                                        Tipos permitidos: JPG, PNG, PDF, DOC, TXT. Tamanho máximo: 5MB.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configurações -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3>Configurações</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="prioridade" class="form-label required">Prioridade</label>
                                    <select id="prioridade" name="prioridade" class="form-control" required>
                                        <option value="">Selecione a prioridade</option>
                                        <option value="Baixa" <?php echo $formData['prioridade'] === 'Baixa' ? 'selected' : ''; ?>>
                                            🟢 Baixa
                                        </option>
                                        <option value="Média" <?php echo $formData['prioridade'] === 'Média' ? 'selected' : ''; ?>>
                                            🟡 Média
                                        </option>
                                        <option value="Urgente" <?php echo $formData['prioridade'] === 'Urgente' ? 'selected' : ''; ?>>
                                            🔴 Urgente
                                        </option>
                                    </select>
                                    <div class="invalid-feedback">Prioridade é obrigatória</div>
                                </div>

                                <div class="form-group mt-4">
                                    <label for="responsavel_id" class="form-label required">Responsável Técnico</label>
                                    <select id="responsavel_id" name="responsavel_id" class="form-control" required>
                                        <option value="">Selecione o responsável</option>
                                        <?php foreach ($responsaveisPorSetor as $grupo): ?>
                                            <?php if (empty($grupo['responsaveis'])) {
                                                continue;
                                            } ?>
                                            <optgroup label="<?php echo htmlspecialchars($grupo['setor_nome'] ?? 'Geral'); ?>">
                                                <?php foreach ($grupo['responsaveis'] as $responsavel): ?>
                                                    <?php $optionSetor = (int)($grupo['setor_id'] ?? 0); ?>
                                                    <option value="<?php echo $responsavel['id_usuario']; ?>"
                                                        data-setor="<?php echo $optionSetor; ?>"
                                                        <?php echo ((int)$formData['responsavel_id'] === (int)$responsavel['id_usuario']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($responsavel['nome']); ?> — <?php echo htmlspecialchars($responsavel['cargo'] ?? ''); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text" id="responsavel-helper">Selecione um setor para listar os responsáveis disponíveis.</div>
                                    <div class="invalid-feedback">Selecione quem acompanhará esta solicitação</div>
                                </div>

                                <!-- Informações do Solicitante -->
                                <div class="bg-light p-3 rounded mt-4">
                                    <h4>Solicitante</h4>
                                    <p class="mb-1"><strong>Nome:</strong> <?php echo htmlspecialchars($currentUser['nome']); ?></p>
                                    <p class="mb-1"><strong>Matrícula:</strong> <?php echo htmlspecialchars($currentUser['matricula']); ?></p>
                                    <p class="mb-1"><strong>Cargo:</strong> <?php echo htmlspecialchars($currentUser['cargo'] ?? 'Não informado'); ?></p>
                                    <p class="mb-0"><strong>Setor:</strong> <?php echo htmlspecialchars($currentUser['setor_nome'] ?? 'Não informado'); ?></p>
                                </div>

                                <!-- Dicas -->
                                <div class="mt-4">
                                    <h4>💡 Dicas</h4>
                                    <ul class="list-unstyled small">
                                        <li>✓ Seja específico na descrição</li>
                                        <li>✓ Inclua códigos de erro se houver</li>
                                        <li>✓ Anexe fotos do problema</li>
                                        <li>✓ Informe horário de disponibilidade</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="d-flex justify-between align-center mt-4">
                    <a href="minhas_solicitacoes.php" class="btn btn-outline">
                        ← Voltar
                    </a>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                            🔄 Limpar
                        </button>
                        <button type="submit" class="btn btn-primary" id="submit-btn">
                            ✅ Criar Solicitação
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
    <script src="../../public/js/email-service.js"></script>
    <script src="../../public/js/dark-mode.js"></script>
    <script src="../../public/js/main.js"></script>
    <script>
        const draftFields = ['local', 'descricao', 'prioridade', 'tipo_id', 'setor_id', 'curso', 'responsavel_id'];

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('request-form');
            const submitBtn = document.getElementById('submit-btn');
            const setorField = document.getElementById('setor_id');
            const responsavelField = document.getElementById('responsavel_id');
            const responsavelHelper = document.getElementById('responsavel-helper');

            // Validação do formulário
            const validator = new AppUtils.FormValidator(form, {
                local: {
                    required: true,
                    minLength: 2
                },
                descricao: {
                    required: true,
                    minLength: 10
                },
                prioridade: {
                    required: true
                },
                tipo_id: {
                    required: true
                },
                setor_id: {
                    required: true
                },
                curso: {
                    required: true,
                    minLength: 3
                },
                responsavel_id: {
                    required: true
                }
            });

            // Contador de caracteres para descrição
            const descricaoField = document.getElementById('descricao');
            const charCounter = document.createElement('div');
            charCounter.className = 'form-text text-end';
            descricaoField.parentNode.appendChild(charCounter);

            descricaoField.addEventListener('input', function() {
                const count = this.value.length;
                charCounter.textContent = `${count} caracteres`;
                charCounter.style.color = count < 10 ? 'var(--color-danger)' : 'var(--color-success)';
            });

            // Preview do arquivo
            document.getElementById('anexo').addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const fileInfo = document.createElement('div');
                    fileInfo.className = 'alert alert-info mt-2';
                    fileInfo.innerHTML = `
                        <strong>Arquivo selecionado:</strong><br>
                        📄 ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)
                    `;

                    // Remove preview anterior
                    const existingPreview = this.parentNode.querySelector('.alert');
                    if (existingPreview) {
                        existingPreview.remove();
                    }

                    this.parentNode.appendChild(fileInfo);
                }
            });

            // Submit do formulário
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!validator.validate()) {
                    AppUtils.Toast.show('Por favor, corrija os erros no formulário', 'error');
                    return;
                }

                // Desabilitar botão para evitar duplo submit
                submitBtn.disabled = true;
                submitBtn.textContent = '⏳ Criando...';

                // Submit normal do formulário
                setTimeout(() => {
                    this.submit();
                }, 500);
            });

            // Auto-save no localStorage
            draftFields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                if (field) {
                    // Carregar valor salvo
                    const savedValue = localStorage.getItem(`request_draft_${fieldName}`);
                    if (savedValue && !field.value) {
                        field.value = savedValue;
                    }

                    // Salvar mudanças
                    field.addEventListener('input', function() {
                        localStorage.setItem(`request_draft_${fieldName}`, this.value);
                    });
                }
            });

            const handleResponsavelOptions = () => {
                if (!responsavelField || !responsavelHelper) {
                    return;
                }

                const setorValue = setorField.value;
                let available = 0;

                Array.from(responsavelField.options).forEach(option => {
                    if (option.value === '') {
                        option.hidden = false;
                        option.disabled = false;
                        return;
                    }

                    const optionSetor = option.dataset.setor || '0';
                    const isGlobal = optionSetor === '0';
                    const matches = setorValue ? (optionSetor === setorValue || isGlobal) : false;

                    option.hidden = !matches;
                    option.disabled = !matches;

                    if (!matches && option.selected) {
                        option.selected = false;
                    }

                    if (matches) {
                        available++;
                    }
                });

                if (!setorValue) {
                    responsavelHelper.textContent = 'Selecione um setor para listar os responsáveis disponíveis.';
                    responsavelHelper.classList.remove('text-danger');
                    return;
                }

                if (available === 0) {
                    responsavelHelper.textContent = 'Nenhum responsável cadastrado para este setor. Contate o administrador.';
                    responsavelHelper.classList.add('text-danger');
                } else {
                    responsavelHelper.textContent = `Responsáveis disponíveis: ${available}.`;
                    responsavelHelper.classList.remove('text-danger');
                }
            };

            if (setorField && responsavelField) {
                setorField.addEventListener('change', handleResponsavelOptions);
                handleResponsavelOptions();
            }

            // Trigger contador inicial
            descricaoField.dispatchEvent(new Event('input'));
        });

        // Função para limpar formulário
        function resetForm() {
            if (confirm('Deseja realmente limpar todos os campos?')) {
                document.getElementById('request-form').reset();

                // Limpar localStorage
                draftFields.forEach(fieldName => {
                    localStorage.removeItem(`request_draft_${fieldName}`);
                });

                // Remover preview de arquivo
                const filePreview = document.querySelector('#anexo').parentNode.querySelector('.alert');
                if (filePreview) {
                    filePreview.remove();
                }

                AppUtils.Toast.show('Formulário limpo', 'info');
            }
        }

        // Limpar rascunhos quando formulário é enviado com sucesso
        <?php if ($success): ?>
            draftFields.forEach(fieldName => {
                localStorage.removeItem(`request_draft_${fieldName}`);
            });
        <?php endif; ?>
    </script>
</body>

</html>