import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ExibicaoComponent } from './exibicao.component';

describe('ExibicaoComponent', () => {
  let component: ExibicaoComponent;
  let fixture: ComponentFixture<ExibicaoComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ExibicaoComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ExibicaoComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
