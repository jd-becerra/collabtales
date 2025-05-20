<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("POST");
// ESTA RUTA NO USA JWT
include('config.php');
include("rate_limit.php");

// Primero, checar que no se exceda de 5 peticiones por minuto
$ip = $_SERVER['REMOTE_ADDR'];
$endpoint_name = "generar_token_restauracion";
$limit = 5;
$interval_seconds = 60; // 1 minuto
if (is_rate_limited($conn, $endpoint_name, $ip, $limit, $interval_seconds)) {
    echo json_encode(["error" => "Demasiadas peticiones. Intenta de nuevo más tarde."]);
    exit;
}


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

// Obtener el último token generado para este usuario y verificar si ha pasado el tiempo de espera
$stmt = $conn->prepare("SELECT expiracion FROM TokenRestauracion WHERE fk_alumno = ? ORDER BY expiracion DESC LIMIT 1");
$stmt->bind_param("i", $id_alumno);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($last_token_time);
$stmt->fetch();
$stmt->close();

$current_time = time();
$cooldown = 60; // 1 minuto

// Si el último token fue generado hace menos de 1 minuto, rechazamos la solicitud
if ($last_token_time && ($current_time - $last_token_time < $cooldown)) {
    echo json_encode(["error" => "Debes esperar 1 minuto antes de solicitar otro token. Revisa tu correo."]);
    exit;
}

// Eliminar tokens anteriores
$stmt = $conn->prepare("DELETE FROM TokenRestauracion WHERE fk_alumno = ?");
$stmt->bind_param("i", $id_alumno);
$stmt->execute();
$stmt->close();

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


// Usar PHPMailer para enviar el correo con gmail
$rdir = str_replace("\\", "/", __DIR__);  // Root Dir
require_once $rdir . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once $rdir . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once $rdir . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

// Usar gmail para enviar el correo
$mail->IsSMTP(); // telling the class to use SMTP
$mail->SMTPAuth = true; // enable SMTP authentication
$mail->SMTPSecure = "ssl"; // sets the prefix to the servier
$mail->Host = "smtp.gmail.com"; // sets GMAIL as the SMTP server
$mail->Port = 465; // set the SMTP port for the GMAIL server
$mail->Username = getenv("SMTP_USER");
$mail->Password = getenv("SMTP_PASS");

$mail->setFrom($from, 'Collabtales');
$mail->addAddress($to);  // Add the recipient's email address
$mail->addReplyTo($reply_to);
$mail->Subject = $subject;
$mail->Body = $message;


// TODO: Eliminar token de este mensaje en producción
try {
    $mail->send();
    echo json_encode(["success" => "Se ha enviado un enlace a tu correo para restablecer tu contraseña. Link: $restore_link"]);
} catch (Exception $e) {
    var_dump(getenv("SMTP_PASS"));
    echo json_encode(["error" => "Error al enviar el correo: " . $mail->ErrorInfo. " Link: $restore_link"]);
    exit;  // Stop further execution after failure
}

$conn->close();
?>