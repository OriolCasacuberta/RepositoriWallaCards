<?php

function generarPassResetCode($email)
{
    require('./web/connecta_db_persistent.php');
    
    if (empty($email)) 
    {
        return "Error: El correo electrónico está vacío.";
    }

    // Verificar si el correo electrónico está registrado
    $sqlCheck = 'SELECT mail FROM users WHERE mail = :email';
    $stmt = $db->prepare($sqlCheck);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        return 'Error: No se encuentra el correo electrónico en nuestra base de datos.';
    }

    // Generar un código único y seguro para el restablecimiento de la contraseña
    $passCode = bin2hex(random_bytes(16));  // Código más seguro

    try
    {
        // Actualizar el código de activación y la fecha de expiración en la base de datos
        $sql = "UPDATE users SET resetPassCode = :passCode, resetPassExpiry = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE mail = :email";
    
        $update = $db->prepare($sql);
        $update->bindParam(':email', $email, PDO::PARAM_STR);
        $update->bindParam(':passCode', $passCode, PDO::PARAM_STR);
    
        if ($update->execute()) {
            // Retornar el código generado para que pueda ser usado en el correo
            return $passCode;
        } else {
            return 'Error: No se ha podido actualizar el código de restablecimiento.';
        }
    }
    
    catch (PDOException $e)
    {
        return "Error en la base de datos: " . $e->getMessage();
    }
}

?>