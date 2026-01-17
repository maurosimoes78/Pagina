<?php
/**
 * Endpoint auxiliar para enviar eventos de teste SSE
 * Compatível com PHP 5.3.29
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Tratar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Autoload de classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Funções auxiliares para compatibilidade com PHP 5.3.29
if (!function_exists('setHttpResponseCode')) {
    function setHttpResponseCode($code) {
        if (function_exists('http_response_code')) {
            http_response_code($code);
        } else {
            $statusCodes = array(
                200 => 'OK', 201 => 'Created', 400 => 'Bad Request', 401 => 'Unauthorized',
                403 => 'Forbidden', 404 => 'Not Found', 405 => 'Method Not Allowed', 500 => 'Internal Server Error'
            );
            $statusText = isset($statusCodes[$code]) ? $statusCodes[$code] : 'OK';
            header("HTTP/1.1 $code $statusText", true, $code);
        }
    }
}

if (!function_exists('json_encode_unicode')) {
    function json_encode_unicode($data) {
        $json = json_encode($data);
        $json = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', function ($matches) {
            return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UTF-16BE");
        }, $json);
        return $json;
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        setHttpResponseCode(405);
        echo json_encode_unicode(array('success' => false, 'message' => 'Método não permitido'));
        exit;
    }
    
    // Verificar autenticação
    $token = null;
    // Tentar obter do header padrão
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        }
    }
    // Fallback para Apache com mod_rewrite (alguns servidores passam via REDIRECT_HTTP_AUTHORIZATION)
    if (empty($token) && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        }
    }
    
    if (empty($token)) {
        setHttpResponseCode(401);
        echo json_encode_unicode(array('success' => false, 'message' => 'Token de autenticação necessário no header Authorization'));
        exit;
    }
    
    $auth = new Auth();
    $session = $auth->validateToken($token);
    if (!$session) {
        setHttpResponseCode(401);
        echo json_encode_unicode(array('success' => false, 'message' => 'Token inválido ou expirado'));
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        $input = array();
    }
    
    if (empty($input['eventType']) || empty($input['data'])) {
        setHttpResponseCode(400);
        echo json_encode_unicode(array('success' => false, 'message' => 'Parâmetros inválidos: eventType e data são obrigatórios'));
        exit;
    }
    
    $eventBus = new SSEEventBus();
    $targetType = isset($input['targetType']) ? $input['targetType'] : 'user';
    
    // Enviar evento baseado no tipo de destino
    if ($targetType === 'all') {
        // Enviar para todos os usuários (broadcast)
        $result = $eventBus->sendToAll($input['eventType'], $input['data']);
    } elseif ($targetType === 'session') {
        // Enviar para sessão específica
        if (!isset($input['sessionId']) || empty($input['sessionId'])) {
            setHttpResponseCode(400);
            echo json_encode_unicode(array('success' => false, 'message' => 'Parâmetros inválidos: sessionId necessário para destino "session"'));
            exit;
        }
        $result = $eventBus->sendToSession($input['sessionId'], $input['eventType'], $input['data']);
    } elseif ($targetType === 'user') {
        // Enviar para usuário (todas as sessões do usuário)
        if (!isset($input['userId']) || empty($input['userId'])) {
            setHttpResponseCode(400);
            echo json_encode_unicode(array('success' => false, 'message' => 'Parâmetros inválidos: userId necessário para destino "user"'));
            exit;
        }
        $result = $eventBus->sendToUser($input['userId'], $input['eventType'], $input['data']);
    } else {
        setHttpResponseCode(400);
        echo json_encode_unicode(array('success' => false, 'message' => 'Tipo de destino inválido. Use: "user", "session" ou "all"'));
        exit;
    }
    
    setHttpResponseCode($result['success'] ? 200 : 400);
    echo json_encode_unicode($result);
    
} catch (Exception $e) {
    error_log("Test send event error: " . $e->getMessage());
    setHttpResponseCode(500);
    echo json_encode_unicode(array('success' => false, 'message' => 'Erro ao enviar evento'));
}

