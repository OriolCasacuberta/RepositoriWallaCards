<?php

    // session_start();
    include 'functions.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $username       = $_POST['username'];
        $email          = $_POST['email'];
        $firstName      = $_POST['first_name'];
        $lastName       = $_POST['last_name'];
        $password       = $_POST['password'];
        $verifyPassword = $_POST['verify_password'];

        if ($password === $verifyPassword)
        {
            $result = registrarUsuari($username, $email, $firstName, $lastName, $password);
            $_SESSION['register_message'] = $result;
            
            header("Location: register.php");
            exit();
        }
        
        else
        {
            $_SESSION['register_message'] = "Les contrasenyes no coincideixen";
            header("Location: register.php");
            exit();
        }
    }
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registre - WallaCards</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/register.css">
</head>
<body>
    <div class="contenidorLogin">
        <img src="../img/WallaCards.png" alt="Logo de WallaCards" class="logo">

        <form action="register.php" method="POST">
            <input type="text" name="username" placeholder="Nom d'usuari" required>
            <input type="email" name="email" placeholder="Correu electrònic" required>
            <input type="text" name="first_name" placeholder="Nom">
            <input type="text" name="last_name" placeholder="Cognom">
            <input type="password" name="password" placeholder="Contrasenya" required>
            <input type="password" name="verify_password" placeholder="Verifica la contrasenya" required>
            <button type="submit">Registra't</button>
        </form>

        <p class="register-link">Ja tens un compte? <a href="../index.php">Inicia sessió aquí</a></p>
    </div>
</body>
</html>