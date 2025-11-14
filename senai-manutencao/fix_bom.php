<?php

/**
 * Script para remover BOM (Byte Order Mark) dos arquivos PHP
 * Execute este script uma única vez para corrigir problemas de encoding
 */

$directories = [
    __DIR__ . '/models',
    __DIR__ . '/controllers',
    __DIR__ . '/views/admin',
    __DIR__ . '/views/solicitante',
    __DIR__ . '/utils',
    __DIR__ . '/config'
];

$filesFixed = [];
$filesChecked = 0;

function removeBOM($filePath)
{
    $content = file_get_contents($filePath);

    // Verificar se tem BOM UTF-8 (EF BB BF)
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        // Remover o BOM
        $content = substr($content, 3);
        file_put_contents($filePath, $content);
        return true;
    }

    return false;
}

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        continue;
    }

    $files = glob($dir . '/*.php');

    foreach ($files as $file) {
        $filesChecked++;

        if (removeBOM($file)) {
            $filesFixed[] = $file;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Correção de BOM - SENAI</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }

        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .file-list {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }

        ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        li {
            margin: 5px 0;
            font-family: monospace;
        }
    </style>
</head>

<body>
    <h1>🔧 Correção de BOM (Byte Order Mark)</h1>

    <div class="info">
        <p><strong>Arquivos verificados:</strong> <?php echo $filesChecked; ?></p>
    </div>

    <?php if (count($filesFixed) > 0): ?>
        <div class="success">
            <h2>✅ BOM removido com sucesso!</h2>
            <p><strong><?php echo count($filesFixed); ?> arquivo(s) corrigido(s):</strong></p>
            <div class="file-list">
                <ul>
                    <?php foreach ($filesFixed as $file): ?>
                        <li><?php echo htmlspecialchars(str_replace(__DIR__, '', $file)); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <p><strong>⚠️ IMPORTANTE:</strong> Reinicie o Apache no XAMPP Control Panel para que as alterações tenham efeito!</p>
            <p>Depois de reiniciar o Apache, teste novamente o dashboard.</p>
        </div>
    <?php else: ?>
        <div class="info">
            <h2>✓ Nenhum BOM encontrado</h2>
            <p>Todos os arquivos estão sem BOM. O problema pode ser outra coisa.</p>
            <p>Verifique se o Apache está configurado corretamente para processar arquivos PHP.</p>
        </div>
    <?php endif; ?>

    <hr>

    <h3>Próximos passos:</h3>
    <ol>
        <li>Se arquivos foram corrigidos, reinicie o Apache</li>
        <li>Acesse novamente o dashboard</li>
        <li>Se o problema persistir, verifique os logs de erro do PHP em: <code>C:\xampp\php\logs\php_error_log</code></li>
    </ol>

    <p><a href="views/admin/dashboard.php">← Voltar ao Dashboard</a></p>
</body>

</html>