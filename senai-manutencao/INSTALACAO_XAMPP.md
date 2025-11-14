# 🚀 Guia de Instalação - Sistema SENAI Alagoinhas

## 📋 Requisitos do Sistema

### Software Necessário
- **XAMPP** versão 8.0 ou superior
  - Apache 2.4+
  - MySQL 8.0+ (Porta 3306)
  - PHP 8.0+
- **Navegador Web Moderno**
  - Google Chrome (recomendado)
  - Firefox
  - Edge

### Especificações Mínimas
- **Sistema Operacional**: Windows 10/11, Linux ou macOS
- **RAM**: 4GB mínimo (8GB recomendado)
- **Espaço em Disco**: 500MB livres
- **Resolução de Tela**: 1366x768 mínimo

---

## 📥 Passo 1: Instalar o XAMPP

### Windows

1. **Baixar o XAMPP**
   - Acesse: https://www.apachefriends.org/pt_br/index.html
   - Baixe a versão mais recente para Windows

2. **Executar o Instalador**
   - Execute o arquivo `xampp-windows-x64-X.X.X-installer.exe`
   - Clique em "Next" em todas as etapas
   - Instale no diretório padrão: `C:\xampp`

3. **Concluir Instalação**
   - Marque a opção "Start XAMPP Control Panel"
   - Clique em "Finish"

### Linux

```bash
# Baixar XAMPP
wget https://www.apachefriends.org/xampp-files/8.2.12/xampp-linux-x64-8.2.12-0-installer.run

# Dar permissão de execução
chmod +x xampp-linux-x64-8.2.12-0-installer.run

# Executar instalador
sudo ./xampp-linux-x64-8.2.12-0-installer.run

# Iniciar XAMPP
sudo /opt/lampp/lampp start
```

---

## ⚙️ Passo 2: Configurar o XAMPP

### 1. Abrir o XAMPP Control Panel

**Windows:**
- Procure por "XAMPP Control Panel" no menu Iniciar
- Execute como Administrador

**Linux:**
```bash
sudo /opt/lampp/manager-linux-x64.run
```

### 2. Iniciar os Serviços

No XAMPP Control Panel:

1. **Iniciar Apache**
   - Clique no botão "Start" ao lado de "Apache"
   - Aguarde até o status ficar verde

2. **Iniciar MySQL**
   - Clique no botão "Start" ao lado de "MySQL"
   - Aguarde até o status ficar verde
   - **Verifique se está rodando na porta 3306**

### 3. Verificar Portas

Se houver conflito de portas:

**Apache (Porta 80):**
- Clique em "Config" > "Apache (httpd.conf)"
- Procure por `Listen 80` e altere para `Listen 8080`
- Salve e reinicie o Apache

**MySQL (Porta 3306):**
- Clique em "Config" > "my.ini"
- Procure por `port=3306`
- **NÃO ALTERE** - o sistema está configurado para porta 3306

---

## 📂 Passo 3: Instalar o Sistema

### 1. Copiar Arquivos do Projeto

**Windows:**
```cmd
# Extrair o arquivo ZIP do projeto
# Copiar a pasta "senai-manutencao" para:
C:\xampp\htdocs\senai-manutencao
```

**Linux:**
```bash
# Extrair o arquivo ZIP
unzip senai-manutencao.zip

# Copiar para htdocs
sudo cp -r senai-manutencao /opt/lampp/htdocs/

# Dar permissões
sudo chmod -R 755 /opt/lampp/htdocs/senai-manutencao
sudo chown -R daemon:daemon /opt/lampp/htdocs/senai-manutencao
```

### 2. Estrutura de Diretórios

Verifique se a estrutura está correta:

```
C:\xampp\htdocs\senai-manutencao\
├── config/
│   └── db.php
├── controllers/
├── models/
├── views/
│   ├── admin/
│   └── solicitante/
├── public/
│   ├── css/
│   ├── js/
│   └── uploads/
├── utils/
├── database.sql
└── index.php
```

---

## 🗄️ Passo 4: Configurar o Banco de Dados

### 1. Acessar o phpMyAdmin

1. Abra seu navegador
2. Acesse: `http://localhost/phpmyadmin`
3. Usuário: `root`
4. Senha: *(deixe em branco)*

### 2. Criar o Banco de Dados

**Opção A: Interface Gráfica**

1. No phpMyAdmin, clique em "Novo" (New) no menu lateral
2. Nome do banco: `senai_manutencao`
3. Collation: `utf8mb4_unicode_ci`
4. Clique em "Criar" (Create)

**Opção B: SQL**

```sql
CREATE DATABASE senai_manutencao 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

### 3. Importar o Script SQL

1. Selecione o banco `senai_manutencao` no menu lateral
2. Clique na aba "Importar" (Import)
3. Clique em "Escolher arquivo" (Choose file)
4. Selecione o arquivo: `C:\xampp\htdocs\senai-manutencao\database.sql`
5. Clique em "Executar" (Go)
6. Aguarde a mensagem de sucesso

### 4. Verificar Importação

Execute no phpMyAdmin (aba SQL):

```sql
USE senai_manutencao;
SHOW TABLES;
```

Você deve ver as seguintes tabelas:
- `usuarios`
- `solicitacoes`
- `setores`
- `tipos_solicitacao`
- `movimentacoes`
- `logs`
- `notificacoes`

---

## 🔧 Passo 5: Configurar o Sistema

### 1. Verificar Configuração do Banco

Abra o arquivo: `C:\xampp\htdocs\senai-manutencao\config\db.php`

Verifique se as configurações estão corretas:

```php
private static $host = 'localhost';
private static $port = '3306';      // PORTA MYSQL
private static $dbname = 'senai_manutencao';
private static $username = 'root';
private static $password = '';      // Vazio no XAMPP padrão
```

### 2. Criar Diretório de Uploads

**Windows:**
```cmd
mkdir C:\xampp\htdocs\senai-manutencao\public\uploads
```

**Linux:**
```bash
sudo mkdir -p /opt/lampp/htdocs/senai-manutencao/public/uploads
sudo chmod 777 /opt/lampp/htdocs/senai-manutencao/public/uploads
```

### 3. Configurar Permissões (Linux apenas)

```bash
sudo chmod -R 755 /opt/lampp/htdocs/senai-manutencao
sudo chmod -R 777 /opt/lampp/htdocs/senai-manutencao/public/uploads
```

---

## 🌐 Passo 6: Acessar o Sistema

### 1. Abrir no Navegador

Acesse: **http://localhost/senai-manutencao**

### 2. Credenciais de Acesso

**Administrador:**
- Matrícula: `admin`
- Senha: `admin123`

**Solicitantes (Professores/Funcionários):**
- Matrícula: `2024001` (ou qualquer outra da tabela)
- Nome: Conforme cadastrado

### 3. Testar Funcionalidades

1. **Login como Admin**
   - Acesse o dashboard
   - Visualize estatísticas
   - Gerencie solicitações

2. **Login como Solicitante**
   - Crie uma nova solicitação
   - Visualize suas solicitações
   - Acompanhe status

---

## ✅ Passo 7: Verificar Instalação

### Checklist de Verificação

- [ ] XAMPP instalado e rodando
- [ ] Apache iniciado (porta 80 ou 8080)
- [ ] MySQL iniciado (porta 3306)
- [ ] Banco de dados criado
- [ ] Tabelas importadas
- [ ] Sistema acessível no navegador
- [ ] Login funcionando
- [ ] Dashboard carregando
- [ ] Criação de solicitações funcionando

### Comandos de Teste

**Teste 1: Verificar Apache**
```
http://localhost
```
Deve mostrar a página inicial do XAMPP

**Teste 2: Verificar phpMyAdmin**
```
http://localhost/phpmyadmin
```
Deve abrir o phpMyAdmin

**Teste 3: Verificar Sistema**
```
http://localhost/senai-manutencao
```
Deve abrir a tela de login do sistema

---

## 🐛 Solução de Problemas

### Problema: Apache não inicia

**Causa:** Porta 80 em uso

**Solução:**
1. Feche Skype, IIS ou outros programas que usam porta 80
2. OU altere a porta do Apache para 8080 (ver Passo 2.3)

### Problema: MySQL não inicia

**Causa:** Porta 3306 em uso

**Solução:**
```cmd
# Windows - Parar serviço MySQL
net stop MySQL80

# Ou desinstalar MySQL standalone se instalado
```

### Problema: Erro ao importar database.sql

**Causa:** Arquivo muito grande ou timeout

**Solução:**
1. Edite `C:\xampp\php\php.ini`
2. Altere:
```ini
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
post_max_size = 50M
upload_max_filesize = 50M
```
3. Reinicie o Apache

### Problema: Erro de conexão com banco

**Causa:** Configurações incorretas

**Solução:**
1. Verifique `config/db.php`
2. Teste conexão no phpMyAdmin
3. Verifique se o banco foi criado

### Problema: Página em branco

**Causa:** Erro de PHP não exibido

**Solução:**
1. Edite `C:\xampp\php\php.ini`
2. Altere:
```ini
display_errors = On
error_reporting = E_ALL
```
3. Reinicie o Apache
4. Verifique o log: `C:\xampp\apache\logs\error.log`

### Problema: Upload de arquivos não funciona

**Causa:** Permissões incorretas

**Solução Windows:**
```cmd
# Dar permissão total na pasta uploads
icacls "C:\xampp\htdocs\senai-manutencao\public\uploads" /grant Everyone:F
```

**Solução Linux:**
```bash
sudo chmod 777 /opt/lampp/htdocs/senai-manutencao/public/uploads
```

---

## 📧 Configuração Opcional: EmailJS

Para ativar o envio automático de emails:

### 1. Criar Conta no EmailJS

1. Acesse: https://www.emailjs.com/
2. Crie uma conta gratuita
3. Adicione um serviço de email (Gmail, Outlook, etc)

### 2. Criar Templates

Crie 3 templates com os IDs:
- `template_nova_solicitacao`
- `template_solicitacao_concluida`
- `template_status_atualizado`

### 3. Configurar no Sistema

Edite: `public/js/email-service.js`

```javascript
config: {
    serviceId: 'SEU_SERVICE_ID',
    publicKey: 'SUA_PUBLIC_KEY',
    // ...
}
```

---

## 🎯 Funcionalidades Principais

### Para Administradores
✅ Dashboard com estatísticas em tempo real
✅ Gerenciamento completo de solicitações
✅ Filtros avançados (tipo, setor, período, curso, prioridade)
✅ Exportação em PDF e CSV
✅ Gráficos interativos
✅ Gestão de usuários
✅ Logs de auditoria
✅ Dark Mode

### Para Solicitantes
✅ Criar solicitações facilmente
✅ Upload de imagens/documentos
✅ Acompanhar status em tempo real
✅ Visualizar histórico completo
✅ Avaliar atendimento
✅ Receber notificações

---

## 📱 Compatibilidade

### Navegadores Suportados
- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Edge 90+
- ✅ Safari 14+
- ✅ Opera 76+

### Dispositivos
- ✅ Desktop (1920x1080 e superiores)
- ✅ Laptop (1366x768 e superiores)
- ✅ Tablet (768x1024)
- ✅ Mobile (375x667 e superiores)

---

## 🔒 Segurança

### Medidas Implementadas
- ✅ Prepared Statements (proteção SQL Injection)
- ✅ Sanitização de inputs (proteção XSS)
- ✅ Hash de senhas (bcrypt)
- ✅ Controle de sessão
- ✅ Middleware de autenticação
- ✅ Logs de auditoria
- ✅ Validação de uploads

### Recomendações Adicionais
- Altere as senhas padrão
- Configure firewall
- Mantenha XAMPP atualizado
- Faça backups regulares

---

## 📞 Suporte

### Documentação Adicional
- `README.md` - Visão geral do projeto
- `ANALISE-COMPLETA.md` - Análise técnica detalhada
- `CREDENCIAIS.md` - Lista de usuários e senhas

### Contato
- **Equipe**: Hackathon SENAI Alagoinhas 2025
- **Instituição**: SENAI Alagoinhas
- **Evento**: Competição de Programação

---

## 🎉 Sistema Pronto!

Parabéns! O sistema está instalado e funcionando.

**Próximos Passos:**
1. Explore o dashboard administrativo
2. Crie solicitações de teste
3. Teste os filtros e exportações
4. Experimente o Dark Mode
5. Teste em diferentes dispositivos

**Boa sorte no Hackathon! 🚀**

---

*Desenvolvido com ❤️ para o SENAI Alagoinhas*
