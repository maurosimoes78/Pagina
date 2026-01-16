import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AkaniAyvuComponent } from './akani-ayvu.component';

describe('AkaniAyvuComponent', () => {
  let component: AkaniAyvuComponent;
  let fixture: ComponentFixture<AkaniAyvuComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AkaniAyvuComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AkaniAyvuComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
