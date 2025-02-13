document.addEventListener("DOMContentLoaded", function () {
    // Función para mostrar el popup
    function mostrarPopup() {
        document.getElementById("popup").style.display = "block";
        document.getElementById("overlay").style.display = "block";
    }

    // Función para cerrar el popup
    function cerrarPopup() {
        document.getElementById("popup").style.display = "none";
        document.getElementById("overlay").style.display = "none";
    }

    // Asignar el evento al <p> para abrir el popup
    document.getElementById("forgotPassword").addEventListener("click", mostrarPopup);

    // Asignar el evento al botón para cerrar el popup
    document.getElementById("closePopupButton").addEventListener("click", cerrarPopup);

    // Asignar el evento al overlay para cerrar el popup al hacer clic
    document.getElementById("overlay").addEventListener("click", cerrarPopup);
});
