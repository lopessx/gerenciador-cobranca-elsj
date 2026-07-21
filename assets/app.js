import './styles/app.css';

document.addEventListener('DOMContentLoaded', () => {
    // Theme toggle
    const themeToggle = document.querySelector('[data-theme-toggle]');
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const isDark = document.documentElement.classList.toggle('theme-dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    }

    // Billing form validation
    const billingForms = document.querySelectorAll('.billing-form');
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

            if (patientSelect && !patientSelect.value) {
                event.preventDefault();
                window.alert('Selecione um paciente.');
                return;
            }
        });
    });

    // Header scroll effect
    const header = document.getElementById('main-header');
    const mainContent = document.querySelector('main');
    if (header && mainContent) {
        mainContent.addEventListener('scroll', () => {
            if (mainContent.scrollTop > 50) {
                header.style.background = 'rgba(18, 19, 23, 0.8)';
                header.style.backdropFilter = 'blur(12px)';
                header.style.webkitBackdropFilter = 'blur(12px)';
            } else {
                header.style.background = '';
                header.style.backdropFilter = '';
                header.style.webkitBackdropFilter = '';
            }
        });
    }

    // Button micro-interactions
    document.querySelectorAll('button, a').forEach(el => {
        el.addEventListener('mousedown', () => { el.style.opacity = '0.8'; });
        el.addEventListener('mouseup', () => { el.style.opacity = '1'; });
        el.addEventListener('mouseleave', () => { el.style.opacity = '1'; });
    });

    // Mobile sidebar toggle
    const menuBtn = document.querySelector('.md\\:hidden.material-symbols-outlined');
    const sidebar = document.querySelector('aside');
    if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('fixed');
            sidebar.classList.toggle('inset-0');
            sidebar.classList.toggle('z-50');
        });
    }

    // Installment slider preview for new billing form
    const slider = document.getElementById('installment_slider');
    const countDisplay = document.getElementById('installment_count');
    const totalInput = document.getElementById('total_amount');
    const entryInput = document.getElementById('entry_amount');
    const previewRemaining = document.getElementById('preview_remaining');
    const installmentList = document.getElementById('installment_list');

    function updatePreview() {
        if (!slider) return;
        const count = parseInt(slider.value) || 3;
        const total = parseFloat(totalInput?.value) || 0;
        const entry = parseFloat(entryInput?.value) || 0;
        const remaining = Math.max(0, total - entry);
        const perInstallment = count > 0 ? (remaining / count) : 0;

        if (countDisplay) countDisplay.innerText = count;
        if (previewRemaining) previewRemaining.innerText = 'R$ ' + remaining.toFixed(2).replace('.', ',');

        if (!installmentList) return;
        const now = new Date();
        const months = ['JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ'];
        let html = '';
        for (let i = 1; i <= Math.min(count, 24); i++) {
            const dueDate = new Date(now.getFullYear(), now.getMonth() + i, now.getDate());
            const label = i === 1 ? 'INÍCIO DO LIVRO' : (i === count ? 'ACERTO FINAL' : 'RECORRENTE');
            const isDimmed = i > 3 && i < count;
            html += `
                <div class="flex items-center justify-between py-4 border-b border-outline-variant/10 group hover:bg-surface-container-high px-2 transition-colors ${isDimmed ? 'opacity-50' : ''}">
                    <div class="flex items-center gap-4">
                        <span class="text-outline font-label-sm font-mono">${String(i).padStart(2, '0')}/${String(count).padStart(2, '0')}</span>
                        <div class="flex flex-col">
                            <span class="text-on-surface font-label-md">${String(dueDate.getDate()).padStart(2, '0')} ${months[dueDate.getMonth()]} ${dueDate.getFullYear()}</span>
                            <span class="text-[10px] text-outline font-label-sm">${label}</span>
                        </div>
                    </div>
                    <span class="text-primary font-bold">R$ ${perInstallment.toFixed(2).replace('.', ',')}</span>
                </div>
            `;
        }
        installmentList.innerHTML = html;
    }

    if (slider) slider.addEventListener('input', updatePreview);
    if (totalInput) totalInput.addEventListener('input', updatePreview);
    if (entryInput) entryInput.addEventListener('input', updatePreview);

    // Run initial preview after a short delay for form fields to settle
    setTimeout(updatePreview, 200);
});