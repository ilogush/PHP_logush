// Простая версия FAQ
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('[data-faq-button]');

    buttons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();

            const answer = btn.nextElementSibling;
            const icon = btn.querySelector('svg');
            
            if (!answer) {
                return;
            }
            
            const isOpen = answer.getAttribute('data-open') === 'true';
            
            if (!isOpen) {
                answer.style.display = 'block';
                setTimeout(function() {
                    answer.style.maxHeight = answer.scrollHeight + 20 + 'px';
                    answer.style.opacity = '1';
                }, 10);
                answer.setAttribute('data-open', 'true');
                if (icon) icon.style.transform = 'rotate(45deg)';
            } else {
                answer.style.maxHeight = '0';
                answer.style.opacity = '0';
                answer.setAttribute('data-open', 'false');
                if (icon) icon.style.transform = 'rotate(0deg)';
                setTimeout(function() {
                    answer.style.display = 'none';
                }, 300);
            }
        });
    });
});
