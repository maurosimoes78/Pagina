import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CandidaturaComponent } from '../candidatura/candidatura.component';

@Component({
  selector: 'app-carreiras',
  standalone: true,
  imports: [CommonModule, CandidaturaComponent],
  templateUrl: './carreiras.component.html',
  styleUrl: './carreiras.component.css'
})
export class CarreirasComponent {
  vagas = [
    { nome: 'Engenheiro de Software Full Stack', descricao: 'Desenvolva soluções completas de software para a indústria de broadcast, trabalhando com tecnologias modernas tanto no frontend quanto no backend.' },
    { nome: 'Desenvolvedor C++', descricao: 'Atue no desenvolvimento de sistemas de alta performance e baixa latência para equipamentos de broadcast, utilizando C++ e tecnologias relacionadas.' },
    { nome: 'Engenheiro de Software', descricao: 'Projete e implemente arquiteturas de software robustas para soluções de broadcast, garantindo qualidade, escalabilidade e manutenibilidade.' },
    { nome: 'Analista de Negócio', descricao: 'Analise necessidades do mercado de broadcast, identifique oportunidades e traduza requisitos de negócio em soluções técnicas viáveis.' },
    { nome: 'Técnico em Infra-estrutura de Redes', descricao: 'Implemente e mantenha infraestruturas de rede para sistemas de broadcast, garantindo alta disponibilidade e performance das conexões.' }
  ];

  candidaturaAberta: boolean = false;
  vagaSelecionada: string = '';

  abrirCandidatura(vagaNome: string) {
    this.vagaSelecionada = vagaNome;
    this.candidaturaAberta = true;
  }

  fecharCandidatura() {
    this.candidaturaAberta = false;
    this.vagaSelecionada = '';
  }
}
