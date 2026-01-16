import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './home.component.html',
  styleUrl: './home.component.css'
})
export class HomeComponent implements OnInit, OnDestroy {
  panels = [
    { 
      id: 0, 
      title: 'Captura', 
      description: 'Soluções avançadas de captura de conteúdo para broadcast',
      image: '/assets/images/captura.png'
    },
    { 
      id: 1, 
      title: 'Exibição', 
      description: 'Sistemas de playout profissionais para transmissão',
      image: '/assets/images/playout.png'
    },
    { 
      id: 2, 
      title: 'Gerenciamento de Conteúdo', 
      description: 'Plataforma completa para gerenciamento de conteúdo de mídia',
      image: '/assets/images/gerenciamento-conteudo.png'
    }
  ];

  currentIndex = 0;
  private autoSlideInterval: any;

  constructor(private router: Router) {}

  ngOnInit() {
    this.startAutoSlide();
  }

  ngOnDestroy() {
    this.stopAutoSlide();
  }

  startAutoSlide() {
    this.autoSlideInterval = setInterval(() => {
      this.nextSlide();
    }, 5000); // Muda a cada 5 segundos
  }

  stopAutoSlide() {
    if (this.autoSlideInterval) {
      clearInterval(this.autoSlideInterval);
    }
  }

  nextSlide() {
    this.currentIndex = (this.currentIndex + 1) % this.panels.length;
  }

  previousSlide() {
    this.currentIndex = (this.currentIndex - 1 + this.panels.length) % this.panels.length;
  }

  goToSlide(index: number) {
    this.currentIndex = index;
    this.stopAutoSlide();
    this.startAutoSlide();
  }

  navigateToPanel(panelTitle: string) {
    if (panelTitle === 'Captura') {
      this.router.navigate(['/captura']);
    }
    // Adicione outras navegações aqui conforme necessário
  }
}
