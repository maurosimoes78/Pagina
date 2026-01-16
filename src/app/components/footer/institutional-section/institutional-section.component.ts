import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-institutional-section',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './institutional-section.component.html',
  styleUrl: './institutional-section.component.css'
})
export class InstitutionalSectionComponent {
  institutionalLinks = [
    { label: 'Sobre Nós', url: '/institucional/sobre' },
    { label: 'Missão e Valores', url: '/institucional/missao' },
    { label: 'Nossa História', url: '/institucional/historia' },
    { label: 'Trabalhe Conosco', url: '/institucional/carreiras' },
    { label: 'Política de Privacidade', url: '/institucional/privacidade' },
    { label: 'Termos de Uso', url: '/institucional/termos' }
  ];
}
