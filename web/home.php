<?php
    // Iniciar la sesión
    session_start();

    // Comprobar si el usuario ha iniciado sesión
    if (!isset($_SESSION['user']))
    {
        // Si no hay una sesión activa, redirigir al inicio de sesión
        header('Location: index.php');
        exit;
    }

    // Obtener el username de la sesión
    $username = htmlspecialchars($_SESSION['user']['username']);
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benvingut - WallaCards</title>
    <link rel="stylesheet" href="./css/home.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <div class="container">
        <img src="./img/WallaCards.png" alt="Logo WallaCards" class="logo">
        <h1>Benvingut, <?php echo $username; ?>!</h1>
        <p>Estem contents de tenir-te aquí. Gaudeix de l'experiència WallaCards.</p>
        <a href="logout.php" class="logout-button">Tancar sessió</a>
    </div>
</body>
</html>