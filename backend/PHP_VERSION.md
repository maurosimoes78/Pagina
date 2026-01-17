# Versão PHP de Referência

## Versão Padrão: PHP 5.3.29

**Este projeto foi desenvolvido e testado para ser compatível com PHP 5.3.29.**

Todas as implementações futuras na pasta `backend/` devem garantir compatibilidade com esta versão do PHP.

## Diretrizes de Desenvolvimento

### ✅ Recursos Permitidos (PHP 5.3.29)

- **Arrays:** Use `array()` em vez de `[]`
- **Funções anônimas:** Disponíveis desde PHP 5.3.0
- **Namespaces:** Disponíveis desde PHP 5.3.0
- **Late Static Binding:** Disponível desde PHP 5.3.0
- **Closures:** Disponíveis desde PHP 5.3.0
- **`__DIR__` constante mágica:** Disponível desde PHP 5.3.0
- **`json_encode()` e `json_decode()`:** Disponíveis desde PHP 5.2.0
- **PDO:** Disponível desde PHP 5.1.0
- **`spl_autoload_register()`:** Disponível desde PHP 5.1.2

### ❌ Recursos NÃO Permitidos

- **Arrays curtos `[]`:** Introduzidos no PHP 5.4
- **Operador null coalescing `??`:** Introduzido no PHP 7.0
- **`password_hash()` e `password_verify()`:** Introduzidos no PHP 5.5
- **`random_bytes()`:** Introduzido no PHP 7.0
- **`http_response_code()`:** Introduzido no PHP 5.4
- **`array_column()`:** Introduzido no PHP 5.5
- **`JSON_UNESCAPED_UNICODE`:** Introduzido no PHP 5.4
- **Return type declarations:** Introduzidos no PHP 7.0
- **Type hints escalares:** Introduzidos no PHP 7.0
- **Spread operator `...`:** Introduzido no PHP 5.6
- **Traits:** Introduzidos no PHP 5.4

## Alternativas para Recursos Não Disponíveis

### Hash de Senhas
```php
// ❌ NÃO usar (PHP 5.5+)
$hash = password_hash($password, PASSWORD_DEFAULT);

// ✅ Usar (PHP 5.3)
$hash = PasswordHelper::hash($password);
```

### Arrays Curtos
```php
// ❌ NÃO usar (PHP 5.4+)
$array = ['key' => 'value'];

// ✅ Usar (PHP 5.3)
$array = array('key' => 'value');
```

### Null Coalescing
```php
// ❌ NÃO usar (PHP 7.0+)
$value = $data['key'] ?? 'default';

// ✅ Usar (PHP 5.3)
$value = isset($data['key']) ? $data['key'] : 'default';
```

### Random Bytes
```php
// ❌ NÃO usar (PHP 7.0+)
$bytes = random_bytes(32);

// ✅ Usar (PHP 5.3)
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

### HTTP Response Code
```php
// ❌ NÃO usar (PHP 5.4+)
http_response_code(200);

// ✅ Usar (PHP 5.3)
if (function_exists('http_response_code')) {
    http_response_code(200);
} else {
    header("HTTP/1.1 200 OK", true, 200);
}
```

### Array Column
```php
// ❌ NÃO usar (PHP 5.5+)
$ids = array_column($array, 'id');

// ✅ Usar (PHP 5.3)
$ids = array();
foreach ($array as $item) {
    $ids[] = $item['id'];
}
```

## Checklist Antes de Commitar

Antes de fazer commit de código novo, verifique:

- [ ] Todos os arrays usam `array()` em vez de `[]`
- [ ] Não há uso do operador `??`
- [ ] Não há uso de `password_hash()` ou `password_verify()` (use `PasswordHelper`)
- [ ] Não há uso de `random_bytes()` (use alternativa compatível)
- [ ] Não há uso de `http_response_code()` (use função auxiliar)
- [ ] Não há uso de `array_column()` (use loop manual)
- [ ] Não há uso de `JSON_UNESCAPED_UNICODE` (use função auxiliar)
- [ ] Código testado em ambiente PHP 5.3.29

## Testes de Compatibilidade

Para verificar compatibilidade:

1. Execute o arquivo `phpinfo.php` para confirmar a versão
2. Execute `example_usage.php` para testar funcionalidades
3. Verifique logs de erro do PHP
4. Teste todos os endpoints da API

## Arquivos de Referência

- `COMPATIBILITY.md` - Documentação detalhada das adaptações
- `classes/PasswordHelper.php` - Classe auxiliar para hash de senhas
- `api/router.php` - Exemplos de funções auxiliares compatíveis

## Notas Importantes

1. **Segurança:** Algumas alternativas (como geração de números aleatórios) são menos seguras que as versões modernas. Em produção, considere atualizar para PHP 7.4+ quando possível.

2. **Performance:** Código compatível com PHP 5.3 pode ser mais verboso, mas garante funcionamento em ambientes legados.

3. **Manutenção:** Documente claramente quando usar alternativas e por quê.

## Atualização Futura

Se houver necessidade de atualizar a versão de referência:

1. Atualize este arquivo
2. Atualize `COMPATIBILITY.md`
3. Atualize `README.md`
4. Revise todos os arquivos do projeto
5. Atualize testes e documentação

---

**Última atualização:** Janeiro 2026  
**Versão PHP de referência:** 5.3.29  
**Status:** Ativo

