// posts.js
document.addEventListener('DOMContentLoaded', () => {
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const deleteModal = document.getElementById('delete-modal');
    const confirmDeleteBtn = document.getElementById('confirm-delete');
    const cancelDeleteBtn = document.getElementById('cancel-delete');
    const closeModalBtn = document.querySelector('#delete-modal .close-modal');
    
    let currentPostId = null;
    
    // Add event listeners to all delete buttons
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Prevent default link behavior
            e.preventDefault();
            
            // Store the post ID
            currentPostId = this.getAttribute('data-post-id');
            console.log("Post ID a eliminar:", currentPostId);
            
            // Show the delete confirmation modal
            deleteModal.style.display = 'flex';
        });
    });
    
    // Confirm delete button handler
    confirmDeleteBtn.addEventListener('click', function() {
        if (currentPostId) {
            console.log("Confirmando eliminación del post ID:", currentPostId);
            deletePost(currentPostId);
        } else {
            console.error("No hay post ID para eliminar");
        }
    });
    
    // Cancel and close modal buttons handlers
    cancelDeleteBtn.addEventListener('click', closeDeleteModal);
    closeModalBtn.addEventListener('click', closeDeleteModal);
    
    // Close modal when clicking outside the content
    window.addEventListener('click', function(event) {
        if (event.target === deleteModal) {
            closeDeleteModal();
        }
    });
    
    function closeDeleteModal() {
        deleteModal.style.display = 'none';
        currentPostId = null;
    }
    
    function deletePost(postId) {
        // Verificar que postId sea válido
        if (!postId || isNaN(parseInt(postId))) {
            console.error("ID de post inválido:", postId);
            showNotification('ID de post inválido', 'error');
            closeDeleteModal();
            return;
        }
        
        console.log("Enviando solicitud de eliminación para post ID:", postId);
        
        // Create FormData object
        const formData = new FormData();
        formData.append('post_id', postId);
        
        // Debug: Verificar que formData contiene el post_id
        console.log("FormData contiene post_id:", formData.has('post_id'));
        
        // Send the request using fetch with POST method
        fetch('delete_post.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin' // Include session cookies
        })
        .then(response => {
            console.log("Respuesta recibida, status:", response.status);
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log("Datos recibidos:", data);
            
            if (data.success) {
                // Post deleted successfully, remove it from the DOM
                const postElement = document.getElementById(`post-${postId}`);
                if (postElement) {
                    postElement.remove();
                    
                    // Check if there are no more posts
                    const postsContainer = document.querySelector('.posts-container');
                    if (postsContainer && !postsContainer.querySelector('.post-card')) {
                        postsContainer.innerHTML = `
                            <div class="no-posts">
                                <p>No hay posts disponibles. ¡Sé el primero en publicar!</p>
                            </div>
                        `;
                    }
                } else {
                    console.warn("No se encontró el elemento post-" + postId + " en el DOM");
                    // Recargar la página como alternativa si no se puede eliminar del DOM
                    window.location.reload();
                }
                
                // Show a notification
                showNotification('Post eliminado correctamente', 'success');
            } else {
                // Show error notification
                showNotification(data.message || 'Error al eliminar el post', 'error');
            }
            
            // Close the modal
            closeDeleteModal();
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión: ' + error.message, 'error');
            closeDeleteModal();
        });
    }
    
    function showNotification(message, type = 'info') {
        console.log("Mostrando notificación:", message, type);
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        // Append to body
        document.body.appendChild(notification);
        
        // Show notification
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Remove notification after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
});