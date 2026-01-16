# Script para parar todos os processos na porta 4200

Write-Host "Procurando processos na porta 4200..." -ForegroundColor Yellow

# Encontra processos usando a porta 4200
$connections = Get-NetTCPConnection -LocalPort 4200 -ErrorAction SilentlyContinue

if ($connections) {
    foreach ($connection in $connections) {
        $processId = $connection.OwningProcess
        $process = Get-Process -Id $processId -ErrorAction SilentlyContinue
        
        if ($process) {
            Write-Host "Encontrado processo: $($process.ProcessName) (PID: $processId)" -ForegroundColor Red
            Write-Host "Encerrando processo..." -ForegroundColor Yellow
            Stop-Process -Id $processId -Force
            Write-Host "Processo encerrado com sucesso!" -ForegroundColor Green
        }
    }
} else {
    Write-Host "Nenhum processo encontrado na porta 4200." -ForegroundColor Green
}

