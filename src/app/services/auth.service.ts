import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { UserManagementService } from './user-management.service';

export interface User {
  email: string;
  name: string;
  role?: string;
  tokenId: string;
  loginTime: Date;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private readonly TOKEN_KEY = 'akani_auth_token';
  private readonly USER_KEY = 'akani_user_data';
  
  private currentUserSubject = new BehaviorSubject<User | null>(null);
  public currentUser$: Observable<User | null> = this.currentUserSubject.asObservable();

  constructor(private userManagementService: UserManagementService) {
    // Verificar se há um usuário salvo no localStorage ao inicializar
    this.loadUserFromStorage();
  }

  /**
   * Verifica se um email existe na lista de usuários
   * @param email Email a ser verificado
   * @returns true se o email existir, false caso contrário
   */
  checkEmailExists(email: string): boolean {
    const user = this.userManagementService.getUserByEmail(email);
    return !!user;
  }

  /**
   * Realiza o login do usuário
   * @param email Email do usuário
   * @param password Senha do usuário
   * @returns Promise com o resultado do login
   */
  async login(email: string, password: string): Promise<{ success: boolean; message: string; user?: User; emailExists?: boolean }> {
    try {
      // Validação básica
      if (!email || !password) {
        return { success: false, message: 'Por favor, preencha todos os campos.' };
      }

      // Validação de formato de email
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        return { success: false, message: 'Por favor, insira um email válido.' };
      }

      // Verificar se o usuário existe
      const userData = this.userManagementService.getUserByEmail(email);
      const emailExists = !!userData;

      // Se o email não existir, retornar informação para cadastro
      if (!emailExists) {
        return {
          success: false,
          message: 'Email não cadastrado. Você será redirecionado para criar uma conta.',
          emailExists: false
        };
      }

      // Verificar se a senha está correta
      if (userData.password !== password) {
        return { success: false, message: 'Senha incorreta. Tente novamente.', emailExists: true };
      }

      // Login bem-sucedido
      const tokenId = this.generateTokenId();
      const user: User = {
        email: email,
        name: userData.name,
        role: userData.role || 'user',
        tokenId: tokenId,
        loginTime: new Date()
      };

      // Salvar no localStorage
      localStorage.setItem(this.TOKEN_KEY, tokenId);
      localStorage.setItem(this.USER_KEY, JSON.stringify(user));

      // Atualizar o BehaviorSubject
      this.currentUserSubject.next(user);

      return {
        success: true,
        message: 'Login realizado com sucesso!',
        user: user,
        emailExists: true
      };
    } catch (error) {
      console.error('Erro ao realizar login:', error);
      return {
        success: false,
        message: 'Erro ao realizar login. Tente novamente.',
        emailExists: undefined
      };
    }
  }

  /**
   * Realiza o logout do usuário
   */
  logout(): void {
    localStorage.removeItem(this.TOKEN_KEY);
    localStorage.removeItem(this.USER_KEY);
    this.currentUserSubject.next(null);
  }

  /**
   * Verifica se o usuário está autenticado
   * @returns true se o usuário estiver autenticado
   */
  isAuthenticated(): boolean {
    const token = localStorage.getItem(this.TOKEN_KEY);
    const user = localStorage.getItem(this.USER_KEY);
    return !!(token && user);
  }

  /**
   * Obtém o token de autenticação atual
   * @returns Token ID ou null
   */
  getToken(): string | null {
    return localStorage.getItem(this.TOKEN_KEY);
  }

  /**
   * Obtém os dados do usuário atual
   * @returns Dados do usuário ou null
   */
  getCurrentUser(): User | null {
    const userData = localStorage.getItem(this.USER_KEY);
    if (userData) {
      try {
        return JSON.parse(userData);
      } catch (error) {
        console.error('Erro ao parsear dados do usuário:', error);
        return null;
      }
    }
    return null;
  }

  /**
   * Gera um token ID único
   * @returns Token ID
   */
  private generateTokenId(): string {
    const timestamp = Date.now().toString(36);
    const randomStr = Math.random().toString(36).substring(2, 15);
    return `${timestamp}-${randomStr}`;
  }

  /**
   * Carrega o usuário do localStorage
   */
  private loadUserFromStorage(): void {
    const user = this.getCurrentUser();
    if (user) {
      this.currentUserSubject.next(user);
    }
  }
}
