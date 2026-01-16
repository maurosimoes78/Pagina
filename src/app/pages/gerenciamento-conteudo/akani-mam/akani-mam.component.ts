import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';

@Component({
  selector: 'app-akani-mohendu',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './akani-mam.component.html',
  styleUrl: './akani-mam.component.css'
})
export class AkaniMohenduComponent {
  constructor(private router: Router) {}

  goBack() {
    this.router.navigate(['/gerenciamento-conteudo'], { skipLocationChange: true });
  }
}
