document.addEventListener('DOMContentLoaded', function() {
    // ==========================================
    // FLOATING LABELS ANIMATION
    // ==========================================
    const inputs = document.querySelectorAll('.form-control-elite');
    inputs.forEach(input => {
        // Check if input has value on load
        if (input.value) {
            input.classList.add('has-value');
        }
        
        input.addEventListener('input', function() {
            if (this.value) {
                this.classList.add('has-value');
            } else {
                this.classList.remove('has-value');
            }
        });
    });

    // ==========================================
    // PASSWORD VALIDATION
    // ==========================================
    const passForm = document.querySelector('form[action*="password"]');
    if (passForm) {
        const pass1 = passForm.querySelector('#password');
        const pass2 = passForm.querySelector('#password_confirm');
        
        function validatePasswords() {
            if (pass1.value && pass2.value) {
                if (pass1.value === pass2.value) {
                    pass2.setCustomValidity('');
                    pass2.style.borderColor = 'var(--success)';
                } else {
                    pass2.setCustomValidity('Las contraseñas no coinciden');
                    pass2.style.borderColor = 'var(--danger)';
                }
            }
        }
        
        pass1.addEventListener('input', validatePasswords);
        pass2.addEventListener('input', validatePasswords);
    }

    // ==========================================
    // DELETE ACCOUNT CONFIRMATION
    // ==========================================
    const deleteForm = document.getElementById('deleteAccountForm');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const confirmMessage = '⚠️ ¿ESTÁS COMPLETAMENTE SEGURO?\n\n' +
                                 '✓ Se eliminará tu cuenta permanentemente\n' +
                                 '✓ Se borrarán todos tus datos\n' +
                                 '✓ Esta acción NO se puede deshacer\n\n' +
                                 'Para confirmar, escribe: ELIMINAR';
            
            const userConfirmation = prompt(confirmMessage);
            
            if (userConfirmation === 'ELIMINAR') {
                deleteForm.submit();
            } else if (userConfirmation !== null) {
                alert('❌ Confirmación incorrecta. Tu cuenta NO ha sido eliminada.');
            }
        });
    }

    // ==========================================
    // STAGGER ANIMATION FOR CARDS
    // ==========================================
    const cards = document.querySelectorAll('.animate-in');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });

    // ==========================================
    // FORM SUBMISSION FEEDBACK
    // ==========================================
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
                
                // Re-enable after 3 seconds in case of error
                setTimeout(() => {
                    submitBtn.disabled = false;
                    const originalIcon = submitBtn.querySelector('i').className.replace('bi-hourglass-split', 'bi-check2-circle');
                    submitBtn.innerHTML = submitBtn.innerHTML.replace('Procesando...', 'Actualizar');
                }, 3000);
            }
        });
    });
});
