import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AkaniRekoveComponent } from './akani-rekove.component';

describe('AkaniRekoveComponent', () => {
  let component: AkaniRekoveComponent;
  let fixture: ComponentFixture<AkaniRekoveComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AkaniRekoveComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AkaniRekoveComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

