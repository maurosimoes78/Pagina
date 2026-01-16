import { ComponentFixture, TestBed } from '@angular/core/testing';

import { GerenciamentoConteudoComponent } from './gerenciamento-conteudo.component';

describe('GerenciamentoConteudoComponent', () => {
  let component: GerenciamentoConteudoComponent;
  let fixture: ComponentFixture<GerenciamentoConteudoComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [GerenciamentoConteudoComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(GerenciamentoConteudoComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
