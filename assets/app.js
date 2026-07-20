import './styles/app.css';

document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.querySelector('[data-theme-toggle]');

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const isDark = document.documentElement.classList.toggle('theme-dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    }

    const billingForms = document.querySelectorAll('.billing-form, .auth-form');

    billingForms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            const totalInput = form.querySelector('input[name="total_amount"]');
            const entryInput = form.querySelector('input[name="entry_amount"]');
            const patientSelect = form.querySelector('select[name="patient_id"]');

            if (totalInput && entryInput) {
                const total = Number(totalInput.value || 0);
                const entry = Number(entryInput.value || 0);

                if (entry > total) {
                    event.preventDefault();
                    window.alert('A entrada não pode ser maior que o valor total.');
                    return;
                }
            }

            if (patientSelect && patientSelect.value === '') {
                event.preventDefault();
                window.alert('Selecione um paciente.');
                return;
            }
        });
    });
});
