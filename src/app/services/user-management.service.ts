import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, of, forkJoin } from 'rxjs';
import { map, catchError, switchMap } from 'rxjs/operators';
import { BackendApiService, User as BackendUser } from './backend-api.service';

export interface UserData {
  id: string;
  email: string;
  password: string; // Em produção, isso deve ser hash
  name: string;
  role?: string;
  cpf?: string;
  telefone?: string;
  empresa?: string;
  endereco?: string;
  bairro?: string;
  cidade?: string;
  estado?: string;
  pais?: string;
  telefoneComercial?: string;
  cnpj?: string;
  createdAt: Date;
  updatedAt: Date;
}

export type DataSource = 'localStorage' | 'backend';

@Injectable({
  providedIn: 'root'
})
export class UserManagementService {
  private readonly USERS_KEY = 'akani_users';
  private usersSubject = new BehaviorSubject<UserData[]>([]);
  public users$: Observable<UserData[]> = this.usersSubject.asObservable();
  
  // Variável de controle para escolher a fonte de dados
  private dataSource: DataSource = 'backend'; // 'localStorage' ou 'backend'

  constructor(private backendApi: BackendApiService) {
    this.loadUsers();
  }

  /**
   * Define a fonte de dados
   */
  setDataSource(source: DataSource): void {
    this.dataSource = source;
    this.loadUsers();
  }

  /**
   * Obtém a fonte de dados atual
   */
  getDataSource(): DataSource {
    return this.dataSource;
  }

  /**
   * Carrega usuários da fonte de dados configurada
   */
  private loadUsers(): void {
    if (this.dataSource === 'localStorage') {
      this.loadUsersFromLocalStorage();
    } else {
      this.loadUsersFromBackend();
    }
  }

  /**
   * Carrega usuários do localStorage
   */
  private loadUsersFromLocalStorage(): void {
    const usersJson = localStorage.getItem(this.USERS_KEY);
    if (usersJson) {
      try {
        const users = JSON.parse(usersJson);
        // Converter datas de string para Date
        const parsedUsers = users.map((user: any) => ({
          ...user,
          createdAt: new Date(user.createdAt),
          updatedAt: new Date(user.updatedAt)
        }));
        this.usersSubject.next(parsedUsers);
      } catch (error) {
        console.error('Erro ao carregar usuários:', error);
        this.usersSubject.next([]);
      }
    } else {
      this.usersSubject.next([]);
    }
  }

  /**
   * Carrega usuários do backend
   */
  private loadUsersFromBackend(): void {
    if (!this.backendApi.isAuthenticated()) {
      this.usersSubject.next([]);
      return;
    }

    this.backendApi.getUsers().pipe(
      map(response => {
        if (response.success && response['users']) {
          // Converter usuários do backend para formato UserData
          return response['users'].map((user: BackendUser) => this.convertBackendUserToUserData(user));
        }
        return [];
      }),
      catchError(error => {
        console.error('Erro ao carregar usuários do backend:', error);
        return of([]);
      })
    ).subscribe(users => {
      this.usersSubject.next(users);
    });
  }

  /**
   * Converte usuário do backend para UserData
   */
  private convertBackendUserToUserData(backendUser: BackendUser): UserData {
    return {
      id: String(backendUser.id || ''),
      email: backendUser.email,
      password: '', // Senha não é retornada do backend
      name: backendUser.name || '',
      role: backendUser.role,
      cpf: backendUser['cpf'],
      telefone: backendUser['telefone'],
      empresa: backendUser['empresa'],
      endereco: backendUser['endereco'],
      bairro: backendUser['bairro'],
      cidade: backendUser['cidade'],
      estado: backendUser['estado'],
      pais: backendUser['pais'],
      telefoneComercial: backendUser['telefoneComercial'],
      cnpj: backendUser['cnpj'],
      createdAt: backendUser['created_at'] ? new Date(backendUser['created_at']) : new Date(),
      updatedAt: backendUser['updated_at'] ? new Date(backendUser['updated_at']) : new Date()
    };
  }

  /**
   * Converte UserData para formato do backend
   */
  private convertUserDataToBackendUser(userData: Partial<UserData>): Partial<BackendUser> {
    const backendUser: Partial<BackendUser> = {
      email: userData.email,
      name: userData.name,
      role: userData.role,
      cpf: userData.cpf,
      telefone: userData.telefone,
      empresa: userData.empresa,
      endereco: userData.endereco,
      bairro: userData.bairro,
      cidade: userData.cidade,
      estado: userData.estado,
      pais: userData.pais,
      telefoneComercial: userData.telefoneComercial,
      cnpj: userData.cnpj
    };

    if (userData.password) {
      (backendUser as any).password = userData.password;
    }

    return backendUser;
  }

  /**
   * Salva usuários no localStorage
   */
  private saveUsers(users: UserData[]): void {
    localStorage.setItem(this.USERS_KEY, JSON.stringify(users));
    this.usersSubject.next(users);
  }

  /**
   * Obtém todos os usuários
   */
  getUsers(): UserData[] {
    return this.usersSubject.value;
  }

  /**
   * Obtém todos os usuários como Observable (útil para backend)
   */
  getUsers$(): Observable<UserData[]> {
    if (this.dataSource === 'localStorage') {
      return this.users$;
    } else {
      // Recarregar do backend
      this.loadUsersFromBackend();
      return this.users$;
    }
  }

  /**
   * Obtém um usuário por ID
   */
  getUserById(id: string): UserData | undefined {
    if (this.dataSource === 'localStorage') {
      return this.getUsers().find(user => user.id === id);
    } else {
      // Para backend, precisamos buscar individualmente
      const userId = parseInt(id, 10);
      if (isNaN(userId)) {
        return undefined;
      }
      // Retornar undefined por enquanto, pode ser implementado com busca individual
      return this.getUsers().find(user => user.id === id);
    }
  }

  /**
   * Obtém um usuário por ID do backend
   */
  getUserByIdFromBackend$(id: string): Observable<UserData | undefined> {
    const userId = parseInt(id, 10);
    if (isNaN(userId)) {
      return of(undefined);
    }

    return this.backendApi.getUserById(userId).pipe(
      map(response => {
        if (response.success && response['user']) {
          return this.convertBackendUserToUserData(response['user']);
        }
        return undefined;
      }),
      catchError(() => of(undefined))
    );
  }

  /**
   * Obtém um usuário por email
   */
  getUserByEmail(email: string): UserData | undefined {
    return this.getUsers().find(user => user.email.toLowerCase() === email.toLowerCase());
  }

  /**
   * Adiciona um novo usuário
   */
  addUser(userData: Omit<UserData, 'id' | 'createdAt' | 'updatedAt'>): Observable<{ success: boolean; message: string; user?: UserData }> {
    // Validações comuns
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(userData.email)) {
      return of({ success: false, message: 'Email inválido.' });
    }

    // Validar campos obrigatórios
    if (!userData.name || !userData.email || !userData.password) {
      return of({ success: false, message: 'Todos os campos são obrigatórios.' });
    }

    // Validar senha (mínimo 6 caracteres)
    if (userData.password.length < 6) {
      return of({ success: false, message: 'A senha deve ter no mínimo 6 caracteres.' });
    }

    if (this.dataSource === 'localStorage') {
      // Verificar se o email já existe
      if (this.getUserByEmail(userData.email)) {
        return of({ success: false, message: 'Este email já está cadastrado.' });
      }

      const newUser: UserData = {
        id: this.generateId(),
        ...userData,
        createdAt: new Date(),
        updatedAt: new Date()
      };

      const users = this.getUsers();
      users.push(newUser);
      this.saveUsers(users);

      return of({ success: true, message: 'Usuário cadastrado com sucesso!', user: newUser });
    } else {
      // Usar backend API
      const backendUser = this.convertUserDataToBackendUser(userData);
      return this.backendApi.createUser(backendUser).pipe(
        map(response => {
          if (response.success && response['user']) {
            const newUser = this.convertBackendUserToUserData(response['user']);
            // Atualizar lista local
            const users = this.getUsers();
            users.push(newUser);
            this.usersSubject.next(users);
            return { success: true, message: response.message || 'Usuário cadastrado com sucesso!', user: newUser };
          }
          return { success: false, message: response.message || 'Erro ao cadastrar usuário.' };
        }),
        catchError(error => {
          return of({ success: false, message: error.message || 'Erro ao cadastrar usuário.' });
        })
      );
    }
  }

  /**
   * Atualiza um usuário existente
   */
  updateUser(id: string, userData: Partial<Omit<UserData, 'id' | 'createdAt'>>): Observable<{ success: boolean; message: string; user?: UserData }> {
    // Validações comuns
    if (userData.email) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(userData.email)) {
        return of({ success: false, message: 'Email inválido.' });
      }
    }

    // Validar senha se fornecida
    if (userData.password && userData.password.length < 6) {
      return of({ success: false, message: 'A senha deve ter no mínimo 6 caracteres.' });
    }

    if (this.dataSource === 'localStorage') {
      const users = this.getUsers();
      const userIndex = users.findIndex(user => user.id === id);

      if (userIndex === -1) {
        return of({ success: false, message: 'Usuário não encontrado.' });
      }

      // Se o email está sendo alterado, verificar se não existe outro usuário com esse email
      if (userData.email && userData.email !== users[userIndex].email) {
        if (this.getUserByEmail(userData.email)) {
          return of({ success: false, message: 'Este email já está cadastrado para outro usuário.' });
        }
      }

      const updatedUser: UserData = {
        ...users[userIndex],
        ...userData,
        updatedAt: new Date()
      };

      users[userIndex] = updatedUser;
      this.saveUsers(users);

      return of({ success: true, message: 'Usuário atualizado com sucesso!', user: updatedUser });
    } else {
      // Usar backend API
      const userId = parseInt(id, 10);
      if (isNaN(userId)) {
        return of({ success: false, message: 'ID de usuário inválido.' });
      }

      const backendUser = this.convertUserDataToBackendUser(userData);
      return this.backendApi.updateUser(userId, backendUser).pipe(
        map(response => {
          if (response.success && response['user']) {
            const updatedUser = this.convertBackendUserToUserData(response['user']);
            // Atualizar lista local
            const users = this.getUsers();
            const userIndex = users.findIndex(user => user.id === id);
            if (userIndex !== -1) {
              users[userIndex] = updatedUser;
              this.usersSubject.next(users);
            }
            return { success: true, message: response.message || 'Usuário atualizado com sucesso!', user: updatedUser };
          }
          return { success: false, message: response.message || 'Erro ao atualizar usuário.' };
        }),
        catchError(error => {
          return of({ success: false, message: error.message || 'Erro ao atualizar usuário.' });
        })
      );
    }
  }

  /**
   * Remove um usuário
   */
  removeUser(id: string): Observable<{ success: boolean; message: string }> {
    if (this.dataSource === 'localStorage') {
      const users = this.getUsers();
      const userIndex = users.findIndex(user => user.id === id);

      if (userIndex === -1) {
        return of({ success: false, message: 'Usuário não encontrado.' });
      }

      users.splice(userIndex, 1);
      this.saveUsers(users);

      return of({ success: true, message: 'Usuário removido com sucesso!' });
    } else {
      // Usar backend API
      const userId = parseInt(id, 10);
      if (isNaN(userId)) {
        return of({ success: false, message: 'ID de usuário inválido.' });
      }

      return this.backendApi.deleteUser(userId).pipe(
        map(response => {
          if (response.success) {
            // Remover da lista local
            const users = this.getUsers();
            const userIndex = users.findIndex(user => user.id === id);
            if (userIndex !== -1) {
              users.splice(userIndex, 1);
              this.usersSubject.next(users);
            }
            return { success: true, message: response.message || 'Usuário removido com sucesso!' };
          }
          return { success: false, message: response.message || 'Erro ao remover usuário.' };
        }),
        catchError(error => {
          return of({ success: false, message: error.message || 'Erro ao remover usuário.' });
        })
      );
    }
  }

  /**
   * Gera um ID único
   */
  private generateId(): string {
    return `user_${Date.now()}_${Math.random().toString(36).substring(2, 15)}`;
  }
}
