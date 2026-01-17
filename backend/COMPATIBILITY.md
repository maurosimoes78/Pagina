# Compatibilidade PHP 5.3.29

Este documento descreve as adaptações feitas no código para garantir compatibilidade com PHP 5.3.29.

## Mudanças Realizadas

### 1. Arrays Curtos `[]` → `array()`

**Antes (PHP 5.4+):**
```php
$array = ['key' => 'value'];
```

**Depois (PHP 5.3):**
```php
$array = array('key' => 'value');
```

### 2. Operador Null Coalescing `??` → `isset() ? :`

**Antes (PHP 7.0+):**
```php
$value = $data['key'] ?? 'default';
```

**Depois (PHP 5.3):**
```php
$value = isset($data['key']) ? $data['key'] : 'default';
```

### 3. Funções de Hash de Senha

**Problema:** `password_hash()` e `password_verify()` foram introduzidas no PHP 5.5.

**Solução:** Criada classe `PasswordHelper` que usa `crypt()` (disponível desde PHP 5.3).

**Arquivo:** `backend/classes/PasswordHelper.php`

### 4. `random_bytes()` → Alternativa Compatível

**Antes (PHP 7.0+):**
```php
$bytes = random_bytes(32);
```

**Depois (PHP 5.3):**
```php
if (function_exists('openssl_random_pseudo_bytes')) {
    $bytes = openssl_random_pseudo_bytes(32);
} else {
    // Fallback menos seguro
    $bytes = '';
    for ($i = 0; $i < 32; $i++) {
        $bytes .= chr(mt_rand(0, 255));
    }
}
```

### 5. `http_response_code()` → Função Auxiliar

**Problema:** `http_response_code()` foi introduzida no PHP 5.4.

**Solução:** Criada função `setHttpResponseCode()` com fallback usando `header()`.

### 6. `array_column()` → Loop Manual

**Problema:** `array_column()` foi introduzida no PHP 5.5.

**Solução:** Substituído por loop `foreach` manual.

**Antes:**
```php
$ids = array_column($events, 'id');
```

**Depois:**
```php
$ids = array();
foreach ($events as $event) {
    $ids[] = $event['id'];
}
```

### 7. `JSON_UNESCAPED_UNICODE` → Função Auxiliar

**Problema:** Constante `JSON_UNESCAPED_UNICODE` foi introduzida no PHP 5.4.

**Solução:** Criada função `json_encode_unicode()` com fallback.

## Arquivos Modificados

1. ✅ `config/config.php` - Arrays convertidos
2. ✅ `classes/Database.php` - Arrays convertidos
3. ✅ `classes/Auth.php` - Arrays, null coalescing, random_bytes, password_hash
4. ✅ `classes/UserManager.php` - Arrays, null coalescing, password_hash
5. ✅ `classes/SSEEventBus.php` - Arrays, array_column
6. ✅ `classes/ActivityManager.php` - Arrays
7. ✅ `classes/SSEConnection.php` - Arrays
8. ✅ `api/router.php` - Arrays, null coalescing, http_response_code, JSON_UNESCAPED_UNICODE

## Arquivos Criados

1. ✅ `classes/PasswordHelper.php` - Classe auxiliar para hash de senhas compatível com PHP 5.3

## Funcionalidades Mantidas

Todas as funcionalidades originais foram mantidas:

- ✅ Autenticação (login/logout)
- ✅ CRUD de usuários
- ✅ Server-Sent Events (SSE)
- ✅ Gerenciamento de atividade
- ✅ Bus de eventos
- ✅ Controle de sessões
- ✅ Validação de tokens

## Requisitos do Sistema

- PHP 5.3.29 ou superior
- PDO e PDO_MySQL
- Extensão OpenSSL (recomendada para segurança)
- MySQL/MariaDB

## Notas de Segurança

1. **Hash de Senhas:** A classe `PasswordHelper` usa `crypt()` com Blowfish quando disponível, ou DES como fallback. Em produção, recomenda-se PHP 5.5+ para usar `password_hash()`.

2. **Geração Aleatória:** O código usa `openssl_random_pseudo_bytes()` quando disponível, com fallback menos seguro para `mt_rand()`. Em produção, recomenda-se garantir que OpenSSL esteja disponível.

3. **Comparação de Strings:** A classe `PasswordHelper` implementa comparação segura (timing-safe) para evitar timing attacks.

## Testes Recomendados

Após a migração, teste:

1. ✅ Login/logout de usuários
2. ✅ Criação/edição/remoção de usuários
3. ✅ Conexões SSE
4. ✅ Envio de eventos
5. ✅ Heartbeat e controle de atividade

## Próximos Passos

Para melhorar a segurança e performance, considere:

1. Atualizar para PHP 7.4+ quando possível
2. Usar `password_hash()` e `password_verify()` nativos
3. Usar `random_bytes()` nativo
4. Usar arrays curtos `[]` para melhor legibilidade

