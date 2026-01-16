import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';

@Component({
  selector: 'app-akani-apyk',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './akani-apyk.component.html',
  styleUrl: './akani-apyk.component.css'
})
export class AkaniApykComponent {
  constructor(private router: Router) {}

  goBack() {
    this.router.navigate(['/captura'], { skipLocationChange: true });
  }
}
