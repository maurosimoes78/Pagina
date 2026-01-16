import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-captura',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './captura.component.html',
  styleUrl: './captura.component.css'
})
export class CapturaComponent {
  panels = [
    {
      id: 1,
      name: 'Akani UEMBY',
      description: 'Capturar ao vivo por hardware dedicado',
      image: '/assets/images/akani-uembi.png'
    },
    {
      id: 2,
      name: 'Akani APYK',
      description: 'Captura em transferência de arquivos individual ou em bloco',
      image: '/assets/images/akani-apyk.png'
    },
    {
      id: 3,
      name: 'Akani AYVU',
      description: 'Captura de audio',
      image: '/assets/images/akani-ayvu.png'
    }
  ];
}
