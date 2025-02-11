<?php

function generarCodiActivacio($email)
{
    require('./connecta_db_persistent.php');
    
    // Verificar que el correo no esté vacío
    if (empty($email)) 
    {
        return "Error: El correo electrónico está vacío.";
    }

    // Generar un código de activación aleatorio usando SHA-256
    $activationCode = hash('sha256', uniqid(mt_rand(), true));

    try
    {
        // Actualizar el código de activación en la base de datos para el usuario
        $sql = "UPDATE users SET activationCode = :activationCode WHERE mail = :email";
        
        $update = $db->prepare($sql);
        $update->bindParam(':email', $email);
        $update->bindParam(':activationCode', $activationCode);

        if ($update->execute()) 
        {
            // Retornar el código de activación generado si la actualización fue exitosa
            return $activationCode;
        } 

    }
    catch (PDOException $e)
    {
        return "Error en la base de datos: " . $e->getMessage();
    }
}

// Llamada a la función para generar el código de activación (esto puede ser invocado desde otros archivos)
if (isset($_POST['email']))
{
    $email = $_POST['email'];
    $result = generarCodiActivacio($email);

    if (strpos($result, "Error") === false) 
    {
        // Si no hay error, redirigir a mailActivacio.php con el código de activación generado
        header("Location: mailActivacio.php?email=" . urlencode($email) . "&code=" . urlencode($result));
        exit(); // Aquí está el exit(), ya que es más apropiado en este lugar.
    }
    else 
    {
        echo $result; // Mostrar el error si hubo alguno
    }
}

?>
