import { Component, OnInit } from '@angular/core';
import { CommonModule, DatePipe } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { UserManagementService, UserData } from '../../../services/user-management.service';

@Component({
  selector: 'app-usuarios',
  standalone: true,
  imports: [CommonModule, FormsModule, DatePipe],
  templateUrl: './usuarios.component.html',
  styleUrl: './usuarios.component.css'
})
export class UsuariosComponent implements OnInit {
  users: UserData[] = [];
  isAddDialogOpen = false;
  isEditDialogOpen = false;
  isDeleteDialogOpen = false;
  selectedUser: UserData | null = null;
  
  formData = {
    name: '',
    email: '',
    password: '',
    role: 'user',
    cpf: '',
    telefone: '',
    empresa: '',
    endereco: '',
    bairro: '',
    cidade: '',
    estado: '',
    pais: '',
    telefoneComercial: '',
    cnpj: ''
  };

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

  errorMessage: string = '';
  successMessage: string = '';
  showPassword: boolean = false; // Controla se a senha está visível

  constructor(private userService: UserManagementService) {}

  ngOnInit() {
    this.loadUsers();
    this.userService.users$.subscribe(users => {
      this.users = users;
    });
  }

  loadUsers() {
    this.users = this.userService.getUsers();
  }

  openAddDialog() {
    this.resetForm();
    this.isAddDialogOpen = true;
    this.errorMessage = '';
    this.successMessage = '';
  }

  openEditDialog(user: UserData) {
    this.selectedUser = user;
    this.formData = {
      name: user.name || '',
      email: user.email || '',
      password: '', // Não preencher senha por segurança
      role: user.role || 'user',
      cpf: user.cpf || '',
      telefone: user.telefone || '',
      empresa: user.empresa || '',
      endereco: user.endereco || '',
      bairro: user.bairro || '',
      cidade: user.cidade || '',
      estado: user.estado || '',
      pais: user.pais || '',
      telefoneComercial: user.telefoneComercial || '',
      cnpj: user.cnpj || ''
    };
    this.isEditDialogOpen = true;
    this.errorMessage = '';
    this.successMessage = '';
  }

  openDeleteDialog(user: UserData) {
    this.selectedUser = user;
    this.isDeleteDialogOpen = true;
  }

  closeDialogs() {
    this.isAddDialogOpen = false;
    this.isEditDialogOpen = false;
    this.isDeleteDialogOpen = false;
    this.selectedUser = null;
    this.resetForm();
    this.errorMessage = '';
    this.successMessage = '';
  }

  resetForm() {
    this.formData = {
      name: '',
      email: '',
      password: '',
      role: 'user',
      cpf: '',
      telefone: '',
      empresa: '',
      endereco: '',
      bairro: '',
      cidade: '',
      estado: '',
      pais: '',
      telefoneComercial: '',
      cnpj: ''
    };
  }

  addUser() {
    if (!this.formData.name || !this.formData.email || !this.formData.password) {
      this.errorMessage = 'Por favor, preencha todos os campos obrigatórios.';
      return;
    }

    const result = this.userService.addUser({
      name: this.formData.name,
      email: this.formData.email,
      password: this.formData.password,
      role: this.formData.role,
      cpf: this.formData.cpf,
      telefone: this.formData.telefone,
      empresa: this.formData.empresa,
      endereco: this.formData.endereco,
      bairro: this.formData.bairro,
      cidade: this.formData.cidade,
      estado: this.formData.estado,
      pais: this.formData.pais,
      telefoneComercial: this.formData.telefoneComercial,
      cnpj: this.formData.cnpj
    });

    if (result.success) {
      this.successMessage = result.message;
      setTimeout(() => {
        this.closeDialogs();
      }, 1000);
    } else {
      this.errorMessage = result.message;
    }
  }

  updateUser() {
    if (!this.selectedUser) return;

    if (!this.formData.name || !this.formData.email) {
      this.errorMessage = 'Por favor, preencha todos os campos obrigatórios.';
      return;
    }

    const updateData: any = {
      name: this.formData.name,
      email: this.formData.email,
      role: this.formData.role,
      cpf: this.formData.cpf,
      telefone: this.formData.telefone,
      empresa: this.formData.empresa,
      endereco: this.formData.endereco,
      bairro: this.formData.bairro,
      cidade: this.formData.cidade,
      estado: this.formData.estado,
      pais: this.formData.pais,
      telefoneComercial: this.formData.telefoneComercial,
      cnpj: this.formData.cnpj
    };

    // Só atualizar senha se fornecida
    if (this.formData.password) {
      updateData.password = this.formData.password;
    }

    const result = this.userService.updateUser(this.selectedUser.id, updateData);

    if (result.success) {
      this.successMessage = result.message;
      setTimeout(() => {
        this.closeDialogs();
      }, 1000);
    } else {
      this.errorMessage = result.message;
    }
  }

  deleteUser() {
    if (!this.selectedUser) return;

    const result = this.userService.removeUser(this.selectedUser.id);

    if (result.success) {
      this.successMessage = result.message;
      setTimeout(() => {
        this.closeDialogs();
      }, 1000);
    } else {
      this.errorMessage = result.message;
    }
  }

  onOverlayClick(event: MouseEvent) {
    if ((event.target as HTMLElement).classList.contains('dialog-overlay')) {
      this.closeDialogs();
    }
  }
}
