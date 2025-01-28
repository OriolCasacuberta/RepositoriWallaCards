<?php

    $cadena_connexio = 'mysql:dbname=wallacards;host=localhost:3335';
    $usuari = 'root';
    $passwd = '';

    try
    {
        $db = new PDO($cadena_connexio, $usuari, $passwd, array(PDO::ATTR_PERSISTENT => true));

        if ($db != null) {
            // echo '<pre>';
            // echo "Connexió establerta! \n ";
            // echo '</pre>';
            // echo 'Connexió establerta!<br>';
        }
    }

    catch (PDOException $e)
    {
        echo 'Error amb la BDs: ' . $e->getMessage() . '<br>';
        return null;
    }

?>
