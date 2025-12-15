document.addEventListener('DOMContentLoaded', function() {
  // Toggle password visibility
  window.togglePassword = function(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (!input || !icon) return;
    
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('bi-eye');
      icon.classList.add('bi-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.remove('bi-eye-slash');
      icon.classList.add('bi-eye');
    }
  };
  
  // Form validation feedback - Login
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
      const button = this.querySelector('button[type="submit"]');
      
      if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Iniciando sesión...';
      }
    });
  }
  
  // Form validation feedback - Register
  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    // Real-time password match validation
    const passwordConfirm = document.getElementById('password_confirm');
    if (passwordConfirm) {
      passwordConfirm.addEventListener('input', function() {
        const password = document.getElementById('password');
        if (!password) return;
        
        const passwordValue = password.value;
        const confirmValue = this.value;
        
        if (confirmValue && passwordValue !== confirmValue) {
          this.style.borderColor = '#dc2626';
          this.style.background = '#fee2e2';
        } else if (confirmValue) {
          this.style.borderColor = '#059669';
          this.style.background = '#d1fae5';
        } else {
          this.style.borderColor = '';
          this.style.background = '';
        }
      });
    }
    
    // Form submit validation
    registerForm.addEventListener('submit', function(e) {
      const password = document.getElementById('password');
      const passwordConfirm = document.getElementById('password_confirm');
      
      if (password && passwordConfirm && password.value !== passwordConfirm.value) {
        e.preventDefault();
        
        // Remove existing alerts
        const existingAlert = this.querySelector('.alert-danger-premium');
        if (existingAlert) {
          existingAlert.remove();
        }
        
        // Create new alert
        const alert = document.createElement('div');
        alert.className = 'alert-premium alert-danger-premium';
        alert.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i><span>Las contraseñas no coinciden. Por favor, verifica e intenta nuevamente.</span>';
        
        const authHeader = this.querySelector('.auth-header');
        if (authHeader) {
          authHeader.after(alert);
        }
        
        passwordConfirm.focus();
        passwordConfirm.style.borderColor = '#dc2626';
        return false;
      }
      
      const button = this.querySelector('button[type="submit"]');
      if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Creando cuenta...';
      }
    });
  }
  
  // Auto-hide alerts after 5 seconds
  const alerts = document.querySelectorAll('.alert-premium');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.5s ease';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }, 5000);
  });
  
  // Animación de entrada para el card
  const authCard = document.querySelector('.auth-card-premium');
  if (authCard) {
    authCard.style.opacity = '0';
    authCard.style.transform = 'translateY(30px)';
    
    setTimeout(() => {
      authCard.style.transition = 'all 0.6s ease-out';
      authCard.style.opacity = '1';
      authCard.style.transform = 'translateY(0)';
    }, 100);
  }
});
