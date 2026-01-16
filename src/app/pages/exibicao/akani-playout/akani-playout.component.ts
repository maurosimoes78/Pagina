import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';

@Component({
  selector: 'app-akani-tova',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './akani-playout.component.html',
  styleUrl: './akani-playout.component.css'
})
export class AkaniTovaComponent {
  constructor(private router: Router) {}

  goBack() {
    this.router.navigate(['/exibicao'], { skipLocationChange: true });
  }
}
