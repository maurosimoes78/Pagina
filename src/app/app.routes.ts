import { Routes } from '@angular/router';
import { AjudaComponent } from './pages/suporte/ajuda/ajuda.component';
import { ContatoComponent } from './pages/suporte/contato/contato.component';
import { FaqComponent } from './pages/suporte/faq/faq.component';
import { ChatComponent } from './pages/suporte/chat/chat.component';
import { TutoriaisComponent } from './pages/suporte/tutoriais/tutoriais.component';
import { NetworkSolutionComponent } from './pages/suporte/network-solution/network-solution.component';
import { ProdutosServicosComponent } from './pages/suporte/produtos-servicos/produtos-servicos.component';
import { ProblemasTecnicosComponent } from './pages/suporte/problemas-tecnicos/problemas-tecnicos.component';
import { OrientacoesUsoComponent } from './pages/suporte/orientacoes-uso/orientacoes-uso.component';
import { InformacoesContaComponent } from './pages/suporte/informacoes-conta/informacoes-conta.component';
import { SobreComponent } from './pages/institucional/sobre/sobre.component';
import { MissaoComponent } from './pages/institucional/missao/missao.component';
import { HistoriaComponent } from './pages/institucional/historia/historia.component';
import { CarreirasComponent } from './pages/institucional/carreiras/carreiras.component';
import { CandidaturaComponent } from './pages/institucional/candidatura/candidatura.component';
import { PrivacidadeComponent } from './pages/institucional/privacidade/privacidade.component';
import { TermosComponent } from './pages/institucional/termos/termos.component';
import { HomeComponent } from './pages/home/home.component';
import { CapturaComponent } from './pages/captura/captura.component';
import { AkaniUembiComponent } from './pages/captura/akani-uembi/akani-uembi.component';
import { AkaniApykComponent } from './pages/captura/akani-apyk/akani-apyk.component';
import { AkaniAyvuComponent } from './pages/captura/akani-ayvu/akani-ayvu.component';
import { ExibicaoComponent } from './pages/exibicao/exibicao.component';
import { AkaniTovaComponent } from './pages/exibicao/akani-playout/akani-playout.component';
import { AkaniAutomacaoComponent } from './pages/exibicao/akani-automacao/akani-automacao.component';
import { GerenciamentoConteudoComponent } from './pages/gerenciamento-conteudo/gerenciamento-conteudo.component';
import { AkaniMohenduComponent } from './pages/gerenciamento-conteudo/akani-mam/akani-mam.component';
import { AkaniPytaComponent } from './pages/gerenciamento-conteudo/akani-arquivo/akani-arquivo.component';
import { UsuariosComponent } from './pages/admin/usuarios/usuarios.component';

export const routes: Routes = [
  // Rota inicial
  { path: '', component: HomeComponent },
  
  // Rotas de Produtos/Soluções
  { path: 'captura', component: CapturaComponent },
  { path: 'captura/uemby', component: AkaniUembiComponent },
  { path: 'captura/apyk', component: AkaniApykComponent },
  { path: 'captura/ayvu', component: AkaniAyvuComponent },
  { path: 'exibicao', component: ExibicaoComponent },
  { path: 'exibicao/tova', component: AkaniTovaComponent },
  { path: 'exibicao/automacao', component: AkaniAutomacaoComponent },
  { path: 'gerenciamento-conteudo', component: GerenciamentoConteudoComponent },
  { path: 'gerenciamento-conteudo/mohendu', component: AkaniMohenduComponent },
  { path: 'gerenciamento-conteudo/pyta', component: AkaniPytaComponent },
  
  // Rotas de Suporte
  { path: 'suporte/ajuda', component: AjudaComponent },
  { path: 'suporte/contato', component: ContatoComponent },
  { path: 'suporte/faq', component: FaqComponent },
  { path: 'suporte/chat', component: ChatComponent },
  { path: 'suporte/tutoriais', component: TutoriaisComponent },
  { path: 'suporte/network-solution', component: NetworkSolutionComponent },
  { path: 'suporte/produtos-servicos', component: ProdutosServicosComponent },
  { path: 'suporte/problemas-tecnicos', component: ProblemasTecnicosComponent },
  { path: 'suporte/orientacoes-uso', component: OrientacoesUsoComponent },
  { path: 'suporte/informacoes-conta', component: InformacoesContaComponent },
  
         // Rotas Institucionais
         { path: 'institucional/sobre', component: SobreComponent },
         { path: 'institucional/missao', component: MissaoComponent },
         { path: 'institucional/historia', component: HistoriaComponent },
         { path: 'institucional/carreiras', component: CarreirasComponent },
         { path: 'institucional/candidatura', component: CandidaturaComponent },
         { path: 'institucional/privacidade', component: PrivacidadeComponent },
         { path: 'institucional/termos', component: TermosComponent },
         
         // Rotas Administrativas
         { path: 'admin/usuarios', component: UsuariosComponent }
       ];
