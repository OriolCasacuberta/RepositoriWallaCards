<?php

    require_once ('./connecta_db_persistent.php');

    try
    {
        $sql = "INSERT INTO users (mail, username, passHash, userFirstName, userLastName, creationDate, active) 
                VALUES (:email, :username, :passHash, :firstName, :lastName, NOW(), 1)";

        echo $sql;
            
        $insert = $db->prepare($sql);

        $insert->bindParam(':email', $email);
        $insert->bindParam(':username', $username);
        $hashGenerat = password_hash($password, PASSWORD_DEFAULT);
        $insert->bindParam(':passHash', $hashGenerat);
        $insert->bindParam(':firstName', $firstName);
        $insert->bindParam(':lastName', $lastName);

        if ($insert->execute())
        {
            return "Usuari creat amb èxit!";
        }
    }
        
    catch (PDOException $e)
    {
        echo $e->getMessage();
        $res = "Error amb la base de dades: " . $e->getMessage();
        echo $res;
    }
?>