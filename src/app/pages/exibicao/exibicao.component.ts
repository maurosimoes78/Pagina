import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';

@Component({
  selector: 'app-exibicao',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './exibicao.component.html',
  styleUrl: './exibicao.component.css'
})
export class ExibicaoComponent {
  panels = [
    {
      id: 1,
      name: 'Akani TOVA',
      description: 'Sistema profissional de playout para transmissão broadcast com alta confiabilidade e recursos avançados de automação',
      image: '/assets/images/playout.png',
      route: '/exibicao/tova'
    },
    {
      id: 2,
      name: 'Akani Automação',
      description: 'Solução completa de automação para operação de canais, agendamento inteligente e gerenciamento de programação',
      image: '/assets/images/automacao.png',
      route: '/exibicao/automacao'
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
