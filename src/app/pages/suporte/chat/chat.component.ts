import { Component } from '@angular/core';
import { CommonModule, DatePipe } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { EmailService } from '../../../services/email.service';

@Component({
  selector: 'app-chat',
  standalone: true,
  imports: [CommonModule, FormsModule, DatePipe],
  templateUrl: './chat.component.html',
  styleUrl: './chat.component.css'
})
export class ChatComponent {
  // Número do WhatsApp (formato internacional sem +)
  private readonly WHATSAPP_NUMBER = '5521983761056';
  
  messages: Array<{ text: string; sender: 'user' | 'system'; timestamp: Date }> = [];
  newMessage: string = '';
  userName: string = '';
  userEmail: string = '';
  userPhone: string = '';
  isSubmitting: boolean = false;
  showUserForm: boolean = true;

  constructor(private emailService: EmailService) {
    // Mensagem de boas-vindas
    this.addSystemMessage('Olá! Como posso ajudá-lo hoje?');
  }

  addSystemMessage(text: string) {
    this.messages.push({
      text,
      sender: 'system',
      timestamp: new Date()
    });
  }

  addUserMessage(text: string) {
    this.messages.push({
      text,
      sender: 'user',
      timestamp: new Date()
    });
  }

  saveUserInfo() {
    if (!this.userName || !this.userEmail) {
      alert('Por favor, preencha pelo menos o nome e email.');
      return;
    }
    this.showUserForm = false;
    this.addSystemMessage(`Olá ${this.userName}! Como posso ajudá-lo?`);
  }

  async sendMessage() {
    if (!this.newMessage.trim()) {
      return;
    }

    if (this.showUserForm) {
      alert('Por favor, preencha seus dados primeiro.');
      return;
    }

    // Adicionar mensagem do usuário
    this.addUserMessage(this.newMessage);
    const userMessage = this.newMessage;
    this.newMessage = '';

    // Resposta automática
    this.isSubmitting = true;
    this.addSystemMessage('Processando sua mensagem...');

    try {
      // Enviar via EmailJS para notificação no celular
      const resultado = await this.emailService.enviarMensagemChat({
        nome: this.userName,
        email: this.userEmail,
        telefone: this.userPhone || 'Não informado',
        mensagem: userMessage
      });

      if (resultado.success) {
        this.addSystemMessage('Mensagem enviada com sucesso! Nossa equipe entrará em contato em breve.');
        this.addSystemMessage('Você também pode falar conosco diretamente pelo WhatsApp clicando no botão abaixo.');
      } else {
        this.addSystemMessage('Houve um problema ao enviar sua mensagem. Tente novamente ou use o WhatsApp.');
      }
    } catch (error) {
      console.error('Erro ao enviar mensagem:', error);
      this.addSystemMessage('Erro ao enviar mensagem. Use o WhatsApp para contato direto.');
    } finally {
      this.isSubmitting = false;
    }
  }

  openWhatsApp() {
    const message = encodeURIComponent(
      `Olá! Meu nome é ${this.userName || 'Usuário'}. ` +
      (this.messages.length > 0 ? `Minha última mensagem: ${this.messages[this.messages.length - 1].text}` : '')
    );
    const whatsappUrl = `https://wa.me/${this.WHATSAPP_NUMBER}?text=${message}`;
    window.open(whatsappUrl, '_blank');
  }

  onKeyPress(event: KeyboardEvent) {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      this.sendMessage();
    }
  }
}
