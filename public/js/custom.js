// Кнопка "Вверх"
const scrollBtn = document.getElementById('scrollTopBtn');
window.addEventListener('scroll', () => {
    if (window.scrollY > 300) {
        scrollBtn.classList.add('show');
    } else {
        scrollBtn.classList.remove('show');
    }
});
scrollBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// Добавляем класс fade-in к основному контенту при загрузке
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('main').classList.add('fade-in');
});