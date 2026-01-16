import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';

@Component({
  selector: 'app-akani-ayvu',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './akani-ayvu.component.html',
  styleUrl: './akani-ayvu.component.css'
})
export class AkaniAyvuComponent {
  constructor(private router: Router) {}

  goBack() {
    this.router.navigate(['/captura'], { skipLocationChange: true });
  }
}
