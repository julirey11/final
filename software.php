<?php
session_start();
if(!isset($_SESSION['usuario'])){
    header("Location: login.php");
    exit;
}
$usuario = $_SESSION['usuario'];

// Conexión a DB
$conn = new mysqli("localhost","root","","cun");
if($conn->connect_error) die("Error de conexión: ".$conn->connect_error);

// Guardar progreso si llega POST desde JS
if(isset($_POST['guardar_progreso'])){
    $seccion_id = intval($_POST['seccion_id']);

    // Verificar si ya existe registro
    $stmt_check = $conn->prepare("SELECT id FROM progreso_usuarios WHERE usuario=? AND seccion_id=?");
    $stmt_check->bind_param("si", $usuario, $seccion_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if($result->num_rows > 0){
        $stmt_update = $conn->prepare("UPDATE progreso_usuarios SET completado=1 WHERE usuario=? AND seccion_id=?");
        $stmt_update->bind_param("si", $usuario, $seccion_id);
        $stmt_update->execute();
    } else {
        $stmt_insert = $conn->prepare("INSERT INTO progreso_usuarios (usuario,seccion_id,completado) VALUES (?,?,1)");
        $stmt_insert->bind_param("si", $usuario, $seccion_id);
        $stmt_insert->execute();
    }
    // Para peticiones AJAX
    if(isset($_POST['ajax'])){
        echo json_encode(['status'=>'ok']);
        exit;
    }
}

// Obtener la última pregunta completada
$sql_last = "SELECT MAX(seccion_id) AS last FROM progreso_usuarios WHERE usuario='$usuario' AND completado=1";
$res_last = $conn->query($sql_last);
$row_last = $res_last->fetch_assoc();
$last_completed = $row_last['last'] ?? 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Prueba de Software</title>
<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}

body {
  background: #0a0a0a;
  color: #f4f4f4;
  line-height: 1.6;
}

header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, #00111a 0%, #002b36 100%);
  color: white;
  padding: 15px 20px;
  flex-wrap: wrap;
  gap: 10px;
  text-align: center;
}

header h1 {
  color: #00e5ff;
  font-size: 1.8em;
  text-transform: uppercase;
  text-shadow: 0 0 10px rgba(0, 229, 255, 0.5);
  flex: 1;
  text-align: left;
  min-width: 250px;
}

.logout-btn {
  background: transparent;
  border: 1px solid #00e5ff;
  color: #00e5ff;
  text-decoration: none;
  padding: 8px 18px;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.logout-btn:hover {
  background: #ff0033;
  color: #fff;
  border-color: #ff0033;
  box-shadow: 0 0 10px rgba(255, 0, 51, 0.4);
}

nav {
  background: rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(5px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

nav ul {
  display: flex;
  justify-content: center;
  gap: 35px;
  list-style: none;
  margin: 0;
  padding: 15px;
  flex-wrap: wrap;
}

nav a {
  color: #00e5ff;
  text-decoration: none;
  font-weight: 600;
  font-size: 1em;
  transition: 0.3s;
}

nav a:hover {
  color: #90e0ef;
  text-shadow: 0 0 10px #00e5ff;
}

main {
  max-width: 900px;
  margin: 30px auto;
  padding: 20px;
  text-align: center;
}

.intro h2 {
  font-size: 1.4rem;
  color: #b2d4dd;
  margin-bottom: 20px;
}

.quiz {
  margin-top: 40px;
  text-align: left;
}

.quiz h3 {
  text-align: center;
  margin-bottom: 20px;
  color: #00c8ff;
}

/* ==== ESTILO DE LAS PREGUNTAS ==== */
.pregunta {
  background: rgba(255, 255, 255, 0.05);
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.pregunta p {
  font-weight: bold;
  color: #00e5ff;
  margin-bottom: 10px;
}

.pregunta label {
  display: block; /* 👈 hace que las respuestas se muestren en columna */
  margin: 5px 0;
  padding: 8px 10px;
  background-color: rgba(255, 255, 255, 0.07);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  transition: background-color 0.3s ease, transform 0.1s ease;
  cursor: pointer;
}

.pregunta label:hover {
  background-color: rgba(0, 229, 255, 0.2);
  transform: scale(1.02);
}

.pregunta input[type="radio"] {
  margin-right: 8px;
}

button {
  display: block;
  margin: 20px auto;
  background-color: #0074d9;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  transition: 0.3s;
}

button:hover {
  background-color: #0099ff;
}

.resultado {
  text-align: center;
  margin-top: 20px;
}

.felicitaciones {
  background-color: #00c851;
  color: white;
  padding: 15px;
  border-radius: 10px;
  animation: aparecer 0.8s ease;
}

@keyframes aparecer {
  from { opacity: 0; transform: scale(0.8); }
  to { opacity: 1; transform: scale(1); }
}

@media (max-width: 800px) {
  header { flex-direction: column; align-items: center; text-align: center; }
  header h1 { font-size: 1.5rem; text-align: center; }
  .logout-btn { margin-top: 10px; }
  nav ul { gap: 20px; }
}

@media (max-width: 500px) {
  header h1 { font-size: 1.2rem; }
  .logout-btn { padding: 6px 14px; font-size: 0.9rem; }
}
</style>

</head>
<body>

<header>
<h1>Prueba de Software</h1>
<a href="login.php" class="logout-btn">Cerrar Sesión</a>
</header>

<nav>
<ul>
<li><a href="la_calidad.php">Inicio</a></li>
<li><a href="pruebas.php">Pruebas</a></li>
<li><a href="video.php">Video</a></li>
</ul>
</nav>

<main>
<section class="intro"><h2>Responde las siguientes preguntas para avanzar</h2></section>

<section class="quiz" id="quiz">
<h3>Cuestionario: Fundamentos de Pruebas</h3>

 <!-- Pregunta 1 -->
 <div class="pregunta" id="p1" style="display:<?php echo ($last_completed>=1) ? 'none' : 'block'; ?>">
        <p>1️⃣ ¿Qué es una prueba de software?</p>
        <label><input type="radio" name="q1" value="a"> Un proceso para identificar errores y verificar que el sistema funcione correctamente.</label>
        <label><input type="radio" name="q1" value="b"> Un método para escribir código más rápido.</label>
        <label><input type="radio" name="q1" value="c"> Un proceso opcional después de la entrega.</label>
        <button onclick="verificarRespuesta('q1','a','p1','p2',1)">Responder</button>
    </div>

    <!-- Pregunta 2 -->
    <div class="pregunta" id="p2" style="display:<?php echo ($last_completed>=2) ? 'block' : 'none'; ?>">
        <p>2️⃣ ¿Qué tipo de pruebas se realizan sin ejecutar el código?</p>
        <label><input type="radio" name="q2" value="a"> Pruebas estáticas.</label>
        <label><input type="radio" name="q2" value="b"> Pruebas dinámicas.</label>
        <label><input type="radio" name="q2" value="c"> Pruebas unitarias.</label>
        <button onclick="verificarRespuesta('q2','a','p2','p3',2)">Responder</button>
    </div>

    <!-- Pregunta 3 -->
    <div class="pregunta" id="p3" style="display:<?php echo ($last_completed>=3) ? 'block' : 'none'; ?>">
        <p>3️⃣ ¿Qué se busca con las pruebas de integración?</p>
        <label><input type="radio" name="q3" value="a"> Comprobar el funcionamiento conjunto de los módulos del sistema.</label>
        <label><input type="radio" name="q3" value="b"> Analizar la interfaz gráfica.</label>
        <label><input type="radio" name="q3" value="c"> Revisar la documentación del proyecto.</label>
        <button onclick="verificarRespuesta('q3','a','p3','p4',3)">Responder</button>
    </div>

    <!-- Pregunta 4 -->
    <div class="pregunta" id="p4" style="display:<?php echo ($last_completed>=4) ? 'block' : 'none'; ?>">
        <p>4️⃣ ¿Qué tipo de prueba se centra en la interacción entre componentes del sistema?</p>
        <label><input type="radio" name="q4" value="a"> Prueba de integración.</label>
        <label><input type="radio" name="q4" value="b"> Prueba de sistema.</label>
        <label><input type="radio" name="q4" value="c"> Prueba de aceptación.</label>
        <button onclick="verificarRespuesta('q4','a','p4','p5',4)">Responder</button>
    </div>

    <!-- Pregunta 5 -->
    <div class="pregunta" id="p5" style="display:<?php echo ($last_completed>=5) ? 'block' : 'none'; ?>">
        <p>5️⃣ ¿Cuál es el objetivo principal de una prueba unitaria?</p>
        <label><input type="radio" name="q5" value="a"> Verificar el funcionamiento correcto de una parte específica del código.</label>
        <label><input type="radio" name="q5" value="b"> Evaluar todo el sistema de forma conjunta.</label>
        <label><input type="radio" name="q5" value="c"> Probar la interfaz de usuario.</label>
        <button onclick="verificarRespuesta('q5','a','p5','p6',5)">Responder</button>
    </div>

    <!-- Pregunta 6 -->
    <div class="pregunta" id="p6" style="display:<?php echo ($last_completed>=6) ? 'block' : 'none'; ?>">
        <p>6️⃣ ¿Qué tipo de prueba realiza el usuario final para validar el producto?</p>
        <label><input type="radio" name="q6" value="a"> Prueba de aceptación.</label>
        <label><input type="radio" name="q6" value="b"> Prueba unitaria.</label>
        <label><input type="radio" name="q6" value="c"> Prueba de carga.</label>
        <button onclick="verificarRespuesta('q6','a','p6','p7',6)">Responder</button>
    </div>

    <!-- Pregunta 7 -->
    <div class="pregunta" id="p7" style="display:<?php echo ($last_completed>=7) ? 'block' : 'none'; ?>">
        <p>7️⃣ ¿Cuál es la principal diferencia entre una prueba funcional y una no funcional?</p>
        <label><input type="radio" name="q7" value="a"> La funcional verifica comportamientos; la no funcional mide rendimiento o seguridad.</label>
        <label><input type="radio" name="q7" value="b"> No existe diferencia.</label>
        <label><input type="radio" name="q7" value="c"> La no funcional revisa el código fuente.</label>
        <button onclick="verificarRespuesta('q7','a','p7','p8',7)">Responder</button>
    </div>

    <!-- Pregunta 8 -->
    <div class="pregunta" id="p8" style="display:<?php echo ($last_completed>=8) ? 'block' : 'none'; ?>">
        <p>8️⃣ ¿Qué es una prueba de regresión?</p>
        <label><input type="radio" name="q8" value="a"> Una prueba para verificar que los cambios no afecten funcionalidades previas.</label>
        <label><input type="radio" name="q8" value="b"> Una prueba inicial del sistema.</label>
        <label><input type="radio" name="q8" value="c"> Una prueba manual del código.</label>
        <button onclick="verificarRespuesta('q8','a','p8','p9',8)">Responder</button>
    </div>

    <!-- Pregunta 9 -->
    <div class="pregunta" id="p9" style="display:<?php echo ($last_completed>=9) ? 'block' : 'none'; ?>">
        <p>9️⃣ ¿Qué herramienta se utiliza comúnmente para pruebas unitarias en Java?</p>
        <label><input type="radio" name="q9" value="a"> JUnit.</label>
        <label><input type="radio" name="q9" value="b"> Selenium.</label>
        <label><input type="radio" name="q9" value="c"> Postman.</label>
        <button onclick="verificarRespuesta('q9','a','p9','p10',9)">Responder</button>
    </div>

    <!-- Pregunta 10 -->
    <div class="pregunta" id="p10" style="display:<?php echo ($last_completed>=10) ? 'block' : 'none'; ?>">
        <p>🔟 ¿Qué tipo de prueba mide el rendimiento y la capacidad de respuesta del sistema?</p>
        <label><input type="radio" name="q10" value="a"> Prueba de rendimiento.</label>
        <label><input type="radio" name="q10" value="b"> Prueba estática.</label>
        <label><input type="radio" name="q10" value="c"> Prueba funcional.</label>
        <button onclick="finalizarQuiz()">Finalizar</button>
    </div>
</div>
<div class="resultado" id="resultado"></div>
</section>
</main>

<form id="formProgreso" method="POST" style="display:none;">
<input type="hidden" name="guardar_progreso" value="1">
<input type="hidden" name="seccion_id" value="1">
<input type="hidden" name="ajax" value="1">
</form>

<script>
function verificarRespuesta(nombrePregunta, valorCorrecto, idActual, idSiguiente){
    const radios = document.getElementsByName(nombrePregunta);
    let correcta = false;
    radios.forEach(radio => { if(radio.checked && radio.value===valorCorrecto) correcta = true; });
    if(!correcta){ alert("Respuesta incorrecta, intenta de nuevo"); return; }

    // Mostrar siguiente
    document.getElementById(idActual).style.display = 'none';
    document.getElementById(idSiguiente).style.display = 'block';

    // Guardar progreso con AJAX
    const seccionNum = parseInt(idActual.replace('p',''));
    const formData = new FormData();
    formData.append('guardar_progreso',1);
    formData.append('seccion_id', seccionNum);
    formData.append('ajax',1);

    fetch('', { method:'POST', body:formData })
    .then(res=>res.json())
    .then(data=>{ console.log('Progreso guardado', data); })
    .catch(err=>console.error(err));
}

function finalizarQuiz(){
    const seccionNum = 3;
    const formData = new FormData();
    formData.append('guardar_progreso',1);
    formData.append('seccion_id', seccionNum);
    formData.append('ajax',1);

    fetch('', { method:'POST', body:formData })
    .then(res=>res.json())
    .then(data=>{
        document.getElementById('resultado').innerHTML = '<div class="felicitaciones">🎉 ¡Has completado el quiz!</div>';
    })
    .catch(err=>console.error(err));
}
</script>

</body>
</html>




