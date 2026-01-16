import { Component, Input, Output, EventEmitter, OnInit, OnChanges, SimpleChanges, HostListener } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { EmailService } from '../../../services/email.service';

@Component({
  selector: 'app-candidatura',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './candidatura.component.html',
  styleUrl: './candidatura.component.css'
})
export class CandidaturaComponent implements OnInit, OnChanges {
  @Input() vagaNome: string = '';
  @Input() isOpen: boolean = false;
  @Output() close = new EventEmitter<void>();

  cvFile: File | null = null;
  cvFileName: string = '';
  maxDialogHeight: string = '90vh';
  isSubmitting: boolean = false;

  formData = {
    nome: '',
    email: '',
    telefone: '',
    cargo: '',
    mensagem: ''
  };

  constructor(private emailService: EmailService) {}

  ngOnInit() {
    if (this.vagaNome) {
      this.formData.cargo = this.vagaNome;
    }
  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['vagaNome'] && this.vagaNome) {
      this.formData.cargo = this.vagaNome;
    }
    if (changes['isOpen']) {
      if (this.isOpen) {
        // Resetar formulário quando o diálogo é aberto
        this.resetForm();
        if (this.vagaNome) {
          this.formData.cargo = this.vagaNome;
        }
        // Calcular altura disponível
        this.calculateAvailableHeight();
        // Bloquear scroll do body
        document.body.style.overflow = 'hidden';
      } else {
        // Restaurar scroll do body
        document.body.style.overflow = '';
      }
    }
  }

  calculateAvailableHeight() {
    // Aguardar o próximo ciclo para garantir que o DOM está atualizado
    setTimeout(() => {
      const header = document.querySelector('app-header');
      const footer = document.querySelector('app-footer');
      const viewportHeight = window.innerHeight;
      
      let headerHeight = 0;
      let footerHeight = 0;
      
      if (header) {
        const headerRect = header.getBoundingClientRect();
        headerHeight = headerRect.height;
      }
      
      if (footer) {
        const footerRect = footer.getBoundingClientRect();
        footerHeight = footerRect.height;
      }
      
      // Calcular altura disponível (viewport - header - footer - margens de segurança)
      // Margem de 20px no topo e 20px na base para espaçamento visual
      const availableHeight = viewportHeight - headerHeight - footerHeight - 40;
      
      // Garantir altura mínima e máxima
      const minHeight = 300;
      const maxHeight = viewportHeight * 0.9; // Máximo de 90% da viewport como fallback
      const finalHeight = Math.max(Math.min(availableHeight, maxHeight), minHeight);
      
      this.maxDialogHeight = `${finalHeight}px`;
    }, 10);
  }

  @HostListener('window:resize', ['$event'])
  onWindowResize() {
    if (this.isOpen) {
      this.calculateAvailableHeight();
    }
  }

  @HostListener('window:keydown.escape', ['$event'])
  handleEscapeKey(event: KeyboardEvent) {
    if (this.isOpen) {
      this.closeDialog();
    }
  }

  resetForm() {
    this.formData = {
      nome: '',
      email: '',
      telefone: '',
      cargo: '',
      mensagem: ''
    };
    this.cvFile = null;
    this.cvFileName = '';
  }

  closeDialog() {
    this.resetForm();
    this.close.emit();
  }

  onOverlayClick(event: MouseEvent) {
    if ((event.target as HTMLElement).classList.contains('dialog-overlay')) {
      this.closeDialog();
    }
  }

  onFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
      this.cvFile = input.files[0];
      this.cvFileName = this.cvFile.name;
    }
  }

  async enviarCandidatura() {
    if (!this.formData.nome || !this.formData.email || !this.formData.telefone) {
      alert('Por favor, preencha todos os campos obrigatórios.');
      return;
    }

    // Validar formato de email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(this.formData.email)) {
      alert('Por favor, insira um email válido.');
      return;
    }

    this.isSubmitting = true;

    try {
      // Enviar usando EmailJS
      const resultado = await this.emailService.enviarCandidatura(this.formData, this.cvFile);

      if (resultado.success) {
        alert(resultado.message);
        this.closeDialog();
      } else {
        alert(resultado.message);
      }
    } catch (error) {
      console.error('Erro ao enviar candidatura:', error);
      alert('Erro ao enviar candidatura. Tente novamente mais tarde.');
    } finally {
      this.isSubmitting = false;
    }
  }
}
