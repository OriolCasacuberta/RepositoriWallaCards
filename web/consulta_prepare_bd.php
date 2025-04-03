<?php

    require_once 'connecta_bd_persistent.php';

    try
    {
        $stmt = $db->prepare("SELECT idUser, username, userFirstName, active FROM users WHERE active = 1");
        $stmt->execute();
        $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($resultats)
        {
            echo '<h2>Llista d\'usuaris actius:</h2>';
            echo '<ul>';

            foreach ($resultats as $usuari)
            {
                echo '<li>ID: ' . htmlspecialchars($usuari['idUser']) .
                    ' - Nom d\'usuari: ' . htmlspecialchars($usuari['username']) .
                    ' - Nom complet: ' . htmlspecialchars($usuari['userFirstName']) . '</li>';
            }

            echo '</ul>';
        } 
        
        else 
        {
            echo '<p>No hi ha usuaris actius.</p>';
        }
    }

    catch (PDOException $e)
    {
        echo 'Error amb la BDs: ' . $e->getMessage();
    }

?>