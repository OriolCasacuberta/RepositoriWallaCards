<?php
// Iniciar la sessió
session_start();

// Requereix el fitxer de connexió persistent
require_once('./web/connecta_db_persistent.php');

// Comprovar si la connexió s'ha establert correctament
if ($db) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = '';
        $password = '';

        if (isset($_POST['username'])) {
            $username = $_POST['username'];
        }
        if (isset($_POST['password'])) {
            $password = $_POST['password'];
        }

        if (!empty($username)) {
            if (!empty($password)) {
                $stmt = $db->prepare("SELECT idUser, username, passHash, userFirstName, active 
                FROM users WHERE username = :username AND active = 1");
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    if (password_verify($password, $user['passHash'])) {
                        $updateStmt = $db->prepare("UPDATE users SET lastSignIn = NOW() WHERE idUser = :idUser");
                        $updateStmt->bindParam(':idUser', $user['idUser'], PDO::PARAM_INT);
                        $updateStmt->execute();

                        $_SESSION['user'] = [
                            'id' => $user['idUser'],
                            'username' => $user['username'],
                            'name' => $user['userFirstName']
                        ];

                        header('Location: dashboard.php');
                        exit;
                    } else {
                        $error = "Contrasenya incorrecta.";
                    }
                } else {
                    $error = "Usuari no trobat o no actiu.";
                }
            } else {
                $error = "Si us plau, introdueix una contrasenya.";
            }
        } else {
            $error = "Si us plau, introdueix un nom d'usuari.";
        }
    }
} else {
    die("No s'ha pogut establir la connexió a la base de dades.");
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sessió</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <!-- Contenedor de imagen giratoria -->
    <div class="rotating-images">
        <div class="image-container">
            <img src="./img/Charizard101.png" alt="Image 1" class="rotating-image">
            <img src="./img/PKMReverse.png" alt="Image 2" class="rotating-image">
        </div>
    </div>

    <!-- Formulario de inicio de sesión -->
    <div class="contenidorLogin">
        <img src="./img/WallaCards.png" alt="Logo WallaCards" class="logo">

        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
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
