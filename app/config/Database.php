<?php

class Database
{
    private $pdo;

    private function env($key)
    {
        return getenv($key) ?: ($_ENV[$key] ?? $_SERVER[$key] ?? null);
    }

    public function connect()
    {
        try {
            $host = $this->env("MYSQLHOST");
            $port = $this->env("MYSQLPORT");
            $user = $this->env("MYSQLUSER");
            $pass = $this->env("MYSQLPASSWORD");
            $db   = $this->env("MYSQLDATABASE");

            if (!$host || !$port || !$user || !$pass || !$db) {
                http_response_code(500);

                echo json_encode([
                    "status" => false,
                    "respuesta" => 0,
                    "mensaje" => "Variables de entorno incompletas",
                    "debug" => [
                        "MYSQLHOST" => $host ?: "NO DEFINIDO",
                        "MYSQLPORT" => $port ?: "NO DEFINIDO",
                        "MYSQLUSER" => $user ?: "NO DEFINIDO",
                        "MYSQLDATABASE" => $db ?: "NO DEFINIDO",
                        "MYSQLPASSWORD" => $pass ? "DEFINIDO" : "NO DEFINIDO"
                    ]
                ]);

                exit;
            }

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

            $this->pdo = new PDO(
                $dsn,
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );

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