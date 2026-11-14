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

INSERT IGNORE INTO usuarios (nome, email, senha) VALUES
('Usuário Teste', 'teste@acad.com', '$2y$10$Xasfy8K1LXoJa5VZuMklX.yKFMompVV6Onxwl.x7nkqfdN4M5JK1i'),
('Maria Souza', 'maria@acad.com', '$2y$10$6RyabvUofC7ZkCbNf6lnCOaePEgMk5uhuRmJXrK/9upeLhzKXYnJm');

INSERT INTO treinos (usuario_id, grupo_muscular, exercicio, series, repeticoes, carga_kg, data_treino) VALUES
((SELECT id FROM usuarios WHERE email = 'teste@acad.com'), 'Peito', 'Supino reto', 4, 12, 40.00, '2026-11-02'),
((SELECT id FROM usuarios WHERE email = 'teste@acad.com'), 'Costas', 'Puxada frontal', 4, 10, 45.00, '2026-11-04'),
((SELECT id FROM usuarios WHERE email = 'teste@acad.com'), 'Pernas', 'Agachamento livre', 4, 10, 70.00, '2026-11-06'),
((SELECT id FROM usuarios WHERE email = 'teste@acad.com'), 'Peito', 'Supino reto', 4, 10, 42.50, '2026-11-09'),
((SELECT id FROM usuarios WHERE email = 'teste@acad.com'), 'Costas', 'Remada curvada', 3, 12, 35.00, '2026-11-11'),
((SELECT id FROM usuarios WHERE email = 'maria@acad.com'), 'Ombros', 'Desenvolvimento', 3, 12, 20.00, '2026-11-03'),
((SELECT id FROM usuarios WHERE email = 'maria@acad.com'), 'Pernas', 'Leg press', 4, 12, 120.00, '2026-11-05'),
((SELECT id FROM usuarios WHERE email = 'maria@acad.com'), 'Bíceps', 'Rosca direta', 3, 12, 15.00, '2026-11-10');
