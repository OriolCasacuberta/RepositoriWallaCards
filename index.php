<?php

    session_start();

    // Verificar si ya hay un usuario registrado en la sesión
    if (isset($_SESSION['user'])) {
        header('Location: ./web/home.php');
        exit;
    }

    // Si hay parámetros de activación, procesarlos
    if (isset($_GET['activationCode']) && isset($_GET['email'])) {
        require_once('./web/connecta_db_persistent.php');
        $activationCode = $_GET['activationCode'];
        $email = urldecode($_GET['email']);

        if ($db) {
            // Verificar si el código de activación y el email coinciden con algún registro
            $stmt = $db->prepare("SELECT idUser, activationCode, username, mail, passHash, userFirstName FROM users WHERE mail = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Verificar si el código de activación es correcto
                if ($user['activationCode'] == $activationCode) {
                    $updateStmt = $db->prepare("UPDATE users SET active = 1, activationCode = NULL, activationDate = NOW() WHERE idUser = :idUser");
                    $updateStmt->bindParam(':idUser', $user['idUser'], PDO::PARAM_INT);
                    
                    if ($updateStmt->execute()) {
                        // Iniciar sesión automáticamente después de la activación
                        $_SESSION['user'] = [
                            'id' => $user['idUser'],
                            'username' => $user['username'],
                            'name' => $user['userFirstName']
                        ];

                        // Redirigir al usuario a la página principal
                        header('Location: ./web/home.php');
                        exit;
                    } else {
                        echo "Error al activar el compte.";
                    }
                } else {
                    echo "Codi d'activació invàlid.";
                }
            } else {
                echo "No s'ha trobat cap usuari amb aquest correu.";
            }
        } else {
            die("No s'ha pogut establir la connexió a la base de dades.");
        }
        exit; // Detener el flujo si es un proceso de activación
    }

    // Procesar el inicio de sesión
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once('./web/connecta_db_persistent.php');

        if ($db) {
            $identifier = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (!empty($identifier) && !empty($password)) {
                // Verificar si el nombre de usuario o email existe y está activo
                $stmt = $db->prepare("SELECT idUser, username, mail, passHash, userFirstName, active FROM users
                                    WHERE (username = :identifier OR mail = :identifier) AND active = 1");
                $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['passHash'])) {
                    // Registrar el último inicio de sesión
                    $updateStmt = $db->prepare("UPDATE users SET lastSignIn = NOW() WHERE idUser = :idUser");
                    $updateStmt->bindParam(':idUser', $user['idUser'], PDO::PARAM_INT);
                    $updateStmt->execute();

                    // Iniciar sesión y guardar información del usuario
                    $_SESSION['user'] = [
                        'id' => $user['idUser'],
                        'username' => $user['username'],
                        'name' => $user['userFirstName']
                    ];

                    setcookie('username', $user['username'], time() + 3600, '/', '', true, true);

                    // Redirigir al usuario a la página principal
                    header('Location: ./web/home.php');
                    exit;
                } else {
                    $error = "L'usuari o la contrasenya no són correctes";
                }
            } else {
                $error = "No és possible iniciar sessió amb les dades facilitades.";
            }
        } else {
            die("No s'ha pogut establir la connexió a la base de dades.");
        }
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
    </div>
</body>
</html>
