<?php
/**
 * Classe para gerenciamento de eventos SSE (Server-Sent Events)
 * Suporta eventos individuais, por sessão, por usuário e globais
 * Compatível com PHP 5.3.29
 */

require_once __DIR__ . '/Database.php';

class SSEEventBus {
    private $db;
    private $config;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->config = require __DIR__ . '/../config/config.php';
    }

    /**
     * Envia evento para um usuário específico (todas as sessões)
     */
    public function sendToUser($userId, $eventType, $data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO sse_events (event_type, target_type, target_id, data)
                VALUES (:event_type, 'user', :target_id, :data)
            ");
            $stmt->execute(array(
                'event_type' => $eventType,
                'target_id' => $userId,
                'data' => json_encode($data)
            ));

            return array(
                'success' => true,
                'message' => 'Evento enviado para o usuário'
            );
        } catch (Exception $e) {
            error_log("Send to user error: " . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Erro ao enviar evento'
            );
        }
    }

    /**
     * Envia evento para uma sessão específica
     */
    public function sendToSession($sessionId, $eventType, $data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO sse_events (event_type, target_type, target_id, data)
                VALUES (:event_type, 'session', :target_id, :data)
            ");
            $stmt->execute(array(
                'event_type' => $eventType,
                'target_id' => $sessionId,
                'data' => json_encode($data)
            ));

            return array(
                'success' => true,
                'message' => 'Evento enviado para a sessão'
            );
        } catch (Exception $e) {
            error_log("Send to session error: " . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Erro ao enviar evento'
            );
        }
    }

    /**
     * Envia evento para todos os usuários
     */
    public function sendToAll($eventType, $data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO sse_events (event_type, target_type, target_id, data)
                VALUES (:event_type, 'all', NULL, :data)
            ");
            $stmt->execute(array(
                'event_type' => $eventType,
                'data' => json_encode($data)
            ));

            return array(
                'success' => true,
                'message' => 'Evento enviado para todos os usuários'
            );
        } catch (Exception $e) {
            error_log("Send to all error: " . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Erro ao enviar evento'
            );
        }
    }

    /**
     * Busca eventos pendentes para um usuário/sessão
     */
    public function getPendingEvents($userId, $sessionId) {
        try {
            // Buscar eventos para esta sessão
            $stmt = $this->db->prepare("
                SELECT id, event_type, data, created_at
                FROM sse_events
                WHERE target_type = 'session' 
                AND target_id = :session_id
                AND delivered = FALSE
                ORDER BY created_at ASC
            ");
            $stmt->execute(array('session_id' => $sessionId));
            $sessionEvents = $stmt->fetchAll();

            // Buscar eventos para este usuário (todas as sessões)
            $stmt = $this->db->prepare("
                SELECT id, event_type, data, created_at
                FROM sse_events
                WHERE target_type = 'user' 
                AND target_id = :user_id
                AND delivered = FALSE
                ORDER BY created_at ASC
            ");
            $stmt->execute(array('user_id' => $userId));
            $userEvents = $stmt->fetchAll();

            // Buscar eventos globais
            $stmt = $this->db->prepare("
                SELECT id, event_type, data, created_at
                FROM sse_events
                WHERE target_type = 'all'
                AND delivered = FALSE
                ORDER BY created_at ASC
            ");
            $stmt->execute();
            $allEvents = $stmt->fetchAll();

            // Combinar eventos
            $events = array_merge($sessionEvents, $userEvents, $allEvents);

            // Marcar eventos como entregues
            if (!empty($events)) {
                // array_column não existe no PHP 5.3, usar alternativa
                $eventIds = array();
                foreach ($events as $event) {
                    $eventIds[] = $event['id'];
                }
                
                $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
                $stmt = $this->db->prepare("
                    UPDATE sse_events 
                    SET delivered = TRUE 
                    WHERE id IN ($placeholders)
                ");
                $stmt->execute($eventIds);
            }

            return $events;
        } catch (Exception $e) {
            error_log("Get pending events error: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Limpa eventos antigos já entregues
     */
    public function cleanupOldEvents($daysOld = 7) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM sse_events 
                WHERE delivered = TRUE 
                AND created_at < DATE_SUB(NOW(), INTERVAL :days DAY)
            ");
            $stmt->execute(array('days' => $daysOld));

            return array(
                'success' => true,
                'message' => 'Eventos antigos removidos'
            );
        } catch (Exception $e) {
            error_log("Cleanup events error: " . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Erro ao limpar eventos'
            );
        }
    }
}
