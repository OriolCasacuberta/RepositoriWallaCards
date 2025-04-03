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
    // Consulta para obtener los datos del usuario desde la base de datos
    $sql = "SELECT idUser, mail, username, userFirstName, userLastName, imatgePerfil, descripcio, ubicacio, dataNaixement 
            FROM users WHERE username = :username LIMIT 1";

    $stmt = $db->prepare($sql);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Usuario no encontrado.");
    }

    // Función para calcular la edad a partir de la fecha de nacimiento
    function calcularEdad($fechaNacimiento) {
        $fechaNac = new DateTime($fechaNacimiento);
        $hoy = new DateTime();
        return $hoy->diff($fechaNac)->y;
    }

    $edad = calcularEdad($user['dataNaixement']);
} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($user['username']); ?></title>
    <link rel="stylesheet" href="../css/style.css"> <!-- Estilos generales -->
    <link rel="stylesheet" href="../css/perfil.css"> <!-- Estilos específicos del perfil -->
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-pic">
                <?php
                    $defaultImage = "../img/default.png"; // Imagen por defecto
                    $profileImage = !empty($user['imatgePerfil']) ? $user['imatgePerfil'] : $defaultImage;
                ?>
                <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Foto de perfil">
            </div>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user['userFirstName'] . ' ' . $user['userLastName']); ?></h2>
                <p class="username">@<?php echo htmlspecialchars($user['username']); ?></p>
                <?php if (!empty($user['dataNaixement'])): ?>
                    <p>Edad: <?php echo $edad; ?> años</p>
                <?php endif; ?>
                <?php if (!empty($user['ubicacio'])): ?>
                    <p>Ubicación: <?php echo htmlspecialchars($user['ubicacio']); ?></p>
                <?php endif; ?>
                <?php if (!empty($user['descripcio'])): ?>
                    <p class="bio"><?php echo htmlspecialchars($user['descripcio']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="profile-divider"></div> <!-- Línea blanca separadora -->
    </div>

    <!-- Botón para editar el perfil -->
    <a href="editar_perfil.php" class="edit-profile-btn">✏️ Editar Perfil</a>

</body>
</html>
