<?php
use PHPMailer\PHPMailer\PHPMailer;
require '../vendor/autoload.php';

// Verifica si se recibieron los parámetros
if (!isset($_GET['email']) || !isset($_GET['code'])) {
    die("Error: Falta información para enviar el correo.");
}

$email = $_GET['email'];
$activationCode = $_GET['code'];

// URL del script de verificación con el código de activación
$activationLink = "http://localhost/Projecte/RepositoriWallaCards/index.php?activationCode=$activationCode&email=" . urlencode($email);

$mail = new PHPMailer();
$mail->IsSMTP();
$mail->SMTPDebug = 0;
$mail->SMTPAuth = true;
$mail->SMTPSecure = 'tls';
$mail->Host = 'smtp.gmail.com';
$mail->Port = 587;
$mail->Username = 'oriol.casacubertat@educem.net';
$mail->Password = 'oolk jagu sbbm gpvr';
$mail->isHTML(true);
$mail->CharSet = 'UTF-8';
$mail->AddEmbeddedImage('../img/WallaCards.png', 'logoWallaCards');

$htmlMessage = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Activació del compte</title>
    </head>
    <body>
    <img src='cid:logoWallaCards' alt='WallaCards' width='150'>
    <h2>Benvingut a WallaCards!</h2>
    <p>Si us plau, activa el teu compte fent clic al següent enllaç:</p>
    <p><a href='$activationLink' style='display:inline-block;padding:10px 20px;color:white;background:#007BFF;text-decoration:none;border-radius:5px;'>Activa el teu compte ara!</a></p>
    <br>
    <p>Si no has sol·licitat aquest correu, ignora'l.</p>
    </body>
    </html>
";

$mail->SetFrom('oriol.casacubertat@educem.net', 'WallaCards');
$mail->Subject = 'Activació del compte a WallaCards';
$mail->MsgHTML($htmlMessage);
$mail->AddAddress($email);

// Envío del correo
if ($mail->Send()) {
    echo "Correu d'activació enviat a $email";
    header('Location: ../index.php');
} else {
    echo "Error en l'enviament: " . $mail->ErrorInfo;
}
?>
