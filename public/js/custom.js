// Кнопка "Вверх"
const scrollBtn = document.getElementById('scrollTopBtn');
if (scrollBtn) {
    window.addEventListener('scroll', () => {
        scrollBtn.classList.toggle('show', window.scrollY > 300);
    });
    scrollBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

// Добавляем класс fade-in к основному контенту при загрузке
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('main').classList.add('fade-in');
});