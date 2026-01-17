import { Component, OnInit, OnDestroy } from '@angular/core';
import { RouterLink, Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { LoginDialogComponent } from '../login-dialog/login-dialog.component';
import { AuthService, User } from '../../services/auth.service';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [RouterLink, CommonModule, LoginDialogComponent],
  templateUrl: './header.component.html',
  styleUrl: './header.component.css'
})
export class HeaderComponent implements OnInit, OnDestroy {
  isMenuOpen = false;
  isLoginDialogOpen = false;
  currentUser: User | null = null;
  private userSubscription?: Subscription;

  supportLinksPublic = [
    { label: 'Fale Conosco', url: '/suporte/contato' }
  ];

  supportLinksAuthenticated = [
    { label: 'Central de Ajuda', url: '/suporte/ajuda' },
    { label: 'FAQ', url: '/suporte/faq' },
    { label: 'Chat Online', url: '/suporte/chat' },
    { label: 'Tutoriais', url: '/suporte/tutoriais' },
    { label: 'Network Solution', url: '/suporte/network-solution' }
  ];

  institutionalLinks = [
    { label: 'Sobre Nós', url: '/institucional/sobre' },
    { label: 'Missão e Valores', url: '/institucional/missao' },
    { label: 'Nossa História', url: '/institucional/historia' },
    { label: 'Trabalhe Conosco', url: '/institucional/carreiras' },
    { label: 'Política de Privacidade', url: '/institucional/privacidade' },
    { label: 'Termos de Uso', url: '/institucional/termos' }
  ];

  toggleMenu() {
    this.isMenuOpen = !this.isMenuOpen;
  }

  closeMenu() {
    this.isMenuOpen = false;
  }

  openLoginDialog() {
    this.isLoginDialogOpen = true;
    this.closeMenu();
  }

  closeLoginDialog() {
    this.isLoginDialogOpen = false;
  }

  ngOnInit() {
    // Subscrever às mudanças do usuário autenticado
    this.userSubscription = this.authService.currentUser$.subscribe(user => {
      this.currentUser = user;
    });
  }

  ngOnDestroy() {
    // Limpar subscription ao destruir o componente
    if (this.userSubscription) {
      this.userSubscription.unsubscribe();
    }
  }

  onLoginSuccess() {
    this.closeLoginDialog();
    // O usuário será atualizado automaticamente via subscription
  }

  logout() {
    this.authService.logout();
    this.closeMenu();
    this.router.navigate(['/']);
  }

  isAdmin(): boolean {
    return this.currentUser?.role === 'admin';
  }

  getFirstName(): string {
    if (!this.currentUser?.name) {
      return '';
    }
    // Extrai o primeiro nome (primeira palavra antes do espaço)
    return this.currentUser.name.split(' ')[0];
  }

  constructor(
    public authService: AuthService,
    private router: Router
  ) {}
}
