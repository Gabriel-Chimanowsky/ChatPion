#!/bin/bash
# =====================================================
# Broadcast Pro - Gerenciador de Workers (Linux)
# =====================================================
# Este script inicia múltiplos workers para processamento paralelo
# Uso: ./start_workers.sh [numero_de_workers]
# =====================================================

WORKERS=${1:-4}
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHP_PATH=$(which php)
WORKER_PATH="$SCRIPT_DIR/worker_broadcast.php"
LOG_DIR="$SCRIPT_DIR/logs"

# Cria diretório de logs se não existir
mkdir -p "$LOG_DIR"

echo "====================================================="
echo "Broadcast Pro - Iniciando $WORKERS workers"
echo "====================================================="
echo ""

for i in $(seq 1 $WORKERS); do
    echo "Iniciando Worker $i..."
    nohup $PHP_PATH $WORKER_PATH $i > "$LOG_DIR/worker_$i.log" 2>&1 &
    echo "Worker $i iniciado com PID $!"
    sleep 1
done

echo ""
echo "====================================================="
echo "$WORKERS workers iniciados!"
echo "Logs em: $LOG_DIR"
echo ""
echo "Para parar todos os workers:"
echo "  pkill -f worker_broadcast.php"
echo "====================================================="
