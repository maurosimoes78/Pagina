<?php
/**
 * Classe para gerenciar conexão SSE individual
 * Mantém conexão ativa enquanto usuário estiver online
 * Compatível com PHP 5.3.29
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/SSEEventBus.php';
require_once __DIR__ . '/ActivityManager.php';
require_once __DIR__ . '/Auth.php';

class SSEConnection {
    private $db;
    private $eventBus;
    private $activityManager;
    private $auth;
    private $config;
    private $userId;
    private $sessionId;
    private $isActive;

    public function __construct($token) {
        $this->db = Database::getInstance()->getConnection();
        $this->eventBus = new SSEEventBus();
        $this->activityManager = new ActivityManager();
        $this->auth = new Auth();
        $this->config = require __DIR__ . '/../config/config.php';
        $this->isActive = false;

        // Validar token e obter dados do usuário
        $session = $this->auth->validateToken($token);
        if (!$session) {
            throw new Exception("Token inválido ou expirado");
        }

        $this->userId = $session['user_id'];
        $this->sessionId = $session['id'];
    }

    /**
     * Inicia conexão SSE
     */
    public function start() {

        // Verificar limite de conexões
        $limitCheck = $this->activityManager->checkConnectionLimit($this->userId);
        if (!$limitCheck['canConnect']) {
            $this->sendError("Limite de conexões simultâneas atingido");
            return;
        }


        // Configurar headers SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Desabilita buffering no Nginx

        // Enviar comentário inicial para manter conexão
        $this->sendComment("SSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection establishedSSE connection established");

        ob_implicit_flush(true);
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();    

        $this->isActive = true;
        $heartbeatInterval = $this->config['sse']['heartbeat_interval'];
        $inactivityTimeout = $this->config['sse']['inactivity_timeout'];
        $lastActivity = time();

        // Loop principal
        while ($this->isActive) {

            // Verificar inatividade
            if (time() - $lastActivity > $inactivityTimeout) {
                $this->activityManager->markInactive($this->userId, $this->sessionId);
                $this->sendEvent('connection_timeout', array('message' => 'Conexão encerrada por inatividade'));
                break;
            }

            // Registrar heartbeat
            $this->activityManager->registerHeartbeat($this->userId, $this->sessionId);
            $lastActivity = time();

            // Buscar eventos pendentes
            $events = $this->eventBus->getPendingEvents($this->userId, $this->sessionId);

            // Enviar eventos
            foreach ($events as $event) {
                // Verificar se há dados no evento
                if (empty($event['data'])) {
                    error_log("SSE: Evento ID " . $event['id'] . " sem dados");
                    $eventData = array('message' => 'Evento sem dados');
                } else {
                    // Decodificar dados do evento
                    $eventData = json_decode($event['data'], true);
                    
                    // Verificar se houve erro na decodificação (json_decode retorna null em caso de erro)
                    // Mas null também pode ser um valor válido, então verificamos json_last_error()
                    $jsonError = json_last_error();
                    if ($jsonError !== JSON_ERROR_NONE) {
                        // Função json_last_error_msg() só existe no PHP 5.5+
                        $errorMsg = 'Erro JSON ' . $jsonError;
                        if (function_exists('json_last_error_msg')) {
                            $errorMsg = json_last_error_msg();
                        }
                        error_log("SSE: Erro ao decodificar dados do evento ID " . $event['id'] . ": " . $errorMsg);
                        error_log("SSE: Dados brutos: " . substr($event['data'], 0, 200));
                        // Tentar usar os dados como string se não for possível decodificar
                        $eventData = array('raw_data' => $event['data'], 'error' => 'Falha ao decodificar JSON');
                    } elseif ($eventData === null && $event['data'] !== 'null' && $event['data'] !== '') {
                        // Se retornou null mas não era a string "null", pode ser um problema
                        error_log("SSE: Aviso - json_decode retornou null para evento ID " . $event['id']);
                        $eventData = array('raw_data' => $event['data'], 'warning' => 'Dados retornaram null');
                    }
                }
                
                // Garantir que eventData seja um array
                if (!is_array($eventData)) {
                    $eventData = array('data' => $eventData);
                }
                
                // Log para debug (remover em produção se necessário)
                error_log("SSE: Enviando evento '" . $event['event_type'] . "' (ID: " . $event['id'] . ") com " . count($eventData) . " campos");
                
                // Enviar evento com os dados
                $this->sendEvent($event['event_type'], $eventData);
            }

            // Enviar heartbeat periódico
            //$this->sendComment("heartbeat");

            // Limpar buffer de saída
            while (ob_get_level() > 0) ob_end_flush();
            flush();

            // Aguardar antes da próxima iteração
            sleep($heartbeatInterval);
        }

        // Marcar como inativo ao encerrar
        $this->activityManager->markInactive($this->userId, $this->sessionId);
    }

    /**
     * Encerra conexão SSE
     */
    public function stop() {
        $this->isActive = false;
    }

    /**
     * Envia evento SSE
     */
    private function sendEvent($eventType, $data) {
        // Garantir que data seja um array
        if (!is_array($data)) {
            $data = array('data' => $data);
        }
        
        // Codificar dados para JSON
        $jsonData = json_encode($data);
        
        // Verificar se houve erro na codificação
        if ($jsonData === false) {
            $jsonError = json_last_error();
            $errorMsg = 'Erro JSON ' . $jsonError;
            if (function_exists('json_last_error_msg')) {
                $errorMsg = json_last_error_msg();
            }
            error_log("SSE: Erro ao codificar dados do evento '" . $eventType . "': " . $errorMsg);
            error_log("SSE: Dados originais: " . print_r($data, true));
            $jsonData = json_encode(array('error' => 'Erro ao processar dados do evento', 'original_type' => $eventType));
        }
        
        // Enviar evento no formato SSE
        echo "event: " . $eventType . "\n";
        echo "data: " . $jsonData . "\n\n";
        
        // Forçar flush imediato
        while (ob_get_level() > 0) ob_end_flush();
        flush();
    }

    /**
     * Envia comentário SSE
     */
    private function sendComment($comment) {
        echo ": $comment\n\n";
        while (ob_get_level() > 0) ob_end_flush();
        flush();
    }

    /**
     * Envia erro SSE
     */
    private function sendError($message) {
        $this->sendEvent('error', array('message' => $message));
    }

    /**
     * Verifica se conexão está ativa
     */
    public function isConnectionActive() {
        return $this->isActive && $this->activityManager->isUserActive($this->userId, $this->sessionId);
    }
}
