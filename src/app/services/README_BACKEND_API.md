# Backend API Service

Serviço Angular para comunicação com a API PHP do backend.

## Configuração

O serviço está configurado para usar a URL base: `http://www.s3smart.com.br/backend/api`

Para alterar a URL base, modifique a propriedade `API_BASE_URL` no arquivo `backend-api.service.ts`.

## Autenticação

O serviço gerencia automaticamente o token de autenticação no `localStorage` com a chave `akani_auth_token`.

### Login

```typescript
import { BackendApiService } from './services/backend-api.service';

constructor(private backendApi: BackendApiService) {}

// Fazer login
this.backendApi.login({ email: 'user@example.com', password: 'senha123' })
  .subscribe({
    next: (response) => {
      if (response.success) {
        console.log('Login realizado com sucesso!');
        console.log('Token:', response.token);
      }
    },
    error: (error) => {
      console.error('Erro no login:', error);
    }
  });
```

### Logout

```typescript
this.backendApi.logout()
  .subscribe({
    next: (response) => {
      if (response.success) {
        console.log('Logout realizado com sucesso!');
      }
    }
  });
```

## Gerenciamento de Usuários

### Listar Usuários

```typescript
this.backendApi.getUsers()
  .subscribe({
    next: (response) => {
      if (response.success && response.users) {
        console.log('Usuários:', response.users);
      }
    }
  });
```

### Criar Usuário

```typescript
this.backendApi.createUser({
  email: 'novo@example.com',
  password: 'senha123',
  name: 'Novo Usuário',
  role: 'user'
})
  .subscribe({
    next: (response) => {
      if (response.success) {
        console.log('Usuário criado:', response.user);
      }
    }
  });
```

### Atualizar Usuário

```typescript
this.backendApi.updateUser(userId, {
  name: 'Nome Atualizado',
  email: 'novoemail@example.com'
})
  .subscribe({
    next: (response) => {
      if (response.success) {
        console.log('Usuário atualizado:', response.user);
      }
    }
  });
```

### Deletar Usuário

```typescript
this.backendApi.deleteUser(userId)
  .subscribe({
    next: (response) => {
      if (response.success) {
        console.log('Usuário deletado com sucesso');
      }
    }
  });
```

## Atividade e Heartbeat

### Enviar Heartbeat

```typescript
this.backendApi.sendHeartbeat()
  .subscribe({
    next: (response) => {
      if (response.success) {
        console.log('Heartbeat enviado');
      }
    }
  });
```

### Verificar Atividade

```typescript
this.backendApi.checkActivity()
  .subscribe({
    next: (response) => {
      if (response.success) {
        console.log('Usuário ativo:', response.isActive);
      }
    }
  });
```

## Eventos SSE

### Despachar Evento

```typescript
this.backendApi.dispatchEvent({
  event_type: 'notification',
  data: { message: 'Nova notificação', timestamp: new Date().toISOString() },
  target_type: 'user', // 'user', 'session', ou 'all'
  target_id: userId
})
  .subscribe({
    next: (response) => {
      if (response.success) {
        console.log('Evento despachado com sucesso');
      }
    }
  });
```

### Conectar ao SSE

```typescript
import { BackendApiService } from './services/backend-api.service';

constructor(private backendApi: BackendApiService) {}

connectSSE() {
  if (!this.backendApi.isAuthenticated()) {
    console.error('Usuário não autenticado');
    return;
  }

  const sseUrl = this.backendApi.getSSEUrl();
  const eventSource = new EventSource(sseUrl);

  eventSource.onmessage = (event) => {
    const data = JSON.parse(event.data);
    console.log('Evento recebido:', data);
  };

  eventSource.addEventListener('notification', (event) => {
    const data = JSON.parse(event.data);
    console.log('Notificação:', data);
  });

  eventSource.addEventListener('error', (event) => {
    console.error('Erro na conexão SSE:', event);
    eventSource.close();
  });

  // Fechar conexão quando necessário
  // eventSource.close();
}
```

## Verificação de Autenticação

```typescript
if (this.backendApi.isAuthenticated()) {
  console.log('Usuário autenticado');
} else {
  console.log('Usuário não autenticado');
}
```

## Tratamento de Erros

Todos os métodos retornam Observables que podem ser tratados com operadores RxJS:

```typescript
import { catchError } from 'rxjs/operators';
import { of } from 'rxjs';

this.backendApi.getUsers()
  .pipe(
    catchError(error => {
      console.error('Erro ao buscar usuários:', error);
      return of({ success: false, message: 'Erro ao buscar usuários' });
    })
  )
  .subscribe(response => {
    // Tratar resposta
  });
```

## Interfaces TypeScript

O serviço exporta as seguintes interfaces:

- `ApiResponse<T>`: Resposta padrão da API
- `LoginRequest`: Dados para login
- `LoginResponse`: Resposta do login
- `User`: Dados do usuário
- `SSEEvent`: Dados para despachar evento SSE

