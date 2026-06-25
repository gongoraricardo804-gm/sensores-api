<?php

require_once dirname(__DIR__) . '/config/Database.php';

class LecturaModel
{
    private PDO $conexion;

    public function __construct()
    {
        $database = new Database();
        $this->conexion = $database->connect();
    }

    public function actualizarLectura(
        float $temperatura,
        float $humedad,
        float $lluvia
    ): bool {
        $sql = "INSERT INTO lecturas
                    (id, temperatura, humedad, lluvia)
                VALUES
                    (1, :temperatura, :humedad, :lluvia)
                ON DUPLICATE KEY UPDATE
                    temperatura = VALUES(temperatura),
                    humedad = VALUES(humedad),
                    lluvia = VALUES(lluvia),
                    fecha = CURRENT_TIMESTAMP";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':temperatura' => $temperatura,
            ':humedad' => $humedad,
            ':lluvia' => $lluvia
        ]);
    }

    public function obtenerUltimaLectura(): array|false
    {
        $sql = "SELECT
                    id,
                    temperatura,
                    humedad,
                    lluvia,
                    fecha
                FROM lecturas
                WHERE id = 1
                LIMIT 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetch(PDO::FETCH_ASSOC);
    }
}