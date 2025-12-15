// ============================================
// PRELOADER
// ============================================
window.addEventListener('load', function() {
  const preloader = document.getElementById('preloader');
  if (preloader) {
    setTimeout(() => {
      preloader.classList.add('hidden');
      setTimeout(() => preloader.remove(), 500);
    }, 1000);
  }
});


// ============================================
// INICIALIZACIÓN AOS (ANIMACIONES)
// ============================================
document.addEventListener('DOMContentLoaded', function() {
  if (typeof AOS !== 'undefined') {
    AOS.init({
      duration: 800,
      easing: 'ease-out-cubic',
      once: true,
      offset: 50,
      delay: 0
    });
  }

  // ==========================================
  // NAVBAR SCROLL EFFECT
  // ==========================================
  const navbar = document.getElementById('mainNavbar');
  let lastScroll = 0;

  window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    
    if (currentScroll > 100) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
    
    lastScroll = currentScroll;
  }, { passive: true });

  // ==========================================
  // VIDEO BACKGROUND OPTIMIZATION
  // ==========================================
  const heroVideo = document.querySelector('.hero-video');
  
  if (heroVideo) {
    const playVideo = () => {
      heroVideo.play().catch(() => {
        console.log('Autoplay bloqueado por el navegador');
      });
    };

    playVideo();

    heroVideo.addEventListener('timeupdate', () => {
      if (heroVideo.duration && heroVideo.duration - heroVideo.currentTime < 0.5) {
        heroVideo.currentTime = 0.1;
      }
    });

    heroVideo.addEventListener('ended', () => {
      heroVideo.currentTime = 0.1;
      playVideo();
    });

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          playVideo();
        } else {
          heroVideo.pause();
        }
      });
    }, { threshold: 0.5 });

    observer.observe(heroVideo);

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (prefersReducedMotion.matches) {
      heroVideo.pause();
      heroVideo.removeAttribute('autoplay');
    }
  }

  // ==========================================
  // SCROLL TO TOP BUTTON
  // ==========================================
  const scrollTopBtn = document.getElementById('scrollTop');
  
  if (scrollTopBtn) {
    window.addEventListener('scroll', () => {
      if (window.pageYOffset > 500) {
        scrollTopBtn.classList.add('show');
      } else {
        scrollTopBtn.classList.remove('show');
      }
    }, { passive: true });

    scrollTopBtn.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }

  // ==========================================
  // SMOOTH SCROLL PARA ANCLAS
  // ==========================================
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      const href = this.getAttribute('href');
      if (href === '#' || href === '#!') return;
      
      const target = document.querySelector(href);
      if (target) {
        e.preventDefault();
        const offsetTop = target.getBoundingClientRect().top + window.pageYOffset - 80;
        
        window.scrollTo({
          top: offsetTop,
          behavior: 'smooth'
        });
      }
    });
  });

  // ==========================================
  // AGREGAR AL CARRITO (AJAX) - CORREGIDO
  // ==========================================
  document.querySelectorAll('.add-to-cart-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const button = this.querySelector('button[type="submit"]');
      if (!button || button.disabled) return;
      
      const originalHTML = button.innerHTML;
      button.disabled = true;
      button.innerHTML = '<i class="bi bi-hourglass-split"></i>';
      
      const formData = new FormData(this);
      formData.append('ajax', '1');

      const actionUrl = this.getAttribute('action') || (document.body.dataset.baseUrl || '') + 'index.php?r=/cart/add';
      
      try {
        const response = await fetch(actionUrl, {
          method: 'POST',
          body: formData,
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        const text = await response.text();
        let data = null;

        try {
          data = JSON.parse(text);   // intentamos parsear JSON
        } catch (err) {
          console.log('Respuesta no es JSON, se envía el formulario normal:', text);
          // Si no es JSON, dejamos que PHP maneje la redirección normal
          this.submit();
          return;
        }

        if (data.success) {
          // Animación de éxito
          button.innerHTML = '<i class="bi bi-check2"></i>';
          button.style.background = '#10b981';
          
          // Actualizar contador del carrito
          document.querySelectorAll('.cart-count').forEach(badge => {
            badge.textContent = data.cart_count;
            if (data.cart_count > 0) {
              badge.style.display = 'flex';
            }
          });
          
          // Crear badge si no existe
          if (data.cart_count > 0) {
            document.querySelectorAll('.btn-cart').forEach(cartBtn => {
              if (!cartBtn.querySelector('.cart-count')) {
                const badge = document.createElement('span');
                badge.className = 'cart-count';
                badge.textContent = data.cart_count;
                cartBtn.appendChild(badge);
              }
            });
          }
          
          showNotification('Producto agregado al carrito', 'success');
          
          setTimeout(() => {
            button.innerHTML = originalHTML;
            button.style.background = '';
            button.disabled = false;
          }, 2000);

        } else {
          if (data.login_required) {
            showNotification(data.message || 'Debes iniciar sesión para continuar', 'error');
            setTimeout(() => {
              const baseUrl = document.body.dataset.baseUrl || '';
              window.location.href = baseUrl + 'index.php?r=/login';
            }, 1100);
          } else if (data.limited) {
            showNotification(data.message || 'Límite alcanzado', 'error');
          } else {
            showNotification(data.message || 'Error al agregar al carrito', 'error');
          }
          throw new Error(data.message || 'Error al agregar al carrito');
        }
      } catch (error) {
        console.error('Error:', error);
        button.innerHTML = '<i class="bi bi-exclamation-triangle"></i>';
        button.style.background = '#ef4444';
        showNotification('Error al agregar al carrito', 'error');
        
        setTimeout(() => {
          button.innerHTML = originalHTML;
          button.style.background = '';
          button.disabled = false;
        }, 2000);
      }
    });
  });

  // ==========================================
  // VISTA RÁPIDA DE PRODUCTO
  // ==========================================
  window.quickView = async function(productId) {
    const baseUrl = document.body.dataset.baseUrl || '';
    const modalBody = document.getElementById('quickViewBody');
    const modalEl = document.getElementById('quickViewModal');
    if (!modalBody || !modalEl) {
      window.location.href = `${baseUrl}index.php?r=/producto-detalle&id=${productId}`;
      return;
    }

    modalBody.innerHTML = '<div class="text-center p-4">Cargando producto…</div>';

    try {
      const res = await fetch(baseUrl + 'ajax/product_quickview.php?id=' + encodeURIComponent(productId));
      if (!res.ok) throw new Error('No se pudo cargar el producto');
      const html = await res.text();
      modalBody.innerHTML = html;

      const addForm = modalBody.querySelector('.add-to-cart-quick');
      if (addForm) {
        addForm.addEventListener('submit', async (e) => {
          e.preventDefault();
          const formData = new FormData(addForm);
          formData.append('ajax', '1');
          const btn = addForm.querySelector('button[type="submit"]');
          const orig = btn ? btn.innerHTML : null;
          if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Añadiendo'; }
          const resp = await fetch(baseUrl + 'index.php?r=/cart/add', { method: 'POST', body: formData, headers: { 'Accept': 'application/json','X-Requested-With':'XMLHttpRequest' } });
          const text = await resp.text();
          let data = null;
          try {
            data = JSON.parse(text);
          } catch (err) {
            console.log('Respuesta no es JSON:', text);
            if (btn) { btn.innerHTML = orig; btn.disabled = false; }
            return;
          }
          if (data && data.success) {
            if (btn) { btn.innerHTML = '<i class="bi bi-check2"></i> Agregado'; }
            document.querySelectorAll('.cart-count').forEach(b => { b.textContent = data.cart_count; b.style.display = data.cart_count > 0 ? 'flex' : 'none'; });
            setTimeout(() => { if (btn) { btn.innerHTML = orig; btn.disabled = false; } }, 1200);
          } else {
            if (btn) { btn.innerHTML = orig; btn.disabled = false; }
            if (data && data.login_required) {
              showNotification(data.message || 'Debes iniciar sesión para agregar productos', 'error');
              setTimeout(() => { window.location.href = baseUrl + 'index.php?r=/login'; }, 900);
            } else if (data && data.limited) {
              showNotification(data.message || 'Límite alcanzado', 'error');
            } else {
              showNotification(data && data.message ? data.message : 'Error al agregar al carrito', 'error');
            }
          }
        });
      }

      const bsModal = new bootstrap.Modal(modalEl);
      bsModal.show();

    } catch (err) {
      modalBody.innerHTML = '<div class="p-4 text-danger">Error cargando producto.</div>';
      console.error(err);
    }
  };

  // ==========================================
  // SISTEMA DE NOTIFICACIONES
  // ==========================================
  function showNotification(message, type = 'info') {
    const existingNotification = document.querySelector('.notification-toast');
    if (existingNotification) {
      existingNotification.remove();
    }

    const notification = document.createElement('div');
    notification.className = `notification-toast notification-${type}`;
    notification.innerHTML = `
      <div class="notification-content">
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
      </div>
    `;

    notification.style.cssText = `
      position: fixed;
      top: 100px;
      right: 20px;
      background: ${type === 'success' ? '#10b981' : '#ef4444'};
      color: white;
      padding: 1rem 1.5rem;
      border-radius: 0.75rem;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
      z-index: 10000;
      animation: slideInRight 0.3s ease;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-weight: 500;
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
      notification.style.animation = 'slideOutRight 0.3s ease';
      setTimeout(() => notification.remove(), 300);
    }, 3000);
  }

  if (!document.getElementById('notification-styles')) {
    const style = document.createElement('style');
    style.id = 'notification-styles';
    style.textContent = `
      @keyframes slideInRight {
        from {
          transform: translateX(100%);
          opacity: 0;
        }
        to {
          transform: translateX(0);
          opacity: 1;
        }
      }
      @keyframes slideOutRight {
        from {
          transform: translateX(0);
          opacity: 1;
        }
        to {
          transform: translateX(100%);
          opacity: 0;
        }
      }
      .notification-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
      }
      .notification-content i {
        font-size: 1.5rem;
      }
    `;
    document.head.appendChild(style);
  }

  // ==========================================
  // LAZY LOADING DE IMÁGENES (FALLBACK)
  // ==========================================
  if ('loading' in HTMLImageElement.prototype === false) {
    const images = document.querySelectorAll('img[loading="lazy"]');
    const imageObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          if (img.dataset.src) {
            img.src = img.dataset.src;
          }
          imageObserver.unobserve(img);
        }
      });
    });
    
    images.forEach(img => imageObserver.observe(img));
  }

  // ==========================================
  // PREVENIR ZOOM EN INPUTS (iOS)
  // ==========================================
  if (/iPhone|iPad|iPod/.test(navigator.userAgent)) {
    document.querySelectorAll('input, select, textarea').forEach(input => {
      const fontSize = window.getComputedStyle(input).fontSize;
      if (parseFloat(fontSize) < 16) {
        input.style.fontSize = '16px';
      }
    });
  }

  // ==========================================
  // ANIMACIÓN DE CONTADOR EN HERO STATS
  // ==========================================
  const animateCounters = () => {
    const counters = document.querySelectorAll('.stat-number');
    
    counters.forEach(counter => {
      const target = counter.textContent;
      const isDecimal = target.includes('.');
      const numTarget = parseFloat(target);
      
      if (isNaN(numTarget)) return;
      
      let count = 0;
      const increment = numTarget / 50;
      
      const updateCounter = () => {
        count += increment;
        
        if (count < numTarget) {
          counter.textContent = isDecimal ? count.toFixed(1) : Math.ceil(count);
          requestAnimationFrame(updateCounter);
        } else {
          counter.textContent = target;
        }
      };
      
      updateCounter();
    });
  };

  const heroStats = document.querySelector('.hero-stats');
  if (heroStats) {
    const statsObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateCounters();
          statsObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });
    
    statsObserver.observe(heroStats);
  }

  // ==========================================
  // PERFORMANCE: DEBOUNCE PARA SCROLL
  // ==========================================
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

  // ==========================================
  // ANALYTICS: TIEMPO EN PÁGINA
  // ==========================================
  let pageLoadTime = Date.now();
  
  window.addEventListener('beforeunload', () => {
    const timeSpent = Math.round((Date.now() - pageLoadTime) / 1000);
    console.log(`Tiempo en página: ${timeSpent}s`);
  });

  // ==========================================
  // DETECCIÓN DE CONEXIÓN
  // ==========================================
  window.addEventListener('online', () => {
    showNotification('Conexión restaurada', 'success');
  });

  window.addEventListener('offline', () => {
    showNotification('Sin conexión a internet', 'error');
  });

  // ==========================================
  // LOG DE CONSOLA
  // ==========================================
  console.log('%c🚀 Movi Cell', 'font-size: 24px; font-weight: bold; color: #3b82f6;');
  console.log('%c✨ Diseño moderno cargado correctamente', 'font-size: 14px; color: #10b981;');
});

// ==========================================
// PARALLAX EFFECT EN HERO (OPCIONAL)
// ==========================================
window.addEventListener('scroll', () => {
  const heroContent = document.querySelector('.hero-content');
  if (heroContent && window.innerWidth > 768) {
    const scrolled = window.pageYOffset;
    heroContent.style.transform = `translateY(${scrolled * 0.3}px)`;
    heroContent.style.opacity = 1 - (scrolled / 500);
  }
}, { passive: true });

// ==========================================
// EASTER EGG: KONAMI CODE
// ==========================================
let konamiCode = [];
const konamiPattern = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];

document.addEventListener('keydown', (e) => {
  konamiCode.push(e.key);
  konamiCode = konamiCode.slice(-10);
  
  if (konamiCode.join(',') === konamiPattern.join(',')) {
    console.log('🎮 Konami Code activado!');
    document.body.style.animation = 'rainbow 2s linear infinite';
    setTimeout(() => {
      document.body.style.animation = '';
    }, 5000);
  }
});
