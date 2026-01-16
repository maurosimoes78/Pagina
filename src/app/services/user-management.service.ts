import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';

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

@Injectable({
  providedIn: 'root'
})
export class UserManagementService {
  private readonly USERS_KEY = 'akani_users';
  private usersSubject = new BehaviorSubject<UserData[]>([]);
  public users$: Observable<UserData[]> = this.usersSubject.asObservable();

  constructor() {
    this.loadUsers();
  }

  /**
   * Carrega usuários do localStorage
   */
  private loadUsers(): void {
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
   * Obtém um usuário por ID
   */
  getUserById(id: string): UserData | undefined {
    return this.getUsers().find(user => user.id === id);
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
  addUser(userData: Omit<UserData, 'id' | 'createdAt' | 'updatedAt'>): { success: boolean; message: string; user?: UserData } {
    // Validar email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(userData.email)) {
      return { success: false, message: 'Email inválido.' };
    }

    // Verificar se o email já existe
    if (this.getUserByEmail(userData.email)) {
      return { success: false, message: 'Este email já está cadastrado.' };
    }

    // Validar campos obrigatórios
    if (!userData.name || !userData.email || !userData.password) {
      return { success: false, message: 'Todos os campos são obrigatórios.' };
    }

    // Validar senha (mínimo 6 caracteres)
    if (userData.password.length < 6) {
      return { success: false, message: 'A senha deve ter no mínimo 6 caracteres.' };
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

    return { success: true, message: 'Usuário cadastrado com sucesso!', user: newUser };
  }

  /**
   * Atualiza um usuário existente
   */
  updateUser(id: string, userData: Partial<Omit<UserData, 'id' | 'createdAt'>>): { success: boolean; message: string; user?: UserData } {
    const users = this.getUsers();
    const userIndex = users.findIndex(user => user.id === id);

    if (userIndex === -1) {
      return { success: false, message: 'Usuário não encontrado.' };
    }

    // Se o email está sendo alterado, verificar se não existe outro usuário com esse email
    if (userData.email && userData.email !== users[userIndex].email) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(userData.email)) {
        return { success: false, message: 'Email inválido.' };
      }

      if (this.getUserByEmail(userData.email)) {
        return { success: false, message: 'Este email já está cadastrado para outro usuário.' };
      }
    }

    // Validar senha se fornecida
    if (userData.password && userData.password.length < 6) {
      return { success: false, message: 'A senha deve ter no mínimo 6 caracteres.' };
    }

    const updatedUser: UserData = {
      ...users[userIndex],
      ...userData,
      updatedAt: new Date()
    };

    users[userIndex] = updatedUser;
    this.saveUsers(users);

    return { success: true, message: 'Usuário atualizado com sucesso!', user: updatedUser };
  }

  /**
   * Remove um usuário
   */
  removeUser(id: string): { success: boolean; message: string } {
    const users = this.getUsers();
    const userIndex = users.findIndex(user => user.id === id);

    if (userIndex === -1) {
      return { success: false, message: 'Usuário não encontrado.' };
    }

    users.splice(userIndex, 1);
    this.saveUsers(users);

    return { success: true, message: 'Usuário removido com sucesso!' };
  }

  /**
   * Gera um ID único
   */
  private generateId(): string {
    return `user_${Date.now()}_${Math.random().toString(36).substring(2, 15)}`;
  }
}
