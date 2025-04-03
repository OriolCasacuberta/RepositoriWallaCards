<?php

function registrarUsuari($username, $email, $firstName, $lastName, $password)
{
    require('insert_user.php');
    insertarUsuari($email, $username, $password, $firstName, $lastName);
    require_once('generarCodiActivacio.php');

    // Crear el código de activación y enviar el correo
    crearCodiActivacio($email);

    return 'Usuari creat amb èxit';
}

function crearCodiActivacio($email)
{
    // Llama al archivo generarCodiActivacio.php para generar el código
    require_once('generarCodiActivacio.php');
}

function enviarMailVerificacio($email, $activationCode)
{
    // Aquí deberías incluir el código de mail para enviar el enlace de activación
    require_once('mailActivacio.php');
}

function comprovarEmail($usernamemail)
{
    require('./web/connecta_db_persistent.php'); // Incluir la conexión a la base de datos

    if (!$db)
    {
        return false; // Retorna false si la conexión a la base de datos falla
    }

    if (filter_var($usernamemail, FILTER_VALIDATE_EMAIL))
    {
        $sql = 'SELECT mail FROM users WHERE mail = :email';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $usernamemail, PDO::PARAM_STR);
    } 
    
    else
    {
        $sql = 'SELECT mail FROM users WHERE username = :username';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':username', $usernamemail, PDO::PARAM_STR);
    }

    $stmt->execute();
    $result = $stmt->fetchColumn(); // Obtiene solo el primer resultado

    return $result ?: false; // Si no hay resultado, devuelve false en lugar de una cadena vacía
}

function crearCodiPassword($email)
{
    require('generarCodiContrasenya.php');
    return generarPassResetCode($email);
}

function enviarMailCanviarContrasenya($email, $code)
{
    // Pasar el correo y el código al script de envío
    $_GET['email'] = $email;
    $_GET['code'] = $code;
    
    // Capturar la salida del script
    ob_start();
    $result = require(__DIR__ . '/resetPasswordSend.php');
    $output = ob_get_clean();
    
    // Devolver el resultado
    return $result ? true : $output;
}

?>