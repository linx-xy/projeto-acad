CREATE DATABASE IF NOT EXISTS acad_db
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE acad_db;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS treinos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    grupo_muscular VARCHAR(50) NOT NULL,
    exercicio VARCHAR(100) NOT NULL,
    series INT NOT NULL,
    repeticoes INT NOT NULL,
    carga_kg DECIMAL(5,2) DEFAULT 0.00,
    data_treino DATE NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_treinos_usuarios 
        FOREIGN KEY (usuario_id) 
        REFERENCES usuarios(id) 
        ON DELETE CASCADE
) ENGINE=InnoDB;