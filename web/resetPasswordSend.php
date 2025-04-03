<?php
use PHPMailer\PHPMailer\PHPMailer;

require __DIR__ . '/../vendor/autoload.php';

$PASSWORD_RESET_URL = "http://localhost/Projecte/RepositoriWallaCards/web/canviarContrasenya.php";
$PASSWORD_RESET_EXPIRY = 30; // minutos

// Definir la URL base para redireccionar correctamente
$BASE_URL = "http://localhost/Projecte/RepositoriWallaCards/"; // Ajusta esto según tu estructura

if (!isset($_GET['email']) || !isset($_GET['code'])) {
    die("Error: Falta informació per enviar el correu.");
}

$email = $_GET['email'];
$code = $_GET['code'];
$resetLink = "$PASSWORD_RESET_URL?code=" . urlencode($code) . "&email=" . urlencode($email);

$mail = new PHPMailer();

$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->SMTPAuth = true;
$mail->SMTPSecure = 'tls';
$mail->Host = 'smtp.gmail.com';
$mail->Port = 587;
$mail->Username = 'oriol.casacubertat@educem.net';
$mail->Password = 'oolk jagu sbbm gpvr'; // Contraseña corregida
$mail->isHTML(true);
$mail->CharSet = 'UTF-8';

$mail->setFrom('oriol.casacubertat@educem.net', 'WallaCards');
$mail->addAddress($email);
$mail->Subject = 'Restablir contrasenya - WallaCards';

// Verificar si la imagen existe antes de intentar añadirla
$logoPath = '../img/LogoWallaCards.png';
$alternateLogoPath = '../img/WallaCards.png';

if (file_exists($logoPath)) {
    $mail->AddEmbeddedImage($logoPath, 'logoWallaCards');
} elseif (file_exists($alternateLogoPath)) {
    $mail->AddEmbeddedImage($alternateLogoPath, 'logoWallaCards');
} else {
    // Si no existe ninguna de las dos imágenes, podríamos dejar un mensaje en el log
    error_log("Logo files not found: $logoPath or $alternateLogoPath");
}

$htmlMessage = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Canvi de contrasenya</title>
    </head>
    <body>
    <img src='cid:logoWallaCards' alt='WallaCards' width='150'>
    <h2>Solicitud de canvi de contrasenya</h2>
    <p>Si has demanat canviar la teva constrasenya, fes clic a l'enllaç:</p>
    <p><a href='$resetLink' style='display:inline-block;padding:10px 20px;color:white;background:#007BFF;text-decoration:none;border-radius:5px;'>Canviar la meva contrasenya</a></p>
    <p>Aquest enllaç caducarà en $PASSWORD_RESET_EXPIRY minuts.</p>
    <br>
    <p>Si no has sol·licitat aquest correu, ignora'l.</p>
    </body>
    </html>
";

// Usar MsgHTML en lugar de Body directamente
$mail->MsgHTML($htmlMessage);

if ($mail->Send()) {
    echo "S'ha enviat un correu per restablir la contrasenya. Comprova la teva safata d'entrada.";
    
    // Solución 1: Usar URL absoluta en lugar de relativa
    header("refresh:5;url=" . $BASE_URL . "index.php");
    // Solución alternativa: Si lo anterior no funciona, usa la ruta completa del servidor
    // header("Location: /Projecte/RepositoriWallaCards/index.php");
    
    return true;
} else {
    echo "Error en l'enviament: " . $mail->ErrorInfo;
    return false;
}