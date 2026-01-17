-- Estrutura do banco de dados para o sistema Akani

CREATE DATABASE IF NOT EXISTS `akani_system` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `akani_system`;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `role` VARCHAR(50) DEFAULT 'user',
    `cpf` VARCHAR(20) DEFAULT NULL,
    `telefone` VARCHAR(20) DEFAULT NULL,
    `empresa` VARCHAR(255) DEFAULT NULL,
    `endereco` VARCHAR(255) DEFAULT NULL,
    `bairro` VARCHAR(100) DEFAULT NULL,
    `cidade` VARCHAR(100) DEFAULT NULL,
    `estado` VARCHAR(50) DEFAULT NULL,
    `pais` VARCHAR(100) DEFAULT NULL,
    `telefone_comercial` VARCHAR(20) DEFAULT NULL,
    `cnpj` VARCHAR(20) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de sessões
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` VARCHAR(64) NOT NULL,
    `user_id` INT(11) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `last_activity` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_token` (`token`),
    INDEX `idx_last_activity` (`last_activity`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de eventos SSE
CREATE TABLE IF NOT EXISTS `sse_events` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `event_type` VARCHAR(100) NOT NULL,
    `target_type` ENUM('user', 'session', 'all') NOT NULL,
    `target_id` VARCHAR(255) DEFAULT NULL, -- user_id ou session_id ou NULL para 'all'
    `data` TEXT NOT NULL, -- JSON
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `delivered` BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (`id`),
    INDEX `idx_target` (`target_type`, `target_id`),
    INDEX `idx_delivered` (`delivered`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de atividade do usuário (heartbeat)
CREATE TABLE IF NOT EXISTS `user_activity` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `session_id` VARCHAR(64) NOT NULL,
    `last_heartbeat` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `is_active` BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_session` (`user_id`, `session_id`),
    INDEX `idx_last_heartbeat` (`last_heartbeat`),
    INDEX `idx_is_active` (`is_active`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

