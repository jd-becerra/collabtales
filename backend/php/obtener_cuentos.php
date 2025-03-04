    <?php
    include('cors_headers.php');
    include('config.php');

    if (!isset($_GET['id_alumno'])) {
        echo json_encode(["error" => "Invalid request: id_alumno is missing"]);
        exit;
    }

    $id_alumno = intval($_GET['id_alumno']);

    $sql = "CALL ListarCuentosAlumno($id_alumno)";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch all rows and store in an array
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        // Return the array as JSON
        echo json_encode($rows);
    } else {
        echo json_encode([]); // Return an empty array if no data is found
    }

    $conn->close();
    ?>
