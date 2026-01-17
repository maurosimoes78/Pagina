<?php
/**
 * Classe para gerenciamento de atividade do usuário (heartbeat)
 * Controla inatividade e mantém conexões SSE ativas
 * Compatível com PHP 5.3.29
 */

require_once __DIR__ . '/Database.php';

class ActivityManager {
    private $db;
    private $config;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->config = require __DIR__ . '/../config/config.php';
    }

    /**
     * Registra heartbeat do usuário
     */
    public function registerHeartbeat($userId, $sessionId) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_activity (user_id, session_id, last_heartbeat, is_active)
                VALUES (:user_id, :session_id, NOW(), TRUE)
                ON DUPLICATE KEY UPDATE 
                    last_heartbeat = NOW(),
                    is_active = TRUE
            ");
            $stmt->execute(array(
                'user_id' => $userId,
                'session_id' => $sessionId
            ));

            return array(
                'success' => true,
                'message' => 'Heartbeat registrado'
            );
        } catch (Exception $e) {
            error_log("Register heartbeat error: " . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Erro ao registrar heartbeat'
            );
        }
    }

    /**
     * Verifica se usuário está ativo
     */
    public function isUserActive($userId, $sessionId) {
        try {
            $timeout = $this->config['sse']['inactivity_timeout'];
            
            $stmt = $this->db->prepare("
                SELECT is_active, last_heartbeat
                FROM user_activity
                WHERE user_id = :user_id 
                AND session_id = :session_id
                AND last_heartbeat > DATE_SUB(NOW(), INTERVAL :timeout SECOND)
            ");
            $stmt->execute(array(
                'user_id' => $userId,
                'session_id' => $sessionId,
                'timeout' => $timeout
            ));

            $activity = $stmt->fetch();
            return $activity && $activity['is_active'];
        } catch (Exception $e) {
            error_log("Check user activity error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marca usuário como inativo
     */
    public function markInactive($userId, $sessionId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE user_activity 
                SET is_active = FALSE 
                WHERE user_id = :user_id 
                AND session_id = :session_id
            ");
            $stmt->execute(array(
                'user_id' => $userId,
                'session_id' => $sessionId
            ));

            return array(
                'success' => true,
                'message' => 'Usuário marcado como inativo'
            );
        } catch (Exception $e) {
            error_log("Mark inactive error: " . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Erro ao marcar como inativo'
            );
        }
    }

    /**
     * Limpa atividades inativas antigas
     */
    public function cleanupInactiveActivities($hoursOld = 24) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM user_activity 
                WHERE is_active = FALSE 
                AND last_heartbeat < DATE_SUB(NOW(), INTERVAL :hours HOUR)
            ");
            $stmt->execute(array('hours' => $hoursOld));

            return array(
                'success' => true,
                'message' => 'Atividades inativas removidas'
            );
        } catch (Exception $e) {
            error_log("Cleanup inactive activities error: " . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Erro ao limpar atividades'
            );
        }
    }

    /**
     * Obtém todas as sessões ativas de um usuário
     */
    public function getActiveSessions($userId) {
        try {
            $timeout = $this->config['sse']['inactivity_timeout'];
            
            $stmt = $this->db->prepare("
                SELECT session_id, last_heartbeat
                FROM user_activity
                WHERE user_id = :user_id
                AND is_active = TRUE
                AND last_heartbeat > DATE_SUB(NOW(), INTERVAL :timeout SECOND)
                ORDER BY last_heartbeat DESC
            ");
            $stmt->execute(array(
                'user_id' => $userId,
                'timeout' => $timeout
            ));

            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get active sessions error: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Verifica limite de conexões por usuário
     */
    public function checkConnectionLimit($userId) {
        try {
            $maxConnections = $this->config['sse']['max_connections_per_user'];
            $activeSessions = $this->getActiveSessions($userId);

            return array(
                'success' => true,
                'canConnect' => count($activeSessions) < $maxConnections,
                'currentConnections' => count($activeSessions),
                'maxConnections' => $maxConnections
            );
        } catch (Exception $e) {
            error_log("Check connection limit error: " . $e->getMessage());
            return array(
                'success' => false,
                'canConnect' => false
            );
        }
    }
}
