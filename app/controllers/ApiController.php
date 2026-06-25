<?php

require_once __DIR__ . '/../models/LecturaModel.php';

class ApiController
{
    private LecturaModel $modelo;

    public function __construct()
    {
        $this->modelo = new LecturaModel();
    }

    public function update(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responder(405, [
                'status' => false,
                'respuesta' => 0,
                'mensaje' => 'Método no permitido. Utiliza POST'
            ]);
            return;
        }

        $temperatura = $_POST['temperatura'] ?? null;
        $humedad     = $_POST['humedad'] ?? null;
        $lluvia      = $_POST['lluvia'] ?? null;

        if (
            $temperatura === null ||
            $humedad === null ||
            $lluvia === null ||
            $temperatura === '' ||
            $humedad === '' ||
            $lluvia === ''
        ) {
            $this->responder(400, [
                'status' => false,
                'respuesta' => 0,
                'mensaje' => 'Faltan temperatura, humedad o lluvia',
                'recibido' => $_POST
            ]);
            return;
        }

        if (
            !is_numeric($temperatura) ||
            !is_numeric($humedad) ||
            !is_numeric($lluvia)
        ) {
            $this->responder(400, [
                'status' => false,
                'respuesta' => 0,
                'mensaje' => 'Todos los valores deben ser numéricos'
            ]);
            return;
        }

        $temperatura = (float) $temperatura;
        $humedad     = (float) $humedad;
        $lluvia      = (float) $lluvia;

        if ($humedad < 0 || $humedad > 100) {
            $this->responder(400, [
                'status' => false,
                'respuesta' => 0,
                'mensaje' => 'La humedad debe estar entre 0 y 100'
            ]);
            return;
        }

        if ($lluvia < 0 || $lluvia > 100) {
            $this->responder(400, [
                'status' => false,
                'respuesta' => 0,
                'mensaje' => 'La lluvia debe estar entre 0 y 100'
            ]);
            return;
        }

        try {
            $resultado = $this->modelo->actualizarLectura(
                $temperatura,
                $humedad,
                $lluvia
            );

            $this->responder(200, [
                'status' => $resultado,
                'respuesta' => $resultado ? 1 : 0,
                'mensaje' => $resultado
                    ? 'Lectura actualizada correctamente'
                    : 'No se pudo actualizar la lectura',
                'datos' => [
                    'temperatura' => $temperatura,
                    'humedad' => $humedad,
                    'lluvia' => $lluvia
                ]
            ]);
        } catch (Throwable $e) {
            $this->responder(500, [
                'status' => false,
                'respuesta' => 0,
                'mensaje' => 'Error al guardar la lectura',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function latest(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Origin: *');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->responder(405, [
                'status' => false,
                'mensaje' => 'Método no permitido. Utiliza GET'
            ]);
            return;
        }

        try {
            $lectura = $this->modelo->obtenerUltimaLectura();

            if (!$lectura) {
                $this->responder(404, [
                    'status' => false,
                    'mensaje' => 'No hay lecturas registradas'
                ]);
                return;
            }

            $this->responder(200, [
                'status' => true,
                'data' => $lectura
            ]);
        } catch (Throwable $e) {
            $this->responder(500, [
                'status' => false,
                'mensaje' => 'Error al consultar la lectura',
                'error' => $e->getMessage()
            ]);
        }
    }

    private function responder(int $codigo, array $contenido): void
    {
        http_response_code($codigo);

        echo json_encode(
            $contenido,
            JSON_UNESCAPED_UNICODE
        );
    }
}