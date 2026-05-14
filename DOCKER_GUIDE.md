# Guia de Docker - ChatPeão (Mult-Instâncias)

Este sistema permite que você rode várias instâncias isoladas do ChatPeão no mesmo servidor. Cada instância terá seu próprio servidor web e seu próprio banco de dados, inicializado automaticamente.

## 🚀 Como Subir uma Instância Isolada

Para manter as instâncias separadas, use o parâmetro `-p` (nome do projeto) e mude a porta `APP_PORT`.

### Exemplo: Subir a "Instância do Cliente A"

```powershell
# No PowerShell (Windows)
$env:INSTANCE_ID="cliente_a"; $env:APP_PORT="8081"; docker-compose -p chatpeao_cliente_a up -d
```

### Exemplo: Subir a "Instância do Cliente B"

```powershell
# No PowerShell (Windows)
$env:INSTANCE_ID="cliente_b"; $env:APP_PORT="8082"; docker-compose -p chatpeao_cliente_b up -d
```

---

## 💎 Características Principais

1.  **Bancos Isolados**: Ao usar o `-p`, o Docker cria redes e volumes de dados separados (ex: `chatpeao_cliente_a_db_data` e `chatpeao_cliente_b_db_data`).
2.  **Auto-Inicialização**: Na primeira vez que você sobe uma instância, o banco de dados é criado e as tabelas são importadas automaticamente do arquivo `assets/backup_db/initial_db.sql`.
3.  **Acesso Padrão**: Toda instância já vem com uma conta de administrador pré-configurada:
    *   **Email**: `gabriel@chatpeao.com`
    *   **Senha**: `admin123`
4.  **Configuração Automática**: O PHP detecta automaticamente o nome do banco e a URL correta via variáveis de ambiente.

---

## 🛠️ Variáveis Disponíveis

| Variável | Descrição | Padrão |
| :--- | :--- | :--- |
| `INSTANCE_ID` | Texto para identificar os containers (ex: "cliente1") | `1` |
| `APP_PORT` | Porta onde o ChatPeão ficará acessível (ex: 8081, 8082...) | `8080` |
| `DB_PORT` | Porta externa para acessar o banco (opcional) | `3306` |

---

## 📋 Comandos Úteis

**Ver logs de uma instância específica:**
```bash
docker-compose -p chatpeao_cliente_a logs -f
```

**Parar uma instância:**
```bash
docker-compose -p chatpeao_cliente_a down
```

**Remover TUDO (incluindo o banco de dados) de uma instância:**
```bash
docker-compose -p chatpeao_cliente_a down -v
```

---

## 📂 Arquivos Gerados/Modificados

- `Dockerfile`: Configura PHP 7.4 + Apache + MySQL Ext.
- `docker-compose.yml`: Define os serviços e a montagem do SQL inicial.
- `application/config/database.php`: Agora lê `DB_HOSTNAME`, `DB_USERNAME`, etc.
- `application/config/config.php`: Agora lê `BASE_URL` dinamicamente.
