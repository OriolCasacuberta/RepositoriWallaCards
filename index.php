<?php
    session_start();

    // Conexión a la base de datos
    require_once ('./web/connecta_db_persistent.php');

    if ($db)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST')
        {
            $identifier = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (!empty($identifier) && !empty($password))
            {
                // Buscar usuario activo por username o email
                $stmt = $db->prepare("SELECT idUser, username, mail, passHash, userFirstName, active FROM users
                                    WHERE (username = :identifier OR mail = :identifier) AND active = 1");

                $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['passHash']))
                {
                    // Actualizar lastSignIn
                    $updateStmt = $db->prepare("UPDATE users SET lastSignIn = NOW() WHERE idUser = :idUser");
                    $updateStmt->bindParam(':idUser', $user['idUser'], PDO::PARAM_INT);
                    $updateStmt->execute();

                    // Crear sesión y cookies
                    $_SESSION['user'] = [
                        'id' => $user['idUser'],
                        'username' => $user['username'],
                        'name' => $user['userFirstName']
                    ];

                    setcookie('username', $user['username'], time() + 3600, '/', '', true, true);

                    // Redirigir a home.php
                    header('Location: ./web/home.php');
                    exit;
                }
                
                else { $error = "L'usuari o la contrasenya no son correctes"; }
            }
            
            else { $error = "No és possible iniciar sessió amb les dades facilitades."; }
        }

    } 
    
    else { die("No s'ha pogut establir la connexió a la base de dades."); }
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
    <!-- Primera imagen giratoria -->
    <div class="rotating-images">
        <div class="image-container">
            <img src="./img/Charizard101.png" alt="Imatge 1-1" class="rotating-image">
            <img src="./img/PKMReverse.png" alt="Imatge 1-2" class="rotating-image">
        </div>
    </div>

    <!-- Segunda imagen giratoria -->
    <div class="rotating-images">
        <div class="image-container">
            <img src="./img/TigerShark.png" alt="Imatge 2-1" class="rotating-image">
            <img src="./img/TigerSharkQR.png" alt="Imatge 2-2" class="rotating-image">
        </div>
    </div>

    <!-- Tercera imagen giratoria (opcional) -->
    <div class="rotating-images">
        <div class="image-container">
            <img src="./img/SnapShot.png" alt="Imatge 3-1" class="rotating-image">
            <img src="./img/SnapShotBack.png" alt="Imatge 3-2" class="rotating-image">
        </div>
    </div>

    <!-- Tercera imagen giratoria (opcional) -->
    <div class="rotating-images">
        <div class="image-container">
            <img src="./img/Nathan.png" alt="Imatge 4-1" class="rotating-image">
            <img src="./img/NathanBack.png" alt="Imatge 4-2" class="rotating-image">
        </div>
    </div>

    <!-- Formulario de inicio de sesión -->
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
