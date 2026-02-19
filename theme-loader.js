document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    const currentTheme = localStorage.getItem('theme') || 'dark';

    // Aplicar el tema guardado al cargar
    document.documentElement.setAttribute('data-theme', currentTheme);
    updateIcon(currentTheme);

    themeToggle.addEventListener('click', () => {
        let theme = document.documentElement.getAttribute('data-theme');
        let newTheme = theme === 'dark' ? 'light' : 'dark';

        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        // Animación de rotación al cambiar
        themeIcon.style.transform = 'rotate(360deg)';
        setTimeout(() => {
            updateIcon(newTheme);
            themeIcon.style.transform = 'rotate(0deg)';
        }, 200);
    });

    function updateIcon(theme) {
        if (theme === 'light') {
            themeIcon.classList.replace('bi-moon-stars-fill', 'bi-sun-fill');
            themeIcon.style.color = '#ff9f43'; // Color naranja sol
        } else {
            themeIcon.classList.replace('bi-sun-fill', 'bi-moon-stars-fill');
            themeIcon.style.color = '#00d4ff'; // Color cian luna
        }
    }
});
