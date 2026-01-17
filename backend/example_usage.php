<?php
/**
 * Exemplo de uso da API
 * Este arquivo demonstra como usar as classes do sistema
 * Compatível com PHP 5.3.29
 * Exibe resultados em formato HTML com tabelas
 */

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/UserManager.php';
require_once __DIR__ . '/classes/SSEEventBus.php';
require_once __DIR__ . '/classes/ActivityManager.php';

/**
 * Função auxiliar para converter array em tabela HTML
 */
function arrayToTable($data, $title = '', $showDeleteButtons = false, $token = null) {
    if (!is_array($data) || empty($data)) {
        return '<p>Nenhum dado disponível</p>';
    }
    
    $html = '';
    if ($title) {
        $html .= '<h3>' . htmlspecialchars($title) . '</h3>';
    }
    
    $html .= '<table class="result-table">';
    
    // Se for array associativo simples
    if (isset($data[0]) && is_array($data[0])) {
        // Array de arrays (tabela com múltiplas linhas)
        if (!empty($data[0])) {
            $html .= '<thead><tr>';
            foreach (array_keys($data[0]) as $key) {
                $html .= '<th>' . htmlspecialchars($key) . '</th>';
            }
            if ($showDeleteButtons) {
                $html .= '<th>Ações</th>';
            }
            $html .= '</tr></thead><tbody>';
            
            foreach ($data as $row) {
                $html .= '<tr data-user-id="' . (isset($row['id']) ? htmlspecialchars($row['id']) : '') . '">';
                foreach ($row as $key => $value) {
                    if (is_array($value)) {
                        $html .= '<td>' . htmlspecialchars(json_encode($value)) . '</td>';
                    } else {
                        $html .= '<td>' . htmlspecialchars($value) . '</td>';
                    }
                }
                if ($showDeleteButtons && isset($row['id'])) {
                    $html .= '<td>';
                    $html .= '<button class="btn-delete-user" onclick="deleteUser(' . htmlspecialchars($row['id']) . ', \'' . htmlspecialchars($row['email']) . '\')" ';
                    $html .= 'data-user-id="' . htmlspecialchars($row['id']) . '" ';
                    $html .= 'data-user-email="' . htmlspecialchars($row['email']) . '">';
                    $html .= 'Excluir</button>';
                    $html .= '</td>';
                } else if ($showDeleteButtons) {
                    $html .= '<td>-</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
        }
    } else {
        // Array associativo simples (uma linha)
        $html .= '<tbody>';
        foreach ($data as $key => $value) {
            $html .= '<tr>';
            $html .= '<th>' . htmlspecialchars($key) . '</th>';
            if (is_array($value)) {
                // Para arrays aninhados, exibir como JSON
                // JSON_PRETTY_PRINT não existe em PHP 5.3, usar apenas json_encode
                if (defined('JSON_PRETTY_PRINT')) {
                    $jsonValue = json_encode($value, JSON_PRETTY_PRINT);
                } else {
                    $jsonValue = json_encode($value);
                }
                $html .= '<td><pre>' . htmlspecialchars($jsonValue) . '</pre></td>';
            } else {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
    }
    
    $html .= '</table>';
    return $html;
}

/**
 * Função para exibir resultado formatado
 */
function displayResult($title, $result, $successClass = 'success', $errorClass = 'error') {
    $html = '<div class="example-section">';
    $html .= '<h2>' . htmlspecialchars($title) . '</h2>';
    
    if (is_array($result)) {
        if (isset($result['success'])) {
            $class = $result['success'] ? $successClass : $errorClass;
            $html .= '<div class="status ' . $class . '">';
            $html .= '<strong>Status:</strong> ' . ($result['success'] ? 'Sucesso' : 'Erro');
            $html .= '</div>';
        }
        
        $html .= arrayToTable($result);
    } else {
        $html .= '<p>' . htmlspecialchars($result) . '</p>';
    }
    
    $html .= '</div>';
    return $html;
}

// Calcular caminho base para assets
// Tentar diferentes métodos para obter o caminho correto
$scriptPath = dirname($_SERVER['SCRIPT_FILENAME']);
$documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';
$basePath = '';

if (!empty($documentRoot) && strpos($scriptPath, $documentRoot) === 0) {
    // Se o script está dentro do document root
    $basePath = str_replace($documentRoot, '', $scriptPath);
} else {
    // Usar caminho relativo baseado no nome do arquivo
    $basePath = dirname($_SERVER['PHP_SELF']);
}

$basePath = str_replace('\\', '/', $basePath);
if (substr($basePath, 0, 1) !== '/') {
    $basePath = '/' . $basePath;
}
// Remover barra final se existir
$basePath = rtrim($basePath, '/');

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exemplos de Uso da API - Sistema Akani</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($basePath); ?>/css/example_usage.css">
</head>
<body>
    <div class="container">
        <h1>Exemplos de Uso da API</h1>
        <p class="subtitle">Demonstração das funcionalidades do sistema Akani</p>
        
        <?php
        // Exemplo 1: Criar um usuário
        $userManager = new UserManager();
        $createUserResult = $userManager->createUser(array(
            'email' => 'admin@exemplo.com',
            'password' => 'senha123',
            'name' => 'Administrador',
            'role' => 'admin'
        ));
        echo displayResult('Exemplo 1: Criar Usuário', $createUserResult);
        
        // Exemplo 2: Login
        $auth = new Auth();
        $loginResult = $auth->login('admin@exemplo.com', 'senha123');
        echo displayResult('Exemplo 2: Login', $loginResult);
        
        $token = isset($loginResult['token']) ? $loginResult['token'] : null;
        $sessionId = isset($loginResult['sessionId']) ? $loginResult['sessionId'] : null;
        
        // Exemplo 3: Enviar evento SSE
        if ($token) {
            $eventBus = new SSEEventBus();
            $session = $auth->validateToken($token);
            
            if ($session) {
                $userId = $session['user_id'];
                
                $sseResults = array();
                
                // Enviar evento para o usuário
                $result1 = $eventBus->sendToUser($userId, 'notification', array(
                    'message' => 'Bem-vindo ao sistema!',
                    'type' => 'info'
                ));
                $row1 = array('Ação' => 'Enviar para Usuário');
                $row1 = array_merge($row1, $result1);
                $sseResults[] = $row1;
                
                // Enviar evento para a sessão específica
                $result2 = $eventBus->sendToSession($sessionId, 'update', array(
                    'data' => 'Dados atualizados'
                ));
                $row2 = array('Ação' => 'Enviar para Sessão');
                $row2 = array_merge($row2, $result2);
                $sseResults[] = $row2;
                
                // Enviar evento global
                $result3 = $eventBus->sendToAll('broadcast', array(
                    'message' => 'Anúncio para todos os usuários'
                ));
                $row3 = array('Ação' => 'Enviar para Todos');
                $row3 = array_merge($row3, $result3);
                $sseResults[] = $row3;
                
                echo '<div class="example-section">';
                echo '<h2>Exemplo 3: Enviar Eventos SSE</h2>';
                echo arrayToTable($sseResults);
                echo '</div>';
            }
        } else {
            echo '<div class="example-section">';
            echo '<h2>Exemplo 3: Enviar Eventos SSE</h2>';
            echo '<div class="info-message">Token não disponível. Execute o login primeiro.</div>';
            echo '</div>';
        }
        
        // Exemplo 3.5: Ouvir eventos SSE
        // Criar usuário específico para teste SSE
        $sseTestEmail = 'sse_test_' . time() . '@exemplo.com';
        $sseTestPassword = 'sse123';
        $sseTestUserResult = $userManager->createUser(array(
            'email' => $sseTestEmail,
            'password' => $sseTestPassword,
            'name' => 'Usuário Teste SSE',
            'role' => 'user'
        ));
        
        $sseTestToken = null;
        $sseTestSessionId = null;
        $sseTestUserId = null;
        $sseTokenJs = null;
        $sseUserIdJs = null;
        $sseSessionIdJs = null;
        
        if ($sseTestUserResult['success']) {
            // Fazer login do usuário de teste
            $sseTestLoginResult = $auth->login($sseTestEmail, $sseTestPassword);
            if ($sseTestLoginResult['success']) {
                $sseTestToken = isset($sseTestLoginResult['token']) ? $sseTestLoginResult['token'] : null;
                $sseTestSessionId = isset($sseTestLoginResult['sessionId']) ? $sseTestLoginResult['sessionId'] : null;
                $sseTestSession = $auth->validateToken($sseTestToken);
                if ($sseTestSession) {
                    $sseTestUserId = $sseTestSession['user_id'];
                }
            }
        }
        
        echo '<div class="example-section">';
        echo '<h2>Exemplo 3.5: Ouvir Eventos SSE</h2>';
        
        if ($sseTestToken && $sseTestUserId) {
            echo '<div class="status success"><strong>Status:</strong> Usuário de teste criado e autenticado</div>';
            echo '<div class="info-message">';
            echo '<strong>Instruções:</strong> Clique em "Conectar SSE" abaixo para iniciar a conexão Server-Sent Events. ';
            echo 'A conexão usará o token do usuário de teste criado automaticamente ou o token armazenado no localStorage (se você fez login). ';
            echo 'Depois, clique em "Enviar Evento de Teste" para enviar um evento que será recebido em tempo real.';
            echo '</div>';
            
            echo '<div class="sse-container">';
            echo '<div id="sse-status" class="sse-status disconnected">Desconectado</div>';
            echo '<div class="sse-controls">';
            echo '<button id="btn-connect" class="btn-connect" onclick="connectSSE()">Conectar SSE</button>';
            echo '<button id="btn-disconnect" class="btn-disconnect" onclick="disconnectSSE()" disabled>Desconectar</button>';
            echo '<button id="btn-send-event" class="btn-send" onclick="sendTestEvent()" disabled>Enviar Evento de Teste</button>';
            echo '</div>';
            echo '<h3>Eventos Recebidos:</h3>';
            echo '<div id="sse-events" class="sse-events">';
            echo '<p style="color: #666; text-align: center;">Nenhum evento recebido ainda. Conecte-se para começar a receber eventos.</p>';
            echo '</div>';
            echo '</div>';
            
            // Armazenar variáveis para inicialização após carregamento do JS
            $sseTokenJs = json_encode($sseTestToken);
            $sseUserIdJs = json_encode($sseTestUserId);
            $sseSessionIdJs = json_encode($sseTestSessionId);
            
            // Enviar alguns eventos iniciais para demonstração
            if ($sseTestUserId) {
                $eventBus = new SSEEventBus();
                $eventBus->sendToUser($sseTestUserId, 'notification', array(
                    'message' => 'Bem-vindo ao teste de SSE! Conecte-se para receber eventos em tempo real.',
                    'type' => 'info'
                ));
            }
        } else {
            echo '<div class="status error"><strong>Status:</strong> Erro ao criar usuário de teste para SSE</div>';
        }
        
        echo '</div>';
        
        // Exemplo 4: Registrar atividade
        if ($token) {
            $activityManager = new ActivityManager();
            $session = $auth->validateToken($token);
            
            if ($session) {
                $heartbeatResult = $activityManager->registerHeartbeat($session['user_id'], $session['id']);
                $isActive = $activityManager->isUserActive($session['user_id'], $session['id']);
                
                // Converter array de heartbeat para string para exibição
                $heartbeatText = '';
                if (is_array($heartbeatResult)) {
                    $heartbeatText = isset($heartbeatResult['message']) ? $heartbeatResult['message'] : 'Registrado';
                } else {
                    $heartbeatText = $heartbeatResult;
                }
                
                $activityData = array(
                    'Heartbeat' => $heartbeatText,
                    'Usuário Ativo' => $isActive ? 'Sim' : 'Não'
                );
                
                echo '<div class="example-section">';
                echo '<h2>Exemplo 4: Registrar Atividade</h2>';
                echo arrayToTable($activityData);
                echo '</div>';
            }
        } else {
            echo '<div class="example-section">';
            echo '<h2>Exemplo 4: Registrar Atividade</h2>';
            echo '<div class="info-message">Token não disponível. Execute o login primeiro.</div>';
            echo '</div>';
        }
        
        // Exemplo 3.6: Despachar Evento SSE Dinâmico
        echo '<div class="example-section">';
        echo '<h2>Exemplo 3.6: Despachar Evento SSE Dinâmico</h2>';
        echo '<div class="info-message">';
        echo '<strong>Instruções:</strong> Use este teste para despachar eventos SSE dinamicamente usando o token armazenado no localStorage. ';
        echo 'Você pode escolher o tipo de evento, o destino (usuário, sessão ou todos) e os dados a serem enviados.';
        echo '</div>';
        echo '<div class="event-dispatch-container">';
        echo '<form id="event-dispatch-form" onsubmit="dispatchEvent(event); return false;">';
        echo '<div class="form-group">';
        echo '<label for="event-type">Tipo de Evento:</label>';
        echo '<select id="event-type" name="eventType" required style="width: 100%; max-width: 400px; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;">';
        echo '<option value="notification">notification</option>';
        echo '<option value="update">update</option>';
        echo '<option value="broadcast">broadcast</option>';
        echo '<option value="error">error</option>';
        echo '<option value="custom">custom (personalizado)</option>';
        echo '</select>';
        echo '</div>';
        echo '<div class="form-group">';
        echo '<label for="target-type">Destino do Evento:</label>';
        echo '<select id="target-type" name="targetType" required style="width: 100%; max-width: 400px; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;">';
        echo '<option value="user">Usuário (todas as sessões do usuário logado)</option>';
        echo '<option value="session">Sessão (sessão atual do usuário logado)</option>';
        echo '<option value="all">Todos os usuários (broadcast)</option>';
        echo '</select>';
        echo '</div>';
        echo '<div class="form-group">';
        echo '<label for="event-data">Dados do Evento (JSON):</label>';
        echo '<textarea id="event-data" name="eventData" rows="6" required style="width: 100%; max-width: 600px; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; font-family: monospace; font-size: 0.9em;">{"message": "Evento de teste", "timestamp": "", "type": "test"}</textarea>';
        echo '<small style="color: #666; display: block; margin-top: 5px;">Use formato JSON válido. O campo "timestamp" será preenchido automaticamente se estiver vazio.</small>';
        echo '</div>';
        echo '<div class="form-group">';
        echo '<button type="submit" class="btn-dispatch-event">Despachar Evento</button>';
        echo '</div>';
        echo '</form>';
        echo '<div id="event-dispatch-message" style="margin-top: 15px;"></div>';
        echo '<div id="event-dispatch-log" style="margin-top: 15px; max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; background-color: #f8f9fa; font-size: 0.85em; font-family: monospace;">';
        echo '<div style="color: #666; margin-bottom: 5px;"><strong>Log de Eventos Despachados:</strong></div>';
        echo '<div id="event-dispatch-log-content"></div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        // Exemplo 4.5: Heartbeat Dinâmico
        echo '<div class="example-section">';
        echo '<h2>Exemplo 4.5: Heartbeat Dinâmico</h2>';
        echo '<div class="info-message">';
        echo '<strong>Instruções:</strong> Use este teste para enviar heartbeat ao backend usando o token armazenado no localStorage. ';
        echo 'O heartbeat mantém a sessão ativa e pode ser enviado manualmente ou automaticamente em intervalos.';
        echo '</div>';
        echo '<div class="heartbeat-container">';
        echo '<div class="sse-controls">';
        echo '<button id="btn-heartbeat" class="btn-heartbeat" onclick="sendHeartbeat();">Enviar Heartbeat</button>';
        echo '<button id="btn-start-auto-heartbeat" class="btn-start-heartbeat" onclick="startAutoHeartbeat();">Iniciar Auto Heartbeat</button>';
        echo '<button id="btn-stop-auto-heartbeat" class="btn-stop-heartbeat" onclick="stopAutoHeartbeat();" disabled>Parar Auto Heartbeat</button>';
        echo '</div>';
        echo '<div style="margin-top: 15px;">';
        echo '<label><input type="number" id="heartbeat-interval" value="30" min="5" max="300" style="width: 60px; padding: 4px; margin-right: 5px;"> segundos entre heartbeats</label>';
        echo '</div>';
        echo '<div id="heartbeat-status" style="margin-top: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 4px; font-size: 0.9em;">';
        echo '<strong>Status:</strong> <span id="heartbeat-status-text">Aguardando...</span>';
        echo '</div>';
        echo '<div id="heartbeat-message" style="margin-top: 15px;"></div>';
        echo '<div id="heartbeat-log" style="margin-top: 15px; max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; background-color: #f8f9fa; font-size: 0.85em; font-family: monospace;">';
        echo '<div style="color: #666; margin-bottom: 5px;"><strong>Log de Heartbeats:</strong></div>';
        echo '<div id="heartbeat-log-content"></div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        // Exemplo 4.6: Login Interativo (para testes de exclusão)
        echo '<div class="example-section">';
        echo '<h2>Exemplo 4.6: Login Interativo</h2>';
        echo '<div class="info-message">';
        echo '<strong>Instruções:</strong> Use este formulário para fazer login e obter um token de autenticação. ';
        echo 'O token será armazenado no localStorage e usado para excluir usuários na lista abaixo.';
        echo '</div>';
        echo '<div class="login-form-container">';
        echo '<form id="login-form" onsubmit="handleLogin(event); return false;">';
        echo '<div class="form-group">';
        echo '<label for="login-email">Email:</label>';
        echo '<input type="email" id="login-email" name="email" value="admin@exemplo.com" required>';
        echo '</div>';
        echo '<div class="form-group">';
        echo '<label for="login-password">Senha:</label>';
        echo '<div style="position: relative; display: inline-block; width: 100%; max-width: 400px;">';
        echo '<input type="password" id="login-password" name="password" value="senha123" required>';
        echo '<button type="button" id="toggle-password-btn" onclick="togglePasswordVisibility();" title="Mostrar senha">👁️</button>';
        echo '</div>';
        echo '</div>';
        echo '<div class="form-group">';
        echo '<button type="submit" class="btn-login">Fazer Login</button>';
        echo '<button type="button" class="btn-logout" onclick="handleLogout();" style="margin-left: 10px;">Limpar Token</button>';
        echo '</div>';
        echo '</form>';
        echo '<div id="login-message" style="margin-top: 15px;"></div>';
        echo '<div id="token-status" style="margin-top: 10px; padding: 10px; background-color: #f8f9fa; border-radius: 4px; font-size: 0.9em; word-break: break-all;">';
        echo '<strong>Status do Token:</strong> <span id="token-status-text" style="display: block; margin-top: 5px; font-family: monospace; font-size: 0.85em;">Verificando...</span>';
        echo '</div>';
        echo '<div id="token-uri-encoded" style="margin-top: 10px; padding: 10px; background-color: #f8f9fa; border-radius: 4px; font-size: 0.9em; word-break: break-all;">';
        echo '<strong>Token Codificado em URI:</strong>';
        echo '<div class="form-group" style="margin-top: 5px;">';
        echo '<input type="text" id="token-uri-encoded-input" readonly style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; font-family: monospace; font-size: 0.85em; background-color: #fff;" value="">';
        echo '<button type="button" onclick="copyTokenUriEncoded();" style="margin-top: 5px; padding: 4px 8px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.85em;">Copiar Token URI</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        // Exemplo 5: Listar usuários
        $usersResult = $userManager->getAllUsers();
        $userIdToUpdate = null;
        $userIdToDelete = null;
        
        if ($usersResult['success'] && !empty($usersResult['users'])) {
            echo '<div class="example-section" id="example-5-users-list">';
            echo '<h2>Exemplo 5: Listar Usuários</h2>';
            echo '<div class="sse-controls" style="margin-bottom: 15px;">';
            echo '<button id="btn-refresh-users" class="btn-refresh-users" onclick="refreshUsersList();">Atualizar Lista</button>';
            echo '</div>';
            echo '<div id="users-status" class="status success"><strong>Status:</strong> Sucesso - ' . count($usersResult['users']) . ' usuário(s) encontrado(s)</div>';
            // Usar token se disponível para permitir exclusão
            $currentToken = isset($token) ? $token : null;
            echo '<div id="users-table-container">';
            echo arrayToTable($usersResult['users'], '', true, $currentToken);
            echo '</div>';
            echo '<div id="delete-user-message" style="margin-top: 15px;"></div>';
            echo '</div>';
            
            // Obter ID do primeiro usuário para testes de atualização e exclusão
            if (!empty($usersResult['users'][0]['id'])) {
                $userIdToUpdate = $usersResult['users'][0]['id'];
            }
            // Criar um usuário temporário para teste de exclusão
            $tempUserResult = $userManager->createUser(array(
                'email' => 'temp_' . time() . '@exemplo.com',
                'password' => 'temp123',
                'name' => 'Usuário Temporário',
                'role' => 'user'
            ));
            if ($tempUserResult['success'] && isset($tempUserResult['userId'])) {
                $userIdToDelete = $tempUserResult['userId'];
            }
        } else {
            echo displayResult('Exemplo 5: Listar Usuários', $usersResult);
        }
        
        // Exemplo 6: Buscar usuário por ID
        if ($userIdToUpdate) {
            $getUserResult = $userManager->getUserById($userIdToUpdate);
            if ($getUserResult['success']) {
                echo '<div class="example-section">';
                echo '<h2>Exemplo 6: Buscar Usuário por ID</h2>';
                echo '<div class="status success"><strong>Status:</strong> Sucesso - Usuário encontrado</div>';
                echo arrayToTable($getUserResult['user']);
                echo '</div>';
            } else {
                echo displayResult('Exemplo 6: Buscar Usuário por ID', $getUserResult);
            }
        } else {
            echo '<div class="example-section">';
            echo '<h2>Exemplo 6: Buscar Usuário por ID</h2>';
            echo '<div class="info-message">Nenhum usuário disponível para busca.</div>';
            echo '</div>';
        }
        
        // Exemplo 7: Atualizar dados do usuário
        if ($userIdToUpdate) {
            $updateData = array(
                'name' => 'Administrador Atualizado',
                'telefone' => '(21) 98765-4321',
                'empresa' => 'S3 Technologies',
                'cidade' => 'Rio de Janeiro',
                'estado' => 'RJ',
                'pais' => 'Brasil'
            );
            
            $updateResult = $userManager->updateUser($userIdToUpdate, $updateData);
            
            echo '<div class="example-section">';
            echo '<h2>Exemplo 7: Atualizar Dados do Usuário</h2>';
            
            if ($updateResult['success']) {
                echo '<div class="status success"><strong>Status:</strong> Sucesso - Usuário atualizado</div>';
                
                // Mostrar dados antes e depois
                $beforeData = $userManager->getUserById($userIdToUpdate);
                $afterData = $userManager->getUserById($userIdToUpdate);
                
                $comparisonData = array();
                if ($beforeData['success'] && $afterData['success']) {
                    $comparisonData[] = array(
                        'Campo' => 'Nome',
                        'Valor Anterior' => isset($beforeData['user']['name']) ? $beforeData['user']['name'] : 'N/A',
                        'Valor Atual' => isset($afterData['user']['name']) ? $afterData['user']['name'] : 'N/A'
                    );
                    $comparisonData[] = array(
                        'Campo' => 'Telefone',
                        'Valor Anterior' => isset($beforeData['user']['telefone']) ? $beforeData['user']['telefone'] : 'N/A',
                        'Valor Atual' => isset($afterData['user']['telefone']) ? $afterData['user']['telefone'] : 'N/A'
                    );
                    $comparisonData[] = array(
                        'Campo' => 'Empresa',
                        'Valor Anterior' => isset($beforeData['user']['empresa']) ? $beforeData['user']['empresa'] : 'N/A',
                        'Valor Atual' => isset($afterData['user']['empresa']) ? $afterData['user']['empresa'] : 'N/A'
                    );
                    $comparisonData[] = array(
                        'Campo' => 'Cidade',
                        'Valor Anterior' => isset($beforeData['user']['cidade']) ? $beforeData['user']['cidade'] : 'N/A',
                        'Valor Atual' => isset($afterData['user']['cidade']) ? $afterData['user']['cidade'] : 'N/A'
                    );
                }
                
                echo '<h3>Dados Atualizados:</h3>';
                echo arrayToTable($comparisonData);
                
                echo '<h3>Dados Completos do Usuário:</h3>';
                echo arrayToTable($afterData['user']);
            } else {
                echo '<div class="status error"><strong>Status:</strong> Erro</div>';
                echo arrayToTable($updateResult);
            }
            
            echo '</div>';
        } else {
            echo '<div class="example-section">';
            echo '<h2>Exemplo 7: Atualizar Dados do Usuário</h2>';
            echo '<div class="info-message">Nenhum usuário disponível para atualização.</div>';
            echo '</div>';
        }
        
        // Exemplo 8: Excluir usuário
        if ($userIdToDelete) {
            // Buscar dados do usuário antes de excluir
            $userBeforeDelete = $userManager->getUserById($userIdToDelete);
            
            $deleteResult = $userManager->deleteUser($userIdToDelete);
            
            echo '<div class="example-section">';
            echo '<h2>Exemplo 8: Excluir Usuário</h2>';
            
            if ($deleteResult['success']) {
                echo '<div class="status success"><strong>Status:</strong> Sucesso - Usuário excluído</div>';
                
                // Mostrar dados do usuário excluído
                if ($userBeforeDelete['success']) {
                    echo '<h3>Dados do Usuário Excluído:</h3>';
                    echo arrayToTable($userBeforeDelete['user']);
                }
                
                // Tentar buscar o usuário novamente para confirmar exclusão
                $verifyDelete = $userManager->getUserById($userIdToDelete);
                echo '<h3>Verificação de Exclusão:</h3>';
                if (!$verifyDelete['success']) {
                    echo '<div class="status success">✓ Usuário confirmado como excluído (não encontrado no banco)</div>';
                } else {
                    echo '<div class="status error">✗ Erro: Usuário ainda existe no banco</div>';
                }
            } else {
                echo '<div class="status error"><strong>Status:</strong> Erro</div>';
                echo arrayToTable($deleteResult);
            }
            
            echo '</div>';
        } else {
            echo '<div class="example-section">';
            echo '<h2>Exemplo 8: Excluir Usuário</h2>';
            echo '<div class="info-message">Nenhum usuário temporário disponível para exclusão.</div>';
            echo '</div>';
        }
        
        // Exemplo 9: Logout (automático do PHP)
        if ($sessionId) {
            $logoutResult = $auth->logout($sessionId);
            echo displayResult('Exemplo 9: Logout (Automático)', $logoutResult);
        } else {
            echo '<div class="example-section">';
            echo '<h2>Exemplo 9: Logout (Automático)</h2>';
            echo '<div class="info-message">Session ID não disponível. Execute o login primeiro.</div>';
            echo '</div>';
        }
        
        // Exemplo 9.5: Logout Interativo (usando token do localStorage)
        echo '<div class="example-section">';
        echo '<h2>Exemplo 9.5: Logout Interativo</h2>';
        echo '<div class="info-message">';
        echo '<strong>Instruções:</strong> Use este botão para fazer logout usando o token armazenado no localStorage. ';
        echo 'O logout invalidará a sessão no servidor e removerá o token do localStorage.';
        echo '</div>';
        echo '<div class="sse-controls">';
        echo '<button id="btn-logout-api" class="btn-logout-api" onclick="handleLogoutAPI();">Fazer Logout via API</button>';
        echo '</div>';
        echo '<div id="logout-message" style="margin-top: 15px;"></div>';
        echo '</div>';
        ?>
        
        <div class="footer">
            <p><strong>Exemplos concluídos</strong></p>
            <p>Sistema Akani - Backend PHP 5.3.29</p>
        </div>
    </div>
    
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/example_usage.js"></script>
    <script type="text/javascript">
    // Variáveis globais para autenticação
    var apiToken = <?php echo isset($token) ? json_encode($token) : 'null'; ?>;
    
    // Verificar se há token no localStorage
    function getStoredToken() {
        try {
            var storedToken = localStorage.getItem('api_token');
            if (storedToken) {
                console.log("[AUTH] Token encontrado no localStorage");
                return storedToken;
            }
        } catch (e) {
            console.warn("[AUTH] Erro ao acessar localStorage:", e);
        }
        return null;
    }
    
    // Salvar token no localStorage
    function saveTokenToStorage(token) {
        try {
            localStorage.setItem('api_token', token);
            console.log("[AUTH] Token salvo no localStorage");
            updateTokenStatus();
            return true;
        } catch (e) {
            console.error("[AUTH] Erro ao salvar token no localStorage:", e);
            return false;
        }
    }
    
    // Remover token do localStorage
    function removeTokenFromStorage() {
        try {
            localStorage.removeItem('api_token');
            console.log("[AUTH] Token removido do localStorage");
            updateTokenStatus();
            return true;
        } catch (e) {
            console.error("[AUTH] Erro ao remover token do localStorage:", e);
            return false;
        }
    }
    
    // Atualizar status do token na interface
    function updateTokenStatus() {
        var tokenStatusText = document.getElementById('token-status-text');
        var tokenUriInput = document.getElementById('token-uri-encoded-input');
        
        if (tokenStatusText) {
            var storedToken = getStoredToken();
            var currentToken = apiToken || storedToken;
            if (currentToken) {
                tokenStatusText.textContent = 'Token disponível: ' + currentToken;
                tokenStatusText.style.color = '#28a745';
                
                // Atualizar campo de token codificado em URI
                if (tokenUriInput) {
                    var uriEncodedToken = encodeURIComponent(currentToken);
                    tokenUriInput.value = uriEncodedToken;
                }
            } else {
                tokenStatusText.textContent = 'Nenhum token disponível. Faça login para obter um token.';
                tokenStatusText.style.color = '#dc3545';
                
                // Limpar campo de token codificado em URI
                if (tokenUriInput) {
                    tokenUriInput.value = '';
                }
            }
        }
    }
    
    // Função para copiar token codificado em URI
    function copyTokenUriEncoded() {
        var tokenUriInput = document.getElementById('token-uri-encoded-input');
        if (tokenUriInput && tokenUriInput.value) {
            tokenUriInput.select();
            tokenUriInput.setSelectionRange(0, 99999); // Para dispositivos móveis
            
            try {
                document.execCommand('copy');
                console.log("[TOKEN] Token URI copiado para a área de transferência");
                
                // Feedback visual
                var button = event.target;
                var originalText = button.textContent;
                button.textContent = 'Copiado!';
                button.style.backgroundColor = '#28a745';
                
                setTimeout(function() {
                    button.textContent = originalText;
                    button.style.backgroundColor = '#6c757d';
                }, 2000);
            } catch (e) {
                console.error("[TOKEN] Erro ao copiar token:", e);
                alert('Erro ao copiar token. Selecione e copie manualmente.');
            }
        } else {
            alert('Nenhum token disponível para copiar.');
        }
    }
    
    // Função para fazer login
    function handleLogin(event) {
        event.preventDefault();
        
        var email = document.getElementById('login-email').value;
        var password = document.getElementById('login-password').value;
        var messageDiv = document.getElementById('login-message');
        
        if (!email || !password) {
            if (messageDiv) {
                messageDiv.innerHTML = '<div class="delete-message error">Por favor, preencha email e senha.</div>';
            }
            return;
        }
        
        console.log("[LOGIN] Tentando fazer login com:", email);
        
        // Construir URL da API
        var baseUrl = window.location.origin;
        var currentPath = window.location.pathname;
        var basePath = "";
        if (currentPath.indexOf("/backend/") !== -1) {
            var backendIndex = currentPath.indexOf("/backend/");
            basePath = currentPath.substring(0, backendIndex + 8);
        } else {
            var pathParts = currentPath.split("/");
            pathParts = pathParts.slice(0, pathParts.length - 1);
            basePath = pathParts.join("/");
        }
        
        var apiUrl = baseUrl + basePath + "/api/auth/login";
        
        // Desabilitar botão durante a requisição
        var submitButton = event.target.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Fazendo login...';
        }
        
        // Fazer requisição POST usando XMLHttpRequest
        var xhr = new XMLHttpRequest();
        xhr.open("POST", apiUrl, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        
        var loginData = {
            email: email,
            password: password
        };
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Fazer Login';
                }
                
                if (xhr.status === 200) {
                    try {
                        var result = JSON.parse(xhr.responseText);
                        if (result.success && result.token) {
                            console.log("[LOGIN] ✓ Login realizado com sucesso");
                            apiToken = result.token;
                            saveTokenToStorage(result.token);
                            
                            if (messageDiv) {
                                messageDiv.innerHTML = '<div class="delete-message success">✓ Login realizado com sucesso! Token armazenado no localStorage.</div>';
                            }
                        } else {
                            console.error("[LOGIN] ✗ Erro no login:", result.message);
                            if (messageDiv) {
                                messageDiv.innerHTML = '<div class="delete-message error">✗ Erro: ' + (result.message || 'Credenciais inválidas') + '</div>';
                            }
                        }
                    } catch (e) {
                        console.error("[LOGIN] ✗ Erro ao parsear resposta:", e);
                        if (messageDiv) {
                            messageDiv.innerHTML = '<div class="delete-message error">✗ Erro ao processar resposta do servidor</div>';
                        }
                    }
                } else {
                    console.error("[LOGIN] ✗ Erro HTTP:", xhr.status);
                    try {
                        var errorResult = JSON.parse(xhr.responseText);
                        if (messageDiv) {
                            messageDiv.innerHTML = '<div class="delete-message error">✗ Erro ' + xhr.status + ': ' + (errorResult.message || 'Erro desconhecido') + '</div>';
                        }
                    } catch (e) {
                        if (messageDiv) {
                            messageDiv.innerHTML = '<div class="delete-message error">✗ Erro HTTP ' + xhr.status + '</div>';
                        }
                    }
                }
                
                // Limpar mensagem após 5 segundos
                if (messageDiv) {
                    setTimeout(function() {
                        messageDiv.innerHTML = '';
                    }, 5000);
                }
            }
        };
        
        xhr.send(JSON.stringify(loginData));
    }
    
    // Função para fazer logout (limpar token)
    function handleLogout() {
        if (confirm('Deseja remover o token do localStorage?')) {
            apiToken = null;
            removeTokenFromStorage();
            var messageDiv = document.getElementById('login-message');
            if (messageDiv) {
                messageDiv.innerHTML = '<div class="delete-message success">Token removido do localStorage.</div>';
                setTimeout(function() {
                    messageDiv.innerHTML = '';
                }, 3000);
            }
        }
    }
    
    // Função para fazer logout via API usando token do localStorage
    function handleLogoutAPI() {
        // Obter token do localStorage
        var tokenToUse = apiToken || getStoredToken();
        
        if (!tokenToUse) {
            alert('Token de autenticação não disponível. Faça login primeiro.');
            return;
        }
        
        if (!confirm('Deseja fazer logout? Isso invalidará a sessão no servidor e removerá o token do localStorage.')) {
            return;
        }
        
        console.log("[LOGOUT] Fazendo logout via API...");
        
        // Desabilitar botão durante a requisição
        var logoutButton = document.getElementById('btn-logout-api');
        if (logoutButton) {
            logoutButton.disabled = true;
            logoutButton.textContent = 'Fazendo logout...';
        }
        
        // Construir URL da API
        var baseUrl = window.location.origin;
        var currentPath = window.location.pathname;
        var basePath = "";
        if (currentPath.indexOf("/backend/") !== -1) {
            var backendIndex = currentPath.indexOf("/backend/");
            basePath = currentPath.substring(0, backendIndex + 8);
        } else {
            var pathParts = currentPath.split("/");
            pathParts = pathParts.slice(0, pathParts.length - 1);
            basePath = pathParts.join("/");
        }
        
        var apiUrl = baseUrl + basePath + "/api/auth/logout";
        
        // Decodificar o token JWT para obter o sessionId
        var sessionId = null;
        try {
            // JWT tem formato: header.payload.signature
            var tokenParts = tokenToUse.split('.');
            if (tokenParts.length === 3) {
                // Decodificar payload (base64)
                // Compatibilidade com navegadores antigos
                var base64 = tokenParts[1].replace(/-/g, '+').replace(/_/g, '/');
                var payload = JSON.parse(decodeURIComponent(escape(atob(base64))));
                sessionId = payload.session_id || null;
                console.log("[LOGOUT] Session ID extraído do token:", sessionId);
            }
        } catch (e) {
            console.warn("[LOGOUT] Erro ao decodificar token:", e);
        }
        
        if (!sessionId) {
            console.error("[LOGOUT] Não foi possível obter sessionId do token");
            alert('Erro: Não foi possível obter o sessionId do token. O logout pode não funcionar corretamente.');
            if (logoutButton) {
                logoutButton.disabled = false;
                logoutButton.textContent = 'Fazer Logout via API';
            }
            return;
        }
        
        // Fazer requisição POST usando XMLHttpRequest
        var xhr = new XMLHttpRequest();
        xhr.open("POST", apiUrl, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.setRequestHeader("Authorization", "Bearer " + tokenToUse);
        
        var logoutData = {
            sessionId: sessionId
        };
        
        console.log("[LOGOUT] Enviando requisição de logout com sessionId:", sessionId);
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var messageDiv = document.getElementById('logout-message');
                
                if (logoutButton) {
                    logoutButton.disabled = false;
                    logoutButton.textContent = 'Fazer Logout via API';
                }
                
                if (xhr.status === 200) {
                    try {
                        var result = JSON.parse(xhr.responseText);
                        if (result.success) {
                            console.log("[LOGOUT] ✓ Logout realizado com sucesso");
                            
                            // Remover token do localStorage
                            apiToken = null;
                            removeTokenFromStorage();
                            
                            // Também limpar variáveis SSE se existirem
                            if (typeof disconnectSSE === 'function') {
                                disconnectSSE();
                            }
                            
                            if (messageDiv) {
                                messageDiv.innerHTML = '<div class="delete-message success">✓ Logout realizado com sucesso! Sessão invalidada e token removido do localStorage.</div>';
                            }
                        } else {
                            console.error("[LOGOUT] ✗ Erro no logout:", result.message);
                            if (messageDiv) {
                                messageDiv.innerHTML = '<div class="delete-message error">✗ Erro: ' + (result.message || 'Erro desconhecido') + '</div>';
                            }
                        }
                    } catch (e) {
                        console.error("[LOGOUT] ✗ Erro ao parsear resposta:", e);
                        if (messageDiv) {
                            messageDiv.innerHTML = '<div class="delete-message error">✗ Erro ao processar resposta do servidor</div>';
                        }
                    }
                } else {
                    console.error("[LOGOUT] ✗ Erro HTTP:", xhr.status);
                    try {
                        var errorResult = JSON.parse(xhr.responseText);
                        if (messageDiv) {
                            messageDiv.innerHTML = '<div class="delete-message error">✗ Erro ' + xhr.status + ': ' + (errorResult.message || 'Erro desconhecido') + '</div>';
                        }
                    } catch (e) {
                        if (messageDiv) {
                            messageDiv.innerHTML = '<div class="delete-message error">✗ Erro HTTP ' + xhr.status + '</div>';
                        }
                    }
                }
                
                // Limpar mensagem após 5 segundos
                if (messageDiv) {
                    setTimeout(function() {
                        messageDiv.innerHTML = '';
                    }, 5000);
                }
            }
        };
        
        xhr.send(JSON.stringify(logoutData));
    }
    
    // Variáveis para auto heartbeat
    var heartbeatIntervalId = null;
    var heartbeatLogCount = 0;
    
    // Variáveis para despacho de eventos
    var eventDispatchLogCount = 0;
    
    // Função para despachar evento SSE
    function dispatchEvent(event) {
        event.preventDefault();
        
        // Obter token do localStorage
        var tokenToUse = apiToken || getStoredToken();
        
        if (!tokenToUse) {
            alert('Token de autenticação não disponível. Faça login primeiro.');
            return;
        }
        
        var eventType = document.getElementById('event-type').value;
        var targetType = document.getElementById('target-type').value;
        var eventDataText = document.getElementById('event-data').value;
        
        // Validar e parsear JSON
        var eventData = null;
        try {
            eventData = JSON.parse(eventDataText);
        } catch (e) {
            alert('Erro: Os dados do evento devem estar em formato JSON válido.\n\nErro: ' + e.message);
            return;
        }
        
        // Preencher timestamp se estiver vazio
        if (!eventData.timestamp || eventData.timestamp === '') {
            eventData.timestamp = new Date().toISOString();
        }
        
        console.log("[EVENT] Despachando evento:", eventType, "para", targetType);
        console.log("[EVENT] Dados:", eventData);
        
        // Desabilitar botão durante a requisição
        var dispatchButton = null;
        try {
            if (event && event.target) {
                dispatchButton = event.target.querySelector('button[type="submit"]');
            }
            // Se não encontrou pelo event.target, tentar pelo ID
            if (!dispatchButton) {
                dispatchButton = document.getElementById('event-dispatch-form');
                if (dispatchButton) {
                    dispatchButton = dispatchButton.querySelector('button[type="submit"]');
                }
            }
            // Se ainda não encontrou, tentar diretamente pelo ID do botão
            if (!dispatchButton) {
                dispatchButton = document.querySelector('.btn-dispatch-event');
            }
        } catch (e) {
            console.warn("[EVENT] Erro ao encontrar botão:", e);
        }
        
        if (dispatchButton) {
            dispatchButton.disabled = true;
            dispatchButton.textContent = 'Despachando...';
        }
        
        // Construir URL da API
        var baseUrl = window.location.origin;
        var currentPath = window.location.pathname;
        var basePath = "";
        if (currentPath.indexOf("/backend/") !== -1) {
            var backendIndex = currentPath.indexOf("/backend/");
            basePath = currentPath.substring(0, backendIndex + 8);
        } else {
            var pathParts = currentPath.split("/");
            pathParts = pathParts.slice(0, pathParts.length - 1);
            basePath = pathParts.join("/");
        }
        
        var apiUrl = baseUrl + basePath + "/api/test-send-event.php";
        
        // Decodificar token para obter userId e sessionId
        var userId = null;
        var sessionId = null;
        try {
            var tokenParts = tokenToUse.split('.');
            if (tokenParts.length === 3) {
                var base64 = tokenParts[1].replace(/-/g, '+').replace(/_/g, '/');
                var payload = JSON.parse(decodeURIComponent(escape(atob(base64))));
                userId = payload.user_id || null;
                sessionId = payload.session_id || null;
                console.log("[EVENT] User ID:", userId, "Session ID:", sessionId);
            }
        } catch (e) {
            console.error("[EVENT] Erro ao decodificar token:", e);
            alert('Erro: Não foi possível decodificar o token. Verifique se o token é válido.');
            if (dispatchButton) {
                dispatchButton.disabled = false;
                dispatchButton.textContent = 'Despachar Evento';
            }
            return;
        }
        
        // Preparar dados da requisição
        var requestData = {
            eventType: eventType,
            data: eventData,
            targetType: targetType
        };
        
        // Adicionar userId, sessionId baseado no tipo de destino
        if (targetType === 'all') {
            // Para broadcast, não precisamos de userId ou sessionId
            // Não adicionar nada
        } else if (targetType === 'session') {
            // Para sessão, precisamos de sessionId
            if (!sessionId) {
                alert('Erro: Não foi possível obter o sessionId do token. O destino "sessão" requer sessionId.');
                if (dispatchButton) {
                    dispatchButton.disabled = false;
                    dispatchButton.textContent = 'Despachar Evento';
                }
                return;
            }
            requestData.sessionId = sessionId;
            // Também adicionar userId para referência
            if (userId) {
                requestData.userId = userId;
            }
        } else {
            // Para usuário, precisamos de userId
            if (!userId) {
                alert('Erro: Não foi possível obter o ID do usuário do token.');
                if (dispatchButton) {
                    dispatchButton.disabled = false;
                    dispatchButton.textContent = 'Despachar Evento';
                }
                return;
            }
            requestData.userId = userId;
            // Adicionar sessionId se disponível para referência
            if (sessionId) {
                requestData.sessionId = sessionId;
            }
        }
        
        console.log("[EVENT] Dados da requisição:", requestData);
        
        // Fazer requisição POST usando XMLHttpRequest
        var xhr = new XMLHttpRequest();
        xhr.open("POST", apiUrl, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.setRequestHeader("Authorization", "Bearer " + tokenToUse);
        
        var startTime = new Date();
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                try {
                    var messageDiv = document.getElementById('event-dispatch-message');
                    var logContent = document.getElementById('event-dispatch-log-content');
                    
                    if (dispatchButton) {
                        dispatchButton.disabled = false;
                        dispatchButton.textContent = 'Despachar Evento';
                    }
                    
                    var endTime = new Date();
                    var duration = endTime - startTime;
                    
                    if (xhr.status === 200) {
                        try {
                            var result = JSON.parse(xhr.responseText);
                            if (result.success) {
                                console.log("[EVENT] ✓ Evento despachado com sucesso");
                                
                                if (messageDiv) {
                                    messageDiv.innerHTML = '<div class="delete-message success">✓ Evento "' + eventType + '" despachado com sucesso para "' + targetType + '"! (Tempo de resposta: ' + duration + 'ms)</div>';
                                }
                                
                                // Adicionar ao log
                                if (logContent) {
                                    eventDispatchLogCount++;
                                    var logEntry = document.createElement('div');
                                    logEntry.style.marginBottom = '5px';
                                    logEntry.style.padding = '5px';
                                    logEntry.style.backgroundColor = '#d4edda';
                                    logEntry.style.borderLeft = '3px solid #28a745';
                                    logEntry.style.borderRadius = '3px';
                                    var dataStr = JSON.stringify(eventData);
                                    var dataPreview = dataStr.length > 100 ? dataStr.substring(0, 100) + '...' : dataStr;
                                    logEntry.innerHTML = '<strong>[' + endTime.toLocaleTimeString() + ']</strong> Evento #' + eventDispatchLogCount + '<br>' +
                                        '<strong>Tipo:</strong> ' + eventType + ' | <strong>Destino:</strong> ' + targetType + '<br>' +
                                        '<strong>Dados:</strong> ' + dataPreview + '<br>' +
                                        '<strong>Resposta:</strong> ' + duration + 'ms';
                                    logContent.insertBefore(logEntry, logContent.firstChild);
                                    
                                    // Limitar log a 20 entradas
                                    while (logContent.children.length > 20) {
                                        logContent.removeChild(logContent.lastChild);
                                    }
                                }
                            } else {
                                console.error("[EVENT] ✗ Erro ao despachar evento:", result.message);
                                if (messageDiv) {
                                    messageDiv.innerHTML = '<div class="delete-message error">✗ Erro: ' + (result.message || 'Erro desconhecido') + '</div>';
                                }
                            }
                        } catch (e) {
                            console.error("[EVENT] ✗ Erro ao parsear resposta:", e);
                            console.error("[EVENT] Resposta do servidor:", xhr.responseText);
                            if (messageDiv) {
                                messageDiv.innerHTML = '<div class="delete-message error">✗ Erro ao processar resposta do servidor: ' + e.message + '</div>';
                            }
                        }
                    } else {
                        console.error("[EVENT] ✗ Erro HTTP:", xhr.status);
                        console.error("[EVENT] Resposta do servidor:", xhr.responseText);
                        try {
                            var errorResult = JSON.parse(xhr.responseText);
                            if (messageDiv) {
                                messageDiv.innerHTML = '<div class="delete-message error">✗ Erro ' + xhr.status + ': ' + (errorResult.message || 'Erro desconhecido') + '</div>';
                            }
                        } catch (e) {
                            if (messageDiv) {
                                messageDiv.innerHTML = '<div class="delete-message error">✗ Erro HTTP ' + xhr.status + ' - ' + (xhr.responseText || 'Sem resposta do servidor') + '</div>';
                            }
                        }
                    }
                    
                    // Limpar mensagem após 5 segundos
                    if (messageDiv) {
                        setTimeout(function() {
                            messageDiv.innerHTML = '';
                        }, 5000);
                    }
                } catch (e) {
                    console.error("[EVENT] ✗ Erro no callback:", e);
                    if (dispatchButton) {
                        dispatchButton.disabled = false;
                        dispatchButton.textContent = 'Despachar Evento';
                    }
                }
            }
        };
        
        try {
            xhr.send(JSON.stringify(requestData));
        } catch (e) {
            console.error("[EVENT] ✗ Erro ao enviar requisição:", e);
            alert('Erro ao enviar requisição: ' + e.message);
            if (dispatchButton) {
                dispatchButton.disabled = false;
                dispatchButton.textContent = 'Despachar Evento';
            }
        }
    }
    
    // Função para enviar heartbeat
    function sendHeartbeat() {
        // Obter token do localStorage
        var tokenToUse = apiToken || getStoredToken();
        
        if (!tokenToUse) {
            alert('Token de autenticação não disponível. Faça login primeiro.');
            return;
        }
        
        console.log("[HEARTBEAT] Enviando heartbeat...");
        
        // Desabilitar botão durante a requisição
        var heartbeatButton = document.getElementById('btn-heartbeat');
        if (heartbeatButton) {
            heartbeatButton.disabled = true;
            heartbeatButton.textContent = 'Enviando...';
        }
        
        // Construir URL da API
        var baseUrl = window.location.origin;
        var currentPath = window.location.pathname;
        var basePath = "";
        if (currentPath.indexOf("/backend/") !== -1) {
            var backendIndex = currentPath.indexOf("/backend/");
            basePath = currentPath.substring(0, backendIndex + 8);
        } else {
            var pathParts = currentPath.split("/");
            pathParts = pathParts.slice(0, pathParts.length - 1);
            basePath = pathParts.join("/");
        }
        
        var apiUrl = baseUrl + basePath + "/api/activity/heartbeat";
        
        // Fazer requisição POST usando XMLHttpRequest
        var xhr = new XMLHttpRequest();
        xhr.open("POST", apiUrl, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.setRequestHeader("Authorization", "Bearer " + tokenToUse);
        
        var startTime = new Date();
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var messageDiv = document.getElementById('heartbeat-message');
                var statusText = document.getElementById('heartbeat-status-text');
                var logContent = document.getElementById('heartbeat-log-content');
                
                if (heartbeatButton) {
                    heartbeatButton.disabled = false;
                    heartbeatButton.textContent = 'Enviar Heartbeat';
                }
                
                var endTime = new Date();
                var duration = endTime - startTime;
                
                if (xhr.status === 200) {
                    try {
                        var result = JSON.parse(xhr.responseText);
                        if (result.success) {
                            console.log("[HEARTBEAT] ✓ Heartbeat enviado com sucesso");
                            
                            if (statusText) {
                                statusText.textContent = 'Ativo - Último heartbeat: ' + endTime.toLocaleTimeString();
                                statusText.style.color = '#28a745';
                            }
                            
                            if (messageDiv) {
                                messageDiv.innerHTML = '<div class="delete-message success">✓ Heartbeat enviado com sucesso! (Tempo de resposta: ' + duration + 'ms)</div>';
                            }
                            
                            // Adicionar ao log
                            if (logContent) {
                                heartbeatLogCount++;
                                var logEntry = document.createElement('div');
                                logEntry.style.marginBottom = '5px';
                                logEntry.style.padding = '5px';
                                logEntry.style.backgroundColor = '#d4edda';
                                logEntry.style.borderLeft = '3px solid #28a745';
                                logEntry.style.borderRadius = '3px';
                                logEntry.innerHTML = '<strong>[' + endTime.toLocaleTimeString() + ']</strong> Heartbeat #' + heartbeatLogCount + ' enviado com sucesso (Resposta: ' + duration + 'ms)';
                                logContent.insertBefore(logEntry, logContent.firstChild);
                                
                                // Limitar log a 20 entradas
                                while (logContent.children.length > 20) {
                                    logContent.removeChild(logContent.lastChild);
                                }
                            }
                        } else {
                            console.error("[HEARTBEAT] ✗ Erro no heartbeat:", result.message);
                            if (statusText) {
                                statusText.textContent = 'Erro: ' + (result.message || 'Erro desconhecido');
                                statusText.style.color = '#dc3545';
                            }
                            if (messageDiv) {
                                messageDiv.innerHTML = '<div class="delete-message error">✗ Erro: ' + (result.message || 'Erro desconhecido') + '</div>';
                            }
                        }
                    } catch (e) {
                        console.error("[HEARTBEAT] ✗ Erro ao parsear resposta:", e);
                        if (statusText) {
                            statusText.textContent = 'Erro ao processar resposta';
                            statusText.style.color = '#dc3545';
                        }
                        if (messageDiv) {
                            messageDiv.innerHTML = '<div class="delete-message error">✗ Erro ao processar resposta do servidor</div>';
                        }
                    }
                } else {
                    console.error("[HEARTBEAT] ✗ Erro HTTP:", xhr.status);
                    try {
                        var errorResult = JSON.parse(xhr.responseText);
                        if (statusText) {
                            statusText.textContent = 'Erro HTTP ' + xhr.status + ': ' + (errorResult.message || 'Erro desconhecido');
                            statusText.style.color = '#dc3545';
                        }
                        if (messageDiv) {
                            messageDiv.innerHTML = '<div class="delete-message error">✗ Erro ' + xhr.status + ': ' + (errorResult.message || 'Erro desconhecido') + '</div>';
                        }
                    } catch (e) {
                        if (statusText) {
                            statusText.textContent = 'Erro HTTP ' + xhr.status;
                            statusText.style.color = '#dc3545';
                        }
                        if (messageDiv) {
                            messageDiv.innerHTML = '<div class="delete-message error">✗ Erro HTTP ' + xhr.status + '</div>';
                        }
                    }
                }
                
                // Limpar mensagem após 3 segundos
                if (messageDiv) {
                    setTimeout(function() {
                        messageDiv.innerHTML = '';
                    }, 3000);
                }
            }
        };
        
        xhr.send();
    }
    
    // Função para iniciar auto heartbeat
    function startAutoHeartbeat() {
        if (heartbeatIntervalId) {
            console.log("[HEARTBEAT] Auto heartbeat já está ativo");
            return;
        }
        
        var intervalInput = document.getElementById('heartbeat-interval');
        var interval = intervalInput ? parseInt(intervalInput.value) : 30;
        
        if (interval < 5 || interval > 300) {
            alert('Intervalo deve estar entre 5 e 300 segundos.');
            return;
        }
        
        console.log("[HEARTBEAT] Iniciando auto heartbeat com intervalo de " + interval + " segundos");
        
        // Enviar primeiro heartbeat imediatamente
        sendHeartbeat();
        
        // Configurar intervalo
        heartbeatIntervalId = setInterval(function() {
            sendHeartbeat();
        }, interval * 1000);
        
        // Atualizar botões
        var startButton = document.getElementById('btn-start-auto-heartbeat');
        var stopButton = document.getElementById('btn-stop-auto-heartbeat');
        if (startButton) {
            startButton.disabled = true;
        }
        if (stopButton) {
            stopButton.disabled = false;
        }
        
        var statusText = document.getElementById('heartbeat-status-text');
        if (statusText) {
            statusText.textContent = 'Auto heartbeat ativo (intervalo: ' + interval + 's)';
            statusText.style.color = '#007bff';
        }
    }
    
    // Função para parar auto heartbeat
    function stopAutoHeartbeat() {
        if (heartbeatIntervalId) {
            clearInterval(heartbeatIntervalId);
            heartbeatIntervalId = null;
            console.log("[HEARTBEAT] Auto heartbeat parado");
            
            // Atualizar botões
            var startButton = document.getElementById('btn-start-auto-heartbeat');
            var stopButton = document.getElementById('btn-stop-auto-heartbeat');
            if (startButton) {
                startButton.disabled = false;
            }
            if (stopButton) {
                stopButton.disabled = true;
            }
            
            var statusText = document.getElementById('heartbeat-status-text');
            if (statusText) {
                statusText.textContent = 'Auto heartbeat parado';
                statusText.style.color = '#6c757d';
            }
        }
    }
    
    // Inicializar status do token ao carregar a página
    window.addEventListener('load', function() {
        var storedToken = getStoredToken();
        if (storedToken && !apiToken) {
            apiToken = storedToken;
            console.log("[AUTH] Token restaurado do localStorage");
        }
        updateTokenStatus();
        
        // Se houver token no localStorage e não houver token SSE inicializado, 
        // permitir usar o token do localStorage para SSE
        if (storedToken && typeof sseToken === 'undefined' || sseToken === null) {
            console.log("[SSE] Token do localStorage disponível para uso em SSE");
        }
        
        // Parar auto heartbeat ao sair da página
        window.addEventListener('beforeunload', function() {
            stopAutoHeartbeat();
        });
    });
    
    // Função para atualizar lista de usuários
    function refreshUsersList() {
        // Obter token do localStorage
        var tokenToUse = apiToken || getStoredToken();
        
        if (!tokenToUse) {
            alert('Token de autenticação não disponível. Faça login primeiro.');
            return;
        }
        
        console.log("[USERS] Atualizando lista de usuários...");
        
        // Desabilitar botão durante a requisição
        var refreshButton = document.getElementById('btn-refresh-users');
        if (refreshButton) {
            refreshButton.disabled = true;
            refreshButton.textContent = 'Atualizando...';
        }
        
        // Construir URL da API
        var baseUrl = window.location.origin;
        var currentPath = window.location.pathname;
        var basePath = "";
        if (currentPath.indexOf("/backend/") !== -1) {
            var backendIndex = currentPath.indexOf("/backend/");
            basePath = currentPath.substring(0, backendIndex + 8);
        } else {
            var pathParts = currentPath.split("/");
            pathParts = pathParts.slice(0, pathParts.length - 1);
            basePath = pathParts.join("/");
        }
        
        var apiUrl = baseUrl + basePath + "/api/users";
        
        // Fazer requisição GET usando XMLHttpRequest
        var xhr = new XMLHttpRequest();
        xhr.open("GET", apiUrl, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.setRequestHeader("Authorization", "Bearer " + tokenToUse);
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var statusDiv = document.getElementById('users-status');
                var tableContainer = document.getElementById('users-table-container');
                var deleteMessageDiv = document.getElementById('delete-user-message');
                
                if (refreshButton) {
                    refreshButton.disabled = false;
                    refreshButton.textContent = 'Atualizar Lista';
                }
                
                if (xhr.status === 200) {
                    try {
                        var result = JSON.parse(xhr.responseText);
                        if (result.success && result.users) {
                            console.log("[USERS] ✓ Lista de usuários atualizada com sucesso");
                            
                            // Atualizar status
                            if (statusDiv) {
                                statusDiv.className = 'status success';
                                statusDiv.innerHTML = '<strong>Status:</strong> Sucesso - ' + result.users.length + ' usuário(s) encontrado(s)';
                            }
                            
                            // Atualizar tabela
                            if (tableContainer) {
                                // Gerar nova tabela HTML
                                var newTableHtml = generateUsersTable(result.users);
                                tableContainer.innerHTML = newTableHtml;
                            }
                            
                            // Limpar mensagem de exclusão se existir
                            if (deleteMessageDiv) {
                                deleteMessageDiv.innerHTML = '';
                            }
                        } else {
                            console.error("[USERS] ✗ Erro ao atualizar lista:", result.message);
                            if (statusDiv) {
                                statusDiv.className = 'status error';
                                statusDiv.innerHTML = '<strong>Status:</strong> Erro - ' + (result.message || 'Erro desconhecido');
                            }
                        }
                    } catch (e) {
                        console.error("[USERS] ✗ Erro ao parsear resposta:", e);
                        if (statusDiv) {
                            statusDiv.className = 'status error';
                            statusDiv.innerHTML = '<strong>Status:</strong> Erro ao processar resposta do servidor';
                        }
                    }
                } else {
                    console.error("[USERS] ✗ Erro HTTP:", xhr.status);
                    try {
                        var errorResult = JSON.parse(xhr.responseText);
                        if (statusDiv) {
                            statusDiv.className = 'status error';
                            statusDiv.innerHTML = '<strong>Status:</strong> Erro ' + xhr.status + ': ' + (errorResult.message || 'Erro desconhecido');
                        }
                    } catch (e) {
                        if (statusDiv) {
                            statusDiv.className = 'status error';
                            statusDiv.innerHTML = '<strong>Status:</strong> Erro HTTP ' + xhr.status;
                        }
                    }
                }
            }
        };
        
        xhr.send();
    }
    
    // Função auxiliar para gerar tabela de usuários
    function generateUsersTable(users) {
        if (!users || users.length === 0) {
            return '<p>Nenhum usuário encontrado</p>';
        }
        
        var html = '<table class="result-table">';
        html += '<thead><tr>';
        
        // Obter chaves do primeiro usuário
        var firstUser = users[0];
        var keys = [];
        for (var key in firstUser) {
            if (firstUser.hasOwnProperty(key)) {
                keys.push(key);
            }
        }
        
        // Adicionar coluna de ações
        keys.push('Ações');
        
        // Cabeçalho
        for (var i = 0; i < keys.length; i++) {
            html += '<th>' + escapeHtml(keys[i]) + '</th>';
        }
        html += '</tr></thead><tbody>';
        
        // Linhas
        for (var j = 0; j < users.length; j++) {
            var user = users[j];
            html += '<tr data-user-id="' + (user.id ? escapeHtml(String(user.id)) : '') + '">';
            
            for (var k = 0; k < keys.length - 1; k++) {
                var key = keys[k];
                var value = user[key];
                html += '<td>';
                if (value === null || value === undefined) {
                    html += '';
                } else if (typeof value === 'object') {
                    // Para arrays/objetos, exibir como JSON (sem <pre> para manter consistência com arrayToTable)
                    html += escapeHtml(JSON.stringify(value));
                } else {
                    html += escapeHtml(String(value));
                }
                html += '</td>';
            }
            
            // Coluna de ações
            if (user.id) {
                html += '<td>';
                // Escapar o email corretamente para uso em onclick
                var userEmail = (user.email || '').replace(/'/g, "\\'");
                html += '<button class="btn-delete-user" onclick="deleteUser(' + user.id + ', \'' + userEmail + '\')" ';
                html += 'data-user-id="' + escapeHtml(String(user.id)) + '" ';
                html += 'data-user-email="' + escapeHtml(user.email || '') + '">';
                html += 'Excluir</button>';
                html += '</td>';
            } else {
                html += '<td>-</td>';
            }
            
            html += '</tr>';
        }
        
        html += '</tbody></table>';
        return html;
    }
    
    // Função auxiliar para escapar HTML
    function escapeHtml(text) {
        if (text === null || text === undefined) {
            return '';
        }
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Função para excluir usuário via API
     */
    function deleteUser(userId, userEmail) {
        // Tentar obter token do localStorage se não estiver disponível
        var tokenToUse = apiToken || getStoredToken();
        
        if (!tokenToUse) {
            alert('Token de autenticação não disponível. Faça login primeiro usando o formulário acima.');
            return;
        }
        
        if (!confirm('Tem certeza que deseja excluir o usuário "' + userEmail + '" (ID: ' + userId + ')?')) {
            return;
        }
        
        // Desabilitar botão durante a requisição
        var button = document.querySelector('button[data-user-id="' + userId + '"]');
        if (button) {
            button.disabled = true;
            button.textContent = 'Excluindo...';
        }
        
        // Construir URL da API
        var baseUrl = window.location.origin;
        var currentPath = window.location.pathname;
        var basePath = "";
        if (currentPath.indexOf("/backend/") !== -1) {
            var backendIndex = currentPath.indexOf("/backend/");
            basePath = currentPath.substring(0, backendIndex + 8);
        } else {
            var pathParts = currentPath.split("/");
            pathParts = pathParts.slice(0, pathParts.length - 1);
            basePath = pathParts.join("/");
        }
        
        var apiUrl = baseUrl + basePath + "/api/users/" + userId;
        console.log("[DELETE] Excluindo usuário:", userId);
        console.log("[DELETE] URL:", apiUrl);
        console.log("[DELETE] Usando token:", tokenToUse ? tokenToUse.substring(0, 20) + "..." : "nenhum");
        
        // Fazer requisição DELETE usando XMLHttpRequest
        var xhr = new XMLHttpRequest();
        xhr.open("DELETE", apiUrl, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.setRequestHeader("Authorization", "Bearer " + tokenToUse);
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var messageDiv = document.getElementById("delete-user-message");
                
                if (xhr.status === 200) {
                    try {
                        var result = JSON.parse(xhr.responseText);
                        if (result.success) {
                            console.log("[DELETE] ✓ Usuário excluído com sucesso");
                            if (messageDiv) {
                                messageDiv.innerHTML = '<div class="delete-message success">✓ Usuário "' + userEmail + '" excluído com sucesso!</div>';
                            }
                            // Remover linha da tabela
                            var row = document.querySelector('tr[data-user-id="' + userId + '"]');
                            if (row) {
                                // Obter referências antes de remover o elemento
                                var exampleSection = row.closest('.example-section');
                                var tbody = row.closest('tbody');
                                
                                row.style.opacity = "0.5";
                                setTimeout(function() {
                                    row.remove();
                                    // Atualizar contador se existir
                                    if (exampleSection && tbody) {
                                        var statusDiv = exampleSection.querySelector('.status');
                                        if (statusDiv) {
                                            var remainingRows = tbody.querySelectorAll('tr').length;
                                            statusDiv.innerHTML = '<strong>Status:</strong> Sucesso - ' + remainingRows + ' usuário(s) encontrado(s)';
                                        }
                                    }
                                }, 500);
                            }
                        } else {
                            console.error("[DELETE] ✗ Erro ao excluir usuário:", result.message);
                            if (messageDiv) {
                                messageDiv.innerHTML = '<div class="delete-message error">✗ Erro ao excluir usuário: ' + (result.message || 'Erro desconhecido') + '</div>';
                            }
                            if (button) {
                                button.disabled = false;
                                button.textContent = 'Excluir';
                            }
                        }
                    } catch (e) {
                        console.error("[DELETE] ✗ Erro ao parsear resposta:", e);
                        if (messageDiv) {
                            messageDiv.innerHTML = '<div class="delete-message error">✗ Erro ao processar resposta do servidor</div>';
                        }
                        if (button) {
                            button.disabled = false;
                            button.textContent = 'Excluir';
                        }
                    }
                } else {
                    console.error("[DELETE] ✗ Erro HTTP:", xhr.status);
                    try {
                        var errorResult = JSON.parse(xhr.responseText);
                        if (messageDiv) {
                            messageDiv.innerHTML = '<div class="delete-message error">✗ Erro ' + xhr.status + ': ' + (errorResult.message || 'Erro desconhecido') + '</div>';
                        }
                    } catch (e) {
                        if (messageDiv) {
                            messageDiv.innerHTML = '<div class="delete-message error">✗ Erro HTTP ' + xhr.status + '</div>';
                        }
                    }
                    if (button) {
                        button.disabled = false;
                        button.textContent = 'Excluir';
                    }
                }
                
                // Limpar mensagem após 5 segundos
                if (messageDiv) {
                    setTimeout(function() {
                        messageDiv.innerHTML = '';
                    }, 5000);
                }
            }
        };
        
        xhr.send();
    }
    </script>
    <?php
    // Inicializar variáveis JavaScript após o carregamento do arquivo JS
    if (isset($sseTestToken) && isset($sseTestUserId) && isset($sseTestSessionId) && $sseTestToken && $sseTestUserId) {
        if (isset($sseTokenJs) && isset($sseUserIdJs) && isset($sseSessionIdJs)) {
            echo '<script type="text/javascript">';
            echo 'if (typeof initSSE === "function") {';
            echo '    initSSE(' . $sseTokenJs . ', ' . $sseUserIdJs . ', ' . $sseSessionIdJs . ');';
            echo '} else {';
            echo '    window.addEventListener("load", function() {';
            echo '        if (typeof initSSE === "function") {';
            echo '            initSSE(' . $sseTokenJs . ', ' . $sseUserIdJs . ', ' . $sseSessionIdJs . ');';
            echo '        }';
            echo '    });';
            echo '}';
            echo '</script>';
        }
    }
    ?>
</body>
</html>
