function initPerfilImage() {
    const fileInput = document.getElementById('fileInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const perfilForm = document.getElementById('perfilForm');

    if (fileInput && avatarPreview) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Validaciones en cliente
            const maxSize = 3 * 1024 * 1024; // 3MB
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!allowedTypes.includes(file.type)) {
                alert('Por favor selecciona una imagen válida (JPG, PNG, GIF o WebP)');
                fileInput.value = '';
                return;
            }
            
            if (file.size > maxSize) {
                alert('La imagen es muy grande. Máximo 3MB.');
                fileInput.value = '';
                return;
            }
            
            // Preview
            const reader = new FileReader();
            reader.onload = function(ev) {
                // Crear imagen para preview
                const img = document.createElement('img');
                img.src = ev.target.result;
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
                
                // Limpiar y agregar nueva imagen
                avatarPreview.innerHTML = '';
                avatarPreview.appendChild(img);
                
                console.log('Image preview loaded:', file.name, file.size, file.type);
            };
            reader.readAsDataURL(file);
        });
    }
    
    // Agregar evento al submit del formulario
    if (perfilForm) {
        perfilForm.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('fileInput');
            if (fileInput && fileInput.files.length > 0) {
                const file = fileInput.files[0];
                console.log('Submitting form with image:', file.name, file.size);
            } else {
                console.log('Submitting form without image');
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', initPerfilImage);
