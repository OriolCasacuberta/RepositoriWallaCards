// comments.js
document.addEventListener('DOMContentLoaded', () => {
    console.log('Inicializando sistema de comentarios');
    const commentButtons = document.querySelectorAll('.btn-comment');
    
    console.log(`Encontrados ${commentButtons.length} botones de comentarios`);
    
    // Agregar listener global para manejar clics en la X del modal
    document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('close-modal')) {
            console.log('Clic detectado en botón cerrar modal');
            closeModal();
        }
    });
    
    commentButtons.forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            console.log(`Botón de comentarios clickeado para post ID: ${postId}`);
            
            // Open comments modal
            openCommentsModal(postId);
        });
    });
    
    function openCommentsModal(postId) {
        console.log(`Intentando abrir modal para post ID: ${postId}`);
        
        if (!postId) {
            console.error('Error: No se encontró ID del post');
            alert('Error: ID de post no disponible');
            return;
        }
        
        // Corregir la ruta para apuntar a la carpeta web/
        const url = `../web/get_comments.php?post_id=${postId}`;
        console.log(`Fetcheando comentarios desde: ${url}`);
        
        // Fetch existing comments
        fetch(url)
        .then(response => {
            console.log(`Respuesta recibida con status: ${response.status}`);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos de comentarios recibidos:', data);
            
            if (!data.success) {
                throw new Error(data.message || 'Error desconocido al cargar comentarios');
            }
            
            const modalHtml = `
                <div id="comments-modal" class="modal">
                    <div class="modal-content">
                        <span class="close-modal">&times;</span>
                        <h2>Comentarios</h2>
                        <div id="existing-comments">
                            ${data.comments && data.comments.length > 0 
                                ? data.comments.map(comment => `
                                    <div class="comment">
                                        <strong>${escapeHtml(comment.username)}</strong>
                                        <p>${escapeHtml(comment.contenido)}</p>
                                        <small>${formatDate(comment.fecha)}</small>
                                    </div>
                                `).join('') 
                                : '<p>No hay comentarios aún.</p>'}
                        </div>
                        <form id="add-comment-form">
                            <input type="hidden" name="post_id" value="${postId}">
                            <textarea name="contenido" placeholder="Escribe un comentario..." required maxlength="500"></textarea>
                            <button type="submit">Enviar</button>
                        </form>
                    </div>
                </div>
            `;
            
            // Remove any existing modal first
            const existingModal = document.getElementById('comments-modal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Insert modal into body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            console.log('Modal de comentarios insertado en el DOM');
            
            // No agregar el event listener aquí, ya lo manejamos a nivel global
            // document.querySelector('.close-modal').addEventListener('click', closeModal);
            
            // Añadir event listener para el formulario
            const form = document.getElementById('add-comment-form');
            if (form) {
                form.addEventListener('submit', submitComment);
            }
            
            // Mostrar el modal explícitamente
            const modal = document.getElementById('comments-modal');
            if (modal) {
                modal.style.display = 'flex';
            }
            
            // Agregar event listener para cerrar modal al hacer clic fuera
            window.addEventListener('click', function(event) {
                const modal = document.getElementById('comments-modal');
                if (event.target === modal) {
                    closeModal();
                }
            });
        })
        .catch(error => {
            console.error('Error al cargar comentarios:', error);
            alert(`No se pudieron cargar los comentarios: ${error.message}`);
        });
    }
    
    function submitComment(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const postId = formData.get('post_id');
        console.log(`Enviando comentario para post ID: ${postId}`);
        
        // Corregir la ruta para apuntar a la carpeta web/
        fetch('../web/add_comment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log(`Respuesta de envío con status: ${response.status}`);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Respuesta de envío de comentario:', data);
            
            if (!data.success) {
                throw new Error(data.message || 'Error desconocido al enviar comentario');
            }
            
            // Reload comments
            const existingCommentsContainer = document.getElementById('existing-comments');
            existingCommentsContainer.innerHTML = data.comments && data.comments.length > 0 
                ? data.comments.map(comment => `
                    <div class="comment">
                        <strong>${escapeHtml(comment.username)}</strong>
                        <p>${escapeHtml(comment.contenido)}</p>
                        <small>${formatDate(comment.fecha)}</small>
                    </div>
                `).join('') 
                : '<p>No hay comentarios aún.</p>';
            
            // Clear textarea
            e.target.querySelector('textarea').value = '';
            
            // Update comment count in original post
            const commentButton = document.querySelector(`.btn-comment[data-post-id="${postId}"]`);
            if (commentButton) {
                const commentCount = commentButton.querySelector('.comment-count');
                if (commentCount) {
                    commentCount.textContent = data.total_comments;
                }
            }
        })
        .catch(error => {
            console.error('Error al enviar comentario:', error);
            alert(`Error al enviar comentario: ${error.message}`);
        });
    }
    
    function closeModal() {
        console.log('Ejecutando closeModal()');
        const modal = document.getElementById('comments-modal');
        if (modal) {
            console.log('Modal encontrado, ocultando...');
            modal.style.display = 'none';
            console.log('Modal ocultado, eliminando del DOM...');
            setTimeout(() => {
                if (document.getElementById('comments-modal')) {
                    document.getElementById('comments-modal').remove();
                    console.log('Modal eliminado del DOM');
                }
            }, 100);
        } else {
            console.log('No se encontró el modal para cerrar');
        }
    }

    // Utility function to escape HTML to prevent XSS
    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Utility function to format date
    function formatDate(dateString) {
        if (!dateString) return '';
        try {
            const date = new Date(dateString);
            return date.toLocaleString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            console.error('Error al formatear fecha:', e);
            return dateString;
        }
    }
});