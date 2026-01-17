import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, firstValueFrom } from 'rxjs';
import { UserManagementService } from './user-management.service';
import { BackendApiService } from './backend-api.service';

export interface User {
  id: string;
  email: string;
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
  createdAt?: Date;
  updatedAt?: Date;
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

  constructor(
    private userManagementService: UserManagementService,
    private backendApi: BackendApiService
  ) {
    // Verificar se há um usuário salvo no localStorage ao inicializar
    this.loadUserFromStorage();
  }

  /**
   * Verifica se um email existe na lista de usuários
   * @param email Email a ser verificado
   * @returns Promise<boolean> true se o email existir, false caso contrário
   */
  async checkEmailExists(email: string): Promise<boolean> {
    const dataSource = this.userManagementService.getDataSource();
    
    if (dataSource === 'localStorage') {
      // Verificar no localStorage
      const user = this.userManagementService.getUserByEmail(email);
      return !!user;
    } else {
      // Para backend, tentar fazer login (o backend retornará se o email existe)
      // Por enquanto, retornamos true se houver token (usuário autenticado)
      // ou podemos fazer uma verificação específica se necessário
      return this.backendApi.isAuthenticated();
    }
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

      // Verificar a fonte de dados
      const dataSource = this.userManagementService.getDataSource();

      if (dataSource === 'localStorage') {
        // Comportamento original: verificar no localStorage
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
          id: userData.id,
          email: email,
          name: userData.name,
          role: userData.role || 'user',
          cpf: userData.cpf,
          telefone: userData.telefone,
          empresa: userData.empresa,
          endereco: userData.endereco,
          bairro: userData.bairro,
          cidade: userData.cidade,
          estado: userData.estado,
          pais: userData.pais,
          telefoneComercial: userData.telefoneComercial,
          cnpj: userData.cnpj,
          createdAt: userData.createdAt,
          updatedAt: userData.updatedAt,
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
      } else {
        // Comportamento para backend: fazer login via API
        try {
          const loginResponse = await firstValueFrom(
            this.backendApi.login({ email, password })
          );

          if (loginResponse.success && loginResponse.token) {
            // Login bem-sucedido no backend
            // Obter dados do usuário do backend
            let backendUser: any = null;
            if (loginResponse.user) {
              backendUser = loginResponse.user;
            } else {
              // Se não vier no response, buscar do backend
              // Por enquanto, usar os dados básicos
              backendUser = { email, name: email.split('@')[0], role: 'user' };
            }

            // Criar objeto User para o frontend
            const user: User = {
              id: String(backendUser.id || ''),
              email: backendUser.email || email,
              name: backendUser.name || backendUser.email?.split('@')[0] || email.split('@')[0],
              role: backendUser.role || 'user',
              cpf: backendUser.cpf,
              telefone: backendUser.telefone,
              empresa: backendUser.empresa,
              endereco: backendUser.endereco,
              bairro: backendUser.bairro,
              cidade: backendUser.cidade,
              estado: backendUser.estado,
              pais: backendUser.pais,
              telefoneComercial: backendUser.telefone_comercial || backendUser.telefoneComercial,
              cnpj: backendUser.cnpj,
              createdAt: backendUser.created_at ? new Date(backendUser.created_at) : new Date(),
              updatedAt: backendUser.updated_at ? new Date(backendUser.updated_at) : new Date(),
              tokenId: loginResponse.token, // Usar o token do backend
              loginTime: new Date()
            };

            // Salvar no localStorage
            localStorage.setItem(this.TOKEN_KEY, loginResponse.token);
            localStorage.setItem(this.USER_KEY, JSON.stringify(user));

            // Atualizar o BehaviorSubject
            this.currentUserSubject.next(user);

            // Sincronizar dados do usuário com o UserManagementService (localStorage)
            // Converter e salvar no formato UserData
            const userData = {
              id: String(backendUser.id || Date.now()),
              email: user.email,
              password: '', // Senha não é armazenada
              name: user.name,
              role: user.role,
              cpf: backendUser.cpf,
              telefone: backendUser.telefone,
              empresa: backendUser.empresa,
              endereco: backendUser.endereco,
              bairro: backendUser.bairro,
              cidade: backendUser.cidade,
              estado: backendUser.estado,
              pais: backendUser.pais,
              telefoneComercial: backendUser.telefoneComercial,
              cnpj: backendUser.cnpj,
              createdAt: new Date(),
              updatedAt: new Date()
            };

            // Verificar se o usuário já existe no localStorage
            const existingUser = this.userManagementService.getUserByEmail(user.email);
            if (!existingUser) {
              // Adicionar ao localStorage (usando método síncrono para localStorage)
              const users = this.userManagementService.getUsers();
              users.push(userData);
              // Salvar diretamente no localStorage
              localStorage.setItem('akani_users', JSON.stringify(users));
            }

            return {
              success: true,
              message: 'Login realizado com sucesso!',
              user: user,
              emailExists: true
            };
          } else {
            // Login falhou no backend
            return {
              success: false,
              message: loginResponse.message || 'Email ou senha incorretos.',
              emailExists: undefined
            };
          }
        } catch (error: any) {
          console.error('Erro ao fazer login no backend:', error);
          return {
            success: false,
            message: error.message || 'Erro ao conectar com o servidor. Tente novamente.',
            emailExists: false
          };
        }
      }
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
  async logout(): Promise<void> {
    const dataSource = this.userManagementService.getDataSource();
    
    // Se estiver usando backend, fazer logout no backend também
    if (dataSource === 'backend') {
      try {
        const token = this.getToken();
        if (token) {
          // Fazer logout no backend
          await firstValueFrom(this.backendApi.logout());
        }
      } catch (error) {
        console.error('Erro ao fazer logout no backend:', error);
        // Continuar com logout local mesmo se falhar no backend
      }
    }
    
    // Limpar localStorage
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
