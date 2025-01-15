<?php
// Iniciar la sessió
session_start();

// Requereix el fitxer de connexió persistent
require_once ('connecta_bd_persisten.php');

// Comprovar si la connexió s'ha establert correctament
if ($db) {
    // Si la connexió s'ha establert correctament
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        // Recuperar valors del formulari
        $username = '';
        $password = '';

        // Comprovar si s'ha enviat el camp 'username'
        if (isset($_POST['username'])) { $username = $_POST['username']; }

        // Comprovar si s'ha enviat el camp 'password'
        if (isset($_POST['password'])) { $password = $_POST['password']; }


        // Si els camps no estan buits
        if (!empty($username))
        {
            if (!empty($password))
            {
                // Preparar la consulta per verificar l'usuari
                $stmt = $db->prepare("SELECT idUser, username, passHash, userFirstName, active 
                FROM users WHERE username = :username AND active = 1 ");
                
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Si s'ha trobat l'usuari i la contrasenya és correcta
                if ($user) {
                    if (password_verify($password, $user['passHash'])) {
                        // Actualitzar la data de l'última sessió iniciada
                        $updateStmt = $db->prepare("UPDATE users SET lastSignIn = NOW() WHERE idUser = :idUser");
                        $updateStmt->bindParam(':idUser', $user['idUser'], PDO::PARAM_INT);
                        $updateStmt->execute();

                        // Guardar dades a la sessió
                        $_SESSION['user'] = [
                            'id' => $user['idUser'],
                            'username' => $user['username'],
                            'name' => $user['userFirstName']
                        ];

                        // Redirigir a la pàgina principal
                        header('Location: dashboard.php');
                        exit;
                    } else {
                        // Si la contrasenya no és correcta
                        $error = "Contrasenya incorrecta.";
                    }
                } else {
                    // Si l'usuari no existeix o no està actiu
                    $error = "Usuari no trobat o no actiu.";
                }
            } else {
                // Si la contrasenya està buida
                $error = "Si us plau, introdueix una contrasenya.";
            }
        } else {
            // Si el nom d'usuari està buit
            $error = "Si us plau, introdueix un nom d'usuari.";
        }
    }
} else {
    // Si no s'ha pogut establir la connexió a la base de dades
    die("No s'ha pogut establir la connexió a la base de dades.");
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sessió</title>
    <link rel="stylesheet" href="/web/style.css">
</head>
<body>
    <div class="login-container">
        <h1>Iniciar Sessió</h1>
        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Nom d'usuari" required>
            <input type="password" name="password" placeholder="Contrasenya" required>
            <button type="submit">Inicia Sessió</button>
        </form>
    </div>
</body>
</html>
