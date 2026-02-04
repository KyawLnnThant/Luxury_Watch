const header = document.querySelector('.site-header');
const buttons = document.querySelectorAll('.btn');

document.addEventListener('scroll', () => {
    if (!header) return;
    header.classList.toggle('elevated', window.scrollY > 10);
});

const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.card, .hero').forEach(el => {
    el.classList.add('fade-target');
    observer.observe(el);
});

buttons.forEach(btn => {
    btn.addEventListener('click', () => {
        if (btn.dataset.loading) return;
        btn.dataset.loading = 'true';
        const original = btn.textContent;
        btn.textContent = 'Working…';
        setTimeout(() => {
            btn.textContent = original;
            delete btn.dataset.loading;
        }, 800);
    });
});
