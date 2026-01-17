# Guia de Instalação - Backend PHP

## Passo a Passo

### 1. Configurar Banco de Dados

Execute o script SQL para criar o banco de dados:

```bash
mysql -u root -p < config/database.sql
```

Ou importe manualmente o arquivo `config/database.sql` no seu cliente MySQL.

### 2. Configurar Conexão

Edite o arquivo `config/config.php` e ajuste as credenciais do banco de dados:

```php
'database' => [
    'host' => 'localhost',
    'dbname' => 'akani_system',
    'username' => 'seu_usuario',
    'password' => 'sua_senha',
    'charset' => 'utf8mb4'
],
```

### 3. Configurar Servidor Web

#### Opção A: Apache com VirtualHost

```apache
<VirtualHost *:80>
    ServerName api.akani.local
    DocumentRoot "A:/S3/Pagina/backend/public"
    
    <Directory "A:/S3/Pagina/backend/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Opção B: Servidor PHP Built-in (Desenvolvimento)

```bash
cd backend/public
php -S localhost:8000
```

Acesse: `http://localhost:8000/api/...`

### 4. Testar Instalação

Execute o arquivo de exemplo:

```bash
php example_usage.php
```

### 5. Configurar CORS (se necessário)

Edite `config/config.php` e ajuste os domínios permitidos:

```php
'cors' => [
    'allowed_origins' => ['http://localhost:4200', 'http://seu-dominio.com'],
    ...
]
```

## Estrutura de URLs

Com o servidor configurado, os endpoints estarão disponíveis em:

- `http://localhost/api/auth/login`
- `http://localhost/api/users`
- `http://localhost/api/sse`
- `http://localhost/api/activity/heartbeat`

## Segurança em Produção

1. **Altere o secret do JWT** em `config/config.php`
2. **Configure HTTPS** e defina `cookie_secure => true`
3. **Ajuste CORS** para permitir apenas domínios específicos
4. **Configure logs** adequados
5. **Use senhas fortes** para o banco de dados

## Troubleshooting

### Erro de conexão com banco de dados
- Verifique se o MySQL está rodando
- Confirme as credenciais em `config/config.php`
- Verifique se o banco `akani_system` foi criado

### Erro 404 nos endpoints
- Verifique se o mod_rewrite está habilitado no Apache
- Confirme que o `.htaccess` está em `backend/public/`
- Verifique as permissões dos arquivos

### SSE não funciona
- Verifique se o PHP não está com output buffering
- Confirme que os headers estão sendo enviados corretamente
- Verifique o timeout do servidor web

