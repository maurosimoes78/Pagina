import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AkaniUembiComponent } from './akani-uembi.component';

describe('AkaniUembiComponent', () => {
  let component: AkaniUembiComponent;
  let fixture: ComponentFixture<AkaniUembiComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AkaniUembiComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AkaniUembiComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
