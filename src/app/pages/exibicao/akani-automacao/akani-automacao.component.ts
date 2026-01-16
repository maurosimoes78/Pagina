import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';

@Component({
  selector: 'app-akani-automacao',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './akani-automacao.component.html',
  styleUrl: './akani-automacao.component.css'
})
export class AkaniAutomacaoComponent {
  constructor(private router: Router) {}

  goBack() {
    this.router.navigate(['/exibicao'], { skipLocationChange: true });
  }
}
