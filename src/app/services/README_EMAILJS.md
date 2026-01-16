# Configuração do EmailJS

Este projeto usa EmailJS para enviar emails diretamente do frontend, sem necessidade de um servidor backend.

## Passos para Configurar:

### 1. Criar conta no EmailJS
- Acesse https://www.emailjs.com/
- Crie uma conta gratuita (permite até 200 emails/mês)

### 2. Configurar Serviço de Email
- No dashboard do EmailJS, vá em "Email Services"
- Adicione um novo serviço (Gmail, Outlook, ou outro)
- Siga as instruções para conectar sua conta de email
- Anote o **Service ID** gerado

### 3. Criar Template de Email
- Vá em "Email Templates"
- Clique em "Create New Template"
- Use o template abaixo como referência:

**Template:**
```
Assunto: Candidatura - {{cargo}}

Corpo do Email:
Nova candidatura recebida!

Nome: {{from_name}}
Email: {{from_email}}
Telefone: {{telefone}}
Cargo de Interesse: {{cargo}}

Mensagem:
{{mensagem}}

---
Este email foi enviado através do formulário de candidatura do site.
```

- Configure o campo "To Email" como: `vagas@s3smart.com.br`
- Configure o campo "Reply To" como: `{{reply_to}}`
- Anote o **Template ID** gerado

### 4. Obter Public Key
- Vá em "Account" > "General"
- Copie sua **Public Key**

### 5. Configurar no Código
- Abra o arquivo `src/app/services/email.service.ts`
- Substitua as seguintes constantes:
  - `EMAILJS_PUBLIC_KEY`: Sua Public Key
  - `EMAILJS_SERVICE_ID`: ID do seu serviço
  - `EMAILJS_TEMPLATE_ID`: ID do seu template

### Exemplo:
```typescript
private readonly EMAILJS_PUBLIC_KEY = 'abc123xyz';
private readonly EMAILJS_SERVICE_ID = 'service_abc123';
private readonly EMAILJS_TEMPLATE_ID = 'template_xyz789';
```

## Alternativa: Backend Próprio

Se preferir usar um backend próprio, você pode:
1. Criar um endpoint API (ex: `/api/candidatura`)
2. Usar o método `enviarCandidaturaBackend()` no serviço
3. Implementar o envio de email no backend usando Node.js, Python, etc.

## Nota sobre Anexos

O EmailJS gratuito não suporta anexos de arquivo diretamente. Para enviar currículos:
- Opção 1: Solicitar link do Google Drive/Dropbox no formulário
- Opção 2: Usar backend próprio que suporte upload de arquivos
- Opção 3: Upgrade do plano EmailJS para suportar anexos

