@echo off
echo Limpando arquivos de modelo duplicados...
echo.

cd /d "%~dp0"

REM Limpar Request.php
powershell -Command "$content = Get-Content 'models\Request.php' -Raw; $pos = $content.IndexOf('}'+ [Environment]::NewLine + [Environment]::NewLine + '?>'); if ($pos -gt 0) { $clean = $content.Substring(0, $pos + 1); $clean | Set-Content 'models\Request.php' -NoNewline }"
echo ✓ Request.php limpo

REM Limpar Sector.php  
powershell -Command "$content = Get-Content 'models\Sector.php' -Raw; $pos = $content.IndexOf('}'+ [Environment]::NewLine + [Environment]::NewLine + '?>'); if ($pos -gt 0) { $clean = $content.Substring(0, $pos + 1); $clean | Set-Content 'models\Sector.php' -NoNewline }"
echo ✓ Sector.php limpo

REM Limpar Type.php
powershell -Command "$content = Get-Content 'models\Type.php' -Raw; $pos = $content.IndexOf('}'+ [Environment]::NewLine + [Environment]::NewLine + '?>'); if ($pos -gt 0) { $clean = $content.Substring(0, $pos + 1); $clean | Set-Content 'models\Type.php' -NoNewline }"
echo ✓ Type.php limpo

echo.
echo ========================================
echo Arquivos limpos com sucesso!
echo ========================================
echo.
echo Agora reinicie o Apache no XAMPP e teste o dashboard.
pause
