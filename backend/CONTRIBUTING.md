# Guia de Contribuição

## Versão PHP de Referência

**Este projeto requer compatibilidade com PHP 5.3.29.**

Antes de contribuir, leia o arquivo `PHP_VERSION.md` para entender as diretrizes de compatibilidade.

## Padrões de Código

### 1. Arrays
Sempre use `array()` em vez de `[]`:
```php
// ✅ Correto
$data = array('key' => 'value');

// ❌ Incorreto
$data = ['key' => 'value'];
```

### 2. Null Coalescing
Use `isset()` em vez de `??`:
```php
// ✅ Correto
$value = isset($data['key']) ? $data['key'] : 'default';

// ❌ Incorreto
$value = $data['key'] ?? 'default';
```

### 3. Hash de Senhas
Use `PasswordHelper` em vez de funções nativas:
```php
// ✅ Correto
$hash = PasswordHelper::hash($password);
$valid = PasswordHelper::verify($password, $hash);

// ❌ Incorreto
$hash = password_hash($password, PASSWORD_DEFAULT);
$valid = password_verify($password, $hash);
```

### 4. Geração Aleatória
Use alternativas compatíveis:
```php
// ✅ Correto
if (function_exists('openssl_random_pseudo_bytes')) {
    $bytes = openssl_random_pseudo_bytes(32);
} else {
    // Fallback
    $bytes = '';
    for ($i = 0; $i < 32; $i++) {
        $bytes .= chr(mt_rand(0, 255));
    }
}

// ❌ Incorreto
$bytes = random_bytes(32);
```

## Estrutura de Arquivos

Mantenha a estrutura existente:
- `classes/` - Classes PHP
- `api/` - Endpoints da API
- `config/` - Arquivos de configuração
- `public/` - Arquivos públicos

## Testes

Antes de fazer commit:

1. Execute `phpinfo.php` para verificar versão PHP
2. Execute `example_usage.php` para testar funcionalidades
3. Teste todos os endpoints afetados
4. Verifique logs de erro

## Commits

Use mensagens de commit descritivas:
```
feat: Adiciona nova funcionalidade X
fix: Corrige problema Y
docs: Atualiza documentação
refactor: Refatora código Z
```

## Pull Requests

Ao criar um PR, certifique-se de:

- [ ] Código compatível com PHP 5.3.29
- [ ] Testes executados com sucesso
- [ ] Documentação atualizada se necessário
- [ ] Sem erros de lint/sintaxe
- [ ] Segue padrões de código do projeto

## Dúvidas?

Consulte:
- `PHP_VERSION.md` - Diretrizes de compatibilidade
- `COMPATIBILITY.md` - Adaptações realizadas
- `README.md` - Documentação geral

