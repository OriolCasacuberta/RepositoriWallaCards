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

?>
