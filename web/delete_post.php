<?php
session_start();

// Verificar si el usuario está logeado
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

$username = htmlspecialchars($_SESSION['user']['username']);
$mensaje = '';
$error = '';
$post = null;
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Conexión a la base de datos
require_once '../web/connecta_db_persistent.php';

// Primero verificar que el post existe y pertenece al usuario
if ($post_id) {
    try {
        $checkStmt = $db->prepare("SELECT * FROM posts WHERE id = :post_id");
        $checkStmt->bindParam(':post_id', $post_id);
        $checkStmt->execute();
        $post = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$post) {
            $error = "El post no existe";
        } elseif ($post['username'] !== $username) {
            $error = "No tienes permiso para eliminar este post";
            $post = null;
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}

// Procesar la solicitud de eliminación cuando se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_eliminar']) && $post) {
    try {
        // Begin transaction
        $db->beginTransaction();
        
        // Delete comments associated with the post
        $deleteCommentsStmt = $db->prepare("DELETE FROM comentarios WHERE post_id = :post_id");
        $deleteCommentsStmt->bindParam(':post_id', $post_id);
        $deleteCommentsStmt->execute();
        
        // Delete likes associated with the post
        $deleteLikesStmt = $db->prepare("DELETE FROM likes WHERE post_id = :post_id");
        $deleteLikesStmt->bindParam(':post_id', $post_id);
        $deleteLikesStmt->execute();
        
        // Delete the post
        $deletePostStmt = $db->prepare("DELETE FROM posts WHERE id = :post_id AND username = :username");
        $deletePostStmt->bindParam(':post_id', $post_id);
        $deletePostStmt->bindParam(':username', $username);
        $deletePostStmt->execute();
        
        // Verificar cuántas filas fueron afectadas
        $rowsAffected = $deletePostStmt->rowCount();
        
        if ($rowsAffected === 0) {
            $db->rollBack();
            $error = "No se pudo eliminar el post";
        } else {
            // If we got here, commit the transaction
            $db->commit();
            
            // If there's an image associated with the post, delete it
            if (!empty($post['imagen'])) {
                $imagen_path = '../' . $post['imagen'];
                if (file_exists($imagen_path)) {
                    unlink($imagen_path);
                }
            }
            
            $mensaje = "Post eliminado correctamente";
            // Redirigir a la página de inicio después de un tiempo
            header("Refresh: 2; URL=home.php?success=eliminado");
            exit;
        }
    } catch (PDOException $e) {
        // If something went wrong, roll back the transaction
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $error = "Error en la base de datos: " . $e->getMessage();
    } catch (Exception $e) {
        // Capturar cualquier otro tipo de excepción
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $error = "Error general: " . $e->getMessage();
    }
}

// Si no hay post válido y no es una solicitud POST, redirigir
if (!$post && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: home.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Post - WallaCards</title>
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="../css/afegirPost.css">
</head>
<body>
    <!-- Barra superior (Navbar) -->
    <header class="navbar">
        <div class="navbar-left">
            <img src="../img/WallaCards.png" alt="Logo WallaCards" class="logo">
        </div>
        <div class="navbar-right">
            <div class="menu-icon" onclick="toggleMenu()">
                ☰
            </div>
            <nav id="dropdownMenu" class="dropdown-menu">
                <ul>
                    <li><a href="./perfil.php">Perfil</a></li>
                    <li><a href="#">Ajustes</a></li>
                    <li><a href="./logout.php">Tancar sessió</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1>Eliminar post</h1>
        
        <?php if(!empty($mensaje)): ?>
            <div class="mensaje success"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($error)): ?>
            <div class="mensaje error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($post): ?>
            <div class="post-preview">
                <h2><?php echo htmlspecialchars($post['titulo']); ?></h2>
                
                <?php if(!empty($post['imagen'])): ?>
                    <div class="post-image">
                        <img src="../<?php echo htmlspecialchars($post['imagen']); ?>" alt="Imagen del post" style="max-width: 300px; max-height: 300px;">
                    </div>
                <?php endif; ?>
                
                <div class="post-details">
                    <p><strong>Descripción:</strong> <?php echo htmlspecialchars($post['descripcion']); ?></p>
                    <p><strong>Tipo:</strong> <?php echo htmlspecialchars($post['tipo']); ?></p>
                    <?php if($post['tipo'] === 'Venta' || $post['tipo'] === 'Subasta'): ?>
                        <p><strong>Precio:</strong> <?php echo htmlspecialchars($post['precio']); ?> €</p>
                    <?php endif; ?>
                    <p><strong>Estado:</strong> <?php echo htmlspecialchars($post['estado']); ?></p>
                </div>
                
                <form action="delete_post.php?id=<?php echo $post_id; ?>" method="post" class="form-post">
                    <div class="alert-message">
                        <p>¿Estás seguro de que deseas eliminar este post? Esta acción no se puede deshacer.</p>
                        <p>Se eliminarán también todos los comentarios y likes asociados a este post.</p>
                    </div>
                    
                    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                    <input type="hidden" name="confirmar_eliminar" value="1">
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn-danger">Confirmar eliminación</button>
                        <a href="home.php" class="btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleMenu() {
            document.getElementById('dropdownMenu').classList.toggle('show');
        }
    </script>
</body>
</html>