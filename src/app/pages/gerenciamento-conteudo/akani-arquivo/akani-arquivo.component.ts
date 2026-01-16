import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';

@Component({
  selector: 'app-akani-pyta',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './akani-arquivo.component.html',
  styleUrl: './akani-arquivo.component.css'
})
export class AkaniPytaComponent {
  constructor(private router: Router) {}

  goBack() {
    this.router.navigate(['/gerenciamento-conteudo'], { skipLocationChange: true });
  }
}
