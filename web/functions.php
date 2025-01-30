<?php

    function registrarUsuari($username, $email, $firstName, $lastName, $password)
    {
        require_once ('insert_user.php');

        return 'Usuari creat amb èxit';
    }

?>