<?php
function is_rate_limited($conn, $endpoint_name, $ip, $limit, $interval_seconds) {
    $stmt = $conn->prepare("CALL ObtenerRateLimitRecientes(?, ?, ?)");
    $stmt->bind_param("ssi", $endpoint_name, $ip, $interval_seconds);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    // Si el numero de llamadas es >= al limite, se regresa true
    if ($count >= $limit) {
        return true;
    }

    // Si no, se registra la llamada
    $now = time();
    $stmt = $conn->prepare("CALL InsertarRateLimit(?, ?, ?)");
    $stmt->bind_param("ssi", $endpoint_name, $ip, $now);
    $stmt->execute();
    $stmt->close();

    return false;
}
?>