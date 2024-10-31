<?php
require_once "./conexion.php"; // Importa la conexión a la base de datos.

// Inicializa la variable para el mensaje de confirmación.
$mensaje = "";

// Obtener opciones para el campo "estudiante".
$estudiantes = $conexion->query("SELECT ID, Nombres FROM estudiante");

// Obtener opciones para el campo "prueba".
$pruebas = $conexion->query("SELECT ID, nombre FROM prueba");

// Obtener preguntas en relación a la prueba seleccionada.
$id_prueba_seleccionada = isset($_POST['prueba']) ? $_POST['prueba'] : '';
$preguntas_result = null;

if (!empty($id_prueba_seleccionada)) {
    $preguntas = $conexion->prepare("SELECT ID, orden FROM pregunta WHERE IDPrueba = ?");
    $preguntas->bind_param("i", $id_prueba_seleccionada);
    $preguntas->execute();
    $preguntas_result = $preguntas->get_result();
}

// Procesar el formulario al enviar.
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['respuesta'])) {
    $id_estudiante = $_POST['estudiante'];
    $id_prueba = $_POST['prueba'];
    $id_pregunta = $_POST['pregunta'];
    $respuesta = $_POST['respuesta'];

    // Validar que todos los datos están seleccionados antes de insertar.
    if (!empty($id_estudiante) && !empty($id_prueba) && !empty($id_pregunta) && !empty($respuesta)) {
        // Preparar y ejecutar la consulta de inserción.
        $sql = $conexion->prepare("INSERT INTO resultado (IDEstudiante, IDPrueba, IDPregunta, Respuesta) VALUES (?, ?, ?, ?)");
        $sql->bind_param("iiis", $id_estudiante, $id_prueba, $id_pregunta, $respuesta);

        // Si la consulta se ejecuta correctamente, asigna el mensaje de confirmación.
        if ($sql->execute()) {
            $mensaje = "Datos guardados correctamente."; // Mensaje de éxito.
        } else {
            $mensaje = "Error al insertar datos: " . $conexion->error; // Mensaje de error.
        }
        $sql->close();
    } else {
        $mensaje = "Todos los campos son obligatorios."; // Mensaje si algún campo está vacío.
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario Resultado</title>
    <link rel="stylesheet" href="./public/css/styles.css"> <!-- Importa el archivo de estilos CSS. -->
</head>
<body>
    <!-- Muestra el mensaje de confirmación si existe. -->
    <?php if (!empty($mensaje)): ?>
        <p><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="estudiante">Estudiante:</label>
        <select name="estudiante" required>
            <option value="">Seleccione un estudiante</option>
            <?php while ($row = $estudiantes->fetch_assoc()): ?>
                <option value="<?php echo $row['ID']; ?>" <?php echo isset($_POST['estudiante']) && $_POST['estudiante'] == $row['ID'] ? 'selected' : ''; ?>>
                    <?php echo $row['Nombres']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="prueba">Prueba:</label>
        <select name="prueba" required onchange="this.form.submit()">
            <option value="">Seleccione una prueba</option>
            <?php while ($row = $pruebas->fetch_assoc()): ?>
                <option value="<?php echo $row['ID']; ?>" <?php echo $id_prueba_seleccionada == $row['ID'] ? 'selected' : ''; ?>>
                    <?php echo $row['nombre']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="pregunta">Pregunta:</label>
        <select name="pregunta" required>
            <option value="">Seleccione una pregunta</option>
            <?php if ($preguntas_result && $preguntas_result->num_rows > 0): ?>
                <?php while ($row = $preguntas_result->fetch_assoc()): ?>
                    <option value="<?php echo $row['ID']; ?>"><?php echo $row['orden']; ?></option>
                <?php endwhile; ?>
            <?php else: ?>
                <option value="">No hay preguntas disponibles para esta prueba</option>
            <?php endif; ?>
        </select>

        <label for="respuesta">Respuesta:</label>
        <input type="text" name="respuesta" required>

        <button type="submit">Consultar</button>
        <button><a href="./consulta.php">Consulta</a></button>
    </form>
</body>
</html>
