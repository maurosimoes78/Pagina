<?php
/**
 * Arquivo de configuração do sistema
 */

return array(
    'database' => array(
        'host' => 'akani_system.mysql.dbaas.com.br',
        'dbname' => 'akani_system',
        'username' => 'akani_system',
        'password' => 'Beatriz@221109',
        'charset' => 'utf8mb4'
    ),
    'session' => array(
        'lifetime' => 3600, // 1 hora em segundos
        'name' => 'AKANI_SESSION',
        'cookie_lifetime' => 0,
        'cookie_httponly' => true,
        'cookie_secure' => false // Mude para true em produção com HTTPS
    ),
    'sse' => array(
        'heartbeat_interval' => 30, // Intervalo de heartbeat em segundos
        'inactivity_timeout' => 120, // Timeout de inatividade em segundos (2 minutos)
        'max_connections_per_user' => 5 // Máximo de conexões SSE simultâneas por usuário
    ),
    'jwt' => array(
        'secret' => 'your-secret-key-change-in-production', // Mude em produção!
        'algorithm' => 'HS256',
        'expiration' => 3600 // 1 hora
    ),
    'cors' => array(
        'allowed_origins' => array('http://localhost:4200', 'http://localhost', 'http://www.s3smart.com.br'),
        'allowed_methods' => array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'),
        'allowed_headers' => array('Content-Type', 'Authorization', 'X-Requested-With')
    )
);

