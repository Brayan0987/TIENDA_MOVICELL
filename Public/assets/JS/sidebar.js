// Manejo del sidebar
function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.getElementById('menu-toggle');
    const closeBtn = document.getElementById('close-btn');

    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            if (sidebar) sidebar.classList.add('active');
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            if (sidebar) sidebar.classList.remove('active');
        });
    }

    // Cerrar al hacer clic fuera
    window.onclick = function(e) {
        if (sidebar && sidebar.classList.contains('active') && !sidebar.contains(e.target) && e.target !== menuToggle) {
            sidebar.classList.remove('active');
        }
    }
}

document.addEventListener('DOMContentLoaded', initSidebar);