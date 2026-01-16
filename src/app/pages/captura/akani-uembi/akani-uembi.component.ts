import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';

@Component({
  selector: 'app-akani-uembi',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './akani-uembi.component.html',
  styleUrl: './akani-uembi.component.css'
})
export class AkaniUembiComponent {
  constructor(private router: Router) {}

  goBack() {
    this.router.navigate(['/captura'], { skipLocationChange: true });
  }
}
