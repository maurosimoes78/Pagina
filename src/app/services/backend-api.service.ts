import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError, map } from 'rxjs/operators';

export interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T;
  [key: string]: any;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse extends ApiResponse {
  token?: string;
  user?: any;
}

export interface User {
  id?: number;
  email: string;
  name?: string;
  role?: string;
  [key: string]: any;
}

export interface SSEEvent {
  event_type: string;
  data: any;
  target_type?: 'user' | 'session' | 'all';
  target_id?: string | number;
}

@Injectable({
  providedIn: 'root'
})
export class BackendApiService {
  private readonly API_BASE_URL = 'http://www.s3smart.com.br/backend/api';
  private readonly TOKEN_KEY = 'akani_auth_token';

  constructor(private http: HttpClient) {}

  /**
   * Obtém o token de autenticação do localStorage
   */
  private getToken(): string | null {
    return localStorage.getItem(this.TOKEN_KEY);
  }

  /**
   * Cria headers HTTP com autenticação
   */
  private getAuthHeaders(): HttpHeaders {
    const token = this.getToken();
    let headers = new HttpHeaders({
      'Content-Type': 'application/json'
    });

    if (token) {
      headers = headers.set('Authorization', `Bearer ${token}`);
    }

    return headers;
  }

  /**
   * Trata erros HTTP
   */
  private handleError(error: any): Observable<never> {
    let errorMessage = 'Erro desconhecido';
    
    if (error.error instanceof ErrorEvent) {
      // Erro do lado do cliente
      errorMessage = `Erro: ${error.error.message}`;
    } else {
      // Erro do lado do servidor
      errorMessage = error.error?.message || error.message || `Erro ${error.status}: ${error.statusText}`;
    }

    console.error('Backend API Error:', errorMessage);
    return throwError(() => new Error(errorMessage));
  }

  // ==================== AUTENTICAÇÃO ====================

  /**
   * Realiza login no backend
   */
  login(credentials: LoginRequest): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(
      `${this.API_BASE_URL}/auth/login`,
      credentials,
      { headers: this.getAuthHeaders() }
    ).pipe(
      map(response => {
        if (response.success && response.token) {
          localStorage.setItem(this.TOKEN_KEY, response.token);
        }
        return response;
      }),
      catchError(this.handleError)
    );
  }

  /**
   * Realiza logout no backend
   */
  logout(sessionId?: string): Observable<ApiResponse> {
    const body = sessionId ? { sessionId } : {};
    return this.http.post<ApiResponse>(
      `${this.API_BASE_URL}/auth/logout`,
      body,
      { headers: this.getAuthHeaders() }
    ).pipe(
      map(response => {
        if (response.success) {
          localStorage.removeItem(this.TOKEN_KEY);
        }
        return response;
      }),
      catchError(this.handleError)
    );
  }

  // ==================== USUÁRIOS ====================

  /**
   * Lista todos os usuários
   */
  getUsers(): Observable<ApiResponse<{ users: User[] }>> {
    return this.http.get<ApiResponse<{ users: User[] }>>(
      `${this.API_BASE_URL}/users`,
      { headers: this.getAuthHeaders() }
    ).pipe(catchError(this.handleError));
  }

  /**
   * Obtém um usuário por ID
   */
  getUserById(userId: number): Observable<ApiResponse<{ user: User }>> {
    return this.http.get<ApiResponse<{ user: User }>>(
      `${this.API_BASE_URL}/users/${userId}`,
      { headers: this.getAuthHeaders() }
    ).pipe(catchError(this.handleError));
  }

  /**
   * Cria um novo usuário
   */
  createUser(userData: Partial<User>): Observable<ApiResponse<{ user: User }>> {
    return this.http.post<ApiResponse<{ user: User }>>(
      `${this.API_BASE_URL}/users`,
      userData,
      { headers: this.getAuthHeaders() }
    ).pipe(catchError(this.handleError));
  }

  /**
   * Atualiza um usuário
   */
  updateUser(userId: number, userData: Partial<User>): Observable<ApiResponse<{ user: User }>> {
    return this.http.put<ApiResponse<{ user: User }>>(
      `${this.API_BASE_URL}/users/${userId}`,
      userData,
      { headers: this.getAuthHeaders() }
    ).pipe(catchError(this.handleError));
  }

  /**
   * Deleta um usuário
   */
  deleteUser(userId: number): Observable<ApiResponse> {
    return this.http.delete<ApiResponse>(
      `${this.API_BASE_URL}/users/${userId}`,
      { headers: this.getAuthHeaders() }
    ).pipe(catchError(this.handleError));
  }

  // ==================== ATIVIDADE ====================

  /**
   * Envia heartbeat para manter conexão ativa
   */
  sendHeartbeat(): Observable<ApiResponse> {
    return this.http.post<ApiResponse>(
      `${this.API_BASE_URL}/activity/heartbeat`,
      {},
      { headers: this.getAuthHeaders() }
    ).pipe(catchError(this.handleError));
  }

  /**
   * Verifica se o usuário está ativo
   */
  checkActivity(): Observable<ApiResponse<{ isActive: boolean }>> {
    return this.http.get<ApiResponse<{ isActive: boolean }>>(
      `${this.API_BASE_URL}/activity/check`,
      { headers: this.getAuthHeaders() }
    ).pipe(catchError(this.handleError));
  }

  /**
   * Lista sessões ativas do usuário
   */
  getActiveSessions(): Observable<ApiResponse<{ sessions: any[] }>> {
    return this.http.get<ApiResponse<{ sessions: any[] }>>(
      `${this.API_BASE_URL}/activity/sessions`,
      { headers: this.getAuthHeaders() }
    ).pipe(catchError(this.handleError));
  }

  // ==================== EVENTOS SSE ====================

  /**
   * Despacha um evento SSE
   */
  dispatchEvent(event: SSEEvent): Observable<ApiResponse> {
    const body: any = {
      eventType: event.event_type,
      data: event.data,
      targetType: event.target_type || 'user'
    };

    // Adicionar userId ou sessionId baseado no targetType
    if (event.target_type === 'session' && event.target_id) {
      body.sessionId = event.target_id;
    } else if (event.target_type === 'user' && event.target_id) {
      body.userId = event.target_id;
    }
    // Para 'all', não precisa de userId ou sessionId

    return this.http.post<ApiResponse>(
      `${this.API_BASE_URL}/test-send-event.php`,
      body,
      { headers: this.getAuthHeaders() }
    ).pipe(catchError(this.handleError));
  }

  /**
   * Obtém a URL do endpoint SSE com token
   */
  getSSEUrl(): string {
    const token = this.getToken();
    if (!token) {
      throw new Error('Token de autenticação não disponível');
    }
    // EventSource não suporta headers customizados, então usamos query string como fallback
    return `${this.API_BASE_URL}/sse?token=${encodeURIComponent(token)}`;
  }

  /**
   * Verifica se o usuário está autenticado
   */
  isAuthenticated(): boolean {
    return !!this.getToken();
  }

  /**
   * Remove o token de autenticação
   */
  clearToken(): void {
    localStorage.removeItem(this.TOKEN_KEY);
  }
}

