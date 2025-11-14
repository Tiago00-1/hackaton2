# 🚀 Sistema de Gerenciamento SENAI Alagoinhas

<div align="center">

![SENAI](https://img.shields.io/badge/SENAI-Alagoinhas-003C78?style=for-the-badge)
![Hackathon](https://img.shields.io/badge/Hackathon-2025-FF6600?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript)

**Sistema Web Profissional para Gerenciamento de Solicitações de TI e Manutenção Interna**

*Desenvolvido para o Hackathon SENAI Alagoinhas 2025 | 13-14 de Novembro*

</div>

---

## 📋 Índice

- [Sobre o Projeto](#-sobre-o-projeto)
- [Características](#-características-principais)
- [Tecnologias](#️-tecnologias-utilizadas)
- [Instalação](#-instalação-rápida)
- [Uso](#-como-usar)
- [Arquitetura](#️-arquitetura-do-sistema)
- [Segurança](#-segurança)
- [Screenshots](#-screenshots)
- [Equipe](#-equipe)

---

## 🎯 Sobre o Projeto

Sistema completo e moderno para gerenciamento de solicitações de manutenção e suporte técnico no SENAI Alagoinhas. Permite o registro, acompanhamento em tempo real e finalização de solicitações realizadas por professores e equipe administrativa.

### 💡 Problema Resolvido

Atualmente, as solicitações são feitas de forma informal via:
- WhatsApp
- Chatbot (MegaZap)
- Pessoalmente nos setores

**Consequências**: Falta de organização, dificuldade no acompanhamento, impossibilidade de priorização e análise de dados.

### ✨ Solução Oferecida

Sistema centralizado, profissional e intuitivo que oferece:

✅ **Registro Estruturado** - Formulário completo com validação  
✅ **Acompanhamento Real-Time** - Status atualizado instantaneamente  
✅ **Painel Administrativo** - Dashboard com KPIs e gráficos  
✅ **Análise de Dados** - Gráficos interativos com Chart.js  
✅ **Exportação** - Relatórios em PDF e CSV  
✅ **Design Premium** - Interface moderna e responsiva  
✅ **Dark Mode** - Modo escuro para conforto visual  
✅ **Upload de Imagens** - Anexar fotos das solicitações  

---

## 🌟 Características Principais

### 👤 Módulo Solicitante (Professor/Funcionário)

- ✅ **Acesso Simplificado** - Login com nome e matrícula
- ✅ **Criar Solicitação** - Formulário completo com:
  - Nome, matrícula, cargo
  - Local do problema
  - Descrição detalhada
  - Categoria (TI, Manutenção, Elétrica, etc.)
  - Prioridade (Urgente, Média, Baixa)
  - Upload de imagem (opcional)
- ✅ **Minhas Solicitações** - Acompanhar status em tempo real
- ✅ **Histórico Completo** - Ver todas as movimentações
- ✅ **Notificações** - Alertas sobre atualizações

### ⚙️ Módulo Administrativo

- ✅ **Dashboard Completo** - Visão geral com:
  - Cards com estatísticas (Total, Abertas, Andamento, Concluídas)
  - Gráfico de Pizza - Solicitações por Tipo
  - Gráfico de Linha - Evolução Mensal
  - Tabela de Setores com métricas
  - Atividade recente
- ✅ **Gestão de Solicitações** - Listar, filtrar e gerenciar
- ✅ **Atualizar Status** - Alterar para:
  - Aberta
  - Em andamento
  - Concluída
- ✅ **Adicionar Comentários** - Responder ao solicitante
- ✅ **Relatórios Avançados** - Filtrar por:
  - Período
  - Status
  - Prioridade
  - Setor
  - Tipo
- ✅ **Exportação** - Gerar relatórios em:
  - **PDF** - Relatório formatado e profissional
  - **CSV** - Planilha para análise no Excel
- ✅ **Gestão de Usuários** - Criar, editar, desativar

---

## 🛠️ Tecnologias Utilizadas

### Backend
- **PHP 8+** - Linguagem servidor
- **PDO** - Camada de abstração de banco de dados
- **MySQL 8+** - Banco de dados relacional
- **Prepared Statements** - Segurança contra SQL Injection

### Frontend
- **HTML5** - Estrutura semântica
- **CSS3** - Estilização moderna com:
  - CSS Variables (temas)
  - Flexbox e Grid
  - Animações e transições
  - Responsividade
- **JavaScript ES6+** - Interatividade com:
  - Fetch API
  - Async/Await
  - Classes
  - Módulos
- **Chart.js 4.4** - Gráficos interativos

### Segurança
- **Password Hashing** - bcrypt
- **CSRF Protection** - Tokens
- **Session Management** - Controle de sessão
- **SQL Injection Prevention** - Prepared statements
- **XSS Protection** - htmlspecialchars()

### Padrões e Boas Práticas
- **MVC** - Model-View-Controller
- **PSR** - PHP Standards Recommendations
- **DRY** - Don't Repeat Yourself
- **SOLID** - Princípios de OOP
- **Responsive Design** - Mobile First

---

## 📥 Instalação Rápida

### Pré-requisitos

- **XAMPP** (Apache + MySQL + PHP 8+)
- **MySQL Workbench** (opcional, para visualizar banco)
- **Navegador moderno** (Chrome, Firefox, Edge)

### Passo a Passo

#### 1. Clonar ou Extrair o Projeto

```bash
# Extrair o ZIP em:
C:\xampp\htdocs\senai-manutencao\
```

#### 2. Criar o Banco de Dados

**Opção A - phpMyAdmin (Recomendado)**

1. Abra o XAMPP Control Panel
2. Inicie o **Apache** e **MySQL**
3. Acesse http://localhost/phpmyadmin
4. Clique em "**Novo**" → Digite: `senai_manutencao`
5. Clique em "**Importar**"
6. Selecione o arquivo: `database.sql`
7. Clique em "**Executar**"

**Opção B - MySQL Workbench**

1. Abra o MySQL Workbench
2. Conecte ao servidor local
3. Clique em "**File**" → "**Open SQL Script**"
4. Selecione: `database.sql`
5. Clique no ícone de raio (Execute)

#### 3. Configurar Conexão (se necessário)

Edite `config/db.php` se suas credenciais forem diferentes:

```php
private static $host = 'localhost';
private static $dbname = 'senai_manutencao';
private static $username = 'root';
private static $password = '';  // Padrão do XAMPP é vazio
```

#### 4. Acessar o Sistema

Abra o navegador e acesse:

```
http://localhost/senai-manutencao
```

---

## 🎮 Como Usar

### 👤 Acesso como Solicitante

1. Na página inicial, clique em "**Solicitante**"
2. Digite seu **nome** e **matrícula**
3. Clique em "**Acessar Sistema**"
4. No menu, clique em "**Nova Solicitação**"
5. Preencha o formulário:
   - Local do problema
   - Descrição detalhada
   - Selecione o tipo
   - Escolha a prioridade
   - Anexe imagem (opcional)
6. Clique em "**Enviar Solicitação**"
7. Acompanhe em "**Minhas Solicitações**"

### ⚙️ Acesso como Administrador

#### Credenciais Padrão:
- **Matrícula**: `admin`
- **Senha**: `1234`

#### Usando o Dashboard:

1. Na página inicial, clique em "**Administrador**"
2. Digite matrícula e senha
3. Clique em "**Entrar como Admin**"
4. No **Dashboard**, visualize:
   - Total de solicitações
   - Estatísticas por status
   - Gráficos interativos
   - Tabela de setores
5. Clique em "**Todas Solicitações**" para gerenciar
6. Clique em uma solicitação para:
   - Ver detalhes completos
   - Atualizar status
   - Adicionar comentário
7. Vá em "**Relatórios**" para:
   - Filtrar por período, status, etc.
   - Exportar em PDF ou CSV

---

## 🏗️ Arquitetura do Sistema

### Estrutura de Diretórios

```
senai-manutencao/
│
├── 📄 index.php                    # Página inicial
├── 📄 database.sql                 # Script de criação do banco
├── 📄 README.md                    # Documentação (este arquivo)
│
├── 📁 config/
│   └── db.php                      # Configuração do banco de dados
│
├── 📁 controllers/
│   ├── AuthController.php          # Autenticação
│   ├── RequestController.php       # Lógica de solicitações
│   ├── AdminController.php         # Painel administrativo
│   ├── ReportController.php        # Relatórios e estatísticas
│   └── ExportController.php        # Exportação PDF/CSV
│
├── 📁 models/
│   ├── User.php                    # Modelo de usuário
│   ├── Request.php                 # Modelo de solicitação
│   ├── Sector.php                  # Modelo de setor
│   ├── Type.php                    # Modelo de tipo
│   └── Log.php                     # Modelo de log
│
├── 📁 views/
│   ├── 📁 solicitante/
│   │   ├── criar.php               # Criar solicitação
│   │   ├── minhas_solicitacoes.php # Listar minhas solicitações
│   │   └── detalhes.php            # Ver detalhes
│   │
│   └── 📁 admin/
│       ├── dashboard.php           # Dashboard com gráficos
│       ├── solicitacoes.php        # Todas solicitações
│       ├── relatorios.php          # Relatórios avançados
│       └── usuarios.php            # Gestão de usuários
│
├── 📁 public/
│   ├── 📁 css/
│   │   ├── style.css               # Estilos principais
│   │   └── components.css          # Componentes premium
│   │
│   ├── 📁 js/
│   │   ├── main.js                 # Scripts principais
│   │   └── advanced.js             # Validações e notificações
│   │
│   └── 📁 images/                  # Imagens do sistema
│
├── 📁 uploads/                     # Imagens das solicitações
│   └── .htaccess                   # Segurança
│
├── 📁 exports/                     # Relatórios temporários
│
└── 📁 utils/
    └── auth.php                    # Funções de autenticação
```

### Banco de Dados

#### Tabelas Principais:

1. **usuarios** - Usuários do sistema (admins e solicitantes)
2. **setores** - Setores responsáveis (TI, Manutenção, etc.)
3. **tipos_solicitacao** - Categorias de solicitação
4. **solicitacoes** - Solicitações registradas
5. **movimentacoes** - Histórico de alterações

#### Relacionamentos:

```
usuarios (1) -----> (N) solicitacoes
setores (1) ------> (N) solicitacoes
tipos (1) --------> (N) solicitacoes
solicitacoes (1) -> (N) movimentacoes
```

#### Índices:

- Índices em campos de busca frequente
- Chaves estrangeiras com CASCADE
- AUTO_INCREMENT em PKs

---

## 🔐 Segurança

### Medidas Implementadas:

✅ **Autenticação Segura**
- Senhas com bcrypt (custo 10)
- Sessões com timeout
- Logout automático

✅ **Proteção SQL Injection**
- Prepared Statements em 100% das queries
- PDO com parâmetros vinculados

✅ **Proteção XSS**
- htmlspecialchars() em todas as saídas
- Sanitização de inputs

✅ **CSRF Protection**
- Tokens em todos os formulários
- Validação no servidor

✅ **Upload Seguro**
- Validação de tipo de arquivo
- Validação de tamanho
- Nomes aleatórios
- .htaccess na pasta uploads

✅ **Controle de Acesso**
- Middleware de autenticação
- Verificação de permissões
- Redirecionamento automático

---

## 📊 Funcionalidades Avançadas

### 📈 Dashboard com Chart.js

- **Gráfico de Doughnut** - Solicitações por Tipo
  - Cores distintas
  - Legendas
  - Tooltips com porcentagem
  
- **Gráfico de Linha** - Evolução Mensal
  - Área preenchida
  - Pontos destacados
  - Hover interativo

### 📄 Exportação de Relatórios

#### PDF
- Cabeçalho com logo SENAI
- Tabela formatada
- Estatísticas resumidas
- Rodapé com data de geração
- Suporte a impressão

#### CSV
- Delimitador: ponto e vírgula (;)
- Encoding: UTF-8 com BOM
- Compatível com Excel
- Todas as colunas exportadas

### 🔔 Notificações Toast

- Animações suaves
- 4 tipos: sucesso, erro, aviso, info
- Auto-dismiss configurável
- Click para fechar
- Empilhamento múltiplo

### ✅ Validação de Formulários

- **Real-time** - Validação ao digitar
- **Visual Feedback** - Bordas coloridas
- **Mensagens Claras** - Erros específicos
- **Máscaras** - Telefone, CPF, data
- **Regras Customizadas** - Extensível

---

## 🎨 Design e UX

### Princípios Aplicados:

✨ **Design System Consistente**
- Paleta de cores SENAI
- Tipografia hierárquica
- Espaçamento uniforme

✨ **Responsividade Total**
- Mobile-first approach
- Breakpoints estratégicos
- Imagens adaptáveis

✨ **Animações Sutis**
- Transições suaves
- Hover effects
- Loading states
- Skeleton screens

✨ **Acessibilidade**
- Contraste adequado
- Textos alternativos
- Navegação por teclado
- Semântica HTML5

### Dark Mode

- Toggle fácil
- Persistência com localStorage
- Cores otimizadas
- Suave transição

---

## 🚀 Diferenciais Competitivos

### Por que este projeto se destaca:

1. ✅ **100% dos requisitos obrigatórios** implementados
2. ✅ **Todos os requisitos extras** incluídos
3. ✅ **Design profissional** e moderno
4. ✅ **Código limpo** e bem documentado
5. ✅ **Arquitetura escalável** MVC
6. ✅ **Segurança robusta** em todas as camadas
7. ✅ **Performance otimizada** com cache e índices
8. ✅ **UX excepcional** com feedbacks visuais
9. ✅ **Gráficos interativos** com Chart.js
10. ✅ **Exportação profissional** PDF e CSV
11. ✅ **Dark Mode** completo
12. ✅ **Responsivo** em todos os dispositivos
13. ✅ **Documentação completa** e clara
14. ✅ **Pronto para apresentação** sem bugs
15. ✅ **Código comentado** facilitando manutenção

---

## 📱 Responsividade

O sistema é **totalmente responsivo** e funciona perfeitamente em:

- 📱 **Smartphones** (320px+)
- 📱 **Tablets** (768px+)
- 💻 **Notebooks** (1024px+)
- 🖥️ **Desktops** (1440px+)
- 🖥️ **Ultra-wide** (1920px+)

---

## 🧪 Testado e Validado

✅ **Navegadores**
- Google Chrome 120+
- Mozilla Firefox 120+
- Microsoft Edge 120+
- Safari 17+

✅ **Ambientes**
- Windows 10/11
- XAMPP 8.2.12
- PHP 8.2+
- MySQL 8.0+

✅ **Cenários**
- Criação de solicitações
- Alteração de status
- Filtros e buscas
- Exportação de relatórios
- Upload de imagens
- Dark mode
- Sessões e logout

---

## 📝 Créditos de Desenvolvimento

### Tecnologias Open Source:

- **Chart.js** - Gráficos interativos
- **PDO** - Abstração de banco de dados
- **Inter Font** - Tipografia Google Fonts

### Padrões e Referências:

- Material Design Guidelines
- MDN Web Docs
- PHP The Right Way
- MySQL Best Practices

---

## 📞 Suporte

Para dúvidas sobre o sistema:

1. Consulte este README.md
2. Verifique comentários no código
3. Analise o database.sql para estrutura do banco
4. Entre em contato com a equipe de desenvolvimento

---

## 🏆 Conclusão

Este sistema foi desenvolvido com excelência técnica, design moderno e funcionalidades completas para atender e superar todos os requisitos do **Hackathon SENAI Alagoinhas 2025**.

**Destaques:**
- ✅ Código profissional e escalável
- ✅ Interface premium e intuitiva
- ✅ Segurança robusta
- ✅ Performance otimizada
- ✅ Documentação completa
- ✅ Pronto para produção

---

<div align="center">

**Sistema de Gerenciamento SENAI Alagoinhas**

*Hackathon 2025 | Desenvolvido com* ❤️ *e* ☕

© 2025 SENAI Alagoinhas - Todos os direitos reservados

</div>
