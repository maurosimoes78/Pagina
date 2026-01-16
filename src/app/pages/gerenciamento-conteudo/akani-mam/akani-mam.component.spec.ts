import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AkaniMamComponent } from './akani-mam.component';

describe('AkaniMamComponent', () => {
  let component: AkaniMamComponent;
  let fixture: ComponentFixture<AkaniMamComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AkaniMamComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AkaniMamComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
