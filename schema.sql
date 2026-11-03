CREATE DATABASE IF NOT EXISTS ru_teste
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE ru_teste;

CREATE TABLE alunos (
    matricula VARCHAR(11) PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    foto VARCHAR(255),
    foto_atualizada_em DATETIME
);

CREATE TABLE saldos (
    matricula VARCHAR(11) PRIMARY KEY,
    saldo DECIMAL(10,2) NOT NULL,
    atualizado_em DATETIME NOT NULL,

    FOREIGN KEY (matricula)
        REFERENCES alunos(matricula)
);

CREATE TABLE qrcodes (
    matricula VARCHAR(11) PRIMARY KEY,
    codigo VARCHAR(255) NOT NULL,
    gerado_em DATETIME NOT NULL,

    FOREIGN KEY (matricula)
        REFERENCES alunos(matricula)
);