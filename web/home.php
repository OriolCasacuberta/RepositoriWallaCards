<?php
    session_start();

    // Redirect to login if not logged in
    if (!isset($_SESSION['user']))
    {
        header('Location: ../index.php');
        exit;
    }

    // Sanitize username for display
    $username = htmlspecialchars($_SESSION['user']['username']);
    
    // Include database connection
    require_once '../web/connecta_db_persistent.php';
    
    // Prepare query to get posts with user's like status
    $query = "SELECT p.*, 
              (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS num_likes,
              (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id AND l.username = :username) AS user_liked
          FROM posts p 
          ORDER BY num_likes DESC, fecha DESC";
    
    // Prepare and execute statement
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inici - WallaCards</title>
    <!-- CSS Files -->
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/home.css">
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

    <!-- Contenido principal -->
    <div class="container">
        <div class="welcome-section">
            <h1>Benvingut, <?php echo $username; ?>!</h1>
            <a href="afegirPost.php" class="btn-add-post">+ Afegir nou post</a>
        </div>
        
        <div class="posts-container">
            <?php if($stmt && $stmt->rowCount() > 0): ?>
                <?php while($post = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="post-card" id="post-<?php echo $post['id']; ?>">
                        <div class="post-header">
                            <h3><?php echo htmlspecialchars($post['titulo']); ?></h3>
                            <span class="post-type <?php echo strtolower($post['tipo']); ?>"><?php echo $post['tipo']; ?></span>
                        </div>
                        
                        <div class="post-author">
                            <span>Por: <?php echo htmlspecialchars($post['username']); ?></span>
                            <span class="post-date"><?php echo date('d/m/Y H:i', strtotime($post['fecha'])); ?></span>
                        </div>
                        
                        <?php if(!empty($post['imagen'])): ?>
                            <div class="post-image">
                                <img src="../<?php echo htmlspecialchars($post['imagen']); ?>" alt="<?php echo htmlspecialchars($post['titulo']); ?>">
                            </div>
                        <?php endif; ?>
                        
                        <div class="post-description">
                            <p><?php echo nl2br(htmlspecialchars($post['descripcion'])); ?></p>
                        </div>
                        
                        <div class="post-details">
                            <span class="post-status">Estado: <?php echo $post['estado']; ?></span>
                            <?php if($post['tipo'] === 'Venta' || $post['tipo'] === 'Subasta'): ?>
                                <span class="post-price">Precio: <?php echo number_format($post['precio'], 2, ',', '.'); ?> €</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="post-actions">
                            <button class="btn-like" data-post-id="<?php echo $post['id']; ?>">
                                <div class="like-icon" style="background-image: url('../img/<?php echo $post['user_liked'] ? 'fullHeart.png' : 'emptyHeart.png'; ?>');"></div>
                                <span class="like-count"><?php echo $post['num_likes']; ?></span>
                            </button>
                            <button class="btn-comment" data-post-id="<?php echo $post['id']; ?>">
                                <div class="comment-icon"></div>
                                <span class="comment-count"><?php echo $post['num_comentarios']; ?></span>
                            </button>
                        </div>
                        
                        <?php if($post['username'] === $username): ?>
                            <div class="post-owner-actions">
                                <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn-edit">Editar</a>
                                <a href="delete_post.php?id=<?php echo $post['id']; ?>"class="btn-delete" data-post-id="<?php echo $post['id']; ?>">Eliminar</a>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-posts">
                    <p>No hay posts disponibles. ¡Sé el primero en publicar!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div id="delete-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Confirmar eliminación</h2>
            <p>¿Estás seguro de que deseas eliminar este post? Esta acción no se puede deshacer.</p>
            <div class="modal-buttons">
                <button id="confirm-delete" class="btn-primary">Eliminar</button>
                <button id="cancel-delete" class="btn-secondary">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="../js/likes.js"></script>
    <script src="../js/comments.js"></script>
    <script src="../js/posts.js"></script>
    <script>
        function toggleMenu() {
            document.getElementById('dropdownMenu').classList.toggle('show');
        }
    </script>
</body>
</html>