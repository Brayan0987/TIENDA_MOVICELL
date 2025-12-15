// ============================================
// CART.JS - COHERENTE CON HOME
// ============================================

// Get base URL
const baseUrl = document.body.getAttribute('data-base-url') || '';

// ============================================
// PRELOADER
// ============================================
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

// ============================================
// INITIALIZE AOS
// ============================================
AOS.init({
  duration: 1000,
  once: true,
  offset: 100,
  easing: 'ease-out-cubic'
});

// ============================================
// NAVBAR SCROLL EFFECT
// ============================================
const navbar = document.getElementById('mainNavbar');
let lastScroll = 0;

window.addEventListener('scroll', () => {
  const currentScroll = window.pageYOffset;
  
  if (currentScroll > 50) {
    navbar?.classList.add('scrolled');
  } else {
    navbar?.classList.remove('scrolled');
  }
  
  lastScroll = currentScroll;
});

// ============================================
// UPDATE QUANTITY
// ============================================
function updateQuantity(productId, quantity) {
  if (quantity < 1) {
    showNotification('La cantidad mínima es 1', 'warning');
    return;
  }
  
  // Show loading state
  showLoading();
  
  fetch(`${baseUrl}index.php?r=/cart/update`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `product_id=${productId}&quantity=${quantity}&ajax=1`
  })
  .then(response => response.json())
  .then(data => {
    hideLoading();
    if (data.success) {
      showNotification('Cantidad actualizada correctamente', 'success');
      setTimeout(() => {
        location.reload();
      }, 500);
    } else {
      showNotification(data.message || 'Error al actualizar', 'error');
    }
  })
  .catch(error => {
    hideLoading();
    console.error('Error:', error);
    showNotification('Error de conexión. Recargando...', 'error');
    setTimeout(() => {
      location.reload();
    }, 1500);
  });
}

// ============================================
// REMOVE ITEM
// ============================================
function removeItem(productId) {
  // Custom confirm dialog
  if (!confirm('¿Estás seguro de que quieres eliminar este producto del carrito?')) {
    return;
  }
  
  showLoading();
  
  fetch(`${baseUrl}index.php?r=/cart/remove`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `product_id=${productId}&ajax=1`
  })
  .then(response => response.json())
  .then(data => {
    hideLoading();
    if (data.success) {
      showNotification('Producto eliminado del carrito', 'success');
      
      // Animate removal
      const cartItem = document.querySelector(`[onclick*="removeItem(${productId})"]`)?.closest('.cart-item-premium');
      if (cartItem) {
        cartItem.style.transform = 'translateX(100%)';
        cartItem.style.opacity = '0';
        setTimeout(() => {
          location.reload();
        }, 500);
      } else {
        setTimeout(() => {
          location.reload();
        }, 500);
      }
    } else {
      showNotification(data.message || 'Error al eliminar', 'error');
    }
  })
  .catch(error => {
    hideLoading();
    console.error('Error:', error);
    showNotification('Error de conexión. Recargando...', 'error');
    setTimeout(() => {
      location.reload();
    }, 1500);
  });
}

// ============================================
// CLEAR CART
// ============================================
function clearCart() {
  if (!confirm('⚠️ ¿Estás seguro de que quieres vaciar todo el carrito?\n\nEsta acción no se puede deshacer.')) {
    return;
  }
  
  showLoading();
  window.location.href = `${baseUrl}index.php?r=/cart/clear`;
}

// ============================================
// APPLY COUPON
// ============================================
function applyCoupon() {
  const couponCode = prompt('Ingresa tu código de cupón:');
  
  if (!couponCode || couponCode.trim() === '') {
    return;
  }
  
  showLoading();
  
  fetch(`${baseUrl}index.php?r=/cart/apply-coupon`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `coupon_code=${encodeURIComponent(couponCode)}&ajax=1`
  })
  .then(response => response.json())
  .then(data => {
    hideLoading();
    if (data.success) {
      showNotification('¡Cupón aplicado correctamente!', 'success');
      setTimeout(() => {
        location.reload();
      }, 1000);
    } else {
      showNotification(data.message || 'Cupón no válido', 'error');
    }
  })
  .catch(error => {
    hideLoading();
    console.error('Error:', error);
    showNotification('Error al aplicar cupón', 'error');
  });
}

// ============================================
// LOADING INDICATOR
// ============================================
function showLoading() {
  let loader = document.getElementById('global-loader');
  if (!loader) {
    loader = document.createElement('div');
    loader.id = 'global-loader';
    loader.style.cssText = `
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.7);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 99999;
    `;
    loader.innerHTML = `
      <div style="
        width: 60px;
        height: 60px;
        border: 4px solid rgba(255, 255, 255, 0.2);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
      "></div>
    `;
    document.body.appendChild(loader);
    
    // Add animation
    const style = document.createElement('style');
    style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
    document.head.appendChild(style);
  }
  loader.style.display = 'flex';
}

function hideLoading() {
  const loader = document.getElementById('global-loader');
  if (loader) {
    loader.style.display = 'none';
  }
}

// ============================================
// NOTIFICATION SYSTEM
// ============================================
function showNotification(message, type = 'info') {
  const colors = {
    success: '#10b981',
    error: '#ef4444',
    warning: '#f59e0b',
    info: '#06b6d4'
  };
  
  const icons = {
    success: 'bi-check-circle-fill',
    error: 'bi-x-circle-fill',
    warning: 'bi-exclamation-triangle-fill',
    info: 'bi-info-circle-fill'
  };
  
  const notification = document.createElement('div');
  notification.style.cssText = `
    position: fixed;
    top: 100px;
    right: 20px;
    background: white;
    color: ${colors[type]};
    padding: 1rem 1.5rem;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    z-index: 99999;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
    animation: slideIn 0.3s ease;
    border-left: 4px solid ${colors[type]};
    max-width: 400px;
  `;
  
  notification.innerHTML = `
    <i class="bi ${icons[type]}" style="font-size: 1.5rem;"></i>
    <span>${message}</span>
  `;
  
  document.body.appendChild(notification);
  
  // Add animation
  const style = document.createElement('style');
  style.textContent = `
    @keyframes slideIn {
      from { transform: translateX(400px); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
  `;
  document.head.appendChild(style);
  
  setTimeout(() => {
    notification.style.animation = 'slideIn 0.3s ease reverse';
    setTimeout(() => {
      notification.remove();
    }, 300);
  }, 3000);
}

// ============================================
// SMOOTH SCROLL
// ============================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      const navbarHeight = navbar?.offsetHeight || 80;
      const targetPosition = target.offsetTop - navbarHeight - 20;
      
      window.scrollTo({
        top: targetPosition,
        behavior: 'smooth'
      });
    }
  });
});

// ============================================
// SCROLL TO TOP BUTTON
// ============================================
const scrollToTopBtn = document.getElementById('scrollToTop');

window.addEventListener('scroll', () => {
  if (scrollToTopBtn) {
    if (window.pageYOffset > 300) {
      scrollToTopBtn.classList.add('show');
    } else {
      scrollToTopBtn.classList.remove('show');
    }
  }
});

if (scrollToTopBtn) {
  scrollToTopBtn.addEventListener('click', () => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
}

// ============================================
// QUANTITY INPUT VALIDATION
// ============================================
document.querySelectorAll('.quantity-value-premium').forEach(input => {
  input.addEventListener('change', function() {
    const value = parseInt(this.value);
    if (isNaN(value) || value < 1) {
      this.value = 1;
      showNotification('La cantidad mínima es 1', 'warning');
    }
    if (value > 99) {
      this.value = 99;
      showNotification('La cantidad máxima es 99', 'warning');
    }
  });
  
  // Prevent negative numbers
  input.addEventListener('keypress', function(e) {
    if (e.key === '-' || e.key === '+' || e.key === 'e') {
      e.preventDefault();
    }
  });
});

// ============================================
// MOBILE MENU AUTO CLOSE
// ============================================
const mobileMenuLinks = document.querySelectorAll('#mobileMenu .mobile-menu-item');
const offcanvasElement = document.getElementById('mobileMenu');

mobileMenuLinks.forEach(link => {
  link.addEventListener('click', () => {
    if (offcanvasElement) {
      const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
      if (bsOffcanvas) {
        bsOffcanvas.hide();
      }
    }
  });
});

// ============================================
// CART ITEM ANIMATIONS
// ============================================
const cartItems = document.querySelectorAll('.cart-item-premium');
cartItems.forEach((item, index) => {
  item.style.animationDelay = `${index * 0.1}s`;
});

// ============================================
// UPDATE CART COUNT IN NAVBAR
// ============================================
function updateCartCount() {
  fetch(`${baseUrl}index.php?r=/cart/count`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
    }
  })
  .then(response => response.json())
  .then(data => {
    const cartCountElements = document.querySelectorAll('.cart-count, #cart-count');
    cartCountElements.forEach(element => {
      if (data.count > 0) {
        element.textContent = data.count;
        element.style.display = 'flex';
      } else {
        element.style.display = 'none';
      }
    });
  })
  .catch(error => {
    console.error('Error updating cart count:', error);
  });
}

// ============================================
// CALCULATE AND UPDATE TOTALS
// ============================================
function updateTotals() {
  let subtotal = 0;
  const cartItems = document.querySelectorAll('.cart-item-premium');
  
  cartItems.forEach(item => {
    const priceText = item.querySelector('.cart-item-price')?.textContent || '0';
    const price = parseInt(priceText.replace(/[^\d]/g, ''));
    const quantity = parseInt(item.querySelector('.quantity-value-premium')?.value || 0);
    subtotal += price * quantity;
  });
  
  // Update UI
  const totalElement = document.getElementById('total-price');
  if (totalElement) {
    totalElement.textContent = `$${subtotal.toLocaleString('es-CO')}`;
  }
}

// ============================================
// KEYBOARD SHORTCUTS
// ============================================
document.addEventListener('keydown', (e) => {
  // ESC key closes offcanvas
  if (e.key === 'Escape' && offcanvasElement) {
    const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
    if (bsOffcanvas) {
      bsOffcanvas.hide();
    }
  }
  
  // Ctrl/Cmd + Delete clears cart
  if ((e.ctrlKey || e.metaKey) && e.key === 'Delete') {
    e.preventDefault();
    clearCart();
  }
});

// ============================================
// PREVENT DOUBLE SUBMIT
// ============================================
let isProcessing = false;
document.querySelectorAll('.btn-premium, .btn-cart-action').forEach(btn => {
  btn.addEventListener('click', function(e) {
    if (isProcessing && !this.hasAttribute('onclick')) {
      e.preventDefault();
      e.stopPropagation();
      return false;
    }
    
    isProcessing = true;
    setTimeout(() => {
      isProcessing = false;
    }, 1000);
  });
});

// ============================================
// TOOLTIPS INITIALIZATION
// ============================================
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

// ============================================
// PAGE VISIBILITY API
// ============================================
document.addEventListener('visibilitychange', () => {
  if (!document.hidden) {
    // User returned to page, refresh cart count
    updateCartCount();
    AOS.refresh();
  }
});

// ============================================
// CONFIRM BEFORE LEAVING WITH ITEMS
// ============================================
const hasItems = document.querySelectorAll('.cart-item-premium').length > 0;
if (hasItems) {
  window.addEventListener('beforeunload', (e) => {
    // Only show if navigating away from cart page
    const isLeavingCart = !window.location.href.includes('cart');
    if (isLeavingCart) {
      e.preventDefault();
      e.returnValue = '';
    }
  });
}

// ============================================
// AUTO-SAVE CART STATE
// ============================================
function saveCartState() {
  const cartState = {
    timestamp: Date.now(),
    items: []
  };
  
  document.querySelectorAll('.cart-item-premium').forEach(item => {
    const quantity = item.querySelector('.quantity-value-premium')?.value;
    const productId = item.querySelector('[onclick*="removeItem"]')?.getAttribute('onclick')?.match(/\d+/)?.[0];
    
    if (productId && quantity) {
      cartState.items.push({ productId, quantity });
    }
  });
  
  localStorage.setItem('cart_state', JSON.stringify(cartState));
}

// Save state on quantity change
document.querySelectorAll('.quantity-value-premium').forEach(input => {
  input.addEventListener('change', saveCartState);
});

// ============================================
// PRICE ANIMATION
// ============================================
function animateValue(element, start, end, duration) {
  const range = end - start;
  const increment = range / (duration / 16);
  let current = start;
  
  const timer = setInterval(() => {
    current += increment;
    if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
      current = end;
      clearInterval(timer);
    }
    element.textContent = `$${Math.round(current).toLocaleString('es-CO')}`;
  }, 16);
}

// ============================================
// CONSOLE LOG (DEVELOPMENT)
// ============================================
console.log('✅ Cart.js cargado correctamente');
console.log('🛒 Carrito de Compras - Movi Cell');
console.log('🎨 Diseño coherente con Home y Productos');

// ============================================
// INIT ON LOAD
// ============================================
document.addEventListener('DOMContentLoaded', () => {
  // Update cart count on load
  updateCartCount();
  
  // Initialize tooltips
  const tooltips = document.querySelectorAll('[title]');
  tooltips.forEach(el => {
    new bootstrap.Tooltip(el);
  });
  
  console.log('🚀 Cart initialized successfully');
});

// ============================================
// END OF CART.JS
// ============================================
