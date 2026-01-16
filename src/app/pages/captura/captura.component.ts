import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';

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
      description: 'O Akani UEMBY é um sistema profissional de captura de sinais ao vivo para ambientes broadcast e produção crítica. Compatível com placas Matrox, AJA e DeckLink, além de streams IP em tempo real (2110, SRT, RTMP, RTP e UDP), ele garante flexibilidade total entre infraestruturas SDI e IP.\n\nCom captura de ANC (SMPTE 436), timecodes UTC, VITC e LTC, controle de VTR via RS-422, arquitetura redundante, agendamento e segmentação de gravações, o UEMBY entrega confiabilidade absoluta em operações 24/7.\n\nAs saídas em OP1a XDCAM/XDCAM HD, MP4 (H.264/HEVC) e DVCAM tornam o sistema pronto para integração imediata com edição, MOHENDU e TOVA.\n\nAkani UEMBY — captura ao vivo com padrão broadcast.',
      image: '/assets/images/akani-uembi.png',
      route: '/captura/uemby'
    },
    {
      id: 2,
      name: 'Akani APYK',
      description: 'Importação inteligente de mídia para ambientes broadcast. Sistema profissional de importação e ingestão de arquivos de mídia, desenvolvido para agilizar e padronizar a entrada de conteúdos provenientes de discos XDCAM / XDCAM HD e cartões SxS.',
      image: '/assets/images/akani-apyk.png',
      route: '/captura/apyk'
    },
    {
      id: 3,
      name: 'Akani AYVU',
      description: 'Captura de audio',
      image: '/assets/images/akani-ayvu.png',
      route: '/captura/ayvu'
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
