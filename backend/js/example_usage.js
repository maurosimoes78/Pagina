/**
 * JavaScript para example_usage.php
 * Compatível com navegadores antigos (IE8+, navegadores modernos)
 */

// Variáveis globais
var sseEventSource = null;
var sseToken = null;
var sseUserId = null;
var sseSessionId = null;
var eventCount = 0;

/**
 * Inicializa variáveis SSE
 */
function initSSE(token, userId, sessionId) {
    console.log("[SSE] Inicializando variáveis SSE...");
    sseToken = token;
    sseUserId = userId;
    sseSessionId = sessionId;
    eventCount = 0;
    console.log("[SSE] ✓ Variáveis inicializadas:");
    console.log("[SSE]   - Token:", token ? "Disponível (" + token.substring(0, 20) + "...)" : "Não disponível");
    console.log("[SSE]   - User ID:", userId);
    console.log("[SSE]   - Session ID:", sessionId ? sessionId.substring(0, 20) + "..." : "Não disponível");
}

/**
 * Atualiza status da conexão SSE
 */
function updateStatus(status, className) {
    var statusEl = document.getElementById("sse-status");
    if (statusEl) {
        statusEl.textContent = status;
        statusEl.className = "sse-status " + className;
    }
}

/**
 * Adiciona evento à lista de eventos recebidos
 */
function addEvent(type, data) {
    eventCount++;
    var eventsEl = document.getElementById("sse-events");
    if (!eventsEl) {
        return;
    }
    
    if (eventCount === 1) {
        eventsEl.innerHTML = "";
    }
    
    var eventEl = document.createElement("div");
    eventEl.className = "sse-event";
    var now = new Date();
    var timeStr = now.toLocaleTimeString();
    
    var dataStr = "";
    try {
        if (typeof JSON !== "undefined" && JSON.stringify) {
            dataStr = JSON.stringify(data);
        } else {
            dataStr = String(data);
        }
    } catch (e) {
        dataStr = String(data);
    }
    
    var htmlContent = "<strong>Tipo:</strong> " + type + "<br>";
    htmlContent += "<strong>Dados:</strong> " + dataStr;
    htmlContent += "<div class=\"sse-event-time\">Recebido em: " + timeStr + "</div>";
    eventEl.innerHTML = htmlContent;
    eventsEl.insertBefore(eventEl, eventsEl.firstChild);
    eventsEl.scrollTop = 0;
}

/**
 * Conecta ao servidor SSE
 */
function connectSSE() {
    if (sseEventSource) {
        disconnectSSE();
    }
    
    var token = null;
    // Tentar obter token do localStorage se não estiver disponível
    try {
        var storedToken = localStorage.getItem('api_token');
        if (storedToken) {
            console.log("[SSE] Token obtido do localStorage");
            token = storedToken;
        }
    } catch (e) {
        console.warn("[SSE] Erro ao acessar localStorage:", e);
    }
    
    if (!token) {
        console.error("[SSE] Token não disponível. Execute o login primeiro.");
        alert("Token não disponível. Execute o login primeiro usando o formulário de login.");
        return;
    }

    initSSE (token, sseUserId, sseSessionId);

    console.log("[SSE] Iniciando conexão SSE...");
    updateStatus("Conectando...", "connecting");
    var btnConnect = document.getElementById("btn-connect");
    if (btnConnect) {
        btnConnect.disabled = true;
    }
    
    // Construir URL do endpoint SSE
    var baseUrl = window.location.origin;
    var currentPath = window.location.pathname;
    
    // Determinar o caminho base do backend
    // Se estiver em /backend/example_usage.php, o API está em /backend/api/
    var basePath = "";
    if (currentPath.indexOf("/backend/") !== -1) {
        // Extrair o caminho até /backend
        var backendIndex = currentPath.indexOf("/backend/");
        basePath = currentPath.substring(0, backendIndex + 8); // +8 para incluir "/backend"
    } else {
        // Fallback: tentar construir baseado no caminho atual
        var pathParts = currentPath.split("/");
        pathParts = pathParts.slice(0, pathParts.length - 1);
        basePath = pathParts.join("/");
    }
    
    var sseUrl = baseUrl + basePath + "/api/sse?token=" + encodeURIComponent(sseToken);
    console.log("[SSE] URL da conexão:", sseUrl);
    console.log("[SSE] Token disponível:", sseToken ? "Sim" : "Não");
    
    try {
        console.log("[SSE] Criando EventSource...");
        sseEventSource = new EventSource(sseUrl);
        
        sseEventSource.onopen = function(event) {
            console.log("[SSE] ✓ Conexão estabelecida com sucesso!");
            console.log("[SSE] Status da conexão: CONECTADO");
            console.log("[SSE] EventSource readyState:", sseEventSource.readyState);
            updateStatus("Conectado", "connected");
            var btnDisconnect = document.getElementById("btn-disconnect");
            var btnSend = document.getElementById("btn-send-event");
            if (btnDisconnect) {
                btnDisconnect.disabled = false;
            }
            if (btnSend) {
                btnSend.disabled = false;
            }
            addEvent("connection", { message: "Conexão SSE estabelecida com sucesso" });
        };
        
        sseEventSource.onerror = function(event) {
            console.error("[SSE] ✗ Erro na conexão SSE");
            console.error("[SSE] EventSource readyState:", sseEventSource ? sseEventSource.readyState : "null");
            console.error("[SSE] Detalhes do erro:", event);
            
            // Verificar o estado da conexão
            if (sseEventSource) {
                var readyState = sseEventSource.readyState;
                if (readyState === EventSource.CONNECTING) {
                    console.warn("[SSE] Status: CONECTANDO (ainda tentando conectar)");
                } else if (readyState === EventSource.CLOSED) {
                    console.error("[SSE] Status: FECHADO (conexão encerrada)");
                }
            }
            
            updateStatus("Erro na conexão", "disconnected");
            var btnConnect = document.getElementById("btn-connect");
            var btnDisconnect = document.getElementById("btn-disconnect");
            var btnSend = document.getElementById("btn-send-event");
            if (btnConnect) {
                btnConnect.disabled = false;
            }
            if (btnDisconnect) {
                btnDisconnect.disabled = true;
            }
            if (btnSend) {
                btnSend.disabled = true;
            }
            if (sseEventSource) {
                sseEventSource.close();
                sseEventSource = null;
                console.log("[SSE] Conexão fechada devido ao erro");
            }
        };
        
        // Ouvir eventos genéricos
        sseEventSource.onmessage = function(event) {
            console.log("[SSE] Mensagem recebida:", event.data);
            try {
                var data = JSON.parse(event.data);
                console.log("[SSE] Dados parseados:", data);
                addEvent("message", data);
            } catch (e) {
                console.warn("[SSE] Erro ao parsear mensagem:", e);
                addEvent("message", { raw: event.data });
            }
        };
        
        // Ouvir eventos específicos
        sseEventSource.addEventListener("notification", function(event) {
            console.log("[SSE] Evento 'notification' recebido:", event.data);
            try {
                var data = JSON.parse(event.data);
                addEvent("notification", data);
            } catch (e) {
                console.warn("[SSE] Erro ao parsear evento 'notification':", e);
                addEvent("notification", { raw: event.data });
            }
        });
        
        sseEventSource.addEventListener("update", function(event) {
            console.log("[SSE] Evento 'update' recebido:", event.data);
            try {
                var data = JSON.parse(event.data);
                addEvent("update", data);
            } catch (e) {
                console.warn("[SSE] Erro ao parsear evento 'update':", e);
                addEvent("update", { raw: event.data });
            }
        });
        
        sseEventSource.addEventListener("broadcast", function(event) {
            console.log("[SSE] Evento 'broadcast' recebido:", event.data);
            try {
                var data = JSON.parse(event.data);
                addEvent("broadcast", data);
            } catch (e) {
                console.warn("[SSE] Erro ao parsear evento 'broadcast':", e);
                addEvent("broadcast", { raw: event.data });
            }
        });
        
        sseEventSource.addEventListener("error", function(event) {
            console.error("[SSE] Evento 'error' recebido:", event.data);
            try {
                var data = JSON.parse(event.data);
                addEvent("error", data);
            } catch (e) {
                console.warn("[SSE] Erro ao parsear evento 'error':", e);
                addEvent("error", { raw: event.data });
            }
        });
        
        sseEventSource.addEventListener("connection_timeout", function(event) {
            console.warn("[SSE] ⚠ Timeout de conexão detectado");
            console.warn("[SSE] Dados do timeout:", event.data);
            try {
                var data = JSON.parse(event.data);
                addEvent("connection_timeout", data);
            } catch (e) {
                console.warn("[SSE] Erro ao parsear evento 'connection_timeout':", e);
                addEvent("connection_timeout", { raw: event.data });
            }
            disconnectSSE();
        });
        
    } catch (e) {
        console.error("[SSE] ✗ Exceção ao criar EventSource:", e);
        console.error("[SSE] Mensagem de erro:", e.message);
        console.error("[SSE] Stack trace:", e.stack);
        updateStatus("Erro: " + e.message, "disconnected");
        var btnConnect = document.getElementById("btn-connect");
        if (btnConnect) {
            btnConnect.disabled = false;
        }
    }
}

/**
 * Desconecta do servidor SSE
 */
function disconnectSSE() {
    if (sseEventSource) {
        console.log("[SSE] Desconectando do servidor SSE...");
        sseEventSource.close();
        sseEventSource = null;
        console.log("[SSE] ✓ Desconectado com sucesso");
        updateStatus("Desconectado", "disconnected");
        var btnConnect = document.getElementById("btn-connect");
        var btnDisconnect = document.getElementById("btn-disconnect");
        var btnSend = document.getElementById("btn-send-event");
        if (btnConnect) {
            btnConnect.disabled = false;
        }
        if (btnDisconnect) {
            btnDisconnect.disabled = true;
        }
        if (btnSend) {
            btnSend.disabled = true;
        }
    } else {
        console.log("[SSE] Nenhuma conexão ativa para desconectar");
    }
}

/**
 * Envia evento de teste
 */
function sendTestEvent() {
    if (!sseUserId || !sseSessionId || !sseToken) {
        console.error("[SSE] Dados de usuário ou token não disponíveis para enviar evento de teste");
        alert("Dados de usuário ou token não disponíveis.");
        return;
    }
    
    console.log("[SSE] Enviando evento de teste...");
    
    // Enviar evento via PHP usando XMLHttpRequest (compatível com navegadores antigos)
    var baseUrl = window.location.origin;
    var currentPath = window.location.pathname;
    var pathParts = currentPath.split("/");
    pathParts = pathParts.slice(0, pathParts.length - 1);
    var basePath = pathParts.join("/");
    var apiUrl = baseUrl + basePath + "/api/test-send-event.php";
    
    console.log("[SSE] URL da API:", apiUrl);
    
    var xhr = new XMLHttpRequest();
    xhr.open("POST", apiUrl, true);
    xhr.setRequestHeader("Content-Type", "application/json");
    
    var eventData = {
        userId: sseUserId,
        sessionId: sseSessionId,
        eventType: "notification",
        data: {
            message: "Evento de teste enviado em " + new Date().toLocaleTimeString(),
            type: "test",
            timestamp: new Date().toISOString()
        }
    };
    
    console.log("[SSE] Dados do evento:", eventData);
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    var result = JSON.parse(xhr.responseText);
                    if (result.success) {
                        console.log("[SSE] ✓ Evento de teste enviado com sucesso");
                        console.log("[SSE] Resposta do servidor:", result);
                    } else {
                        console.error("[SSE] ✗ Erro ao enviar evento:", result.message);
                    }
                } catch (e) {
                    console.error("[SSE] ✗ Erro ao parsear resposta:", e);
                }
            } else {
                console.error("[SSE] ✗ Erro HTTP ao enviar evento:", xhr.status);
                console.error("[SSE] Resposta do servidor:", xhr.responseText);
            }
        }
    };
    
    xhr.send(JSON.stringify(eventData));
}

