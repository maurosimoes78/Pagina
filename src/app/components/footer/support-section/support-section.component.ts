import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-support-section',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './support-section.component.html',
  styleUrl: './support-section.component.css'
})
export class SupportSectionComponent {
  supportLinks = [
    { label: 'Central de Ajuda', url: '/suporte/ajuda' },
    { label: 'Fale Conosco', url: '/suporte/contato' },
    { label: 'FAQ', url: '/suporte/faq' },
    { label: 'Chat Online', url: '/suporte/chat' },
    { label: 'Tutoriais', url: '/suporte/tutoriais' },
    { label: 'Network Solution', url: '/suporte/network-solution' }
  ];
}
