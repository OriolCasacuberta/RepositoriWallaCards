<?php
session_start();

// Si el usuario ya está logueado, lo redirige al home
if (isset($_SESSION['user'])) {
    header('Location: ./web/home.php');
    exit;
}

// Procesar activación de cuenta
if (isset($_GET['activationCode'], $_GET['email'])) {
    require_once('./web/connecta_db_persistent.php');

    $activationCode = $_GET['activationCode'];
    $email = $_GET['email']; // No es necesario urldecode()

    if ($db) {
        $stmt = $db->prepare("SELECT idUser, activationCode, username, mail, passHash, userFirstName FROM users WHERE mail = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['activationCode'] === $activationCode) {
            $updateStmt = $db->prepare("UPDATE users SET active = 1, activationDate = CURRENT_TIMESTAMP() WHERE idUser = :idUser");
            $updateStmt->bindParam(':idUser', $user['idUser'], PDO::PARAM_INT);
            
            if ($updateStmt->execute()) {
                $_SESSION['user'] = [
                    'id' => $user['idUser'],
                    'username' => $user['username'],
                    'name' => $user['userFirstName']
                ];
                header('Location: ./web/home.php');
                exit;
            } else {
                $error = "Error al activar el compte.";
            }
        } else {
            $error = "Codi d'activació invàlid o usuari inexistent.";
        }
    } else {
        die("Error de connexió a la base de dades.");
    }
}

// Procesar inicio de sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once('./web/connecta_db_persistent.php');

    if ($db) {
        $identifier = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!empty($identifier) && !empty($password)) {
            $stmt = $db->prepare("SELECT idUser, username, mail, passHash, userFirstName, active FROM users
                                WHERE (username = :identifier OR mail = :identifier) AND active = 1");
            $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['passHash'])) {
                $updateStmt = $db->prepare("UPDATE users SET lastSignIn = CURRENT_TIMESTAMP() WHERE idUser = :idUser");
                $updateStmt->bindParam(':idUser', $user['idUser'], PDO::PARAM_INT);
                $updateStmt->execute();

                $_SESSION['user'] = [
                    'id' => $user['idUser'],
                    'username' => $user['username'],
                    'name' => $user['userFirstName']
                ];

                setcookie('username', $user['username'], time() + 3600, '/', '', isset($_SERVER['HTTPS']), true);

                header('Location: ./web/home.php');
                exit;
            } else {
                $error = "Credencials incorrectes.";
            }
        } else {
            $error = "Les dades facilitades són incorrectes.";
        }
    } else {
        die("Error de connexió a la base de dades.");
    }
}

// Si el usuario solicitó restablecer la contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usernamemail'])) {
    include './web/functions.php';

    $usernamemail = $_POST['usernamemail'];
    $email = comprovarEmail($usernamemail);

    crearCodiPassword($email);
    enviarMailCanviarContrasenya($email);
}
?>


<!DOCTYPE html>
<html lang="ca">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Iniciar Sessió - WallaCards</title>
        <link rel="stylesheet" href="./css/style.css">
        <link rel="stylesheet" href="./css/index.css">
    </head>
    <body>
        <div class="rotating-images">
            <div class="image-container">
                <img src="./img/Charizard101.png" alt="Imatge 1-1" class="rotating-image">
                <img src="./img/PKMReverse.png" alt="Imatge 1-2" class="rotating-image">
            </div>
        </div>

        <div class="rotating-images">
            <div class="image-container">
                <img src="./img/TigerShark.png" alt="Imatge 2-1" class="rotating-image">
                <img src="./img/TigerSharkQR.png" alt="Imatge 2-2" class="rotating-image">
            </div>
        </div>

        <div class="rotating-images">
            <div class="image-container">
                <img src="./img/SnapShot.png" alt="Imatge 3-1" class="rotating-image">
                <img src="./img/SnapShotBack.png" alt="Imatge 3-2" class="rotating-image">
            </div>
        </div>

        <div class="rotating-images">
            <div class="image-container">
                <img src="./img/Nathan.png" alt="Imatge 4-1" class="rotating-image">
                <img src="./img/NathanBack.png" alt="Imatge 4-2" class="rotating-image">
            </div>
        </div>

        <div class="contenidorLogin">
            <img src="./img/WallaCards.png" alt="Logo WallaCards" class="logo">

            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="text" name="username" placeholder="Nom d'usuari" required>
                <input type="password" name="password" placeholder="Contrasenya" required>
                <button type="submit">Inicia Sessió</button>
            </form>

            <p class="register-link">
                No tens un compte? <a href="./web/register.php">Registra't aquí</a>
            </p>

            <p id="forgotPassword" class="small-text">Has oblidat la contrasenya?</p>
            <div id="overlay" class="overlay"></div>

            <div id="popup" class="popup">
                <form method="POST" action="">
                    <label id="labelRPass" for="usernamemail">Introduiex el nom d'usuari o el correu</label>
                    <input type="text" id="usernamemail" name="usernamemail" placeholder="Nom d'usuari o correu" required>
                    <button id="sendResetPasswordButton">Reestablir contrasenya</button>
                    <button id="closePopupButton">Cerrar</button>
                </form>
            </div>




        </div>

        <script src="./js/index.js"></script>
    </body>
</html>
