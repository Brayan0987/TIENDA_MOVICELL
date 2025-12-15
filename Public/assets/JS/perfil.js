function initPerfilImage() {
    const fileInput = document.getElementById('fileInput');
    const avatarPreview = document.getElementById('avatarPreview');

    if (fileInput && avatarPreview) {
        fileInput.addEventListener('change', function(e) {
            const f = e.target.files[0];
            if (!f) return;
            
            const reader = new FileReader();
            reader.onload = function(ev) {
                let img;
                if (avatarPreview.tagName.toLowerCase() === 'img') {
                    img = avatarPreview;
                } else {
                    avatarPreview.innerHTML = '';
                    img = document.createElement('img');
                    avatarPreview.appendChild(img);
                }
                img.src = ev.target.result;
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
            };
            reader.readAsDataURL(f);
        });
    }
}

document.addEventListener('DOMContentLoaded', initPerfilImage);