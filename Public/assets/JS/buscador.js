function initBuscador() {
    const buscador = document.getElementById('buscador');
    if (buscador) {
        buscador.addEventListener('keyup', function() {
            let filtro = this.value.toLowerCase();
            let filas = document.querySelectorAll('#tabla-celulares tbody tr');
            filas.forEach(function(fila) {
                let texto = fila.textContent.toLowerCase();
                fila.style.display = texto.includes(filtro) ? '' : 'none';
            });
        });
    }
}

document.addEventListener('DOMContentLoaded', initBuscador);