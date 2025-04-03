// likes.js
document.addEventListener('DOMContentLoaded', () => {
    const likeButtons = document.querySelectorAll('.btn-like');
    
    likeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            
            fetch('toggle_like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update like count
                    const likeCount = this.querySelector('.like-count');
                    likeCount.textContent = data.likes;
                    
                    // Update like button image
                    const likeIcon = this.querySelector('.like-icon');
                    likeIcon.style.backgroundImage = data.liked 
                        ? 'url("../img/fullHeart.png")' 
                        : 'url("../img/emptyHeart.png")';
                } else {
                    // Handle error (optional)
                    console.error('Like toggle failed:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});