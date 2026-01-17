<?php
/**
 * Classe para autenticação de usuários (login/logout)
 * Compatível com PHP 5.3.29
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/PasswordHelper.php';

class Auth {
    private $db;
    private $config;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->config = require __DIR__ . '/../config/config.php';
    }

    /**
     * Realiza login do usuário
     */
    public function login($email, $password) {
        try {
            // Buscar usuário por email
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(array('email' => $email));
            $user = $stmt->fetch();

            if (!$user) {
                return array(
                    'success' => false,
                    'message' => 'Email não encontrado',
                    'emailExists' => false
                );
            }

            // Verificar senha
            if (!PasswordHelper::verify($password, $user['password'])) {
                return array(
                    'success' => false,
                    'message' => 'Senha incorreta',
                    'emailExists' => true
                );
            }

            // Criar sessão
            $sessionId = $this->generateSessionId();
            $token = $this->generateToken($user['id'], $sessionId);

            // Salvar sessão no banco
            $stmt = $this->db->prepare("
                INSERT INTO sessions (id, user_id, token, ip_address, user_agent, last_activity)
                VALUES (:id, :user_id, :token, :ip_address, :user_agent, NOW())
            ");

            $ipAddress = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;

            $stmt->execute(array(
                'id' => $sessionId,
                'user_id' => $user['id'],
                'token' => $token,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent
            ));

            // Registrar atividade
            $this->registerActivity($user['id'], $sessionId);

            // Remover senha da resposta
            unset($user['password']);

            return array(
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'user' => $user,
                'token' => $token,
                'sessionId' => $sessionId
            );
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Erro ao realizar login'
            );
        }
    }

    /**
     * Realiza logout do usuário
     */
    public function logout($sessionId) {
        try {
            // Remover sessão
            $stmt = $this->db->prepare("DELETE FROM sessions WHERE id = :id");
            $stmt->execute(array('id' => $sessionId));

            // Remover atividade
            $stmt = $this->db->prepare("DELETE FROM user_activity WHERE session_id = :session_id");
            $stmt->execute(array('session_id' => $sessionId));

            return array(
                'success' => true,
                'message' => 'Logout realizado com sucesso'
            );
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Erro ao realizar logout'
            );
        }
    }

    /**
     * Verifica se um token é válido
     */
    public function validateToken($token) {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*, u.* 
                FROM sessions s
                INNER JOIN users u ON s.user_id = u.id
                WHERE s.token = :token 
                AND s.last_activity > DATE_SUB(NOW(), INTERVAL :lifetime SECOND)
            ");

            $lifetime = $this->config['session']['lifetime'];
            $stmt->execute(array('token' => $token, 'lifetime' => $lifetime));
            $session = $stmt->fetch();

            if ($session) {
                // Atualizar última atividade
                $this->updateLastActivity($session['id']);
                unset($session['password']);
                return $session;
            }

            return null;
        } catch (Exception $e) {
            error_log("Token validation error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Atualiza última atividade da sessão
     */
    private function updateLastActivity($sessionId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE sessions 
                SET last_activity = NOW() 
                WHERE id = :id
            ");
            $stmt->execute(array('id' => $sessionId));
        } catch (Exception $e) {
            error_log("Update activity error: " . $e->getMessage());
        }
    }

    /**
     * Registra atividade do usuário
     */
    private function registerActivity($userId, $sessionId) {
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
        } catch (Exception $e) {
            error_log("Register activity error: " . $e->getMessage());
        }
    }

    /**
     * Gera ID único para sessão (compatível com PHP 5.3)
     */
    private function generateSessionId() {
        if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes(32));
        } else {
            // Fallback menos seguro
            $bytes = '';
            for ($i = 0; $i < 32; $i++) {
                $bytes .= chr(mt_rand(0, 255));
            }
            return bin2hex($bytes);
        }
    }

    /**
     * Gera token JWT simples
     */
    private function generateToken($userId, $sessionId) {
        $header = base64_encode(json_encode(array('typ' => 'JWT', 'alg' => 'HS256')));
        $payload = base64_encode(json_encode(array(
            'user_id' => $userId,
            'session_id' => $sessionId,
            'exp' => time() + $this->config['jwt']['expiration']
        )));
        $signature = hash_hmac('sha256', "$header.$payload", $this->config['jwt']['secret'], true);
        $signature = base64_encode($signature);
        return "$header.$payload.$signature";
    }
}
