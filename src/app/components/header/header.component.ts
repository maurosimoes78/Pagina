import { Component } from '@angular/core';
import { RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [RouterLink, CommonModule],
  templateUrl: './header.component.html',
  styleUrl: './header.component.css'
})
export class HeaderComponent {
  isMenuOpen = false;

  supportLinks = [
    { label: 'Central de Ajuda', url: '/suporte/ajuda' },
    { label: 'Fale Conosco', url: '/suporte/contato' },
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
}
