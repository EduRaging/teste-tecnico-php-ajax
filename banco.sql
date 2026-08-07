CREATE DATABASE IF NOT EXISTS teste_tecnico
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE teste_tecnico;

CREATE TABLE IF NOT EXISTS usuarios (
    codigo INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nome VARCHAR(150) NOT NULL,
    perfil ENUM('ADM', 'USER') NOT NULL DEFAULT 'USER',
    PRIMARY KEY (codigo),
    UNIQUE KEY uk_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Credencial de teste: admin@teste.com / Admin@123
-- O valor abaixo é um hash bcrypt, e não a senha em texto puro.
INSERT IGNORE INTO usuarios (email, senha, nome, perfil)
VALUES (
    'admin@teste.com',
    '$2b$12$WoHk3xdtMF.LeQQT7urpZOusCOOB1u3xuDP9r.TC82tco/FJZnEnG',
    'Administrador',
    'ADM'
);

