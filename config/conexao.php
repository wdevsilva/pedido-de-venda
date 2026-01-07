<?php

require __DIR__ . '/../vendor/autoload.php';

use \App\Environment;

//CARREGA VARIÁVEIS DE AMBIENTE
Environment::load(__DIR__ . '/../');

define("HOST", getenv('DB_HOST'));
define("PORT", getenv('PORT'));
define("INSTANCIA", getenv('INSTANCIA'));
define("USER", getenv('DB_USERNAME'));
define("PASS", getenv('DB_PASSWORD'));
define("DBNAME", getenv('DB_DATABASE'));

function conectar()
{
    try {
        $servidor  = HOST;
        $instancia = INSTANCIA;
        $porta     = PORT;
        $database  = DBNAME;
        $usuario   = USER;
        $senha     = PASS;

        $dsn = "sqlsrv:Server={$servidor}\\{$instancia},{$porta};Database={$database};Encrypt=yes;TrustServerCertificate=true";

        $conexao = new PDO(
            $dsn,
            $usuario,
            $senha,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        );
    } catch (PDOException $e) {
        echo "Drivers disponíveis: " . implode(", ", PDO::getAvailableDrivers()) . PHP_EOL;
        echo "Erro: " . $e->getMessage();
        exit;
    }

    return $conexao;
}