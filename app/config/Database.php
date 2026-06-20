<?php

class Database
{
    private $pdo;

    public function connect()
    {
        try {
            $host = getenv("thomas.proxy.rlwy.net");
            $port = getenv("48822");
            $user = getenv("root");
            $pass = getenv("WXrWLifHTTmPJltypJrAeCdFlzzTsmrK");
            $db   = getenv("sensores");

            $this->pdo = new PDO(
                "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
                $user,
                $pass
            );

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $this->pdo;

        } catch (PDOException $e) {
            http_response_code(500);

            echo json_encode([
                "status" => false,
                "respuesta" => 0,
                "mensaje" => "Error de conexión",
                "error" => $e->getMessage()
            ]);

            exit;
        }
    }
}