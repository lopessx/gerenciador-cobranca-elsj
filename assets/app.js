import './bootstrap.js';
import './styles/app.css';

const themeToggle = document.querySelector('[data-theme-toggle]');

if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        const isDark = document.documentElement.classList.toggle('theme-dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });
}

const form = document.querySelector('.billing-form');

if (form) {
    form.addEventListener('submit', (event) => {
        const total = Number(form.querySelector('input[name="total_amount"]').value || 0);
        const entry = Number(form.querySelector('input[name="entry_amount"]').value || 0);

        if (entry > total) {
            event.preventDefault();
            window.alert('A entrada não pode ser maior que o valor total.');
        }
    });
}
