<?php
    include('cors_headers.php');
    include('config.php');

    // Obtener todos los cuentos globales
    $sql = "SELECT c.id_cuento, c.nombre FROM Cuento c where c.publicado = 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        echo json_encode($rows);
    } else {
        echo json_encode([]);
    }

    $conn->close();
    ?>
