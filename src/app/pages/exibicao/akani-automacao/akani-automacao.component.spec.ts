import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AkaniAutomacaoComponent } from './akani-automacao.component';

describe('AkaniAutomacaoComponent', () => {
  let component: AkaniAutomacaoComponent;
  let fixture: ComponentFixture<AkaniAutomacaoComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AkaniAutomacaoComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AkaniAutomacaoComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
