API RU — Consulta de Saldo

API desenvolvida em PHP para consulta de informações de alunos, saldo do RU, QR Code e foto do aluno.

Tecnologias utilizadas

PHP 8.2+

MySQL

PDO

XAMPP

Postman

Git/GitHub

Estrutura do projeto

banco_ru/
├── public/
│   └── index.php
├── .env
├── .env.example
├── .gitignore
├── config.php
├── schema.sql
├── seed.sql
└── README.md

Configuração do banco de dados

Abra o XAMPP Control Panel e inicie o MySQL.

Acesse o phpMyAdmin.

Crie o banco de dados utilizando o arquivo schema.sql.

Execute o arquivo seed.sql para inserir os dados de teste.

Configuração do ambiente

Crie um arquivo .env na raiz do projeto com as configurações do banco de dados.

Utilize o arquivo .env.example como modelo.

Exemplo:

DB_HOST=localhost
DB_USER=seu_usuario
DB_PASS=sua_senha
DB_NAME=ru_teste
API_KEY=sua_chave

Importante: o arquivo .env contém informações sensíveis e não deve ser enviado para o GitHub.

Executando a API

Abra o XAMPP Control Panel.

Inicie o Apache e o MySQL.

Verifique se a pasta banco_ru está dentro de C:\xampp\htdocs.

Acesse a API pelo endereço:

http://localhost/banco_ru/public/index.php

A API utiliza o Apache do XAMPP para receber as requisições HTTP.

Endpoint

POST /saldo

Endpoint utilizado para consultar os dados do aluno.

O endereço e a rota final devem corresponder à configuração utilizada pela API no Apache.

Cabeçalhos

Content-Type: application/json

X-API-Key: sua_chave

A chave deve ser a mesma configurada no arquivo .env.

Corpo da requisição

Exemplo em JSON:

{
  "matricula": "20251200022",
  "data": "2026-09-22T00:00:00"
}

A matrícula deve possuir exatamente 11 dígitos. O campo data deve seguir o formato AAAA-MM-DDTHH:MM:SS.

Resposta

A resposta pode conter a matrícula, o nome do aluno, o saldo, a data de atualização do saldo, o QR Code e a foto.

Os campos de foto e QR Code podem retornar null quando não estiverem disponíveis, conforme a regra implementada na API.

Códigos HTTP

200: consulta realizada com sucesso.

400: requisição inválida ou dados em formato incorreto.

401: chave de API inválida.

404: matrícula não encontrada.

405: método HTTP não permitido.

500: erro interno do servidor.

Segurança

A chave de API é configurada por meio do arquivo .env.

O arquivo .env não deve ser enviado ao GitHub.

As consultas ao banco utilizam PDO e instruções preparadas para reduzir o risco de SQL Injection.