import { Injectable } from '@angular/core';
import emailjs from '@emailjs/browser';

@Injectable({
  providedIn: 'root'
})
export class EmailService {
  // Configurações do EmailJS
  // IMPORTANTE: Você precisa configurar estas variáveis com suas credenciais do EmailJS
  // 1. Crie uma conta em https://www.emailjs.com/
  // 2. Crie um serviço de email (Gmail, Outlook, etc.)
  // 3. Crie um template de email
  // 4. Obtenha seu Public Key, Service ID e Template ID
  private readonly EMAILJS_PUBLIC_KEY = 'tLbC9GhYgVsYSLx45'; // Substitua pela sua chave pública
  private readonly EMAILJS_SERVICE_ID = 'service_9lsbews'; // Substitua pelo ID do seu serviço
  private readonly EMAILJS_TEMPLATE_ID = 'template_sr2kkfn'; // Substitua pelo ID do seu template
  private readonly EMAILJS_SAC_TEMPLATE_ID = 'template_zzx04oc'; // Substitua pelo ID do seu template
  private readonly EMAILJS_RECUPERACAO_SENHA_TEMPLATE_ID = 'template_sr2kkfn'; // Use o mesmo template ou crie um específico
  
  constructor() {
    // Inicializar EmailJS
    emailjs.init(this.EMAILJS_PUBLIC_KEY);
  }

  /**
   * Envia um email de candidatura usando EmailJS
   * @param formData Dados do formulário de candidatura
   * @param cvFile Arquivo do currículo (opcional)
   * @returns Promise com o resultado do envio
   */
  async enviarCandidatura(formData: {
    nome: string;
    email: string;
    telefone: string;
    cargo: string;
    mensagem: string;
  }, cvFile: File | null): Promise<{ success: boolean; message: string }> {
    try {
      // Preparar dados do template
      const templateParams = {
        to_email: 'vagas@s3smart.com.br',
        from_name: formData.nome,
        from_email: formData.email,
        telefone: formData.telefone,
        cargo: formData.cargo,
        mensagem: formData.mensagem || 'Sem mensagem adicional.',
        cv_file_name: cvFile ? cvFile.name : 'Nenhum currículo anexado.',
        reply_to: formData.email
      };

      // Enviar email usando EmailJS
      const response = await emailjs.send(
        this.EMAILJS_SERVICE_ID,
        this.EMAILJS_TEMPLATE_ID,
        templateParams
      );

      if (response.status === 200) {
        return {
          success: true,
          message: 'Candidatura enviada com sucesso!'
        };
      } else {
        return {
          success: false,
          message: 'Erro ao enviar candidatura. Tente novamente.'
        };
      }
    } catch (error: any) {
      console.error('Erro ao enviar email:', error);
      return {
        success: false,
        message: error.text || 'Erro ao enviar candidatura. Verifique sua conexão e tente novamente.'
      };
    }
  }

  /**
   * Envia uma mensagem de chat usando EmailJS
   * As mensagens são enviadas por email e podem ser encaminhadas para SMS/WhatsApp
   * @param chatData Dados da mensagem do chat
   * @returns Promise com o resultado do envio
   */
  async enviarMensagemChat(chatData: {
    nome: string;
    email: string;
    telefone: string;
    mensagem: string;
  }): Promise<{ success: boolean; message: string }> {
    try {
      // Preparar dados do template
      const templateParams = {
        to_email: 'sac@s3smart.com.br', // Email que receberá as notificações
        from_name: chatData.nome,
        from_email: chatData.email,
        telefone: chatData.telefone,
        mensagem: chatData.mensagem,
        reply_to: chatData.email,
        subject: `Nova mensagem do chat - ${chatData.nome}`
      };

      // Enviar email usando EmailJS
      // Nota: Você pode criar um template específico para chat no EmailJS
      const response = await emailjs.send(
        this.EMAILJS_SERVICE_ID,
        this.EMAILJS_SAC_TEMPLATE_ID, // Use o mesmo template ou crie um específico para chat
        templateParams
      );

      if (response.status === 200) {
        return {
          success: true,
          message: 'Mensagem enviada com sucesso!'
        };
      } else {
        return {
          success: false,
          message: 'Erro ao enviar mensagem. Tente novamente.'
        };
      }
    } catch (error: any) {
      console.error('Erro ao enviar mensagem de chat:', error);
      return {
        success: false,
        message: error.text || 'Erro ao enviar mensagem. Verifique sua conexão e tente novamente.'
      };
    }
  }

  /**
   * Método alternativo: Envia dados para um endpoint de backend
   * Use este método se preferir ter um backend próprio
   */
  async enviarCandidaturaBackend(formData: {
    nome: string;
    email: string;
    telefone: string;
    cargo: string;
    mensagem: string;
  }, cvFile: File | null): Promise<{ success: boolean; message: string }> {
    try {
      // Criar FormData para enviar arquivo
      const formDataToSend = new FormData();
      formDataToSend.append('nome', formData.nome);
      formDataToSend.append('email', formData.email);
      formDataToSend.append('telefone', formData.telefone);
      formDataToSend.append('cargo', formData.cargo);
      formDataToSend.append('mensagem', formData.mensagem || '');
      
      if (cvFile) {
        formDataToSend.append('curriculo', cvFile);
      }

      // Fazer requisição para o backend
      const response = await fetch('/api/candidatura', {
        method: 'POST',
        body: formDataToSend
      });

      if (response.ok) {
        return {
          success: true,
          message: 'Candidatura enviada com sucesso!'
        };
      } else {
        return {
          success: false,
          message: 'Erro ao enviar candidatura. Tente novamente.'
        };
      }
    } catch (error: any) {
      console.error('Erro ao enviar para backend:', error);
      return {
        success: false,
        message: 'Erro ao enviar candidatura. Verifique sua conexão e tente novamente.'
      };
    }
  }

  /**
   * Envia a senha de recuperação por email usando EmailJS
   * @param recoveryData Dados para recuperação de senha
   * @returns Promise com o resultado do envio
   */
  async enviarSenhaRecuperacao(recoveryData: {
    email: string;
    nome: string;
    senha: string;
  }): Promise<{ success: boolean; message: string }> {
    try {
      // Preparar dados do template
      const templateParams = {
        to_email: recoveryData.email,
        from_name: 'Sistema Akani',
        from_email: 'noreply@s3smart.com.br',
        nome: recoveryData.nome,
        senha: recoveryData.senha,
        reply_to: 'sac@s3smart.com.br',
        subject: 'Recuperação de Senha - Akani'
      };

      // Enviar email usando EmailJS
      const response = await emailjs.send(
        this.EMAILJS_SERVICE_ID,
        this.EMAILJS_RECUPERACAO_SENHA_TEMPLATE_ID,
        templateParams
      );

      if (response.status === 200) {
        return {
          success: true,
          message: 'Senha enviada para seu email com sucesso!'
        };
      } else {
        return {
          success: false,
          message: 'Erro ao enviar email. Tente novamente.'
        };
      }
    } catch (error: any) {
      console.error('Erro ao enviar email de recuperação:', error);
      return {
        success: false,
        message: error.text || 'Erro ao enviar email. Verifique sua conexão e tente novamente.'
      };
    }
  }
}

