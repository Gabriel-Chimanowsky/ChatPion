# Guia de Deploy - Servidor Contabo (VPS)

Este guia explica como configurar sua VPS da Contabo para rodar várias instâncias do ChatPeão usando Docker e um Proxy Reverso para gerenciar domínios e SSL.

## 1. Preparando o Servidor (Ubuntu/Debian)

Após acessar sua VPS via SSH, rode os seguintes comandos para instalar o Docker:

```bash
# Atualizar o sistema
sudo apt update && sudo apt upgrade -y

# Instalar Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Instalar Docker Compose
sudo apt install docker-compose-plugin -y
```

---

## 2. Configurando o Gerenciador de Instâncias (Nginx Proxy Manager)

Para que você possa acessar cada instância via um domínio diferente (ex: `cliente1.seuapp.com`) com SSL automático, usaremos o **Nginx Proxy Manager (NPM)**.

Crie uma pasta para o NPM:
```bash
mkdir ~/proxy && cd ~/proxy
```

Crie um arquivo `docker-compose.yml` básico para o proxy:
```yaml
version: '3.8'
services:
  app:
    image: 'jc21/nginx-proxy-manager:latest'
    restart: always
    ports:
      - '80:80'
      - '81:81'
      - '443:443'
    volumes:
      - ./data:/data
      - ./letsencrypt:/etc/letsencrypt
```

Suba o proxy: `docker compose up -d`. Acesse em `IP_DO_SERVIDOR:81` (Login padrão: `admin@example.com` / `changeme`).

---

## 3. Baixando o Código via GitHub

No seu servidor Contabo, escolha uma pasta para o projeto e clone o repositório:

```bash
# Navegar para a home
cd ~

# Clonar o repositório (Substitua SEU_USUARIO pelo seu nome no GitHub)
# Use o Token de Acesso como senha se o repositório for privado
git clone https://github.com/Gabriel-Chimanowsky/ChatPion.git
cd chatpeao
```

## 4. Subindo Instâncias do ChatPeão

### Exemplo para o Cliente "Gabriel":

1. Vá para a pasta do projeto.
2. Rode o comando definindo uma porta interna única (ex: 8081):

```bash
export INSTANCE_ID="gabriel" APP_PORT="8081" && docker compose -p chatpeao_gabriel up -d
```

### Exemplo para o Cliente "EmpresaX":

```bash
export INSTANCE_ID="empresax" APP_PORT="8082" && docker compose -p chatpeao_empresax up -d
```

---

## 5. Apontando os Domínios no Nginx Proxy Manager

No painel do NPM (porta 81):
1. Vá em **Proxy Hosts** -> **Add Proxy Host**.
2. **Domain Names**: `gabriel.seudominio.com`
3. **Forward Hostname / IP**: `172.17.0.1` (IP padrão do host docker) ou o IP interno da rede se estiverem na mesma rede.
4. **Forward Port**: `8081` (a porta que você definiu no comando acima).
5. Na aba **SSL**, selecione **Request a new SSL Certificate** para ativar o HTTPS grátis.

---

## 7. Como Atualizar (Update)

Sempre que você fizer alterações no código localmente:

1. **Local**: `git add .`, `git commit -m "descrição"`, `git push`.
2. **VPS**:
   ```bash
   cd ~/chatpeao
   git pull
   # Se houver mudanças no Dockerfile ou docker-compose:
   docker compose up --build -d
   ```

---

## 8. Dicas da Contabo

*   **Firewall**: Certifique-se de que as portas 80, 443 e 81 estão abertas no painel da Contabo (se houver firewall externo) ou no `ufw` do Linux.
*   **Performance**: Como as instâncias são leves, uma VPS média da Contabo aguenta dezenas de instâncias do ChatPeão tranquilamente.
