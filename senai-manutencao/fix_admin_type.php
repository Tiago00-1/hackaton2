<?php

/**
 * Script para corrigir tipo de usuário do admin
 */

require_once __DIR__ . '/config/db.php';

try {
    echo "<h2>Corrigindo tipo de usuário do admin...</h2>";

    // Atualizar tipo de usuário
    $sql = "UPDATE usuarios SET tipo_usuario = 'admin' WHERE matricula = 'admin'";
    $stmt = Database::execute($sql);

    echo "<p style='color: green;'>✓ Tipo de usuário atualizado com sucesso!</p>";

    // Verificar
    $sql = "SELECT id_usuario, nome, matricula, tipo_usuario, ativo FROM usuarios WHERE matricula = 'admin'";
    $stmt = Database::execute($sql);
    $admin = $stmt->fetch();

    echo "<h3>Dados do Admin:</h3>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> " . $admin['id_usuario'] . "</li>";
    echo "<li><strong>Nome:</strong> " . $admin['nome'] . "</li>";
    echo "<li><strong>Matrícula:</strong> " . $admin['matricula'] . "</li>";
    echo "<li><strong>Tipo:</strong> " . $admin['tipo_usuario'] . "</li>";
    echo "<li><strong>Ativo:</strong> " . ($admin['ativo'] ? 'Sim' : 'Não') . "</li>";
    echo "</ul>";

    echo "<hr>";
    echo "<h3 style='color: green;'>✓ TUDO CORRETO AGORA!</h3>";
    echo "<p><strong>Próximo passo:</strong></p>";
    echo "<ol>";
    echo "<li>Feche todas as abas do navegador (para limpar a sessão antiga)</li>";
    echo "<li>Abra uma nova aba</li>";
    echo "<li>Acesse: <a href='index.php'>http://localhost/senai-manutencao/</a></li>";
    echo "<li>Faça login novamente com admin/1234</li>";
    echo "</ol>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
