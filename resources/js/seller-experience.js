export function initializeSellerExperience() {
    const workspace = document.querySelector('.growth-workspace');
    if (!workspace) return;
    const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const animations = new Set();
    const animate = (element, delay = 0) => {
        if (motion.matches || typeof element.animate !== 'function') return;
        const animation = element.animate([
            { opacity: 0.4, transform: 'translateY(14px)' },
            { opacity: 1, transform: 'translateY(0)' },
        ], { duration: 480, delay, easing: 'cubic-bezier(.2,.75,.25,1)' });
        animations.add(animation);
        animation.finished.then(() => animations.delete(animation)).catch(() => animations.delete(animation));
    };
    if ('IntersectionObserver' in window && !motion.matches) {
        const observer = new IntersectionObserver((entries) => {
            entries.filter((entry) => entry.isIntersecting).forEach((entry, index) => {
                // Content stays visible even when animation is unsupported.
                animate(entry.target, Math.min(index * 45, 135));
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.06 });
        workspace.querySelectorAll('.growth-hero, .subscription-hero, .seller-card, .growth-table-wrap, .growth-empty').forEach((item) => {
            if (!item.parentElement.closest('.seller-card, .growth-empty')) observer.observe(item);
        });
        motion.addEventListener('change', () => {
            if (!motion.matches) return;
            observer.disconnect();
            animations.forEach((animation) => animation.cancel());
        });
    }
    workspace.querySelectorAll('textarea[maxlength]').forEach((field, index) => {
        const counter = document.createElement('span');
        counter.className = 'growth-counter';
        counter.id = `growth-character-count-${index}`;
        field.insertAdjacentElement('afterend', counter);
        field.setAttribute('aria-describedby', [field.getAttribute('aria-describedby'), counter.id].filter(Boolean).join(' '));
        const update = () => {
            counter.textContent = `${field.value.length.toLocaleString('es-MX')} / ${field.maxLength.toLocaleString('es-MX')} caracteres`;
            counter.classList.toggle('is-near-limit', field.value.length >= field.maxLength * 0.9);
        };
        field.addEventListener('input', update);
        update();
    });
    workspace.querySelectorAll('[data-growth-compose]').forEach((form) => {
        const title = form.querySelector('[name="title"]');
        const body = form.querySelector('[name="body"], [name="description"]');
        const titlePreview = form.querySelector('[data-preview-title]');
        const bodyPreview = form.querySelector('[data-preview-body]');
        const defaultTitle = titlePreview?.textContent;
        const defaultBody = bodyPreview?.textContent;
        const update = () => {
            if (titlePreview) titlePreview.textContent = title.value.trim() || defaultTitle;
            if (bodyPreview) bodyPreview.textContent = body.value.trim() || defaultBody;
            const discount = form.querySelector('[name="discount_percent"]');
            const discountPreview = form.querySelector('[data-preview-discount]');
            if (discountPreview) {
                const value = Number(discount.value);
                discountPreview.textContent = value >= 1 && value <= 90 ? `${value}% de descuento` : 'Oferta especial';
            }
            const product = form.querySelector('[name="id_producto"]');
            const productPreview = form.querySelector('[data-preview-product]');
            if (productPreview) productPreview.textContent = product.value ? product.selectedOptions[0].textContent : 'Tu producto seleccionado';
            const timingPreview = form.querySelector('[data-preview-timing]');
            const date = form.querySelector('[name="publish_at"]');
            if (timingPreview) {
                // Compare Manzanillo wall times without using the device's timezone.
                const value = date?.value;
                const parts = new Intl.DateTimeFormat('en-CA', { timeZone: 'America/Mexico_City', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hourCycle: 'h23' }).formatToParts(new Date());
                const part = (type) => parts.find((item) => item.type === type).value;
                const localNow = `${part('year')}-${part('month')}-${part('day')}T${part('hour')}:${part('minute')}`;
                timingPreview.textContent = value && value > localNow ? `Programada · ${value.slice(8, 10)}/${value.slice(5, 7)}/${value.slice(0, 4)} ${value.slice(11, 16)}` : 'Publicación inmediata';
            }
        };
        form.addEventListener('input', update);
        form.addEventListener('change', update);
        update();
    });
    const designForm = workspace.querySelector('[data-design-form]');
    const designPreview = workspace.querySelector('[data-design-preview]');
    if (designForm && designPreview) {
        const update = () => {
            designPreview.dataset.theme = designForm.elements.theme.value;
            designPreview.querySelector('[data-design-tagline]').textContent = designForm.elements.tagline.value.trim() || 'Una frase que invite a conocerte';
        };
        designForm.addEventListener('input', update);
        designForm.addEventListener('change', update);
        update();
    }
    const days = workspace.querySelectorAll('[data-calendar-day]');
    const appointments = workspace.querySelectorAll('[data-calendar-appointment]');
    const filter = workspace.querySelector('[data-calendar-filter]');
    const empty = workspace.querySelector('[data-calendar-empty]');
    if (days.length && filter) {
        days.forEach((day) => day.addEventListener('click', () => {
            days.forEach((item) => { item.dataset.selected = String(item === day); });
            let count = 0;
            appointments.forEach((appointment) => {
                const visible = appointment.dataset.calendarAppointment === day.dataset.calendarDay;
                appointment.hidden = !visible;
                if (visible) count++;
            });
            workspace.querySelector('[data-calendar-month-empty]')?.setAttribute('hidden', '');
            filter.hidden = false;
            filter.querySelector('[data-calendar-filter-label]').textContent = `${day.dataset.calendarLabel} · ${count} ${count === 1 ? 'entrega registrada' : 'entregas registradas'}`;
            empty.hidden = count !== 0;
        }));
        filter.querySelector('[data-calendar-reset]').addEventListener('click', () => {
            days.forEach((day) => { delete day.dataset.selected; });
            appointments.forEach((appointment) => { appointment.hidden = false; });
            workspace.querySelector('[data-calendar-month-empty]')?.removeAttribute('hidden');
            filter.hidden = true;
            empty.hidden = true;
            const heading = workspace.querySelector('#calendar-deliveries h2');
            heading.setAttribute('tabindex', '-1');
            heading.focus({ preventScroll: true });
        });
    }
    workspace.querySelectorAll('form[method="POST"]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (event.defaultPrevented || !event.submitter) return;
            const button = event.submitter;
            if (button.querySelector('.growth-submit-indicator')) return;
            const spinner = document.createElement('span');
            spinner.className = 'growth-submit-indicator';
            spinner.setAttribute('aria-hidden', 'true');
            button.prepend(spinner);
            button.setAttribute('aria-busy', 'true');
        });
    });
    window.addEventListener('pageshow', () => {
        workspace.querySelectorAll('.growth-submit-indicator').forEach((indicator) => {
            indicator.parentElement.removeAttribute('aria-busy');
            indicator.remove();
        });
    });
}
