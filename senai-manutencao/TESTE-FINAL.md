# ✅ CHECKLIST DE TESTE FINAL

## 🎯 OBJETIVO
Garantir que TUDO está funcionando perfeitamente antes da apresentação.

---

## 🔧 PRÉ-REQUISITOS

### XAMPP
- [ ] Apache está rodando (porta 80)
- [ ] MySQL está rodando (porta 3306)
- [ ] Luzes verdes no XAMPP Control Panel

### Banco de Dados
- [ ] Banco `senai_manutencao` existe
- [ ] Todas as 5 tabelas criadas
- [ ] Dados de exemplo inseridos
- [ ] Usuário admin existe
- [ ] Solicitantes de teste existem

### Navegador
- [ ] Cache limpo (Ctrl + Shift + Delete)
- [ ] Cookies limpos
- [ ] Console do desenvolvedor aberto (F12) para debug

---

## 📱 TESTE 1: PÁGINA INICIAL

### Acessar
```
http://localhost/senai-manutencao
```

### Verificar
- [ ] Página carrega sem erros
- [ ] Logo SENAI aparece
- [ ] Título "Sistema de Gerenciamento" visível
- [ ] Dois cards de acesso (Solicitante e Administrador)
- [ ] Design está bonito e profissional
- [ ] Botão de Dark Mode aparece
- [ ] Sem erros no console (F12)

### Testar Dark Mode
- [ ] Clicar no botão 🌙
- [ ] Tema escuro é aplicado
- [ ] Cores mudam corretamente
- [ ] Ícone muda para ☀️
- [ ] Toast de confirmação aparece
- [ ] Recarregar página (F5)
- [ ] Tema permanece escuro (localStorage)
- [ ] Voltar ao tema claro

---

## 👤 TESTE 2: ACESSO SOLICITANTE

### Login
- [ ] Clicar no card "Solicitante"
- [ ] Formulários deslizam suavemente
- [ ] Formulário de solicitante aparece
- [ ] Campos: Nome e Matrícula visíveis

### Preencher e Entrar
- [ ] Digite: Nome: "Teste Hackathon"
- [ ] Digite: Matrícula: "TESTE2025"
- [ ] Clicar "Acessar Sistema"
- [ ] Loading aparece
- [ ] Redireciona para área do solicitante
- [ ] Menu lateral aparece
- [ ] Nome do usuário aparece no cabeçalho

---

## 📝 TESTE 3: CRIAR SOLICITAÇÃO

### Navegar
- [ ] Clicar em "Nova Solicitação" no menu
- [ ] Página de criação carrega
- [ ] Formulário completo aparece

### Preencher Formulário
- [ ] **Local**: "Laboratório de Informática 1"
- [ ] **Descrição**: "Computador 05 apresenta tela azul ao iniciar. Problema pode ser na memória RAM ou HD."
- [ ] **Tipo**: Selecionar "Suporte de TI"
- [ ] **Setor**: Selecionar "Tecnologia da Informação"
- [ ] **Prioridade**: Selecionar "Urgente"

### Testar Validações
- [ ] Limpar campo "Local" e sair
- [ ] Borda vermelha aparece
- [ ] Mensagem de erro aparece
- [ ] Preencher novamente
- [ ] Borda fica verde

### Upload de Imagem (OPCIONAL)
- [ ] Clicar em "Escolher arquivo"
- [ ] Selecionar imagem (JPG, PNG)
- [ ] Nome do arquivo aparece
- [ ] Preview da imagem (se implementado)

### Enviar
- [ ] Clicar "Enviar Solicitação"
- [ ] Loading aparece
- [ ] Toast de sucesso aparece
- [ ] Redireciona para "Minhas Solicitações"
- [ ] Nova solicitação aparece na lista
- [ ] Status: "Aberta"
- [ ] Prioridade: "Urgente" (badge laranja/vermelho)

---

## 📋 TESTE 4: MINHAS SOLICITAÇÕES

### Verificar Lista
- [ ] Todas as solicitações do usuário aparecem
- [ ] Cards bem formatados
- [ ] Badges de status coloridos
- [ ] Data de abertura visível

### Ver Detalhes
- [ ] Clicar em "Ver Detalhes" na solicitação criada
- [ ] Modal ou página de detalhes abre
- [ ] Todas as informações aparecem:
  - [ ] ID da solicitação
  - [ ] Local
  - [ ] Descrição completa
  - [ ] Tipo
  - [ ] Setor responsável
  - [ ] Prioridade
  - [ ] Status
  - [ ] Data de abertura
  - [ ] Imagem (se anexou)
- [ ] Histórico de movimentações aparece
- [ ] Fechar detalhes

### Fazer Logout
- [ ] Clicar em "Sair"
- [ ] Confirmação aparece (se implementado)
- [ ] Redireciona para página inicial
- [ ] Sessão encerrada

---

## 🔐 TESTE 5: LOGIN ADMINISTRATIVO

### Acessar
- [ ] Na página inicial, clicar "Administrador"
- [ ] Formulário de login admin aparece
- [ ] Campos: Matrícula e Senha

### Login
- [ ] **Matrícula**: `admin`
- [ ] **Senha**: `1234`
- [ ] Clicar "Entrar como Admin"
- [ ] Loading aparece
- [ ] Redireciona para Dashboard

---

## 📊 TESTE 6: DASHBOARD ADMINISTRATIVO

### Verificar Carregamento
- [ ] Página carrega sem erros
- [ ] Cabeçalho com logo e menu
- [ ] Nome "Administrador" aparece
- [ ] Menu lateral com 5 opções

### Cards de Estatísticas
- [ ] 4 cards aparecem:
  - [ ] Total de Solicitações
  - [ ] Abertas
  - [ ] Em Andamento
  - [ ] Concluídas
- [ ] Números estão corretos
- [ ] Ícones coloridos
- [ ] Animação de contador (números incrementando)

### Gráfico de Pizza (Solicitações por Tipo)
- [ ] Gráfico carrega
- [ ] Cores distintas para cada tipo
- [ ] Legendas aparecem
- [ ] Hover mostra tooltip com:
  - [ ] Nome do tipo
  - [ ] Quantidade
  - [ ] Porcentagem

### Gráfico de Linha (Evolução Mensal)
- [ ] Gráfico carrega
- [ ] Linha azul SENAI
- [ ] Área preenchida
- [ ] Pontos laranjas destacados
- [ ] Hover mostra tooltip com dados
- [ ] Eixos X e Y corretos

### Tabela de Setores
- [ ] Tabela carrega
- [ ] Cabeçalhos corretos
- [ ] Dados de cada setor:
  - [ ] Nome
  - [ ] Total
  - [ ] Abertas
  - [ ] Em Andamento
  - [ ] Concluídas
  - [ ] % Conclusão
  - [ ] Tempo Médio
- [ ] Badges coloridos
- [ ] Hover destaca linha

### Atividade Recente (se implementado)
- [ ] Lista de atividades aparece
- [ ] Ordem cronológica (mais recente primeiro)
- [ ] Informações completas

---

## 📂 TESTE 7: TODAS AS SOLICITAÇÕES

### Navegar
- [ ] Clicar "Todas Solicitações" no menu
- [ ] Página carrega
- [ ] Lista de todas as solicitações aparece

### Verificar Listagem
- [ ] Tabela bem formatada
- [ ] Colunas corretas:
  - [ ] ID
  - [ ] Solicitante
  - [ ] Local
  - [ ] Tipo
  - [ ] Prioridade
  - [ ] Status
  - [ ] Data
  - [ ] Ações
- [ ] Paginação (se muitos registros)
- [ ] Busca/Filtro (se implementado)

### Abrir Solicitação
- [ ] Clicar em "Ver" ou "Editar" na solicitação teste
- [ ] Detalhes completos aparecem
- [ ] Formulário de atualização visível

### Atualizar Status
- [ ] Selecionar status: "Em andamento"
- [ ] Campo de comentário aparece
- [ ] Digitar: "Técnico designado. Verificando o computador."
- [ ] Clicar "Salvar" ou "Atualizar"
- [ ] Loading aparece
- [ ] Toast de sucesso
- [ ] Status atualizado na lista
- [ ] Badge muda de cor

### Testar Novamente
- [ ] Abrir a mesma solicitação
- [ ] Mudar status para: "Concluída"
- [ ] Comentário: "Problema resolvido. Memória RAM substituída."
- [ ] Salvar
- [ ] Verificar atualização

---

## 📈 TESTE 8: RELATÓRIOS

### Navegar
- [ ] Clicar "Relatórios" no menu
- [ ] Página carrega
- [ ] Filtros aparecem

### Testar Filtros
- [ ] Filtro por período:
  - [ ] Data início: Primeiro dia do mês
  - [ ] Data fim: Hoje
- [ ] Filtro por status: "Todas"
- [ ] Filtro por prioridade: "Todas"
- [ ] Filtro por setor: "Todos"
- [ ] Clicar "Filtrar" ou "Buscar"
- [ ] Resultados aparecem

### Estatísticas
- [ ] Cards resumo aparecem
- [ ] Gráficos atualizam com filtro

### Exportar CSV
- [ ] Clicar botão "Exportar CSV"
- [ ] Loading aparece
- [ ] Download inicia
- [ ] Arquivo `.csv` baixado
- [ ] Abrir no Excel/LibreOffice
- [ ] Verificar:
  - [ ] Cabeçalhos corretos
  - [ ] Dados completos
  - [ ] Encoding UTF-8 (acentos corretos)
  - [ ] Delimitador: ponto e vírgula

### Exportar PDF
- [ ] Clicar botão "Exportar PDF"
- [ ] Loading aparece
- [ ] PDF abre ou baixa
- [ ] Verificar:
  - [ ] Cabeçalho com logo SENAI
  - [ ] Título do relatório
  - [ ] Período filtrado
  - [ ] Estatísticas resumidas
  - [ ] Tabela formatada
  - [ ] Dados corretos
  - [ ] Rodapé com data

---

## 👥 TESTE 9: GESTÃO DE USUÁRIOS (se implementado)

### Navegar
- [ ] Clicar "Usuários" no menu
- [ ] Página carrega
- [ ] Lista de usuários aparece

### Verificar
- [ ] Usuários cadastrados visíveis
- [ ] Informações: Nome, Matrícula, Cargo, Tipo
- [ ] Botões de ação (Editar, Desativar)

### Criar Usuário (se implementado)
- [ ] Clicar "Novo Usuário"
- [ ] Formulário aparece
- [ ] Preencher dados
- [ ] Salvar
- [ ] Usuário aparece na lista

---

## 🔄 TESTE 10: RESPONSIVIDADE

### Desktop (1920x1080)
- [ ] Layout perfeito
- [ ] Sidebar visível
- [ ] Gráficos em grid 2 colunas
- [ ] Sem quebras

### Tablet (768x1024)
- [ ] Layout se adapta
- [ ] Menu colapsa (hamburger)
- [ ] Gráficos empilham
- [ ] Tabelas scrolláveis

### Mobile (375x667 - iPhone)
- [ ] Layout mobile-first
- [ ] Menu hamburger funciona
- [ ] Cards empilhados
- [ ] Fontes legíveis
- [ ] Botões tocáveis (min 44x44px)
- [ ] Formulários usáveis

**Como testar:**
1. F12 → Toggle device toolbar
2. Ou redimensionar janela do navegador
3. Testar em diferentes resoluções

---

## 🌓 TESTE 11: DARK MODE COMPLETO

### No Dashboard
- [ ] Ativar dark mode
- [ ] Fundo escuro
- [ ] Textos claros
- [ ] Cards com fundo escuro
- [ ] Gráficos adaptados
- [ ] Bordas visíveis
- [ ] Contraste adequado

### Em Todas as Páginas
- [ ] Navegar por todas as páginas
- [ ] Tema escuro persiste
- [ ] Nenhum elemento branco destoante
- [ ] Legibilidade mantida

---

## 🔐 TESTE 12: SEGURANÇA

### SQL Injection
- [ ] No login, tentar: `' OR '1'='1`
- [ ] Sistema deve bloquear ou não funcionar
- [ ] Sem erro exposto

### XSS
- [ ] Criar solicitação com descrição: `<script>alert('XSS')</script>`
- [ ] Salvar
- [ ] Ver detalhes
- [ ] Script não deve executar
- [ ] Aparece como texto

### CSRF
- [ ] Inspecionar formulário (F12)
- [ ] Verificar campo `csrf_token` existe
- [ ] Token tem valor aleatório

### Upload Seguro
- [ ] Tentar upload de .php
- [ ] Sistema deve rejeitar
- [ ] Apenas imagens permitidas

---

## ⚡ TESTE 13: PERFORMANCE

### Tempo de Carregamento
- [ ] Limpar cache
- [ ] Cronometrar tempo de carregamento
- [ ] Deve ser < 3 segundos

### Console
- [ ] Abrir console (F12)
- [ ] Navegar pelo sistema
- [ ] Verificar:
  - [ ] Sem erros JavaScript
  - [ ] Sem avisos críticos
  - [ ] Sem recursos 404

### Network
- [ ] Aba Network (F12)
- [ ] Recarregar página
- [ ] Verificar:
  - [ ] Todos recursos carregam (200 OK)
  - [ ] Nenhum 404
  - [ ] Nenhum 500

---

## 🗄️ TESTE 14: BANCO DE DADOS

### phpMyAdmin
```
http://localhost/phpmyadmin
```

### Verificar Estrutura
- [ ] Banco `senai_manutencao` existe
- [ ] 5 tabelas:
  - [ ] `usuarios`
  - [ ] `setores`
  - [ ] `tipos_solicitacao`
  - [ ] `solicitacoes`
  - [ ] `movimentacoes`

### Verificar Dados
- [ ] Tabela `usuarios`:
  - [ ] Admin existe
  - [ ] Senha está em hash
  - [ ] Solicitantes de teste existem
- [ ] Tabela `solicitacoes`:
  - [ ] Solicitação teste foi criada
  - [ ] Campos preenchidos corretamente
  - [ ] Status atualizado

### Verificar Relacionamentos
- [ ] Clicar em "Designer" ou "Modelo ER"
- [ ] Visualizar relacionamentos
- [ ] Todas as FKs conectadas

---

## 🎭 TESTE 15: CENÁRIO COMPLETO

### Simular Usuário Real

1. **Professor cria solicitação urgente**
   - [ ] Login como solicitante
   - [ ] Criar solicitação urgente
   - [ ] Anexar imagem
   - [ ] Confirmar criação

2. **Admin recebe e atende**
   - [ ] Logout
   - [ ] Login como admin
   - [ ] Ver nova solicitação no dashboard
   - [ ] Abrir solicitação
   - [ ] Atualizar para "Em andamento"
   - [ ] Adicionar comentário

3. **Professor verifica atualização**
   - [ ] Logout
   - [ ] Login como solicitante novamente
   - [ ] Ver "Minhas Solicitações"
   - [ ] Status mudou
   - [ ] Comentário do admin visível

4. **Admin finaliza**
   - [ ] Logout
   - [ ] Login como admin
   - [ ] Abrir solicitação
   - [ ] Atualizar para "Concluída"
   - [ ] Comentário final

5. **Gerar relatório**
   - [ ] Ir em Relatórios
   - [ ] Filtrar período: hoje
   - [ ] Exportar PDF
   - [ ] Solicitação aparece no relatório

---

## ✅ CHECKLIST FINAL PRÉ-APRESENTAÇÃO

### Ambiente
- [ ] XAMPP rodando
- [ ] Banco populado
- [ ] Cache do navegador limpo
- [ ] Aba do sistema aberta
- [ ] Aba do phpMyAdmin aberta (backup)

### Dados de Teste Prontos
- [ ] Matrícula admin anotada: `admin`
- [ ] Senha admin anotada: `1234`
- [ ] Nome/matrícula solicitante prontos
- [ ] Imagem para upload separada
- [ ] Dados de solicitação preparados

### Sistema
- [ ] Todas as funcionalidades testadas
- [ ] Nenhum erro no console
- [ ] Dark mode funcionando
- [ ] Gráficos carregando
- [ ] Exportação testada

### Apresentação
- [ ] Roteiro memorizado
- [ ] Cronômetro preparado
- [ ] Equipe alinhada
- [ ] Respirar fundo

---

## 🐛 PROBLEMAS COMUNS E SOLUÇÕES

### "Cannot connect to database"
**Solução:** Verificar se MySQL está rodando no XAMPP

### "Page not found" (404)
**Solução:** Verificar se Apache está rodando e URL está correta

### "Blank page"
**Solução:** Ver erros no console (F12) e em `php_error.log`

### Gráficos não aparecem
**Solução:** Verificar se Chart.js carregou (aba Network)

### Imagens não carregam
**Solução:** Verificar pasta `uploads/` existe e tem permissões

---

## 🎯 RESULTADO ESPERADO

Se TODOS os itens acima forem ✅:

**🎉 SEU SISTEMA ESTÁ 100% PRONTO PARA A APRESENTAÇÃO! 🎉**

---

## 📊 ESTATÍSTICAS

- **Total de Testes**: 150+
- **Tempo Estimado**: 30-45 minutos
- **Criticidade**: ALTA
- **Obrigatório**: SIM

---

<div align="center">

**BOA SORTE! 🍀**

*Um sistema bem testado é meio caminho andado para a vitória!*

</div>
