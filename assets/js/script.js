// Navigasi seluler
const navToggle = document.getElementById('navToggle');
const mainNav = document.getElementById('mainNav');
navToggle?.addEventListener('click', () => {
    const opened = mainNav.classList.toggle('open');
    navToggle.setAttribute('aria-expanded', opened ? 'true' : 'false');
});
mainNav?.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
    mainNav.classList.remove('open');
    navToggle?.setAttribute('aria-expanded', 'false');
}));

// Filter visual kategori menu; semua produk tetap ada di form pemesanan.
document.querySelectorAll('.category-pill').forEach(button => {
    button.addEventListener('click', () => {
        document.querySelectorAll('.category-pill').forEach(item => item.classList.remove('selected'));
        button.classList.add('selected');
        const filter = button.dataset.filter;
        document.querySelectorAll('.product-card').forEach(card => {
            card.hidden = filter !== 'all' && card.dataset.category !== filter;
        });
    });
});

// Cegah pengiriman jika belum ada produk yang dipilih.
document.getElementById('orderForm')?.addEventListener('submit', event => {
    const quantities = [...document.querySelectorAll('input[name^="qty["]')];
    const hasProduct = quantities.some(input => Number(input.value) > 0);
    if (!hasProduct) {
        event.preventDefault();
        alert('Silakan pilih minimal satu produk sebelum mengirim pesanan.');
        document.getElementById('menu')?.scrollIntoView({behavior: 'smooth'});
    }
});