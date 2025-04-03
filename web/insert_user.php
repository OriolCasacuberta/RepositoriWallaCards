<?php

function insertarUsuari($email, $username, $password, $firstName, $lastName)
{
    try
    {
        require('./connecta_db_persistent.php');
        // Insertar el usuario en la base de datos con el estado 'inactive' (active = 0)
        $sql = "INSERT INTO users (mail, username, passHash, userFirstName, userLastName, creationDate, active) 
                VALUES (:email, :username, :passHash, :firstName, :lastName, NOW(), 0)";
        
        $insert = $db->prepare($sql);

        $insert->bindParam(':email', $email);
        $insert->bindParam(':username', $username);
        $hashGenerat = password_hash($password, PASSWORD_DEFAULT); // Encriptación de la contraseña
        $insert->bindParam(':passHash', $hashGenerat);
        $insert->bindParam(':firstName', $firstName);
        $insert->bindParam(':lastName', $lastName);

        if ($insert->execute()) 
        {
            // Si la inserción fue exitosa, retornar mensaje o continuar con la activación
            return "Usuari creat amb èxit!";
        }
    }
    catch (PDOException $e)
    {
        echo "Error en la base de dades: " . $e->getMessage();
    }
}
?>
