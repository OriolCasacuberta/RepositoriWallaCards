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
            $error = "No tienes permiso para editar este post";
            $post = null;
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $post) {
    // Validar y recoger datos del formulario
    $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : null;
    $estado = isset($_POST['estado']) ? $_POST['estado'] : '';
    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
    
    // Validaciones
    if (empty($titulo)) {
        $error = "El título es obligatorio";
    } elseif (empty($descripcion)) {
        $error = "La descripción es obligatoria";
    } elseif (!in_array($estado, ['Nuevo', 'Usado'])) {
        $error = "El estado debe ser 'Nuevo' o 'Usado'";
    } elseif (!in_array($tipo, ['Venta', 'Intercambio', 'Subasta'])) {
        $error = "El tipo debe ser 'Venta', 'Intercambio' o 'Subasta'";
    } elseif (($tipo === 'Venta' || $tipo === 'Subasta') && ($precio === null || $precio <= 0)) {
        $error = "Para ventas o subastas, el precio debe ser mayor que 0";
    }
    
    // Procesar la imagen si se ha subido
    $imagen = $post['imagen']; // Mantener la imagen anterior por defecto
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['imagen']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $temp_name = $_FILES['imagen']['tmp_name'];
            $img_name = time() . '_' . $_FILES['imagen']['name']; // Nombre único
            $upload_dir = '../uploads/';
            
            // Crear directorio si no existe
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (move_uploaded_file($temp_name, $upload_dir . $img_name)) {
                // Si hay una imagen anterior, eliminarla
                if (!empty($post['imagen'])) {
                    $imagen_anterior = '../' . $post['imagen'];
                    if (file_exists($imagen_anterior)) {
                        unlink($imagen_anterior);
                    }
                }
                
                $imagen = 'uploads/' . $img_name;
            } else {
                $error = "Error al subir la imagen";
            }
        } else {
            $error = "El formato de imagen no es válido. Solo se permiten JPG, PNG y GIF";
        }
    }
    
    // Si no hay errores, actualizar en la base de datos
    if (empty($error)) {
        try {
            // Preparar la sentencia SQL usando PDO
            $stmt = $db->prepare("UPDATE posts SET titulo = :titulo, descripcion = :descripcion, 
                                  imagen = :imagen, precio = :precio, estado = :estado, 
                                  tipo = :tipo WHERE id = :post_id AND username = :username");
            
            // Bind de parámetros
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':imagen', $imagen);
            $stmt->bindParam(':precio', $precio, PDO::PARAM_STR);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':post_id', $post_id);
            $stmt->bindParam(':username', $username);
            
            if ($stmt->execute()) {
                $mensaje = "Post actualizado correctamente";
                // Redirigir a la página de inicio después de un tiempo
                header("Refresh: 2; URL=home.php");
                exit;
            } else {
                $error = "Error al actualizar el post: " . implode(', ', $stmt->errorInfo());
            }
        } catch (PDOException $e) {
            $error = "Error en la base de datos: " . $e->getMessage();
        }
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
    <title>Editar Post - WallaCards</title>
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
        <h1>Editar post</h1>
        
        <?php if(!empty($mensaje)): ?>
            <div class="mensaje success"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($error)): ?>
            <div class="mensaje error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($post): ?>
            <form action="edit_post.php?id=<?php echo $post_id; ?>" method="post" enctype="multipart/form-data" class="form-post">
                <div class="form-group">
                    <label for="titulo">Título de la carta:</label>
                    <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($post['titulo']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="5" required><?php echo htmlspecialchars($post['descripcion']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="imagen">Imagen de la carta:</label>
                    <?php if(!empty($post['imagen'])): ?>
                        <div class="current-image">
                            <p>Imagen actual:</p>
                            <img src="../<?php echo htmlspecialchars($post['imagen']); ?>" alt="Imagen actual" style="max-width: 200px; max-height: 200px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                    <small>Deja vacío para mantener la imagen actual</small>
                </div>
                
                <div class="form-group">
                    <label for="tipo">Tipo de publicación:</label>
                    <select id="tipo" name="tipo" required onchange="mostrarPrecio()">
                        <option value="">Selecciona un tipo</option>
                        <option value="Venta" <?php echo ($post['tipo'] === 'Venta') ? 'selected' : ''; ?>>Venta</option>
                        <option value="Intercambio" <?php echo ($post['tipo'] === 'Intercambio') ? 'selected' : ''; ?>>Intercambio</option>
                        <option value="Subasta" <?php echo ($post['tipo'] === 'Subasta') ? 'selected' : ''; ?>>Subasta</option>
                    </select>
                </div>
                
                <div class="form-group" id="precio-container" style="display: <?php echo ($post['tipo'] === 'Venta' || $post['tipo'] === 'Subasta') ? 'block' : 'none'; ?>;">
                    <label for="precio">Precio (€):</label>
                    <input type="number" id="precio" name="precio" step="0.01" min="0" value="<?php echo htmlspecialchars($post['precio']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="estado">Estado de la carta:</label>
                    <select id="estado" name="estado" required>
                        <option value="">Selecciona un estado</option>
                        <option value="Nuevo" <?php echo ($post['estado'] === 'Nuevo') ? 'selected' : ''; ?>>Nuevo</option>
                        <option value="Usado" <?php echo ($post['estado'] === 'Usado') ? 'selected' : ''; ?>>Usado</option>
                    </select>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="btn-primary">Actualizar</button>
                    <a href="home.php" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function toggleMenu() {
            document.getElementById('dropdownMenu').classList.toggle('show');
        }
        
        function mostrarPrecio() {
            const tipo = document.getElementById('tipo').value;
            const precioContainer = document.getElementById('precio-container');
            
            if (tipo === 'Venta' || tipo === 'Subasta') {
                precioContainer.style.display = 'block';
                document.getElementById('precio').required = true;
            } else {
                precioContainer.style.display = 'none';
                document.getElementById('precio').required = false;
            }
        }
    </script>
</body>
</html>