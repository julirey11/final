<?php
session_start();

// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// ==== FUNCIÓN DE EVALUACIÓN REALISTA ====
function evaluarSitio($url) {
    $html = @file_get_contents($url);
    $resultados = [];
    $titulo = "No se pudo obtener el título";

    if ($html !== false) {
        // Obtener título de la página
        if (preg_match("/<title>(.*?)<\/title>/i", $html, $matches)) {
            $titulo = trim($matches[1]);
        }

        // Evaluaciones basadas en el contenido
        $tieneMeta = preg_match_all("/<meta/i", $html);
        $tieneImgs = preg_match_all("/<img/i", $html);
        $tieneScripts = preg_match_all("/<script/i", $html);
        $tieneLinks = preg_match_all("/<a /i", $html);
        $tieneAlt = preg_match_all("/alt=\"[^\"]*\"/i", $html);

        // Cálculo “simulado pero coherente” con prácticas de calidad
        $rendimiento = 100 - min($tieneScripts * 3 + $tieneImgs * 2, 40); // penaliza exceso
        $accesibilidad = min(($tieneAlt * 100) / max($tieneImgs, 1), 100);
        $seguridad = (strpos($url, "https://") === 0) ? 95 : 70;
        $diseno = min(70 + ($tieneImgs * 1.5) + ($tieneLinks * 0.5), 100);
        $seo = min(60 + ($tieneMeta * 2), 100);

        $parametros = [
            "Rendimiento" => $rendimiento,
            "Accesibilidad" => round($accesibilidad, 1),
            "Seguridad" => $seguridad,
            "Diseño y usabilidad" => round($diseno, 1),
            "SEO (Optimización en buscadores)" => round($seo, 1)
        ];

        foreach ($parametros as $criterio => $porcentaje) {
            if ($porcentaje < 70) {
                $retro = "⚠️ Nivel bajo: se recomienda revisar la estructura del código, optimizar recursos y aplicar buenas prácticas de desarrollo.";
            } elseif ($porcentaje < 90) {
                $retro = "✅ Cumple con un nivel aceptable, pero se sugiere mejorar aspectos técnicos y estéticos para alcanzar la excelencia.";
            } else {
                $retro = "🏆 Excelente resultado: cumple con altos estándares de calidad web.";
            }

            $resultados[] = [
                "criterio" => $criterio,
                "porcentaje" => $porcentaje,
                "retro" => $retro
            ];
        }
    } else {
        $titulo = "No se pudo acceder al sitio (verifique la URL o la conexión)";
    }

    return [
        "titulo" => $titulo,
        "resultados" => $resultados
    ];
}

// ==== PROCESAMIENTO DEL FORMULARIO ====
$analisis = null;
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["url"])) {
    $url = htmlspecialchars($_POST["url"]);
    $analisis = evaluarSitio($url);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Calidad de Software</title>
    <link rel="stylesheet" href="css/pruebas.css">
</head>
<body>
    <header>
        <h1>🧠 Prueba de Software</h1>
        <a href="login.php" class="logout-btn">Cerrar sesión</a>
    </header>

    <nav>
        <ul class="menu">
            <li><a href="la_calidad.php">Inicio</a></li>
            <li><a href="video.php">Videos</a></li>
            <li><a href="software.php">software</a></li>
        </ul>
    </nav>

    <main class="contenido">
        <section class="formulario">
            <h2>🔍 Analizador de Calidad de Sitio Web</h2>
            <p>Ingresa una URL válida y el sistema realizará una evaluación técnica de su calidad web.</p>

            <form method="POST">
                <input type="url" name="url" placeholder="https://ejemplo.com" required>
                <button type="submit">Evaluar</button>
            </form>
        </section>

        <?php if ($analisis): ?>
        <section class="resultados fade-in">
            <h3>📊 Resultados de evaluación</h3>
            <p class="url-evaluada">🔗 <strong><?php echo $url; ?></strong></p>
            <p class="titulo-sitio">📄 <em><?php echo $analisis['titulo']; ?></em></p>

            <?php if (!empty($analisis["resultados"])): ?>
            <div class="tabla-resultados">
                <?php foreach ($analisis["resultados"] as $r): ?>
                    <div class="item-resultado">
                        <h4><?php echo $r["criterio"]; ?></h4>
                        <div class="barra">
                            <div class="progreso" style="width: <?php echo $r["porcentaje"]; ?>%;"></div>
                        </div>
                        <p><strong><?php echo $r["porcentaje"]; ?>%</strong> - <?php echo $r["retro"]; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <p style="color:#ff7b7b;">❌ No se pudo realizar el análisis. Verifique que la página esté en línea.</p>
            <?php endif; ?>
        </section>
        <?php endif; ?>
    </main>

    <footer>
        <p>© 2025 Evaluador de Calidad | Desarrollado por Julián Reyes</p>
    </footer>
</body>
</html>
