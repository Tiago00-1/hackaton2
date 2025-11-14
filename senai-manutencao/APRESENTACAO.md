# 🎤 GUIA DE APRESENTAÇÃO - HACKATHON SENAI 2025

## ⏱️ TEMPO: 3 MINUTOS

---

## 📋 ROTEIRO DE APRESENTAÇÃO

### 1️⃣ INTRODUÇÃO (30 segundos)

**Fala sugerida:**
> "Bom dia/tarde! Apresentamos o **Sistema de Gerenciamento de TI e Manutenção do SENAI Alagoinhas**. 
> 
> Nosso sistema resolve o problema atual de solicitações descentralizadas feitas por WhatsApp, chatbot ou pessoalmente, que dificultam o acompanhamento e a gestão eficiente.
>
> Criamos uma plataforma completa, profissional e intuitiva para centralizar e organizar todas as solicitações."

---

### 2️⃣ DEMONSTRAÇÃO DO SISTEMA (1min 30s)

#### 🔵 Página Inicial (10s)
- Mostrar design moderno e profissional
- Destacar **Dark Mode**
- Clicar em "Solicitante"

**Fala:**
> "A página inicial oferece dois tipos de acesso distintos com design moderno e responsivo."

#### 🔵 Criar Solicitação (30s)
- Preencher formulário rapidamente:
  - Nome: "João Silva"
  - Matrícula: "2024001"
  - Local: "Laboratório 1"
  - Descrição: "Computador 5 não liga"
  - Tipo: "Suporte de TI"
  - Prioridade: "Urgente"
- Mostrar validação em tempo real
- Anexar imagem (demonstrar upload)
- Enviar

**Fala:**
> "O solicitante preenche um formulário completo com validações em tempo real, pode anexar imagens e selecionar a prioridade."

#### 🔵 Minhas Solicitações (15s)
- Mostrar lista de solicitações
- Ver detalhes de uma solicitação
- Mostrar histórico de movimentações

**Fala:**
> "O solicitante pode acompanhar suas solicitações em tempo real, ver o status e o histórico completo."

#### 🔵 Área Administrativa (35s)
- Fazer logout
- Login como admin (admin / 1234)
- **DASHBOARD:**
  - Cards com estatísticas animadas
  - Gráfico de Pizza - Solicitações por Tipo
  - Gráfico de Linha - Evolução Mensal
  - Tabela de setores com métricas
- Clicar em "Todas Solicitações"
- Abrir uma solicitação
- Atualizar status para "Em andamento"
- Adicionar comentário
- Salvar

**Fala:**
> "O painel administrativo oferece dashboard completo com gráficos interativos usando Chart.js, estatísticas em tempo real e gestão total das solicitações."

---

### 3️⃣ DESTAQUE TÉCNICO - BANCO DE DADOS (30s)

#### Abrir MySQL Workbench ou phpMyAdmin

**Mostrar:**
- Diagrama ER (se preparado)
- Ou navegar nas tabelas:
  - `usuarios` - Usuários com senha criptografada
  - `solicitacoes` - Com campos obrigatórios
  - `movimentacoes` - Histórico completo
  - `setores` e `tipos_solicitacao`

**Fala:**
> "Nosso banco de dados está totalmente normalizado com 5 tabelas principais:
> - Usuários com senhas criptografadas
> - Solicitações com todos os campos obrigatórios
> - Movimentações para auditoria completa
> - Relacionamentos com chaves estrangeiras e CASCADE
> - Índices para performance otimizada"

---

### 4️⃣ DIFERENCIAIS E EXTRAS (30s)

**Fala:**
> "Implementamos TODOS os requisitos obrigatórios E todos os extras:
> 
> ✅ **Dashboard com gráficos** - Chart.js interativo
> ✅ **Exportação de relatórios** - PDF e CSV profissionais
> ✅ **Upload de imagens** - Com validação e segurança
> ✅ **Dark Mode** - Tema escuro completo
> ✅ **Design Premium** - Interface moderna e responsiva
> ✅ **Validações avançadas** - Real-time com feedback visual
> ✅ **Notificações Toast** - Alertas elegantes
> ✅ **Segurança robusta** - CSRF, SQL Injection, XSS
> ✅ **Código limpo** - Padrão MVC, PSR, bem documentado"

---

## 🎯 PONTOS-CHAVE PARA DESTACAR

### 💪 Atendimento aos Requisitos (50 pontos)
- ✅ **100%** dos requisitos obrigatórios
- ✅ **Todos** os requisitos extras
- ✅ Funciona completamente offline

### 🎨 Criatividade e Interface (15 pontos)
- ✅ Design moderno e profissional
- ✅ Animações suaves
- ✅ Dark Mode
- ✅ Responsivo total
- ✅ UX excepcional

### 💻 Boas Práticas (15 pontos)
- ✅ Padrão MVC
- ✅ Código limpo e comentado
- ✅ Funções reutilizáveis
- ✅ Nomenclatura clara
- ✅ Separação de responsabilidades

### 🏭 Aplicabilidade (10 pontos)
- ✅ Resolve problema real do SENAI
- ✅ Fácil de usar
- ✅ Escalável
- ✅ Manutenível

### 🎤 Apresentação (10 pontos)
- ✅ Clara e objetiva
- ✅ Demonstração fluida
- ✅ Domínio total do código
- ✅ Explicação técnica sólida

---

## 📊 DADOS PARA MEMORIZAR

- **5 Tabelas principais** no banco de dados
- **2 Tipos de acesso**: Solicitante e Administrador
- **3 Status**: Aberta, Em andamento, Concluída
- **3 Prioridades**: Urgente, Média, Baixa
- **7 Tipos de solicitação** cadastrados
- **5 Setores** responsáveis
- **2 Formatos de exportação**: PDF e CSV
- **100% Responsivo** - Mobile, Tablet, Desktop
- **Dark Mode** - Tema claro e escuro

---

## 🚀 CHECKLIST PRÉ-APRESENTAÇÃO

### ✅ Ambiente
- [ ] XAMPP iniciado (Apache e MySQL)
- [ ] Banco de dados importado e populado
- [ ] Navegador aberto em: `http://localhost/senai-manutencao`
- [ ] MySQL Workbench ou phpMyAdmin aberto
- [ ] Testar todo o fluxo antes

### ✅ Demonstração
- [ ] Dados de teste prontos para preencher rapidamente
- [ ] Imagem para upload separada
- [ ] Credenciais admin anotadas: admin / 1234
- [ ] Atalhos de teclado salvos (Ctrl+Tab para trocar abas)

### ✅ Backup
- [ ] Segunda aba do navegador com sistema aberto
- [ ] Ter decorado o fluxo principal
- [ ] Saber onde estão todas as funcionalidades

---

## 💡 DICAS IMPORTANTES

### ✅ O QUE FAZER:
- ✅ Falar com confiança e clareza
- ✅ Mostrar entusiasmo pelo projeto
- ✅ Destacar os diferenciais técnicos
- ✅ Demonstrar domínio do código
- ✅ Ser objetivo e direto
- ✅ Sorrir e manter contato visual
- ✅ Preparar-se para perguntas sobre:
  - Segurança (CSRF, SQL Injection)
  - Banco de dados (relacionamentos, índices)
  - Tecnologias (por que escolheu cada uma)
  - Escalabilidade

### ❌ O QUE EVITAR:
- ❌ Falar muito rápido
- ❌ Gagueiras e hesitações
- ❌ Erro técnico visível (testar antes!)
- ❌ Perder tempo com detalhes menos importantes
- ❌ Ultrapassar os 3 minutos

---

## 🎬 ROTEIRO CRONOMETRADO

| Tempo | Ação |
|-------|------|
| 0:00-0:30 | Introdução e contexto do problema |
| 0:30-2:00 | Demonstração completa do sistema |
| 2:00-2:30 | Mostrar banco de dados |
| 2:30-3:00 | Destacar diferenciais e tecnologias |

---

## 📱 PERGUNTAS FREQUENTES - RESPOSTAS PRONTAS

### "Por que PHP?"
> "PHP é robusto, rápido, amplamente utilizado e funciona perfeitamente com XAMPP, que é o ambiente disponibilizado pelo hackathon."

### "Como garantiram a segurança?"
> "Implementamos múltiplas camadas: senhas com bcrypt, prepared statements contra SQL injection, CSRF tokens, XSS protection com htmlspecialchars e validação de uploads."

### "O sistema é escalável?"
> "Sim! Utilizamos padrão MVC, código modular, banco normalizado com índices e cache. Pode suportar milhares de solicitações sem problemas."

### "Quanto tempo levou?"
> "Trabalhamos intensamente nos 2 dias do hackathon, com foco em qualidade, boas práticas e experiência do usuário."

---

## 🏆 MENSAGEM FINAL

**Fala de encerramento:**
> "Nosso sistema está completo, testado e pronto para uso. Implementamos todos os requisitos com excelência técnica, design profissional e foco na experiência do usuário. Obrigado pela atenção!"

---

<div align="center">

**BOA SORTE! 🍀**

*Vocês têm um projeto excepcional!*

</div>

---

## 📞 CONTATOS DE EMERGÊNCIA

**Se algo der errado:**

1. **Banco não conecta**: Verificar se MySQL está rodando no XAMPP
2. **Página em branco**: Verificar erros no `php_error.log`
3. **Credenciais não funcionam**: Reimportar `database.sql`
4. **Upload não funciona**: Verificar permissões da pasta `uploads/`

---

## 🎯 ÚLTIMA CHECAGEM - 5 MINUTOS ANTES

- [ ] Sistema funcionando 100%
- [ ] Todos os membros sabem suas partes
- [ ] Cronômetro preparado
- [ ] Respirar fundo e relaxar
- [ ] Lembrar: vocês são os melhores! 💪

---

**AGORA É SUA HORA! ARRASE NA APRESENTAÇÃO! 🚀**
