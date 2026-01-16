import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AkaniArquivoComponent } from './akani-arquivo.component';

describe('AkaniArquivoComponent', () => {
  let component: AkaniArquivoComponent;
  let fixture: ComponentFixture<AkaniArquivoComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AkaniArquivoComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AkaniArquivoComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
