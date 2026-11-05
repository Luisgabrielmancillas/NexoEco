export function initializeStoreCarousels() {
    document.querySelectorAll('[data-store-carousel]').forEach((carousel) => {
        if (carousel.dataset.enhanced) return;
        carousel.dataset.enhanced = 'true';
        const track = carousel.querySelector('[data-carousel-track]');
        const cards = [...track.children];
        const motion = matchMedia('(prefers-reduced-motion: reduce)');
        const delay = Number(carousel.dataset.interval) || 5000;
        let paused = motion.matches;
        let hovering = false;
        let focused = false;
        let touching = false;
        let visible = false;
        let suspended = false;
        let timer;
        let scrollTimer;
        let stops = [0];
        const cancel = () => { clearTimeout(timer); timer = null; };
        const schedule = () => {
            cancel();
            if (paused || hovering || focused || touching || !visible || document.hidden || suspended || stops.length < 2) return;
            timer = setTimeout(() => { advance(1); schedule(); }, delay);
        };
        const update = () => {
            const bounds = track.getBoundingClientRect();
            cards.forEach((card) => {
                const rect = card.getBoundingClientRect();
                const inView = rect.left >= bounds.left - 1 && rect.right <= bounds.right + 1;
                // Offscreen links must not steal focus or expose hidden cards to readers.
                card.inert = !inView && !card.contains(document.activeElement);
            });
        };
        const measure = () => {
            const maximum = Math.max(0, track.scrollWidth - track.clientWidth);
            const first = cards[0]?.offsetLeft || 0;
            stops = [...new Set(cards.map(card => Math.round(Math.min(maximum, Math.max(0, card.offsetLeft - first)))))];
            if (!stops.length) stops = [0];
            update();
            schedule();
        };
        const advance = (direction) => {
            const current = stops.reduce((best, stop, index) => Math.abs(stop - track.scrollLeft) < Math.abs(stops[best] - track.scrollLeft) ? index : best, 0);
            const next = (current + direction + stops.length) % stops.length;
            track.scrollTo({ left: stops[next], behavior: motion.matches ? 'instant' : 'smooth' });
        };
        track.addEventListener('keydown', event => {
            if (event.target !== track || !['ArrowLeft', 'ArrowRight', 'Home', 'End', ' '].includes(event.key)) return;
            event.preventDefault();
            if (event.key === ' ') { paused = !paused; schedule(); }
            else if (event.key === 'Home' || event.key === 'End') track.scrollTo({ left: event.key === 'Home' ? 0 : stops.at(-1), behavior: 'instant' });
            else advance(event.key === 'ArrowRight' ? 1 : -1);
        });
        track.addEventListener('focusin', () => { focused = true; cancel(); });
        track.addEventListener('focusout', () => requestAnimationFrame(() => { focused = track.contains(document.activeElement); schedule(); }));
        track.addEventListener('pointerdown', () => { touching = true; cancel(); }, { passive: true });
        const release = () => { if (touching) { touching = false; schedule(); } };
        window.addEventListener('pointerup', release, { passive: true });
        window.addEventListener('pointercancel', release, { passive: true });
        track.addEventListener('scroll', () => { clearTimeout(scrollTimer); scrollTimer = setTimeout(update, 120); }, { passive: true });
        carousel.addEventListener('mouseenter', () => { hovering = true; cancel(); });
        carousel.addEventListener('mouseleave', () => { hovering = false; schedule(); });
        document.addEventListener('visibilitychange', schedule);
        motion.addEventListener('change', () => { paused = motion.matches; schedule(); });
        window.addEventListener('pagehide', () => { suspended = true; cancel(); clearTimeout(scrollTimer); });
        window.addEventListener('pageshow', () => { suspended = false; measure(); });
        new ResizeObserver(measure).observe(track);
        new IntersectionObserver(entries => { visible = entries[0].isIntersecting; schedule(); }, { threshold: .1 }).observe(carousel);
        measure();
    });
}
