<?php
// Se incluye el archivo conexion.php que contiene la configuración para conectar a la base de datos, creando el objeto $conexion.
require_once "./conexion.php";

// Obtener opciones para el campo "Estudiante"
$estudiantes = $conexion->query("SELECT ID, Nombres FROM estudiante");

// Obtener opciones para el campo "Prueba"
$pruebas = $conexion->query("SELECT ID, nombre FROM prueba");

// Inicializar variables para almacenar los resultados de la consulta
$results = [];
$mensaje = ''; // Variable para el mensaje de "sin resultados"

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['estudiante']) && isset($_POST['prueba'])) {
    $id_estudiante = $_POST['estudiante'];
    $id_prueba = $_POST['prueba'];

    // Consultar los datos de la primera pregunta y respuesta del estudiante
    $query = "
        SELECT p.orden AS OrdenPregunta, p.respuesta AS RespuestaCorrecta, r.Respuesta AS RespuestaEstudiante,
               IF(p.respuesta = r.Respuesta, 'Si', 'No') AS Acierto
        FROM pregunta p
        INNER JOIN resultado r ON p.ID = r.IDPregunta
        WHERE r.IDEstudiante = '$id_estudiante' AND r.IDPrueba = '$id_prueba' AND p.IDPrueba = '$id_prueba'
        ORDER BY p.orden
        LIMIT 1;
    ";
    
    $result = $conexion->query($query);

    // Verificar si hay resultados y, si no, mostrar un mensaje
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    } else {
        $mensaje = "No se encontraron resultados para el estudiante y la prueba seleccionados.";
    }
}
// Cerrar la conexión a la base de datos
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consulta de Resultados</title>
    <link rel="stylesheet" href="./public/css/styles.css">
</head>
<body>
    <div class="form-container">
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
            <select name="prueba" required>
                <option value="">Seleccione una prueba</option>
                <?php while ($row = $pruebas->fetch_assoc()): ?>
                    <option value="<?php echo $row['ID']; ?>" <?php echo isset($_POST['prueba']) && $_POST['prueba'] == $row['ID'] ? 'selected' : ''; ?>>
                        <?php echo $row['nombre']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Consultar</button>
        </form>
    </div>
    
    <div class="table-container">
        <?php if (!empty($results)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Orden de la Pregunta</th>
                        <th>Respuesta Correcta</th>
                        <th>Respuesta Estudiante</th>
                        <th>Acierto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?php echo $row['OrdenPregunta']; ?></td>
                            <td><?php echo $row['RespuestaCorrecta']; ?></td>
                            <td><?php echo $row['RespuestaEstudiante'] ?: 'N/A'; ?></td>
                            <td><?php echo $row['Acierto']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?php echo $mensaje; ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
