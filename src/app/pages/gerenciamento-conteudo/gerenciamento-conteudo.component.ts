import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';

@Component({
  selector: 'app-gerenciamento-conteudo',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './gerenciamento-conteudo.component.html',
  styleUrl: './gerenciamento-conteudo.component.css'
})
export class GerenciamentoConteudoComponent {
  panels = [
    {
      id: 1,
      name: 'Akani MOHENDU',
      description: 'Sistema completo de gerenciamento de ativos de mídia (MAM) para organização, catalogação e distribuição de conteúdo',
      image: '/assets/images/gerenciamento-conteudo.png',
      route: '/gerenciamento-conteudo/mohendu'
    },
    {
      id: 2,
      name: 'Akani PYTA',
      description: 'Solução de arquivamento e preservação de conteúdo com acesso rápido e gerenciamento inteligente de storage',
      image: '/assets/images/akani-pyta.png',
      route: '/gerenciamento-conteudo/pyta'
    }
  ];

  constructor(private router: Router) {}

  navigateToProduct(route: string) {
    this.router.navigate([route], { skipLocationChange: true });
  }

  goBack() {
    this.router.navigate(['/'], { skipLocationChange: true });
  }
}
