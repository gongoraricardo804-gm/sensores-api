<?php

require_once __DIR__ . "/../models/LecturaModel.php";

class ApiController
{
    private $model;

    public function __construct()
    {
        $this->model = new LecturaModel();
    }

    public function update()
    {
        header("Content-Type: application/json; charset=utf-8");

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            echo json_encode([
                "status" => false,
                "respuesta" => 0,
                "mensaje" => "Método no permitido. Usa POST."
            ]);
            return;
        }

        $temperatura = $_POST["temperatura"] ?? null;
        $humedad     = $_POST["humedad"] ?? null;
        $distancia   = $_POST["distancia"] ?? null;

        if ($temperatura === null || $humedad === null || $distancia === null) {
            echo json_encode([
                "status" => false,
                "respuesta" => 0,
                "mensaje" => "Faltan datos",
                "recibido" => $_POST
            ]);
            return;
        }

        if (!is_numeric($temperatura) || !is_numeric($humedad) || !is_numeric($distancia)) {
            echo json_encode([
                "status" => false,
                "respuesta" => 0,
                "mensaje" => "Los valores deben ser numéricos",
                "recibido" => $_POST
            ]);
            return;
        }

        try {
            $resultado = $this->model->actualizarLectura(
                $temperatura,
                $humedad,
                $distancia
            );

            echo json_encode([
                "status" => $resultado,
                "respuesta" => $resultado ? 1 : 0,
                "mensaje" => $resultado ? "Lectura actualizada correctamente" : "Error al actualizar"
            ]);

        } catch (Exception $e) {
            echo json_encode([
                "status" => false,
                "respuesta" => 0,
                "mensaje" => "Error en la consulta",
                "error" => $e->getMessage()
            ]);
        }
    }

    public function latest()
    {
        header("Content-Type: application/json; charset=utf-8");

        try {
            $lectura = $this->model->obtenerUltimaLectura();

            if (!$lectura) {
                echo json_encode([
                    "status" => false,
                    "mensaje" => "No hay lecturas registradas"
                ]);
                return;
            }

            echo json_encode([
                "status" => true,
                "data" => $lectura
            ]);

        } catch (Exception $e) {
            echo json_encode([
                "status" => false,
                "mensaje" => "Error al obtener lectura",
                "error" => $e->getMessage()
            ]);
        }
    }
}