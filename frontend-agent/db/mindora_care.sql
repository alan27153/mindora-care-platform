-- Archivo: mindora_care.sql

-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS `mindora_care` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos
USE `mindora_care`;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS `users` (
    `id` VARCHAR(36) PRIMARY KEY NOT NULL COMMENT 'UUID del usuario',
    `name` VARCHAR(255) NOT NULL COMMENT 'Nombre completo del usuario',
    `email` VARCHAR(255) UNIQUE NOT NULL COMMENT 'Correo electrónico único del usuario',
    `password_hash` VARCHAR(255) NOT NULL COMMENT 'Hash de la contraseña del usuario',
    `role` ENUM('admin', 'patient', 'psychologist') NOT NULL COMMENT 'Rol del usuario',
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Estado activo/inactivo del usuario',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de creación del registro',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha y hora de la última actualización del registro'
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tabla de perfiles de psicólogos
CREATE TABLE IF NOT EXISTS `psychologist_profiles` (
    `user_id` VARCHAR(36) PRIMARY KEY NOT NULL COMMENT 'UUID del usuario psicólogo',
    `specialization` VARCHAR(255) COMMENT 'Especialización del psicólogo',
    `license_number` VARCHAR(255) UNIQUE COMMENT 'Número de licencia profesional',
    `bio` TEXT COMMENT 'Biografía o descripción profesional',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tabla de asignaciones paciente-psicólogo
CREATE TABLE IF NOT EXISTS `patient_psychologist` (
    `patient_id` VARCHAR(36) NOT NULL COMMENT 'UUID del paciente',
    `psychologist_id` VARCHAR(36) NOT NULL COMMENT 'UUID del psicólogo asignado',
    `assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de asignación',
    PRIMARY KEY (`patient_id`, `psychologist_id`),
    FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`psychologist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tabla de chats
CREATE TABLE IF NOT EXISTS `chats` (
    `id` VARCHAR(36) PRIMARY KEY NOT NULL COMMENT 'UUID del chat',
    `patient_id` VARCHAR(36) NOT NULL COMMENT 'UUID del paciente propietario del chat',
    `psychologist_id` VARCHAR(36) COMMENT 'UUID del psicólogo, si aplica',
    `type` ENUM('ai_client', 'ai_patient', 'psychologist') NOT NULL COMMENT 'Tipo de chat',
    `status` ENUM('open', 'closed') NOT NULL DEFAULT 'open' COMMENT 'Estado del chat',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`psychologist_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tabla de mensajes de chat
CREATE TABLE IF NOT EXISTS `chat_messages` (
    `id` VARCHAR(36) PRIMARY KEY NOT NULL COMMENT 'UUID del mensaje',
    `chat_id` VARCHAR(36) NOT NULL COMMENT 'UUID del chat al que pertenece el mensaje',
    `sender_id` VARCHAR(36) NOT NULL COMMENT 'UUID del remitente',
    `content` TEXT NOT NULL COMMENT 'Contenido del mensaje',
    `sent_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`chat_id`) REFERENCES `chats`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tabla de citas
CREATE TABLE IF NOT EXISTS `appointments` (
    `id` VARCHAR(36) PRIMARY KEY NOT NULL COMMENT 'UUID de la cita',
    `patient_id` VARCHAR(36) NOT NULL COMMENT 'UUID del paciente que solicita la cita',
    `psychologist_id` VARCHAR(36) NOT NULL COMMENT 'UUID del psicólogo con quien se solicita la cita',
    `requested_date` DATE NOT NULL COMMENT 'Fecha solicitada para la cita',
    `requested_time` TIME NOT NULL COMMENT 'Hora solicitada para la cita',
    `status` ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending' COMMENT 'Estado de la cita',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`psychologist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
