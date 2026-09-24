CREATE DATABASE ru_teste;

CREATE TABLE ru_teste.alunos (
    matricula INT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    foto VARCHAR(255),
    foto_atualizada_em DATETIME
);

CREATE TABLE ru_teste.saldos (
    matricula INT PRIMARY KEY,
    saldo DECIMAL(10,2) NOT NULL,
    atualizado_em DATETIME NOT NULL,

    FOREIGN KEY (matricula)
        REFERENCES ru_teste.alunos(matricula)
);

CREATE TABLE ru_teste.qrcodes (
    matricula INT PRIMARY KEY,
    codigo VARCHAR(255) NOT NULL,
    gerado_em DATETIME NOT NULL,

    FOREIGN KEY (matricula)
        REFERENCES ru_teste.alunos(matricula)
);