import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AkaniApykComponent } from './akani-apyk.component';

describe('AkaniApykComponent', () => {
  let component: AkaniApykComponent;
  let fixture: ComponentFixture<AkaniApykComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AkaniApykComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AkaniApykComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
