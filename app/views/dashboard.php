<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sensores ESP32</title>

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

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
            padding: 22px;
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
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
        }

        .card h2 {
            margin: 0;
            font-size: 18px;
            color: #aab4c8;
        }

        .value {
            margin-top: 15px;
            font-size: 38px;
            font-weight: bold;
        }

        .unit {
            font-size: 18px;
            color: #aab4c8;
        }

        .rain-state {
            display: inline-block;
            margin-top: 14px;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            background: #26334c;
            color: #d8e1f0;
        }

        .rain-dry {
            background: rgba(74, 222, 128, 0.15);
            color: #4ade80;
        }

        .rain-light {
            background: rgba(250, 204, 21, 0.15);
            color: #facc15;
        }

        .rain-medium {
            background: rgba(96, 165, 250, 0.15);
            color: #60a5fa;
        }

        .rain-heavy {
            background: rgba(167, 139, 250, 0.15);
            color: #a78bfa;
        }

        .chart-box {
            position: relative;
            min-height: 420px;
            background: #172033;
            border: 1px solid #2d3b55;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
        }

        .status {
            margin-top: 20px;
            padding: 15px;
            background: #172033;
            border: 1px solid #2d3b55;
            border-radius: 10px;
            color: #aab4c8;
            line-height: 1.8;
        }

        .ok {
            color: #4ade80;
            font-weight: bold;
        }

        .error {
            color: #f87171;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .cards {
                grid-template-columns: 1fr;
            }

            header h1 {
                font-size: 22px;
            }

            .chart-box {
                min-height: 330px;
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>Sensores ESP32</h1>
    <p>Monitoreo de temperatura, humedad y lluvia</p>
</header>

<main class="container">

    <section class="cards">

        <article class="card">
            <h2>Temperatura</h2>

            <div class="value">
                <span id="temperatura">--</span>
                <span class="unit">°C</span>
            </div>
        </article>

        <article class="card">
            <h2>Humedad</h2>

            <div class="value">
                <span id="humedad">--</span>
                <span class="unit">%</span>
            </div>
        </article>

        <article class="card">
            <h2>Nivel de lluvia</h2>

            <div class="value">
                <span id="lluvia">--</span>
                <span class="unit">%</span>
            </div>

            <span
                id="estadoLluvia"
                class="rain-state"
            >
                Esperando lectura
            </span>
        </article>

    </section>

    <section class="chart-box">
        <canvas id="graficaSensores"></canvas>
    </section>

    <section class="status">
        Estado del servidor:
        <span id="estado" class="error">
            Esperando datos...
        </span>

        <br>

        Última lectura del sensor:
        <span id="fecha">--</span>

        <br>

        Última consulta del dashboard:
        <span id="ultimaConsulta">--</span>
    </section>

</main>

<script>
    const temperaturaTexto =
        document.getElementById("temperatura");

    const humedadTexto =
        document.getElementById("humedad");

    const lluviaTexto =
        document.getElementById("lluvia");

    const estadoLluviaTexto =
        document.getElementById("estadoLluvia");

    const estadoTexto =
        document.getElementById("estado");

    const fechaTexto =
        document.getElementById("fecha");

    const ultimaConsultaTexto =
        document.getElementById("ultimaConsulta");

    const contexto =
        document
            .getElementById("graficaSensores")
            .getContext("2d");

    const etiquetas = [];
    const datosTemperatura = [];
    const datosHumedad = [];
    const datosLluvia = [];

    let ultimaFechaRegistrada = null;

    const grafica = new Chart(contexto, {
        type: "line",

        data: {
            labels: etiquetas,

            datasets: [
                {
                    label: "Temperatura °C",
                    data: datosTemperatura,
                    borderColor: "#f87171",
                    backgroundColor: "rgba(248, 113, 113, 0.15)",
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 3
                },
                {
                    label: "Humedad %",
                    data: datosHumedad,
                    borderColor: "#60a5fa",
                    backgroundColor: "rgba(96, 165, 250, 0.15)",
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 3
                },
                {
                    label: "Lluvia %",
                    data: datosLluvia,
                    borderColor: "#a78bfa",
                    backgroundColor: "rgba(167, 139, 250, 0.15)",
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 3
                }
            ]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,

            interaction: {
                mode: "index",
                intersect: false
            },

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
                    beginAtZero: true,

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

    function mostrarEstadoLluvia(lluvia) {
        estadoLluviaTexto.className = "rain-state";

        if (lluvia < 20) {
            estadoLluviaTexto.textContent = "Seco";
            estadoLluviaTexto.classList.add("rain-dry");
            return;
        }

        if (lluvia < 50) {
            estadoLluviaTexto.textContent = "Lluvia ligera";
            estadoLluviaTexto.classList.add("rain-light");
            return;
        }

        if (lluvia < 80) {
            estadoLluviaTexto.textContent = "Lluvia moderada";
            estadoLluviaTexto.classList.add("rain-medium");
            return;
        }

        estadoLluviaTexto.textContent = "Lluvia fuerte";
        estadoLluviaTexto.classList.add("rain-heavy");
    }

    function agregarDatosGrafica(
        fecha,
        temperatura,
        humedad,
        lluvia
    ) {
        /*
         * El dashboard consulta cada 2 segundos, pero el ESP32
         * puede enviar cada 20 segundos. Esta validación evita
         * agregar varias veces la misma lectura a la gráfica.
         */
        if (fecha === ultimaFechaRegistrada) {
            return;
        }

        ultimaFechaRegistrada = fecha;

        const hora = fecha
            ? fecha.substring(11, 19)
            : new Date().toLocaleTimeString();

        etiquetas.push(hora);
        datosTemperatura.push(temperatura);
        datosHumedad.push(humedad);
        datosLluvia.push(lluvia);

        const limiteLecturas = 20;

        if (etiquetas.length > limiteLecturas) {
            etiquetas.shift();
            datosTemperatura.shift();
            datosHumedad.shift();
            datosLluvia.shift();
        }

        grafica.update();
    }

    async function cargarDatos() {
        try {
            const response = await fetch(
                "index.php?r=api/latest",
                {
                    method: "GET",
                    cache: "no-store"
                }
            );

            if (!response.ok) {
                throw new Error(
                    `Error HTTP ${response.status}`
                );
            }

            const json = await response.json();

            if (!json.status || !json.data) {
                estadoTexto.textContent =
                    json.mensaje || "No hay datos disponibles";

                estadoTexto.className = "error";
                return;
            }

            const lectura = json.data;

            const temperatura =
                Number.parseFloat(lectura.temperatura);

            const humedad =
                Number.parseFloat(lectura.humedad);

            const lluvia =
                Number.parseFloat(lectura.lluvia);

            if (
                !Number.isFinite(temperatura) ||
                !Number.isFinite(humedad) ||
                !Number.isFinite(lluvia)
            ) {
                throw new Error(
                    "La API devolvió valores inválidos"
                );
            }

            temperaturaTexto.textContent =
                temperatura.toFixed(2);

            humedadTexto.textContent =
                humedad.toFixed(2);

            lluviaTexto.textContent =
                lluvia.toFixed(2);

            fechaTexto.textContent =
                lectura.fecha || "--";

            ultimaConsultaTexto.textContent =
                new Date().toLocaleString();

            estadoTexto.textContent = "Conectado";
            estadoTexto.className = "ok";

            mostrarEstadoLluvia(lluvia);

            agregarDatosGrafica(
                lectura.fecha,
                temperatura,
                humedad,
                lluvia
            );

        } catch (error) {
            estadoTexto.textContent =
                "Error al conectar con la API";

            estadoTexto.className = "error";

            ultimaConsultaTexto.textContent =
                new Date().toLocaleString();

            console.error(
                "Error al cargar las lecturas:",
                error
            );
        }
    }

    cargarDatos();

    setInterval(cargarDatos, 2000);
</script>

</body>
</html>