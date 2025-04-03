<?php
// Conexión a la base de datos
require('../web/connecta_db_persistent.php');

if (isset($_GET['email']) && isset($_GET['code'])) {
    $email = $_GET['email'];
    $passCode = $_GET['code'];

    // Verificar si el código de restablecimiento es válido
    $sqlCheck = 'SELECT resetPassCode, resetPassExpiry FROM users WHERE mail = :email';
    $stmt = $db->prepare($sqlCheck);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $expiry = $row['resetPassExpiry'];
        $storedCode = $row['resetPassCode'];
        
        // Verificar si el código es correcto
        if ($storedCode !== $passCode) {
            $error = "El codi de restabliment no és vàlid.";
        }
        // Verificar si el código ha expirado
        else if (new DateTime() > new DateTime($expiry)) {
            $error = "El codi de restabliment ha caducat. Sol·licita'n un de nou.";
        }
        else {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $newPassword = $_POST['newPassword'];
                $confirmPassword = $_POST['confirmPassword'];

                if ($newPassword == $confirmPassword) {
                    // Actualizar la contraseña en la base de datos
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                    $sqlUpdate = "UPDATE users SET passHash = :password, resetPassCode = NULL, resetPassExpiry = NULL WHERE mail = :email";
                    $update = $db->prepare($sqlUpdate);
                    $update->bindParam(':email', $email, PDO::PARAM_STR);
                    $update->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

                    if ($update->execute()) {
                        $success = "La contrasenya ha estat actualitzada correctament.";
                        // Redirigir a la página de inicio después de 3 segundos
                        header("refresh:3;url=../index.php");
                    } else {
                        $error = "Hi ha hagut un error en actualitzar la contrasenya.";
                    }
                } else {
                    $error = "Les contrasenyes no coincideixen.";
                }
            }
        }
    } else {
        $error = "El correu electrònic o el codi no són vàlids.";
    }
} else {
    $error = "No s'han proporcionat paràmetres vàlids.";
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canviar Contrasenya - WallaCards</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/canviarContrasenya.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <img src="../img/WallaCards.png" alt="Logo WallaCards" class="logo">
            
            <h2>Canviar Contrasenya</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (!isset($error) || (isset($error) && $error == "Les contrasenyes no coincideixen.")): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="newPassword">Nova Contrasenya:</label>
                        <input type="password" id="newPassword" name="newPassword" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Confirmar Contrasenya:</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                    </div>

                    <button type="submit" class="btn-submit">Actualitzar Contrasenya</button>
                </form>
            <?php endif; ?>
            
            <div class="back-link">
                <a href="../index.php">Tornar a la pàgina d'inici</a>
            </div>
        </div>
    </div>
</body>
</html>