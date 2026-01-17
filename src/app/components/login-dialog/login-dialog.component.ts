import { Component, EventEmitter, Output, HostListener } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AuthService } from '../../services/auth.service';
import { UserManagementService } from '../../services/user-management.service';
import { EmailService } from '../../services/email.service';

@Component({
  selector: 'app-login-dialog',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './login-dialog.component.html',
  styleUrl: './login-dialog.component.css'
})
export class LoginDialogComponent {
  @Output() close = new EventEmitter<void>();
  @Output() loginSuccess = new EventEmitter<void>();

  email: string = '';
  password: string = '';
  name: string = ''; // Para cadastro
  cpf: string = '';
  telefone: string = '';
  empresa: string = '';
  endereco: string = '';
  bairro: string = '';
  cidade: string = '';
  estado: string = '';
  pais: string = '';
  telefoneComercial: string = '';
  cnpj: string = '';
  isLoading: boolean = false;
  errorMessage: string = '';
  successMessage: string = '';
  showSignupForm: boolean = false; // Controla se mostra formulário de cadastro
  showPassword: boolean = false; // Controla se a senha está visível
  showForgotPasswordDialog: boolean = false; // Controla se mostra dialog de recuperação de senha
  forgotPasswordEmail: string = ''; // Email para recuperação de senha

  countries = [
    'Brasil', 'Argentina', 'Chile', 'Colômbia', 'Peru', 'Venezuela', 'Equador', 'Bolívia',
    'Paraguai', 'Uruguai', 'Guiana', 'Suriname', 'Guiana Francesa',
    'Estados Unidos', 'Canadá', 'México', 'Cuba', 'Jamaica', 'Haiti', 'República Dominicana',
    'Portugal', 'Espanha', 'França', 'Itália', 'Alemanha', 'Reino Unido', 'Holanda', 'Bélgica',
    'Suíça', 'Áustria', 'Suécia', 'Noruega', 'Dinamarca', 'Finlândia', 'Polônia', 'Grécia',
    'Rússia', 'Ucrânia', 'Turquia', 'Israel', 'Japão', 'China', 'Índia', 'Coreia do Sul',
    'Austrália', 'Nova Zelândia', 'África do Sul', 'Egito', 'Nigéria', 'Quênia',
    'Outro'
  ];

  constructor(
    private authService: AuthService,
    private userManagementService: UserManagementService,
    private emailService: EmailService
  ) {}

  @HostListener('window:keydown.escape', ['$event'])
  handleEscapeKey(event: KeyboardEvent) {
    if (!this.isLoading) {
      this.closeDialog();
    }
  }

  async onSubmit() {
    if (!this.email || !this.password) {
      this.errorMessage = 'Por favor, preencha todos os campos.';
      return;
    }

    this.isLoading = true;
    this.errorMessage = '';
    this.successMessage = '';

    try {
      const result = await this.authService.login(this.email, this.password);
      
      if (result.success) {
        this.successMessage = result.message;
        setTimeout(() => {
          this.loginSuccess.emit();
          this.closeDialog();
        }, 1000);
      } else {
        // Se o email não existe, mostrar formulário de cadastro
        if (result.emailExists === false) {
          this.showSignupForm = true;
          this.errorMessage = '';
        } else {
          this.errorMessage = result.message;
        }
      }
    } catch (error) {
      console.error('Erro no login:', error);
      this.errorMessage = 'Erro ao realizar login. Tente novamente.';
    } finally {
      this.isLoading = false;
    }
  }

  async onSignup() {
    if (!this.email || !this.password || !this.name) {
      this.errorMessage = 'Por favor, preencha todos os campos.';
      return;
    }

    this.isLoading = true;
    this.errorMessage = '';
    this.successMessage = '';

    // Criar o usuário
    this.userManagementService.addUser({
      name: this.name,
      email: this.email,
      password: this.password,
      role: 'user',
      cpf: this.cpf,
      telefone: this.telefone,
      empresa: this.empresa,
      endereco: this.endereco,
      bairro: this.bairro,
      cidade: this.cidade,
      estado: this.estado,
      pais: this.pais,
      telefoneComercial: this.telefoneComercial,
      cnpj: this.cnpj
    }).subscribe({
      next: async (result) => {
        if (result.success) {
          // Após criar, fazer login automaticamente
          const loginResult = await this.authService.login(this.email, this.password);
          
          if (loginResult.success) {
            this.successMessage = 'Conta criada e login realizado com sucesso!';
            setTimeout(() => {
              this.loginSuccess.emit();
              this.closeDialog();
            }, 1000);
          } else {
            this.errorMessage = 'Conta criada, mas houve um erro ao fazer login. Tente fazer login novamente.';
            this.showSignupForm = false;
          }
        } else {
          this.errorMessage = result.message;
        }
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Erro ao criar conta:', error);
        this.errorMessage = 'Erro ao criar conta. Tente novamente.';
        this.isLoading = false;
      }
    });
  }

  goBackToLogin() {
    this.showSignupForm = false;
    this.name = '';
    this.cpf = '';
    this.telefone = '';
    this.empresa = '';
    this.endereco = '';
    this.bairro = '';
    this.cidade = '';
    this.estado = '';
    this.pais = '';
    this.telefoneComercial = '';
    this.cnpj = '';
    this.errorMessage = '';
    this.successMessage = '';
  }

  closeDialog() {
    this.email = '';
    this.password = '';
    this.name = '';
    this.cpf = '';
    this.telefone = '';
    this.empresa = '';
    this.endereco = '';
    this.bairro = '';
    this.cidade = '';
    this.estado = '';
    this.pais = '';
    this.telefoneComercial = '';
    this.cnpj = '';
    this.errorMessage = '';
    this.successMessage = '';
    this.showSignupForm = false;
    this.showForgotPasswordDialog = false;
    this.forgotPasswordEmail = '';
    this.close.emit();
  }

  openForgotPasswordDialog() {
    this.showForgotPasswordDialog = true;
    this.forgotPasswordEmail = this.email; // Preencher com o email do login se existir
    this.errorMessage = '';
    this.successMessage = '';
  }

  closeForgotPasswordDialog() {
    this.showForgotPasswordDialog = false;
    this.forgotPasswordEmail = '';
    this.errorMessage = '';
    this.successMessage = '';
  }

  async onForgotPasswordSubmit() {
    if (!this.forgotPasswordEmail) {
      this.errorMessage = 'Por favor, informe seu email.';
      return;
    }

    // Validar formato de email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(this.forgotPasswordEmail)) {
      this.errorMessage = 'Por favor, insira um email válido.';
      return;
    }

    this.isLoading = true;
    this.errorMessage = '';
    this.successMessage = '';

    try {
      // Buscar usuário pelo email
      const user = this.userManagementService.getUserByEmail(this.forgotPasswordEmail);

      if (!user) {
        this.errorMessage = 'Email não encontrado em nosso sistema.';
        this.isLoading = false;
        return;
      }

      // Enviar senha por email
      const result = await this.emailService.enviarSenhaRecuperacao({
        email: this.forgotPasswordEmail,
        nome: user.name,
        senha: user.password
      });

      if (result.success) {
        this.successMessage = 'Senha enviada para seu email com sucesso!';
        setTimeout(() => {
          this.closeForgotPasswordDialog();
        }, 2000);
      } else {
        this.errorMessage = result.message || 'Erro ao enviar email. Tente novamente.';
      }
    } catch (error) {
      console.error('Erro ao recuperar senha:', error);
      this.errorMessage = 'Erro ao recuperar senha. Tente novamente.';
    } finally {
      this.isLoading = false;
    }
  }

  onOverlayClick(event: MouseEvent) {
    if ((event.target as HTMLElement).classList.contains('dialog-overlay')) {
      if (this.showForgotPasswordDialog) {
        this.closeForgotPasswordDialog();
      } else {
        this.closeDialog();
      }
    }
  }
}
