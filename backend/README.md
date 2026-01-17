# Backend PHP - Sistema Akani

Sistema backend em PHP com suporte a Server-Sent Events (SSE), autenticação e gerenciamento de usuários.

## Estrutura do Projeto

```
backend/
├── api/
│   └── router.php          # Router principal da API
├── classes/
│   ├── Database.php        # Gerenciamento de conexão com BD
│   ├── Auth.php            # Autenticação (login/logout)
│   ├── UserManager.php     # CRUD de usuários
│   ├── SSEEventBus.php     # Bus de eventos SSE
│   ├── ActivityManager.php # Gerenciamento de atividade
│   └── SSEConnection.php   # Conexão SSE individual
├── config/
│   ├── config.php          # Configurações do sistema
│   └── database.sql        # Estrutura do banco de dados
└── public/
    └── .htaccess           # Configuração Apache
```

## Requisitos

- **PHP 5.3.29 ou superior** (versão de referência: 5.3.29)
- MySQL/MariaDB
- Apache com mod_rewrite habilitado
- Extensões PHP: PDO, PDO_MySQL, JSON, OpenSSL (recomendado)

> **Nota:** Este projeto foi desenvolvido para compatibilidade com PHP 5.3.29. Consulte `PHP_VERSION.md` para diretrizes de desenvolvimento.

## Instalação

1. **Configurar banco de dados:**
   ```bash
   mysql -u root -p < config/database.sql
   ```

2. **Configurar conexão:**
   Edite `config/config.php` com suas credenciais do banco de dados.

3. **Configurar servidor web:**
   - Configure o DocumentRoot do Apache para apontar para `backend/public/`
   - Ou configure um VirtualHost

## Endpoints da API

### Autenticação

#### POST `/api/auth/login`
Realiza login do usuário.

**Request:**
```json
{
  "email": "usuario@exemplo.com",
  "password": "senha123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login realizado com sucesso",
  "user": {...},
  "token": "jwt.token.here",
  "sessionId": "session.id.here"
}
```

#### POST `/api/auth/logout`
Realiza logout do usuário.

**Request:**
```json
{
  "sessionId": "session.id.here"
}
```

### Usuários (CRUD)

#### GET `/api/users`
Lista todos os usuários (requer autenticação).

**Headers:**
```
Authorization: Bearer {token}
```

#### GET `/api/users/{id}`
Busca usuário por ID (requer autenticação).

#### GET `/api/users/email/{email}`
Busca usuário por email (requer autenticação).

#### POST `/api/users`
Cria novo usuário (requer autenticação e role admin).

**Request:**
```json
{
  "email": "novo@exemplo.com",
  "password": "senha123",
  "name": "Nome Completo",
  "role": "user",
  "cpf": "000.000.000-00",
  "telefone": "(00) 00000-0000",
  ...
}
```

#### PUT `/api/users/{id}`
Atualiza usuário (requer autenticação e role admin).

#### DELETE `/api/users/{id}`
Remove usuário (requer autenticação e role admin).

### Server-Sent Events (SSE)

#### GET `/api/sse`
Estabelece conexão SSE (requer autenticação via token).

**Headers:**
```
Authorization: Bearer {token}
```

**Uso no frontend:**
```javascript
const eventSource = new EventSource('/api/sse', {
  headers: {
    'Authorization': 'Bearer ' + token
  }
});

eventSource.addEventListener('message', (event) => {
  const data = JSON.parse(event.data);
  console.log('Evento recebido:', data);
});
```

### Atividade

#### POST `/api/activity/heartbeat`
Registra heartbeat do usuário (requer autenticação).

#### GET `/api/activity/sessions`
Lista sessões ativas do usuário (requer autenticação).

#### GET `/api/activity/check`
Verifica se usuário está ativo (requer autenticação).

## Tipos de Eventos SSE

O sistema suporta três tipos de eventos:

1. **Por usuário:** Evento enviado para todas as sessões de um usuário específico
2. **Por sessão:** Evento enviado para uma sessão específica
3. **Global:** Evento enviado para todos os usuários conectados

### Enviar Eventos (via código PHP)

```php
$eventBus = new SSEEventBus();

// Enviar para um usuário específico
$eventBus->sendToUser($userId, 'notification', ['message' => 'Olá!']);

// Enviar para uma sessão específica
$eventBus->sendToSession($sessionId, 'update', ['data' => '...']);

// Enviar para todos
$eventBus->sendToAll('broadcast', ['message' => 'Anúncio global']);
```

## Configurações

### Configuração de SSE (`config/config.php`)

- `heartbeat_interval`: Intervalo de heartbeat em segundos (padrão: 30)
- `inactivity_timeout`: Timeout de inatividade em segundos (padrão: 120)
- `max_connections_per_user`: Máximo de conexões SSE por usuário (padrão: 5)

### Configuração de Sessão

- `lifetime`: Tempo de vida da sessão em segundos (padrão: 3600)

## Segurança

- Senhas são armazenadas com hash usando `password_hash()`
- Tokens JWT para autenticação
- Validação de sessões com timeout
- Proteção contra múltiplas conexões simultâneas
- Headers de segurança configurados

## Limpeza Automática

O sistema possui mecanismos para limpeza automática:

- Eventos SSE entregues são marcados e podem ser limpos periodicamente
- Atividades inativas são removidas após período configurado
- Sessões expiradas são automaticamente invalidadas

## Desenvolvimento

Para desenvolvimento local, você pode usar o servidor built-in do PHP:

```bash
cd backend/public
php -S localhost:8000
```

Acesse: `http://localhost:8000/api/...`

## Notas

- Em produção, altere o `secret` do JWT em `config/config.php`
- Configure `cookie_secure` para `true` se usar HTTPS
- Ajuste as configurações de CORS conforme necessário
- Configure logs adequados para produção

## Versão PHP de Referência

Este projeto foi desenvolvido para ser **compatível com PHP 5.3.29**.

Todas as implementações devem seguir as diretrizes de compatibilidade definidas em:
- **`PHP_VERSION.md`** - Diretrizes completas de compatibilidade
- **`COMPATIBILITY.md`** - Adaptações realizadas
- **`CONTRIBUTING.md`** - Guia de contribuição

### Recursos Não Disponíveis em PHP 5.3.29

O projeto usa alternativas para recursos modernos do PHP:
- `PasswordHelper` em vez de `password_hash()`
- `array()` em vez de `[]`
- `isset() ? :` em vez de `??`
- Funções auxiliares para `http_response_code()`, `array_column()`, etc.

Consulte a documentação para mais detalhes.

