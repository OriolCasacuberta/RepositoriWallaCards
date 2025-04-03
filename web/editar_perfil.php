<?php
session_start();
require './connecta_db_persistent.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user']['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['user']['username'];

try {
    // Obtener datos actuales del usuario
    $sql = "SELECT userFirstName, userLastName, imatgePerfil, descripcio, ubicacio, dataNaixement 
            FROM users WHERE username = :username LIMIT 1";

    $stmt = $db->prepare($sql);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Usuario no encontrado.");
    }

    // Si se envió el formulario, procesamos la actualización
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $userFirstName = trim($_POST['userFirstName']);
        $userLastName = trim($_POST['userLastName']);
        $descripcio = trim($_POST['descripcio']);
        $ubicacio = trim($_POST['ubicacio']);
        $dataNaixement = $_POST['dataNaixement'];

        // Manejo de la imagen de perfil
        if (!empty($_FILES['imatgePerfil']['name'])) {
            $targetDir = "../img/";
            $imageName = basename($_FILES["imatgePerfil"]["name"]);
            $targetFilePath = $targetDir . $imageName;
            move_uploaded_file($_FILES["imatgePerfil"]["tmp_name"], $targetFilePath);
        } else {
            $targetFilePath = $user['imatgePerfil']; // Mantener la imagen anterior si no se cambia
        }

        // Actualizar datos en la base de datos
        $updateSql = "UPDATE users SET userFirstName = :userFirstName, userLastName = :userLastName, 
                      imatgePerfil = :imatgePerfil, descripcio = :descripcio, ubicacio = :ubicacio, 
                      dataNaixement = :dataNaixement WHERE username = :username";

        $stmt = $db->prepare($updateSql);
        $stmt->execute([
            'userFirstName' => $userFirstName,
            'userLastName' => $userLastName,
            'imatgePerfil' => $targetFilePath,
            'descripcio' => $descripcio,
            'ubicacio' => $ubicacio,
            'dataNaixement' => $dataNaixement,
            'username' => $username
        ]);

        header("Location: perfil.php"); // Redirigir al perfil tras la actualización
        exit();
    }
} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="../css/afegirPost.css">
    <link rel="stylesheet" href="../css/editar_perfil.css"> <!-- Estilos específicos para editar perfil -->
</head>
<body>
    <div class="edit-profile-container">
        <h2>Editar Perfil</h2>
        <form action="editar_perfil.php" method="POST" enctype="multipart/form-data">
            <label for="userFirstName">Nombre:</label>
            <input type="text" name="userFirstName" value="<?php echo htmlspecialchars($user['userFirstName']); ?>" required>

            <label for="userLastName">Apellido:</label>
            <input type="text" name="userLastName" value="<?php echo htmlspecialchars($user['userLastName']); ?>" required>

            <label for="descripcio">Descripción:</label>
            <textarea name="descripcio"><?php echo htmlspecialchars($user['descripcio']); ?></textarea>

            <label for="ubicacio">Ubicación:</label>
            <input type="text" name="ubicacio" value="<?php echo htmlspecialchars($user['ubicacio']); ?>">

            <label for="dataNaixement">Fecha de Nacimiento:</label>
            <input type="date" name="dataNaixement" value="<?php echo htmlspecialchars($user['dataNaixement']); ?>">

            <label for="imatgePerfil">Imagen de Perfil:</label>
            <input type="file" name="imatgePerfil" accept="image/*">

            <!-- Contenedor para los botones alineados horizontalmente -->
            <div class="button-container">
                <button type="submit">Guardar Cambios</button>
                <a href="perfil.php" class="cancel-btn">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>