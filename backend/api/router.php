<?php
/**
 * Router principal da API
 * Gerencia todos os endpoints
 * Compatível com PHP 5.3.29
 */

// Headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

// Tratar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    setHttpResponseCode(200);
    exit;
}

// Autoload de classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Carregar configuração
$config = require __DIR__ . '/../config/config.php';

// Obter método e path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Debug: log do path original (remover em produção)
// error_log("Original path: " . $path);

// Remover /backend/api do path se existir
$path = str_replace('/backend/api', '', $path);
// Também remover /api se existir (caso o .htaccess já tenha redirecionado)
if (strpos($path, '/api/') === 0) {
    $path = substr($path, 4); // Remove '/api'
}
// Remover router.php se estiver no path (caso acesso direto)
$path = str_replace('/router.php', '', $path);
$path = trim($path, '/');

$segments = empty($path) ? array() : explode('/', $path);

// Obter dados do corpo da requisição
$inputData = file_get_contents('php://input');
$input = json_decode($inputData, true);
if (!is_array($input)) {
    $input = array();
}

// Obter token do header Authorization
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

if (($token == null || empty($token)) && isset($_GET['token'])) {
    $token = $_GET['token']; 
}

// Router
try {
    // Se não houver segmentos, retornar 404
    if (empty($segments) || empty($segments[0])) {
        sendResponse(404, array('success' => false, 'message' => 'Endpoint não encontrado'));
    }
    
    switch ($segments[0]) {
        case 'auth':
            handleAuth($method, $segments, $input);
            break;

        case 'users':
            handleUsers($method, $segments, $input, $token);
            break;

        case 'sse':
            handleSSE($method, $segments, $token);
            break;

        case 'activity':
            handleActivity($method, $segments, $input, $token);
            break;

        default:
            sendResponse(404, array('success' => false, 'message' => 'Endpoint não encontrado'));
    }
} catch (Exception $e) {
    error_log("Router error: " . $e->getMessage());
    sendResponse(500, array('success' => false, 'message' => 'Erro interno do servidor'));
}

/**
 * Função auxiliar para definir código de resposta HTTP (compatível com PHP 5.3)
 */
function setHttpResponseCode($code) {
    if (function_exists('http_response_code')) {
        http_response_code($code);
    } else {
        // Fallback para PHP 5.3
        $statusCodes = array(
            200 => 'OK',
            201 => 'Created',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error'
        );
        $statusText = isset($statusCodes[$code]) ? $statusCodes[$code] : 'OK';
        header("HTTP/1.1 $code $statusText", true, $code);
    }
}

/**
 * Handlers de autenticação
 */
function handleAuth($method, $segments, $input) {
    $auth = new Auth();

    switch ($method) {
        case 'POST':
            if (isset($segments[1]) && $segments[1] === 'login') {
                $email = isset($input['email']) ? $input['email'] : '';
                $password = isset($input['password']) ? $input['password'] : '';
                $result = $auth->login($email, $password);
                sendResponse($result['success'] ? 200 : 401, $result);
            } elseif (isset($segments[1]) && $segments[1] === 'logout') {
                $sessionId = isset($input['sessionId']) ? $input['sessionId'] : '';
                $result = $auth->logout($sessionId);
                sendResponse($result['success'] ? 200 : 400, $result);
            } else {
                sendResponse(404, array('success' => false, 'message' => 'Endpoint não encontrado'));
            }
            break;

        default:
            sendResponse(405, array('success' => false, 'message' => 'Método não permitido'));
    }
}

/**
 * Handlers de usuários (CRUD)
 */
function handleUsers($method, $segments, $input, $token) {
    // Verificar autenticação
    $auth = new Auth();
    $session = $auth->validateToken($token);

    if (in_array($method, array('PUT', 'DELETE'))) {
        if (!$session) {
            sendResponse(401, array('success' => false, 'message' => 'Não autenticado'));
            return;
        }

        // Verificar se é admin para operações de escrita
        if ($session['role'] !== 'admin') {
            sendResponse(403, array('success' => false, 'message' => 'Acesso negado'));
            return;
        }
    }

    $userManager = new UserManager();

    switch ($method) {
        case 'GET':
            if (empty($segments[1])) {
                // Listar todos
                $result = $userManager->getAllUsers();
                sendResponse(200, $result);
            } elseif (isset($segments[1]) && $segments[1] === 'email' && !empty($segments[2])) {
                // Buscar por email
                $result = $userManager->getUserByEmail($segments[2]);
                sendResponse($result['success'] ? 200 : 404, $result);
            } else {
                // Buscar por ID
                $result = $userManager->getUserById($segments[1]);
                sendResponse($result['success'] ? 200 : 404, $result);
            }
            break;

        case 'POST':
            // Criar usuário
            if ($input['role'] === 'admin' && (!$session || $session['role'] !== 'admin')) {
                sendResponse(403, array('success' => false, 'message' => 'Não é possivel criar um usuario com nivel administrativo não sendo um administrador'));
                return;
            }

            $result = $userManager->createUser($input);
            sendResponse($result['success'] ? 201 : 400, $result);
            break;

        case 'PUT':
            if (empty($segments[1])) {
                sendResponse(400, array('success' => false, 'message' => 'ID do usuário necessário'));
                return;
            }
            // Atualizar usuário
            $result = $userManager->updateUser($segments[1], $input);
            sendResponse($result['success'] ? 200 : 400, $result);
            break;

        case 'DELETE':
            if (empty($segments[1])) {
                sendResponse(400, array('success' => false, 'message' => 'ID do usuário necessário'));
                return;
            }
            // Remover usuário
            $result = $userManager->deleteUser($segments[1]);
            sendResponse($result['success'] ? 200 : 400, $result);
            break;

        default:
            sendResponse(405, array('success' => false, 'message' => 'Método não permitido'));
    }
}

/**
 * Handlers de SSE
 */
function handleSSE($method, $segments, $token) {
    if ($method !== 'GET') {
        sendResponse(405, array('success' => false, 'message' => 'Método não permitido'));
        return;
    }

    if (empty($token)) {
        sendResponse(401, array('success' => false, 'message' => 'Token necessário no header Authorization'));
        return;
    }

    try {
        $sseConnection = new SSEConnection($token);
        $sseConnection->start();
    } catch (Exception $e) {
        sendResponse(401, array('success' => false, 'message' => $e->getMessage()));
    }
}

/**
 * Handlers de atividade
 */
function handleActivity($method, $segments, $input, $token) {
    // Verificar autenticação
    $auth = new Auth();
    $session = $auth->validateToken($token);
    if (!$session) {
        sendResponse(401, array('success' => false, 'message' => 'Não autenticado'));
        return;
    }

    $activityManager = new ActivityManager();

    switch ($method) {
        case 'POST':
            if (isset($segments[1]) && $segments[1] === 'heartbeat') {
                // Registrar heartbeat
                $result = $activityManager->registerHeartbeat($session['user_id'], $session['id']);
                sendResponse($result['success'] ? 200 : 400, $result);
            } else {
                sendResponse(404, array('success' => false, 'message' => 'Endpoint não encontrado'));
            }
            break;

        case 'GET':
            if (isset($segments[1]) && $segments[1] === 'sessions') {
                // Listar sessões ativas
                $sessions = $activityManager->getActiveSessions($session['user_id']);
                sendResponse(200, array('success' => true, 'sessions' => $sessions));
            } elseif (isset($segments[1]) && $segments[1] === 'check') {
                // Verificar se está ativo
                $isActive = $activityManager->isUserActive($session['user_id'], $session['id']);
                sendResponse(200, array('success' => true, 'isActive' => $isActive));
            } else {
                sendResponse(404, array('success' => false, 'message' => 'Endpoint não encontrado'));
            }
            break;

        default:
            sendResponse(405, array('success' => false, 'message' => 'Método não permitido'));
    }
}

/**
 * Envia resposta JSON
 */
function sendResponse($statusCode, $data) {
    setHttpResponseCode($statusCode);
    // JSON_UNESCAPED_UNICODE foi introduzido no PHP 5.4, usar alternativa para 5.3
    if (defined('JSON_UNESCAPED_UNICODE')) {
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    } else {
        // Fallback para PHP 5.3 - usar função personalizada
        echo json_encode_unicode($data);
    }
    exit;
}

/**
 * Função auxiliar para json_encode com suporte a Unicode (PHP 5.3)
 */
function json_encode_unicode($data) {
    if (function_exists('json_encode')) {
        $json = json_encode($data);
        // Escapar caracteres Unicode manualmente se necessário
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', function($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
        }, $json);
    }
    return '';
}
