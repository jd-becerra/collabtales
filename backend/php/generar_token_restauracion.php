<?php
include('cors_headers.php');
include('config.php');

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['correo'] ?? null;

if (empty($email)) {
    echo json_encode(["error" => "El correo es obligatorio"]);
    exit;
}

// Validar y sanitizar correo
$email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
if (!$email) {
    echo json_encode(["error" => "Correo inválido"]);
    exit;
}

// Obtener usuario con el correo proporcionado
$stmt = $conn->prepare("SELECT id_alumno FROM Alumno WHERE correo = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($id_alumno);
$stmt->fetch();
$stmt->close();

if (!$id_alumno) {
    echo json_encode(["error" => "No se encontró un usuario con ese correo"]);
    exit;
}

// Utilizar BCRYPT para generar un token seguro
$token = bin2hex(random_bytes(32));
$hashed_token = password_hash($token, PASSWORD_BCRYPT);
$expiracion = time() + 3600;

$stmt = $conn->prepare("INSERT INTO TokenRestauracion (token, expiracion, fk_alumno)
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE token = ?, expiracion = ?");
$stmt->bind_param("sssss", $hashed_token, $expiracion, $id_alumno, $hashed_token, $expiracion);
$stmt->execute();
$stmt->close();

// Como ya validamos que el correo existe, podemos usarlo para enviar un correo con el token
$to = $email;
$subject = 'Restablecer contraseña en Collabtales';
// El link debe contener el token generado y el correo del usuario
$restore_link = "http://localhost:5173/restaurar_contrasena?token=$token&correo=$email";
$message = "Para restablecer tu contraseña, haz clic en el siguiente enlace: $restore_link. Expira en 1 hora. Si no solicitaste restablecer tu contraseña, ignora este mensaje.";
$from = getenv("SMTP_USER");
$reply_to = $from;

$headers = "From: $from" . "\r\n" .
    "Reply-To: $reply_to" . "\r\n" .
    "X-Mailer: PHP/" . phpversion();

mail($to, $subject, $message, $headers);

// TODO: Eliminar token de este mensaje en producción
echo json_encode(["message" => "Se ha enviado un enlace a tu correo para restablecer tu contraseña. Link: $restore_link"]);

$conn->close();
?>