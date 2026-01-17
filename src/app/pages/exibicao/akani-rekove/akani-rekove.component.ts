import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';

@Component({
  selector: 'app-akani-rekove',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './akani-rekove.component.html',
  styleUrl: './akani-rekove.component.css'
})
export class AkaniRekoveComponent {
  constructor(private router: Router) {}

  goBack() {
    this.router.navigate(['/exibicao'], { skipLocationChange: true });
  }
}

