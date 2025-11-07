<?php
session_start();

// Verifica si el usuario inició sesión
if(!isset($_SESSION['usuario'])){
    header("Location: login.php");
    exit;
}

// Si el usuario hace clic en "Cerrar sesión"
if(isset($_GET['logout'])){
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video sobre Calidad de Software</title>
    <link rel="stylesheet" href="css/estilos_video.css">
</head>
<body>

    <header>
        <h1>🎥 Video sobre la Calidad del Software</h1>
        <a href="?logout=true" class="logout-btn">Cerrar Sesión</a>
    </header>

    <nav>
        <ul>
            <li><a href="la_calidad.php">Inicio</a></li>
            <li><a href="pruebas.php">Pruebas</a></li>
            <li><a href="software.php">software</a></li>
        </ul>
    </nav>

    <main>
        <section class="intro">
            <h2>Aprende conmigo sobre calidad de software y sobre este aplicativo</h2>
        </section>

        <section class="video-section">
        <div style="width:100%; max-width:600px; margin:auto;">
    <iframe width="100%" height="315" 
        src="https://drive.google.com/file/d/1yTYhGBgaMuigKvS4Fg0FB2YXHAIw4rMl/preview" 
        title="Video de YouTube" 
        frameborder="0" 
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
        allowfullscreen>
    </iframe>
</div>
            <p class="descripcion">
                En este video exploramos los fundamentos de la calidad del software: cómo evaluarla, sus normas ISO más importantes y la importancia del aseguramiento en el ciclo de vida del desarrollo. 
                Observa con atención para responder correctamente las preguntas al final.
            </p>
        </section>

        <section class="quiz">
            <h3>🧩 Certifica tu conocimiento</h3>
            <form id="quizForm">
                <div class="pregunta">
                    <p>1️⃣ ¿Qué mide la calidad del software?</p>
                    <label><input type="radio" name="p1" value="a"> La cantidad de código.</label><br>
                    <label><input type="radio" name="p1" value="b"> El grado en que cumple los requisitos del usuario.</label><br>
                    <label><input type="radio" name="p1" value="c"> El precio del sistema.</label>
                </div>

                <div class="pregunta">
                    <p>2️⃣ ¿Cuál norma internacional se enfoca en la gestión de calidad del software?</p>
                    <label><input type="radio" name="p2" value="a"> ISO 9001</label><br>
                    <label><input type="radio" name="p2" value="b"> ISO 25010</label><br>
                    <label><input type="radio" name="p2" value="c"> ISO 14000</label>
                </div>

                <div class="pregunta">
                    <p>3️⃣ ¿Qué aspecto evalúa la mantenibilidad?</p>
                    <label><input type="radio" name="p3" value="a"> Qué tan fácil es modificar el software.</label><br>
                    <label><input type="radio" name="p3" value="b"> Qué tan rápido carga la página.</label><br>
                    <label><input type="radio" name="p3" value="c"> La estética visual del sitio.</label>
                </div>

                <button type="button" onclick="evaluarQuiz()">Enviar respuestas</button>
            </form>

            <div id="resultado" class="resultado"></div>
        </section>
    </main>

    <script>
    function evaluarQuiz(){
        const respuestas = {
            p1: "b",
            p2: "b",
            p3: "a"
        };

        let puntaje = 0;
        for(let i in respuestas){
            const seleccion = document.querySelector(`input[name="${i}"]:checked`);
            if(seleccion && seleccion.value === respuestas[i]) puntaje++;
        }

        const resultadoDiv = document.getElementById("resultado");
        if(puntaje === 3){
            resultadoDiv.innerHTML = `
                <div class="felicitaciones">
                    🎉 ¡Felicitaciones! Has demostrado un excelente conocimiento en calidad de software. 👏
                </div>`;
            resultadoDiv.classList.add("mostrar");
        } else {
            resultadoDiv.innerHTML = `
                <p>Has acertado ${puntaje} de 3 preguntas. 💡 Vuelve a ver el video y mejora tu resultado.</p>`;
            resultadoDiv.classList.remove("mostrar");
        }
    }
    </script>

</body>
</html>
