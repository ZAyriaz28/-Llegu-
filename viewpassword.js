document.addEventListener('DOMContentLoaded', function() {
    const btnToggle = document.getElementById('btnToggle');
    const passInput = document.getElementById('pass');
    const icono = document.getElementById('icono');

    if (btnToggle && passInput) {
        btnToggle.addEventListener('click', function() {
            // Cambiar el tipo de input
            if (passInput.type === 'password') {
                passInput.type = 'text';
                icono.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passInput.type = 'password';
                icono.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    }
});
