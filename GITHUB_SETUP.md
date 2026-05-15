# Configurando o GitHub

Siga estes passos para subir o seu código no GitHub pela primeira vez:

1. **Crie um Repositório no GitHub**
   - Vá para [github.com/new](https://github.com/new).
   - Nomeie como `chatpeao`.
   - Mantenha como **Privado** (recomendado para este tipo de app).
   - **NÃO** inicialize com README, .gitignore ou License (já criamos localmente).

2. **Vincule e Suba o Código**
   Abra o terminal na pasta do projeto e rode:

   ```powershell
   # Mudar o nome da branch para main (padrão GitHub)
   git branch -M main

   # Adicionar o endereço do seu repositório (Substitua SEU_USUARIO pelo seu nome no GitHub)
   git remote add origin https://github.com/Gabriel-Chimanowsky/ChatPion.git

   # Subir o código
   git push -u origin main
   ```

3. **Gerando um Token de Acesso (Recomendado)**
   Como o repositório é privado, o servidor Contabo precisará de acesso.
   - Vá em **Settings** -> **Developer settings** -> **Personal access tokens** -> **Tokens (classic)**.
   - Gere um novo token com permissão `repo`.
   - **Guarde este token!** Você o usará no lugar da senha ao dar `git clone` na Contabo.
