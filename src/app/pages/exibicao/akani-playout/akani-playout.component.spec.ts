import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AkaniPlayoutComponent } from './akani-playout.component';

describe('AkaniPlayoutComponent', () => {
  let component: AkaniPlayoutComponent;
  let fixture: ComponentFixture<AkaniPlayoutComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AkaniPlayoutComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AkaniPlayoutComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
