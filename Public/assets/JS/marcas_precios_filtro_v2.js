// Filtro de búsqueda en tiempo real para marcas_precios.php
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('btnSearch');
    const searchForm = document.getElementById('searchForm');
    
    if (!searchInput) return;
    
    // Obtener todas las tarjetas de atributos
    const atributCards = document.querySelectorAll('.atributo-card');
    
    // Función para filtrar elementos
    function filterItems(searchTerm) {
        const searchLower = searchTerm.toLowerCase();
        
        atributCards.forEach((card, index) => {
            const listItems = card.querySelectorAll('.list-item');
            const listTitle = card.querySelector('.list-title');
            let visibleCount = 0;
            
            listItems.forEach(item => {
                const text = item.querySelector('.item-text').textContent.toLowerCase();
                if (text.includes(searchLower)) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Actualizar contador en el título
            if (listTitle) {
                const titleText = listTitle.textContent;
                const baseText = titleText.split('(')[0].trim();
                listTitle.textContent = baseText + ' (' + visibleCount + ')';
            }
        });
    }
    
    // Evento en el input (filtro en tiempo real)
    searchInput.addEventListener('input', function() {
        filterItems(this.value);
    });
    
    // Evento al hacer click en el botón
    if (searchBtn) {
        searchBtn.addEventListener('click', function(e) {
            e.preventDefault();
            filterItems(searchInput.value);
        });
    }
    
    // Prevenir que el formulario se envíe
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            filterItems(searchInput.value);
        });
    }
    
    // Si hay un valor inicial en el search, aplicar filtro al cargar
    if (searchInput.value.trim()) {
        filterItems(searchInput.value);
    }
});

console.log('✅ Filtro de búsqueda marcas_precios.js cargado');
