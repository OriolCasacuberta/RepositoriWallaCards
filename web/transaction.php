<?php
require_once 'connecta_bd_persistent.php';

try {
    // Iniciem la transacció
    $db->beginTransaction();

    // Inserció d'un usuari nou
    $sql = "INSERT INTO users (mail, username, passHash, userFirstName, userLastName, creationDate, active) 
            VALUES (:mail, :username, :passHash, :userFirstName, :userLastName, NOW(), 1)";
    $stmt = $db->prepare($sql);

    // Paràmetres de l'usuari nou
    $usuari = [
        ':mail' => 'bambi@example.com',
        ':username' => 'Bambi',
        ':passHash' => password_hash('pass1234', PASSWORD_DEFAULT),
        ':userFirstName' => 'Bambi',
        ':userLastName' => 'El Cérvol'
    ];

    // Executar la consulta
    $stmt->execute($usuari);

    // Provocar un error repetint la inserció amb la mateixa informació
    $stmt->execute($usuari);

    // Si tot va bé, fem commit
    $db->commit();
    echo '<p>Transacció completada correctament.</p>';
} catch (PDOException $e) {
    // Si hi ha un error, fem rollback
    $db->rollback();
    echo '<p>Error amb la BDs: ' . $e->getMessage() . '</p>';
}
?>
