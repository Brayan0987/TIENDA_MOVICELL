document.addEventListener('DOMContentLoaded', function() {
    // 1. Lógica del Carrito (Existente + Mejorada)
    const form = document.getElementById('addToCartForm');
    if (form) {
        const quantityInput = form.querySelector('input[name="quantity"]');
        if (quantityInput) {
            const maxStock = parseInt(quantityInput.getAttribute('max')) || 100;

            quantityInput.addEventListener('change', function() {
                let value = parseInt(this.value);
                if (isNaN(value) || value < 1) this.value = 1;
                else if (value > maxStock) {
                    this.value = maxStock;
                    // Opcional: Mostrar un toast o tooltip en lugar de alert
                    console.warn('Stock máximo alcanzado');
                }
            });
        }

        form.addEventListener('submit', function(e) {
            // Animación del botón
            const button = this.querySelector('.btn-add-to-cart');
            const originalContent = button.innerHTML;
            
            // No prevenimos el submit real si es un form normal, 
            // pero si usas AJAX, aquí iría e.preventDefault().
            // Asumiendo envío tradicional, la animación se verá brevemente antes de recargar.
            
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Agregando...';
        });
    }

    // 2. Animación de Entrada Suave
    const container = document.querySelector('.product-container');
    if (container) {
        container.style.opacity = '0';
        container.style.transform = 'translateY(20px)';
        container.style.transition = 'opacity 0.8s ease, transform 0.8s cubic-bezier(0.2, 0.8, 0.2, 1)';
        
        requestAnimationFrame(() => {
            container.style.opacity = '1';
            container.style.transform = 'translateY(0)';
        });
    }

    // 3. SINCRONIZACIÓN DE GALERÍA (Nuevo)
    const carouselEl = document.getElementById('carouselProduct');
    const thumbBtns = document.querySelectorAll('.carousel-thumb-btn');

    if (carouselEl && thumbBtns.length > 0) {
        // Cuando el carrusel cambia (evento nativo de Bootstrap 5)
        carouselEl.addEventListener('slide.bs.carousel', function (event) {
            const nextIndex = event.to; // Índice de la diapositiva a la que va
            
            // Remover active de todos
            thumbBtns.forEach(btn => btn.classList.remove('active'));
            
            // Agregar active al correspondiente
            if(thumbBtns[nextIndex]) {
                thumbBtns[nextIndex].classList.add('active');
            }
        });

        // Click manual en thumbnails (ya lo maneja data-bs-slide-to, pero añadimos efecto visual inmediato)
        thumbBtns.forEach((btn, index) => {
            btn.addEventListener('click', () => {
                thumbBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });
    }
});