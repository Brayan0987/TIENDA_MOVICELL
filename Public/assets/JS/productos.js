// ============================================
// PRODUCTOS.JS - CATÃLOGO MOVI CELL
// ============================================

// ------------------------------------------------
// CARGAR FILTROS DESDE URL (parÃ¡metro ?brand=)
// ------------------------------------------------
document.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  const brandParam = urlParams.get('brand');
  
  if (brandParam) {
    const brandLower = brandParam.toLowerCase();
    const checkbox = document.querySelector(`input[name="brand"][value="${brandLower}"]`);
    if (checkbox) {
      checkbox.checked = true;
      // Aplicar el filtro automÃ¡ticamente despuÃ©s de un pequeÃ±o delay
      setTimeout(() => {
        applyAllFilters();
      }, 100);
    }
  }
});

// PRELOADER
window.addEventListener('load', () => {
  const preloader = document.getElementById('preloader');
  if (preloader) {
    setTimeout(() => {
      preloader.classList.add('hidden');
      setTimeout(() => {
        preloader.style.display = 'none';
      }, 500);
    }, 800);
  }
});

// AOS
AOS.init({
  duration: 1000,
  once: true,
  offset: 100,
  easing: 'ease-out-cubic'
});

// NAVBAR SCROLL EFFECT
const navbar = document.getElementById('mainNavbar');
window.addEventListener('scroll', () => {
  if (!navbar) return;
  const currentScroll = window.pageYOffset;
  if (currentScroll > 50) {
    navbar.classList.add('scrolled');
  } else {
    navbar.classList.remove('scrolled');
  }
});

// ------------------------------------------------
// UTILIDADES
// ------------------------------------------------
function updateResultCount(count) {
  const totalSpan = document.getElementById('totalSpan');
  if (totalSpan) totalSpan.textContent = count;
}

// Debounce genÃ©rico
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// ------------------------------------------------
// REFERENCIAS
// ------------------------------------------------
const searchInput       = document.getElementById('searchInput');
const brandCheckboxes   = document.querySelectorAll('input[name="brand"]');
const storageCheckboxes = document.querySelectorAll('input[name="storage"]');
const btnApplyPrice     = document.getElementById('btnApplyPrice');
const btnClear          = document.getElementById('btnClear');
const sortSelect        = document.getElementById('sortSelect');
const scrollToTopBtn    = document.getElementById('scrollToTop');
const grid              = document.getElementById('grid');

// Obtener baseUrl del atributo data-base-url del body
const baseUrl = document.body.getAttribute('data-base-url') || '/TIENDA_MOVICELL/public/';

// Helper para obtener todas las cards
function getProductCards() {
  return document.querySelectorAll('.product-card-premium');
}

// ------------------------------------------------
// LÃ“GICA PRINCIPAL DE FILTRADO
// ------------------------------------------------
function applyAllFilters() {
  const q = (searchInput?.value || '').toLowerCase().trim();

  const selectedBrands = Array.from(brandCheckboxes)
    .filter(ch => ch.checked)
    .map(ch => ch.value.toLowerCase()); // samsung, apple, ...

  const selectedStorages = Array.from(storageCheckboxes)
    .filter(ch => ch.checked)
    .map(ch => ch.value.toLowerCase()); // 128, 256, 512

  const minPriceInput = document.getElementById('minPrice');
  const maxPriceInput = document.getElementById('maxPrice');
  const minValue = minPriceInput?.value ? parseInt(minPriceInput.value, 10) : null;
  const maxValue = maxPriceInput?.value ? parseInt(maxPriceInput.value, 10) : null;
  const min = minValue !== null ? minValue : 0;
  const max = maxValue !== null ? maxValue : Infinity;

  const cards = getProductCards();
  let visibleCount = 0;

  cards.forEach(card => {
    const parentCol = card.closest('.col-md-6, .col-xl-4');
    if (!parentCol) return;

    const title = (card.querySelector('.product-title-premium')?.textContent || '').toLowerCase();
    const specsRaw = (card.querySelector('.product-specs-premium')?.textContent || '').toLowerCase();
    const specs = specsRaw.replace(/\s+/g, ' ').trim();
    const priceText = (card.querySelector('.product-price-premium')?.textContent || '').replace(/[^\d]/g, '');
    const price = parseInt(priceText || '0', 10);
    const brandAttr = card.getAttribute('data-brand') || ''; // Obtener marca del atributo data-brand

    let show = true;

    // BÃºsqueda por marca (desde el input de bÃºsqueda)
    // La bÃºsqueda solo aplica a la marca, no al nombre del producto
    if (q) {
      const matchesBrand = title.includes(q);
      if (!matchesBrand) show = false;
    }

    // Filtro de marca (desde checkboxes)
    if (show && selectedBrands.length > 0) {
      // Comparar directamente con el atributo data-brand
      const matchesBrand = selectedBrands.some(brand => brandAttr.includes(brand));
      if (!matchesBrand) show = false;
    }

    // Almacenamiento (detectamos nÃºmeros seguidos de GB en specs)
    if (show && selectedStorages.length > 0) {
      // Extraer valores de almacenamiento dentro de specs, p.ej. "128GB" -> "128"
      const found = [];
      const regex = /(\d+)\s*gb/gi;
      let m;
      while ((m = regex.exec(specsRaw)) !== null) {
        if (m.index === regex.lastIndex) regex.lastIndex++;
        found.push(m[1]);
      }

      // Si no se detecta con GB, intentar extraer nÃºmeros simples (fallback)
      if (found.length === 0) {
        const nums = specsRaw.match(/\b(\d{2,4})\b/g);
        if (nums) found.push(...nums);
      }

      // ComprobaciÃ³n mÃ¡s tolerante: mirar si specsRaw contiene el token (con o sin 'gb')
      const matchesStorage = selectedStorages.some(storage => {
        // coincidencia exacta en los numeros encontrados
        if (found.some(f => f === storage)) return true;
        // coincidencia en cadena (128gb, 128 gb, etc.)
        if (specsRaw.includes(storage + 'gb') || specsRaw.includes(storage + ' gb')) return true;
        // tambiÃ©n comprobar en el title (en caso de que el almacenamiento aparezca ahÃ­)
        if (title.includes(storage + 'gb') || title.includes(storage + ' gb') || title.includes(storage + ' ')) return true;
        return false;
      });
      if (!matchesStorage) show = false;
    }

    // Precio - Aplicar filtro solo si hay al menos un valor ingresado
    if (show && (minValue !== null || maxValue !== null)) {
      if (minValue !== null && price < min) show = false;
      if (maxValue !== null && price > max) show = false;
    }

    parentCol.style.display = show ? 'block' : 'none';
    if (show) visibleCount++;
  });

  updateResultCount(visibleCount);
  AOS.refresh();
}// ------------------------------------------------
// EVENTOS DE BÃšSQUEDA Y FILTROS
// ------------------------------------------------
if (searchInput) {
  // Filtrar en tiempo real
  searchInput.addEventListener('input', debounce(() => {
    applyAllFilters();
  }, 200));
  
  // Buscar al presionar Enter (sin redirigir, solo filtrar en la pÃ¡gina)
  searchInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      e.stopPropagation();
      applyAllFilters();
      return false;
    }
  });
  
  // Prevenir envÃ­o por otros mÃ©todos
  searchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      e.stopPropagation();
      return false;
    }
  });
}

// Manejar el submit del formulario de bÃºsqueda (sin redirigir)
const searchForm = document.getElementById('searchForm');
if (searchForm) {
  searchForm.addEventListener('submit', function(e) {
    e.preventDefault();
    e.stopPropagation();
    applyAllFilters();
    return false;
  });
}

brandCheckboxes.forEach(ch => {
  ch.addEventListener('change', applyAllFilters);
});

storageCheckboxes.forEach(ch => {
  ch.addEventListener('change', applyAllFilters);
});

if (btnApplyPrice) {
  btnApplyPrice.addEventListener('click', () => {
    applyAllFilters();
    btnApplyPrice.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Aplicado';
    setTimeout(() => {
      btnApplyPrice.innerHTML = '<i class="bi bi-check-circle me-1"></i>Aplicar Filtros';
    }, 1500);
  });
}

if (btnClear) {
  btnClear.addEventListener('click', () => {
    if (searchInput) searchInput.value = '';
    const minPrice = document.getElementById('minPrice');
    const maxPrice = document.getElementById('maxPrice');
    if (minPrice) minPrice.value = '';
    if (maxPrice) maxPrice.value = '';

    document.querySelectorAll('.form-check-input').forEach(cb => { cb.checked = false; });

    const cards = getProductCards();
    let totalCount = 0;
    cards.forEach(card => {
      const parentCol = card.closest('.col-md-6, .col-xl-4');
      if (parentCol) {
        parentCol.style.display = 'block';
        totalCount++;
      }
    });

    updateResultCount(totalCount);
    btnClear.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Limpiado';
    setTimeout(() => {
      btnClear.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Limpiar Todo';
    }, 1500);
    AOS.refresh();
  });
}

// ------------------------------------------------
// ORDENAR POR PRECIO / NUEVOS
// ------------------------------------------------
if (sortSelect && grid) {
  sortSelect.addEventListener('change', e => {
    const sortValue = e.target.value;
    const products = Array.from(grid.querySelectorAll('.col-md-6, .col-xl-4'));

    products.sort((a, b) => {
      const priceA = parseInt(a.querySelector('.product-price-premium')?.textContent.replace(/[^\d]/g, '') || '0', 10);
      const priceB = parseInt(b.querySelector('.product-price-premium')?.textContent.replace(/[^\d]/g, '') || '0', 10);

      switch (sortValue) {
        case 'price_asc':
          return priceA - priceB;
        case 'price_desc':
          return priceB - priceA;
        case 'newest':
        default:
          return 0; // ya vienen ordenados por ID DESC desde PHP
      }
    });

    products.forEach(p => grid.appendChild(p));
    AOS.refresh();
  });
}

// ------------------------------------------------
// SCROLL TO TOP
// ------------------------------------------------
window.addEventListener('scroll', () => {
  if (!scrollToTopBtn) return;
  if (window.pageYOffset > 300) {
    scrollToTopBtn.classList.add('show');
  } else {
    scrollToTopBtn.classList.remove('show');
  }
});

if (scrollToTopBtn) {
  scrollToTopBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

// ------------------------------------------------
// HOVER EN CARDS
// ------------------------------------------------
document.querySelectorAll('.product-card-premium').forEach(card => {
  card.addEventListener('mouseenter', function () {
    this.style.transform = 'translateY(-8px)';
  });
  card.addEventListener('mouseleave', function () {
    this.style.transform = 'translateY(0)';
  });
});

// ------------------------------------------------
// LAZY LOADING (si usas data-src)
// ------------------------------------------------
if ('IntersectionObserver' in window) {
  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        if (img.dataset.src) {
          img.src = img.dataset.src;
          img.removeAttribute('data-src');
        }
        observer.unobserve(img);
      }
    });
  });

  document.querySelectorAll('img[data-src]').forEach(img => {
    imageObserver.observe(img);
  });
}

// ------------------------------------------------
// MENÃš MÃ“VIL: autocerrar
// ------------------------------------------------
const mobileMenuLinks = document.querySelectorAll('#mobileMenu .mobile-menu-item');
const offcanvasElement = document.getElementById('mobileMenu');

mobileMenuLinks.forEach(link => {
  link.addEventListener('click', () => {
    if (offcanvasElement) {
      const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
      if (bsOffcanvas) bsOffcanvas.hide();
    }
  });
});

// ------------------------------------------------
// ATALLOS / ACCESIBILIDAD
// ------------------------------------------------
document.addEventListener('keydown', e => {
  // ESC cierra offcanvas
  if (e.key === 'Escape' && offcanvasElement) {
    const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
    if (bsOffcanvas) bsOffcanvas.hide();
  }
  // Ctrl/Cmd + K enfoca bÃºsqueda
  if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
    e.preventDefault();
    searchInput?.focus();
  }
});

// TOOLTIP BOOTSTRAP
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
[...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el));

// PAGE VISIBILITY (solo logs)
document.addEventListener('visibilitychange', () => {
  if (!document.hidden) {
    AOS.refresh();
  }
});

// LOG
console.log('âœ… productos.js cargado');

