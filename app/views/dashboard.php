<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Sensores ESP32</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            margin: 0;
            background: #101827;
            color: #ffffff;
        }

        header {
            padding: 20px;
            background: #172033;
            text-align: center;
            border-bottom: 1px solid #2d3b55;
        }

        header h1 {
            margin: 0;
            font-size: 28px;
        }

        header p {
            margin: 8px 0 0;
            color: #aab4c8;
        }

        .container {
            width: 95%;
            max-width: 1200px;
            margin: 25px auto;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .card {
            background: #172033;
            border: 1px solid #2d3b55;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
        }

        .card h2 {
            margin: 0;
            font-size: 18px;
            color: #aab4c8;
        }

        .card .value {
            margin-top: 15px;
            font-size: 38px;
            font-weight: bold;
        }

        .card .unit {
            font-size: 18px;
            color: #aab4c8;
        }

        .chart-box {
            background: #172033;
            border: 1px solid #2d3b55;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
        }

        .status {
            margin-top: 20px;
            padding: 15px;
            background: #172033;
            border: 1px solid #2d3b55;
            border-radius: 10px;
            color: #aab4c8;
        }

        .ok {
            color: #4ade80;
        }

        .error {
            color: #f87171;
        }

        @media (max-width: 768px) {
            .cards {
                grid-template-columns: 1fr;
            }

            header h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>Dashboard de Sensores ESP32</h1>
    <p>Monitoreo en tiempo real de temperatura, humedad y distancia</p>
</header>

<div class="container">

    <div class="cards">
        <div class="card">
            <h2>Temperatura</h2>
            <div class="value">
                <span id="temperatura">--</span>
                <span class="unit">°C</span>
            </div>
        </div>

        <div class="card">
            <h2>Humedad</h2>
            <div class="value">
                <span id="humedad">--</span>
                <span class="unit">%</span>
            </div>
        </div>

        <div class="card">
            <h2>Distancia</h2>
            <div class="value">
                <span id="distancia">--</span>
                <span class="unit">cm</span>
            </div>
        </div>
    </div>

    <div class="chart-box">
        <canvas id="graficaSensores"></canvas>
    </div>

    <div class="status">
        Estado: <span id="estado" class="error">Esperando datos...</span><br>
        Última actualización: <span id="fecha">--</span>
    </div>

</div>

<script>
    const temperaturaTexto = document.getElementById("temperatura");
    const humedadTexto = document.getElementById("humedad");
    const distanciaTexto = document.getElementById("distancia");
    const estadoTexto = document.getElementById("estado");
    const fechaTexto = document.getElementById("fecha");

    const ctx = document.getElementById("graficaSensores").getContext("2d");

    const labels = [];
    const datosTemperatura = [];
    const datosHumedad = [];
    const datosDistancia = [];

    const grafica = new Chart(ctx, {
        type: "line",
        data: {
            labels: labels,
            datasets: [
                {
                    label: "Temperatura °C",
                    data: datosTemperatura,
                    borderColor: "#f87171",
                    backgroundColor: "rgba(248, 113, 113, 0.15)",
                    tension: 0.3
                },
                {
                    label: "Humedad %",
                    data: datosHumedad,
                    borderColor: "#60a5fa",
                    backgroundColor: "rgba(96, 165, 250, 0.15)",
                    tension: 0.3
                },
                {
                    label: "Distancia cm",
                    data: datosDistancia,
                    borderColor: "#4ade80",
                    backgroundColor: "rgba(74, 222, 128, 0.15)",
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            animation: false,
            plugins: {
                legend: {
                    labels: {
                        color: "#ffffff"
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: "#aab4c8"
                    },
                    grid: {
                        color: "#2d3b55"
                    }
                },
                y: {
                    ticks: {
                        color: "#aab4c8"
                    },
                    grid: {
                        color: "#2d3b55"
                    }
                }
            }
        }
    });

    async function cargarDatos() {
        try {
            const response = await fetch("index.php?r=api/latest");
            const json = await response.json();

            if (!json.status) {
                estadoTexto.textContent = json.mensaje || "Sin datos";
                estadoTexto.className = "error";
                return;
            }

            const lectura = json.data;

            const temperatura = parseFloat(lectura.temperatura);
            const humedad = parseFloat(lectura.humedad);
            const distancia = parseFloat(lectura.distancia);

            temperaturaTexto.textContent = temperatura.toFixed(2);
            humedadTexto.textContent = humedad.toFixed(2);
            distanciaTexto.textContent = distancia.toFixed(2);

            fechaTexto.textContent = lectura.fecha;

            estadoTexto.textContent = "Conectado";
            estadoTexto.className = "ok";

            const hora = new Date().toLocaleTimeString();

            labels.push(hora);
            datosTemperatura.push(temperatura);
            datosHumedad.push(humedad);
            datosDistancia.push(distancia);

            if (labels.length > 20) {
                labels.shift();
                datosTemperatura.shift();
                datosHumedad.shift();
                datosDistancia.shift();
            }

            grafica.update();

        } catch (error) {
            estadoTexto.textContent = "Error al conectar con la API";
            estadoTexto.className = "error";
            console.error(error);
        }
    }

    cargarDatos();
    setInterval(cargarDatos, 2000);
</script>

</body>
</html>