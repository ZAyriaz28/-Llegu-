const btnDarkMode = document.getElementById('btnDarkMode');
const body = document.body;
const icon = btnDarkMode.querySelector('i');

// Cargar preferencia
if (localStorage.getItem('theme') === 'dark') {
    body.classList.add('dark-mode');
    icon.className = 'bi bi-sun-fill fs-4 text-warning';
}

btnDarkMode.addEventListener('click', () => {
    body.classList.toggle('dark-mode');
    const isDark = body.classList.contains('dark-mode');
    
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    
    if(isDark) {
        icon.className = 'bi bi-sun-fill fs-4 text-warning';
    } else {
        icon.className = 'bi bi-moon-stars fs-4';
    }
});