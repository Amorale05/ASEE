<?php
    include_once('config/db.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {

    $id = (int) $_POST['eliminar_id'];

    // 1. Obtener la imagen para borrarla del servidor
    $stmt = $conn->prepare("SELECT imagen FROM cursos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($curso) {
            $rutaImagen = __DIR__ . 'assets/img/' . $curso['imagen'];

            // 2. Borrar imagen si existe
            if (file_exists($rutaImagen)) {
                unlink($rutaImagen);
            }

            // 3. Borrar registro
            $delete = $conn->prepare("DELETE FROM cursos WHERE id = :id");
            $delete->execute([':id' => $id]);
        }

        // 4. Redirigir (PRG pattern)
        header('Location: subir.php');
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // 1. Sanitizar entradas
        $nombre = trim($_POST['nombre']);
        $fechainicio = $_POST['fechainicio'];
        $duracion = trim($_POST['duracion']);
        $descripcion = trim($_POST['descripcion']);

        // 2. Validación básica
        if (!$nombre || !$fechainicio || !$duracion || !$descripcion) {
            die('Todos los campos son obligatorios');
        }

        // 3. Manejo de imagen
        $imagen = $_FILES['imagen'];

        if ($imagen['error'] !== UPLOAD_ERR_OK) {
            die('Error al subir la imagen');
        }

        $ext = pathinfo($imagen['name'], PATHINFO_EXTENSION);
        $nombreImagen = uniqid('curso_') . '.' . $ext;

        $rutaDestino = 'assets/img/' . $nombreImagen;

        move_uploaded_file($imagen['tmp_name'], $rutaDestino);

        // 4. Guardar en BD
        $stmt = $conn->prepare("
            INSERT INTO cursos (nombre, fechainicio, duracion, descripcion, imagen)
            VALUES (:nombre, :fechainicio, :duracion, :descripcion, :imagen)
        ");

        $stmt->execute([
            ':nombre' => $nombre,
            ':fechainicio' => $fechainicio,
            ':duracion' => $duracion,
            ':descripcion' => $descripcion,
            ':imagen' => 'assets/img/' . $nombreImagen
        ]);

        // 5. Redirección (evita reenvío del form)
        header('Location: subir.php');
        exit;
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ASEE · Asesoría de Servicios Educativos y Empresariales</title>
  <meta name="description" content="ASEE: Formamos personas, ayudamos a mejorar la educación y fortalecemos negocios. Distribuidor autorizado de Educare Innovación, facilitador certificado de LEGO® Serious Play, workshops para empresas."/>
  <link rel="icon" type="image/png" href="assets/img/logo-asee.png" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            asee: {50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81'},
            educare: {500:'#0ea5e9'},
            lati: {500:'#22c55e'}
          }
        }
      }
    }
  </script>
  <style>
    html { scroll-behavior: smooth; }
    .section-accent{ background: linear-gradient(90deg, rgba(99,102,241,.1), rgba(14,165,233,.08), rgba(34,197,94,.08)); }
    .hero-mark{
      position:absolute; left:2%; top:8%;
      width:min(22vw,260px);
      opacity:.22;
      filter:saturate(1) brightness(1.1) contrast(1.1);
      mix-blend-mode:lighten;
      mask-image:radial-gradient(ellipse at 60% 50%, rgba(0,0,0,1) 60%, rgba(0,0,0,0) 100%);
      -webkit-mask-image:radial-gradient(ellipse at 60% 50%, rgba(0,0,0,1) 60%, rgba(0,0,0,0) 100%);
    }
    .pill{border:1px solid rgba(255,255,255,.25)}
  </style>
</head>
<body class="text-slate-800">
  <section>
    <div class="min-h-screen bg-slate-100 flex items-center justify-center px-4">
    <form
        action="subir.php"
        method="POST"
        enctype="multipart/form-data"
        class="w-full max-w-2xl bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6"
    >

        <!-- Título -->
        <div>
        <h2 class="text-2xl font-semibold text-slate-800">Nuevo curso</h2>
        <p class="text-sm text-slate-500">
            Completa la información para registrar un nuevo curso
        </p>
        </div>

        <!-- Nombre -->
        <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">
            Nombre del curso
        </label>
        <input
            type="text"
            name="nombre"
            required
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-800
                focus:outline-none focus:ring-2 focus:ring-educare-500 focus:border-educare-500"
            placeholder=" "
        >
        </div>

        <!-- Fecha + duración -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
            Fecha de inicio
            </label>
            <input
            type="date"
            name="fechainicio"
            required
            class="w-full rounded-lg border border-slate-300 px-3 py-2
                    focus:outline-none focus:ring-2 focus:ring-educare-500 focus:border-educare-500"
            >
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
            Duración
            </label>
            <input
            type="text"
            name="duracion"
            required
            placeholder="Solo el número de días"
            class="w-full rounded-lg border border-slate-300 px-3 py-2
                    focus:outline-none focus:ring-2 focus:ring-educare-500 focus:border-educare-500"
            >
        </div>
        </div>

        <!-- Descripción -->
        <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">
            Descripción
        </label>
        <textarea
            name="descripcion"
            rows="4"
            required
            class="w-full rounded-lg border border-slate-300 px-3 py-2 resize-none
                focus:outline-none focus:ring-2 focus:ring-educare-500 focus:border-educare-500"
            placeholder="Describe brevemente el contenido del curso"
        ></textarea>
        </div>

        <!-- Imagen -->
        <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">
            Imagen del curso
        </label>
        <input
            type="file"
            name="imagen"
            accept="image/*"
            required
            class="block w-full text-sm text-slate-600
                file:mr-4 file:py-2 file:px-4
                file:rounded-lg file:border-0
                file:bg-slate-100 file:text-slate-700
                hover:file:bg-slate-200"
        >
        </div>

        <!-- Botón -->
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
        <a
            href="index.php"
            class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100"
        >
            Volver
        </a>
        <button
            type="submit"
            class="px-6 py-2 rounded-lg bg-educare-500 text-white font-medium
                hover:bg-educare-600 transition"
        >
            Guardar curso
        </button>
        </div>

    </form>
    </div>
</section>

<section class="py-16 bg-slate-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <article id="workshops" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <?php 
            $query = 'SELECT * FROM cursos';
            foreach ($conn->query($query) as $curso): ?>
            
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 hover:shadow-md transition">

                <!-- Encabezado -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                <!-- Imagen + nombre -->
                <div class="flex items-center gap-4">
                    <div class="w-36 h-20 rounded-lg bg-white border border-slate-200 flex items-center justify-center overflow-hidden">
                    <img 
                        src="<?= htmlspecialchars($curso['imagen']) ?>" 
                        alt="<?= htmlspecialchars($curso['nombre']) ?>" 
                        class="w-full h-full object-cover"
                    />
                    </div>

                    <div>
                    <h4 class="text-lg font-semibold text-slate-800">
                        <?= htmlspecialchars($curso['nombre']) ?>
                    </h4>
                    <p class="text-sm text-slate-500">
                        Duración: <?= htmlspecialchars($curso['duracion']) ?> días
                    </p>
                    </div>
                </div>

                <!-- Fecha de inicio -->
                <div class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-educare-500 text-white text-sm font-medium whitespace-nowrap">
                    Inicia: <?= htmlspecialchars($curso['fechainicio']) ?>
                </div>

                </div>

                <!-- Descripción -->
                <p class="mt-4 text-sm text-slate-600 leading-relaxed">
                <?= nl2br(htmlspecialchars($curso['descripcion'])) ?>
                </p>

                <!-- Borrar -->
                <form method="POST" action="" class="mt-2">
                <input type="hidden" name="eliminar_id" value="<?= $curso['id'] ?>">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-red-500 text-white font-medium hover:bg-red-600"
                    onclick="return confirm('¿Seguro que deseas eliminar este curso?')"
                >
                    Eliminar
                </button>
                </form>
            </div>

            <?php endforeach; ?>
      </article>
        
    </div>
</section>
</body>
</html>