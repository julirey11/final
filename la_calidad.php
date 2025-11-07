<?php
session_start();
if(!isset($_SESSION['usuario'])){
    header("Location: login.php");
    exit;
}
$usuario = $_SESSION['usuario'];
$conn = new mysqli("localhost", "root", "", "cun");
if ($conn->connect_error) { die("Error de conexión: " . $conn->connect_error); }

if (isset($_POST['guardar_progreso'])) {
    $seccion_id = intval($_POST['seccion_id']);
    $stmt_check = $conn->prepare("SELECT id FROM progreso_calidad WHERE usuario=? AND seccion_id=?");
    $stmt_check->bind_param("si", $usuario, $seccion_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    if ($result->num_rows > 0) {
        $stmt_update = $conn->prepare("UPDATE progreso_calidad SET completado=1 WHERE usuario=? AND seccion_id=?");
        $stmt_update->bind_param("si", $usuario, $seccion_id);
        $stmt_update->execute();
    } else {
        $stmt_insert = $conn->prepare("INSERT INTO progreso_calidad (usuario,seccion_id,completado) VALUES (?,?,1)");
        $stmt_insert->bind_param("si", $usuario, $seccion_id);
        $stmt_insert->execute();
    }
    if (isset($_POST['ajax'])) {
        echo json_encode(['status' => 'ok']);
        exit;
    }
}

$sql_last = "SELECT MAX(seccion_id) AS last FROM progreso_calidad WHERE usuario='$usuario' AND completado=1";
$res_last = $conn->query($sql_last);
$row_last = $res_last->fetch_assoc();
$last_completed = $row_last['last'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Normas de Calidad</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{background:#0a0a0a;color:#f1f1f1;line-height:1.7;}
header{background:linear-gradient(135deg,#00111a,#00384a);padding:15px 25px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;}
header h1{color:#00ffc3;text-shadow:0 0 10px #00ffc3;font-size:1.8rem;}
.logout-btn{color:#fff;background:#ff0033;padding:8px 15px;border:none;border-radius:8px;text-decoration:none;transition:.3s;}
.logout-btn:hover{background:#ff3355;}
nav{background:rgba(255,255,255,0.05);padding:10px;}
nav ul{display:flex;justify-content:center;gap:30px;list-style:none;flex-wrap:wrap;}
nav a{color:#00e5ff;text-decoration:none;font-weight:600;}
nav a:hover{text-shadow:0 0 8px #00e5ff;}
section.contenido{max-width:1000px;margin:30px auto;padding:20px;text-align:center;}
.bienvenida h2{color:#00ffc3;font-size:1.6rem;margin-bottom:10px;}
.bienvenida p{color:#b2d4dd;font-size:1rem;margin-bottom:20px;}
.knowledge-text{font-size:2rem;color:#00ffc3;animation:glow 3s ease-in-out infinite;text-shadow:0 0 12px rgba(0,255,200,0.5);}
@keyframes glow{0%,100%{opacity:.6;transform:scale(.95);}50%{opacity:1;transform:scale(1.05);}}
.info-extra{margin-top:25px;text-align:left;background:rgba(255,255,255,0.05);padding:15px;border-radius:10px;box-shadow:0 0 10px rgba(0,0,0,0.3);}
.info-extra h3{color:#00ffc3;margin-bottom:8px;}
.seccion{margin-top:30px;padding:20px;border-radius:10px;background:rgba(255,255,255,0.05);box-shadow:0 0 8px rgba(255,255,255,0.05);}
.bloqueada{filter:blur(5px);pointer-events:none;position:relative;}
.bloqueada::after{content:"🔒 Contenido bloqueado. Contesta la pregunta anterior.";position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(0,0,0,0.8);padding:10px 20px;border-radius:8px;}
button{margin-top:10px;padding:8px 18px;background:#0074d9;color:white;border:none;border-radius:8px;cursor:pointer;transition:.3s;}
button:hover{background:#0099ff;}
.pregunta label{display:block;margin:5px 0;padding:8px 10px;background-color:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.2);border-radius:8px;cursor:pointer;transition:background .3s,transform .1s;}
.pregunta label:hover{background-color:rgba(0,229,255,0.2);transform:scale(1.02);}
.resultado{margin-top:10px;}
#evaluacionFinal{margin-top:30px;padding:15px;border-radius:10px;background:rgba(0,255,200,0.1);color:#00ffc3;display:none;}
footer{text-align:center;margin-top:30px;padding:15px;background:rgba(255,255,255,0.05);}
@keyframes fadeSlideIn{from{opacity:0;transform:translateY(30px);}to{opacity:1;transform:translateY(0);}}
.animar-desbloqueo{animation:fadeSlideIn .8s ease-in-out;}
</style>
</head>
<body>
<header>
  <h1>Normas de Calidad</h1>
  <a href="login.php" class="logout-btn">Cerrar Sesión</a>
</header>

<nav>
  <ul>
    <li><a href="video.php">Video</a></li>
    <li><a href="pruebas.php">Pruebas</a></li>
    <li><a href="software.php">Software</a></li>
    <li><a href="#" id="toggle-btn">Calidad</a></li>
  </ul>
</nav>

<section class="contenido" id="contenido">
  <div class="bienvenida" id="bienvenida">
    <h2>Bienvenido, <?php echo htmlspecialchars($usuario); ?> 👋</h2>
    <p>Explora las normas y buenas prácticas de calidad en el desarrollo de software.</p>
    <div class="knowledge-animation"><span class="knowledge-text">CONOCIMIENTO</span></div>

    <div class="info-extra">
      <h3>📘 ¿Qué es la Calidad de Software?</h3>
      <p>La calidad de software es el conjunto de características y propiedades que determinan el grado en que un sistema, componente o proceso cumple con los requisitos establecidos, las necesidades del usuario y los estándares internacionales de desarrollo.
      Su objetivo principal es garantizar que el producto final sea confiable, funcional, eficiente y fácil de mantener.</p>
      
      <h3>🧠Importancia de la calidad en el desarrollo de software</h3>
      <p>La calidad no solo se refleja en la ausencia de errores, sino también en la satisfacción del usuario final, la usabilidad del sistema, y la eficiencia de los procesos internos de desarrollo.
      Un software de baja calidad puede provocar fallas críticas, pérdida de datos, vulnerabilidades de seguridad y disminución de la confianza del cliente.</p>
    
      <h3>⚙️Factores que determinan la calidad de software</h3>
      <p>Confiabilidad: El sistema debe funcionar correctamente durante un periodo prolongado sin errores.

         Eficiencia: Utilizar adecuadamente los recursos del sistema (memoria, CPU, tiempo de respuesta).

         Usabilidad: La interfaz debe ser fácil de entender y de utilizar.

         Mantenibilidad: El código debe ser limpio y comprensible para facilitar futuras actualizaciones.

         Portabilidad: Debe poder ejecutarse en diferentes entornos o plataformas.</p>
    
      <h3>🧩Normas internacionales relacionadas con la calidad de software</h3>
      <p>Las normas ISO proporcionan marcos estandarizados que permiten evaluar y mejorar la calidad del software. Entre las más relevantes se encuentran:</p>

      <p>🛠️ ISO/IEC 25010: Define un modelo de calidad del producto software basado en características como fiabilidad, seguridad y usabilidad.</p>

      <p>🛠️ ISO/IEC 12207: Especifica los procesos del ciclo de vida del software.</p>

      <p>🛠️ ISO 9001: Se enfoca en la gestión de calidad en las organizaciones.</p>

      <p>🛠️ ISO/IEC 15504 (SPICE): Evalúa la madurez de los procesos de desarrollo.</p>
    
      <h3>🔍Beneficios de aplicar calidad en el software</h3>
      <p>✅ Reducción de errores en etapas tempranas del desarrollo.</p>
      <p>✅ Aumento de la satisfacción del cliente.</p>
      <p>✅ Optimización de costos y tiempos de mantenimiento.</p>
      <p>✅ Mejor reputación y competitividad de la empresa.</p>
      <p>✅ Cumplimiento normativo y confianza en auditorías externas.</p>
    
      <h3>🔄La mejora continua en la calidad del software</h3>
      <p>La calidad no es un objetivo estático, sino un proceso de mejora continua.
      Esto implica medir resultados, analizar errores, implementar retroalimentación y optimizar los procesos de desarrollo y pruebas constantemente.</p>
    
      <h3>🧩Calidad del software desde la perspectiva del usuario</h3>
      <p>Para los usuarios, la calidad se traduce en una experiencia fluida, confiable y agradable.
      Un software puede ser técnicamente perfecto, pero si el usuario no puede usarlo con facilidad, la percepción de calidad se pierde.</p>
    
      <h3>🧰Cómo asegurar la calidad en un proyecto</h3>
      <p>Para garantizar la calidad del software, se deben aplicar metodologías y herramientas específicas:</p>

      <p>💻 Pruebas automatizadas.</p>

      <p>💻 Integración continua (CI/CD).</p>

      <p>💻 Control de versiones (Git).</p>

      <p>💻 Revisiones de código y auditorías técnicas.</p>

      <p>💻 Evaluaciones de desempeño.</p>
    
      <h3>🧭Conclusión</h3>
      <p>La calidad de software es un compromiso con la excelencia técnica, la satisfacción del usuario y la mejora continua.
      No se trata solo de entregar un programa que funcione, sino de construir una solución eficiente, segura, sostenible y confiable que perdure en el tiempo.</p>
    
    </div>
  </div>
</section>

<footer><p>&copy; <?php echo date("Y"); ?> - Proyecto Calidad de Software | CUN</p></footer>

<script>
const toggleBtn=document.getElementById('toggle-btn');
const contenido=document.getElementById('contenido');
const bienvenida=document.getElementById('bienvenida');

toggleBtn.addEventListener('click',(e)=>{
  e.preventDefault();
  if(toggleBtn.textContent==="Calidad"){
    bienvenida.style.display="none";
    toggleBtn.textContent="Inicio";
    contenido.innerHTML=generarQuizHTML();
    inicializarBloqueos(<?php echo $last_completed; ?>);
  }else{
    contenido.innerHTML="";
    contenido.appendChild(bienvenida);
    bienvenida.style.display="block";
    toggleBtn.textContent="Calidad";
  }
});

function generarQuizHTML(){
return `
<h2>📗 Evaluación: Normas de Calidad de Software</h2>
<p>Responde correctamente las 10 preguntas para obtener tu calificación final.</p>
<div id="evaluacionFinal"></div>

${[...Array(10)].map((_,i)=>crearPregunta(i+1)).join('')}
`;
}

function crearPregunta(n){
const preguntas={
1:["¿Cuál es el propósito de las normas ISO de calidad?","Garantizar calidad y eficiencia","Aumentar costos","Eliminar auditorías","b"],
2:["¿Cuándo surgieron las normas ISO?","Después de la Segunda Guerra Mundial","En la era digital","En el siglo XIX","a"],
3:["¿Qué norma evalúa la calidad del producto software?","ISO/IEC 25010","ISO/IEC 12207","ISO 9001","a"],
4:["¿Qué norma regula el ciclo de vida del software?","ISO/IEC 12207","ISO/IEC 9000","ISO 14001","a"],
5:["¿Qué mide la norma ISO/IEC 25010?","Características de calidad del software","Eficiencia energética","Diseño visual","a"],
6:["¿Qué norma se enfoca en la gestión de calidad organizacional?","ISO 9001","ISO/IEC 12207","ISO/IEC 25010","a"],
7:["¿Qué busca la mejora continua en la calidad de software?","Optimizar procesos y resultados","Reducir documentación","Eliminar pruebas","a"],
8:["¿Qué principio guía la calidad en procesos?","Planear, hacer, verificar, actuar (PHVA)","Probar sin documentar","Entregar rápido sin pruebas","a"],
9:["¿Qué tipo de prueba valida el sistema con el cliente?","Prueba de aceptación","Prueba unitaria","Prueba de integración","a"],
10:["¿Cuál es el objetivo final de aplicar normas de calidad?","Satisfacer al cliente con productos confiables","Reducir el tiempo de desarrollo","Evitar auditorías","a"]
};
const [preg,op1,op2,op3,correcta]=preguntas[n];
return `
<div id="seccion${n}" class="seccion ${n>1?'bloqueada':''}">
  <h3>${n}️⃣ ${preg}</h3>
  <div class="pregunta">
    <label><input type="radio" name="preg${n}" value="a"> ${op1}</label>
    <label><input type="radio" name="preg${n}" value="b"> ${op2}</label>
    <label><input type="radio" name="preg${n}" value="c"> ${op3}</label>
    <button onclick="validarRespuesta(${n},'${correcta}')">Validar</button>
    <p class="resultado"></p>
  </div>
</div>`;
}

function inicializarBloqueos(last){
  const secciones=document.querySelectorAll('.seccion');
  secciones.forEach((s,i)=>{ if(i<last) s.classList.remove('bloqueada'); });
}

let aciertos=0;
function validarRespuesta(id,correcta){
  const seleccionada=document.querySelector(`input[name="preg${id}"]:checked`);
  const res=document.querySelector(`#seccion${id} .resultado`);
  if(!seleccionada){res.textContent="⚠️ Selecciona una opción";res.style.color="orange";return;}
  if(seleccionada.value===correcta){
    res.textContent="✅ Correcto";res.style.color="green";
    guardarProgreso(id);
    aciertos++;
    const siguiente=document.getElementById(`seccion${id+1}`);
    if(siguiente){siguiente.classList.remove('bloqueada');siguiente.classList.add('animar-desbloqueo');}
    else mostrarCalificacion();
  }else{
    res.textContent="❌ Incorrecto, intenta de nuevo";res.style.color="red";
  }
}

function mostrarCalificacion(){
  const puntaje=Math.round((aciertos/10)*100);
  const div=document.getElementById('evaluacionFinal');
  div.style.display='block';
  div.innerHTML=`<h3>🎯 Calificación final: ${puntaje}/100</h3>
  <p>${puntaje>=70?"Excelente trabajo, has aprobado.":"Necesitas repasar los conceptos."}</p>`;
}

function guardarProgreso(id){
  const data=new FormData();
  data.append('guardar_progreso',1);
  data.append('seccion_id',id);
  data.append('ajax',1);
  fetch('',{method:'POST',body:data});
}
</script>
</body>
</html>
