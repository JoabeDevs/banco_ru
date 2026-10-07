\# API RU — Consulta de Saldo



API desenvolvida em PHP para consulta de informações de alunos, saldo do RU, QR Code e foto do aluno.



\## Tecnologias utilizadas



\* PHP 8.2+

\* MySQL

\* PDO

\* XAMPP

\* Postman

\* Git/GitHub



\## Estrutura do projeto



```text

banco\_ru/

├── public/

│   └── index.php

├── .env

├── .env.example

├── .gitignore

├── config.php

├── schema.sql

├── seed.sql

└── README.md

```



\## Configuração do banco de dados



1\. Abra o XAMPP e inicie o MySQL.

2\. Acesse o phpMyAdmin.

3\. Crie o banco de dados utilizando o arquivo `schema.sql`.

4\. Execute o arquivo `seed.sql` para inserir os dados de teste.



\## Configuração do ambiente



Crie um arquivo `.env` na raiz do projeto com as configurações do banco de dados.



Utilize o arquivo `.env.example` como modelo.



Exemplo:



```env

DB\_HOST=localhost

DB\_USER=seu\_usuario

DB\_PASS=sua\_senha

DB\_NAME=ru\_teste

API\_KEY=sua\_chave

```



\*\*Importante:\*\* o arquivo `.env` contém informações sensíveis e não deve ser enviado para o GitHub.



\## Executando a API



Abra o PowerShell na pasta do projeto:



```powershell

cd banco\_ru

```



Execute o servidor PHP:



```powershell

php -S localhost:8000 -t public

```



A API ficará disponível em:



```text

http://localhost:8000

```



\## Endpoint



\### POST `/saldo`



Endpoint utilizado para consultar os dados do aluno.



Exemplo:



```text

POST http://localhost:8000/saldo

```



A req



