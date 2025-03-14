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