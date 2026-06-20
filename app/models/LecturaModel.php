<?php

require_once __DIR__ . "/../config/Database.php";

class LecturaModel
{
    private $conexion;

    public function __construct()
    {
        $db = new Database();
        $this->conexion = $db->connect();
    }

    public function actualizarLectura($temperatura, $humedad, $distancia)
    {
        $sql = "INSERT INTO lecturas 
                (id, temperatura, humedad, distancia)
                VALUES (1, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    temperatura = VALUES(temperatura),
                    humedad = VALUES(humedad),
                    distancia = VALUES(distancia),
                    fecha = CURRENT_TIMESTAMP";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([
            $temperatura,
            $humedad,
            $distancia
        ]);
    }

    public function obtenerUltimaLectura()
    {
        $sql = "SELECT id, temperatura, humedad, distancia, fecha
                FROM lecturas
                WHERE id = 1
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();

        return $stmt->fetch();
    }
}