# Sistema de Gestão para Barbearia

Um sistema completo para gerenciamento de agendamentos, produtos e vendas, desenvolvido em PHP e MySQL.

## 📋 Pré-requisitos

*   Um servidor web (Apache recomendado, como o **XAMPP**, **WAMP** ou **Laragon**)
*   PHP 7.4 ou superior
*   MySQL/MariaDB

## 🚀 Instalação e Configuração

1.  **Clonar o repositório:**
    ```bash
    git clone https://github.com/Arroiiss/Barbearia.git
    ```

2.  **Mover para o servidor:**
    Coloque os arquivos do projeto na pasta raiz do seu servidor web (ex: `htdocs`, `www` ou `public_html`).

3.  **Configurar o Banco de Dados:**
    *   Certifique-se de que o serviço MySQL está rodando.
    *   Acesse o arquivo `config.php` e ajuste as credenciais de conexão (`DB_HOST`, `DB_USER`, `DB_PASS`), se necessário.
    *   No navegador, acesse o script de instalação automática:
        `http://localhost/nome-da-pasta/setup.php`
    *   Este script criará o banco de dados `barbearia_db` e todas as tabelas necessárias.

4.  **Acessar o sistema:**
    Acesse `http://localhost/nome-da-pasta/index.php` para iniciar.

## 🔑 Acesso Padrão (Admin)
Após rodar o `setup.php`, você pode acessar a área administrativa com:
*   **Usuário:** `admin`
*   **Senha:** `123`

## 🛠️ Tecnologias Utilizadas

*   **Linguagem:** PHP
*   **Banco de Dados:** MySQL
*   **Frontend:** HTML5, CSS3, JavaScript
*   **Bibliotecas:** FullCalendar (para a agenda interativa)
