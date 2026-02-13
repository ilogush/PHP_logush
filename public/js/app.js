(function () {
    'use strict';

    function ensureCartBadges() {
        // SSR snapshots may not contain badge markup. Inject badges into header cart links.
        if (document.querySelector('[data-cart-count]')) return;

        const header = document.querySelector('header');
        if (!header) return;

        const cartLinks = header.querySelectorAll('a[href=\"/cart\"]');
        cartLinks.forEach(function (a) {
            if (!a || !(a instanceof HTMLAnchorElement)) return;
            if (a.querySelector('[data-cart-count]')) return;

            // Ensure anchor can position the badge.
            a.classList.add('relative');

            const badge = document.createElement('span');
            badge.setAttribute('data-cart-count', '');
            badge.className =
                'hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full ' +
                'items-center justify-center text-[10px] leading-none font-semibold ' +
                'bg-black text-white';
            badge.textContent = '';
            a.appendChild(badge);
        });
    }

    function setCartBadges(count) {
        const n = Math.max(0, parseInt(count || 0, 10) || 0);
        document.querySelectorAll('[data-cart-count]').forEach(function (el) {
            if (!el || !(el instanceof HTMLElement)) return;
            if (n > 0) {
                el.textContent = String(n);
                el.classList.remove('hidden');
                el.classList.add('flex');
            } else {
                el.textContent = '';
                el.classList.add('hidden');
                el.classList.remove('flex');
            }
        });
    }

    function refreshCartCount() {
        try {
            return fetch('/api/cart/count', { headers: { 'Accept': 'application/json' } })
                .then(function (res) { return res.ok ? res.json() : null; })
                .then(function (data) { if (data && typeof data.count !== 'undefined') setCartBadges(data.count); });
        } catch (_) {
            return Promise.resolve();
        }
    }

    function emitCartChanged() {
        try {
            window.dispatchEvent(new CustomEvent('cart:changed'));
        } catch (_) {
            // IE11 not supported; ignore.
        }
    }

    function onReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn, { once: true });
            return;
        }
        fn();
    }

    function getMenuPanel(openButton) {
        const scope = openButton.closest('header') || document;
        return (
            scope.querySelector('#mobile-menu') ||
            scope.querySelector('div.fixed.top-0.left-0.w-full.h-screen') ||
            null
        );
    }

    function initMobileMenu() {
        const openButtons = Array.from(
            document.querySelectorAll('#mobile-menu-button, [aria-label="Открыть меню"]')
        );
        const handled = new Set();

        openButtons.forEach(function (openButton, index) {
            const panel = getMenuPanel(openButton);
            if (!panel || handled.has(panel)) {
                return;
            }
            handled.add(panel);

            const closeButton =
                panel.querySelector('#mobile-menu-close') ||
                panel.querySelector('[aria-label="Закрыть меню"]');

            if (!panel.id) {
                panel.id = 'mobile-menu-' + String(index + 1);
            }
            openButton.setAttribute('aria-controls', panel.id);

            function openMenu() {
                panel.classList.remove('translate-x-full');
                panel.classList.add('translate-x-0');
                document.body.style.overflow = 'hidden';
                openButton.setAttribute('aria-expanded', 'true');
            }

            function closeMenu() {
                panel.classList.add('translate-x-full');
                panel.classList.remove('translate-x-0');
                document.body.style.overflow = '';
                openButton.setAttribute('aria-expanded', 'false');
            }

            openButton.addEventListener('click', function () {
                openMenu();
            });

            if (closeButton) {
                closeButton.addEventListener('click', function () {
                    closeMenu();
                });
            }

            panel.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    closeMenu();
                });
            });

            panel.addEventListener('click', function (event) {
                if (event.target === panel) {
                    closeMenu();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && panel.classList.contains('translate-x-0')) {
                    closeMenu();
                }
            });
        });
    }

    function initFAQ() {
        const faqButtons = document.querySelectorAll('[data-faq-button]');
        faqButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const answer = button.nextElementSibling;
                const icon = button.querySelector('svg');
                
                if (!answer) return;

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
    }

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

	    function showToast(message, type) {
	        if (typeof window.showToast === 'function') {
	            window.showToast(message, type);
	        }
	    }

    function validateForm(formId) {
        const form = document.getElementById(formId);
        if (!form) {
            return false;
        }

        let isValid = true;
        form.querySelectorAll('[required]').forEach(function (input) {
            const value = typeof input.value === 'string' ? input.value.trim() : '';
            const invalid = value === '';
            input.classList.toggle('border-red-500', invalid);
            if (invalid) {
                isValid = false;
            }
        });

        return isValid;
    }

    function addToCart(productId, color, size, quantity) {
        fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new URLSearchParams({
                productId: productId,
                color: color,
                size: size,
                quantity: String(quantity || 1),
            }),
        })
            .then(function (response) {
                if (response.ok) {
                    showToast('Товар добавлен в корзину', 'success');
                    refreshCartCount();
                    emitCartChanged();
                } else {
                    showToast('Ошибка при добавлении товара', 'error');
                }
            })
            .catch(function () {
                showToast('Ошибка при добавлении товара', 'error');
            });
    }

    function initCartRemove() {
        // Intercept /cart/remove to update cart badge immediately (and keep UI responsive).
        document.querySelectorAll('form[action=\"/cart/remove\"]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                // If JS disabled, it will still work via normal POST+redirect.
                e.preventDefault();

                const btn = form.querySelector('button[type=\"submit\"]');
                if (btn) btn.disabled = true;

                fetch('/cart/remove', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: new URLSearchParams(new FormData(form)),
                })
                    .then(function (res) { return res.ok ? res.json() : null; })
                    .then(function (data) {
                        refreshCartCount();
                        emitCartChanged();

                        // Totals are server-rendered; simplest reliable approach is to reload.
                        if (data && data.ok) {
                            showToast('Товар удалён', 'success');
                            window.location.reload();
                            return;
                        }
                        showToast('Ошибка при удалении товара', 'error');
                        if (btn) btn.disabled = false;
                    })
                    .catch(function () {
                        showToast('Ошибка при удалении товара', 'error');
                        if (btn) btn.disabled = false;
                    });
            });
        });
    }

    function initSlider(sliderId) {
        const slider = document.getElementById(sliderId);
        if (!slider) {
            return;
        }

        const slides = slider.querySelectorAll('[data-slide]');
        if (!slides.length) {
            return;
        }

        let currentSlide = 0;

        function showSlide(index) {
            slides.forEach(function (slide, i) {
                slide.style.display = i === index ? 'block' : 'none';
            });
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(currentSlide);
        }

        showSlide(0);
        setInterval(nextSlide, 5000);

        const prevBtn = slider.querySelector('[data-slider-prev]');
        const nextBtn = slider.querySelector('[data-slider-next]');
        if (prevBtn) {
            prevBtn.addEventListener('click', prevSlide);
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', nextSlide);
        }
    }

    // Анимация появления элементов при скролле
    function initScrollAnimations() {
        const elements = document.querySelectorAll('[data-animate]');
        if (!elements.length) {
            return;
        }

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const delay = entry.target.getAttribute('data-delay') || 0;
                    setTimeout(function() {
                        entry.target.classList.add('animate-in');
                    }, delay * 1000);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        });

        elements.forEach(function(el) {
            observer.observe(el);
        });
    }

    // Анимация счетчиков
    function initCounterAnimations() {
        const counters = document.querySelectorAll('[data-counter]');
        if (!counters.length) {
            return;
        }

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(function(counter) {
            observer.observe(counter);
        });
    }

    function animateCounter(element) {
        const target = parseInt(element.getAttribute('data-counter'));
        const duration = parseInt(element.getAttribute('data-duration') || 2000);
        const start = 0;
        const startTime = Date.now();

        function update() {
            const now = Date.now();
            const progress = Math.min((now - startTime) / duration, 1);
            const easeOutQuad = progress * (2 - progress);
            const current = Math.floor(start + (target - start) * easeOutQuad);
            
            element.textContent = current;
            
            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                element.textContent = target;
            }
        }

        update();
    }

	    function initReviews() {
	        const roots = Array.from(document.querySelectorAll('[data-reviews]'));
	        if (!roots.length) {
	            return;
	        }

	        const visibleCount = 3;

	        roots.forEach(function(reviewsRoot) {
	            const showAllButton = reviewsRoot.querySelector('[data-action="show-all-reviews"]');
	            const buttonWrap = showAllButton ? showAllButton.parentElement : null;
	            const items = Array.from(reviewsRoot.querySelectorAll('[data-review-item]'));

	            if (!showAllButton || !buttonWrap || !items.length) {
	                return;
	            }

	            if (items.length <= visibleCount) {
	                buttonWrap.style.display = 'none';
	                return;
	            }

	            // Показываем первые N, остальные скрываем.
	            items.forEach(function(item, idx) {
	                const shouldHide = idx >= visibleCount;
	                item.hidden = shouldHide;
	                if (shouldHide) {
	                    item.setAttribute('data-hidden', 'true');
	                    item.setAttribute('aria-hidden', 'true');
	                } else {
	                    item.removeAttribute('data-hidden');
	                    item.removeAttribute('aria-hidden');
	                }
	            });

	            showAllButton.addEventListener('click', function(e) {
	                e.preventDefault();

	                items.forEach(function(item) {
	                    item.hidden = false;
	                    item.removeAttribute('data-hidden');
	                    item.removeAttribute('aria-hidden');
	                });

	                buttonWrap.style.display = 'none';
	            });
	        });
	    }

    // Инициализация слайдеров на главной странице
    function initHomeSliders() {
        // Hero слайдер
        const heroSlider = document.querySelector('[data-hero-slider]');
        if (heroSlider) {
            const slides = Array.from(heroSlider.querySelectorAll('.hero-slide'));
            if (slides.length > 1) {
                let currentIndex = 0;
                
                function showNextSlide() {
                    slides[currentIndex].classList.remove('active');
                    currentIndex = (currentIndex + 1) % slides.length;
                    slides[currentIndex].classList.add('active');
                }
                
                setInterval(showNextSlide, 4000); // Смена каждые 4 секунды
            }
        }
        
        // Media strip слайдер
        const mediaSlider = document.querySelector('[data-media-slider]');
        if (mediaSlider) {
            const slides = Array.from(mediaSlider.querySelectorAll('.media-slide'));
            if (slides.length > 2) {
                let currentIndex = 0;
                
                function showNextMediaSlide() {
                    slides[currentIndex].classList.remove('active');
                    if (currentIndex + 1 < slides.length) {
                        slides[currentIndex + 1].classList.remove('active');
                    }
                    
                    currentIndex = (currentIndex + 2) % slides.length;
                    
                    slides[currentIndex].classList.add('active');
                    if (currentIndex + 1 < slides.length) {
                        slides[currentIndex + 1].classList.add('active');
                    } else {
                        slides[0].classList.add('active');
                    }
                }
                
                // Показываем первые 2 слайда
                if (slides.length > 1) {
                    slides[1].classList.add('active');
                }
                
                setInterval(showNextMediaSlide, 5000); // Смена каждые 5 секунд
            }
        }
    }

    onReady(function () {
        initMobileMenu();
        initFAQ();
        initScrollAnimations();
        initCounterAnimations();
        initReviews();
        initHomeSliders();
        initCartRemove();
        ensureCartBadges();
        refreshCartCount();
    });

    window.addEventListener('cart:changed', function () {
        refreshCartCount();
    });

	    window.openModal = openModal;
	    window.closeModal = closeModal;
	    // `window.showToast` is provided by `/js/toast.js` (shared UI). Keep a safe alias for legacy calls.
	    window.showToast = window.showToast || showToast;
	    window.validateForm = validateForm;
	    window.addToCart = addToCart;
        window.refreshCartCount = refreshCartCount;
    window.initSlider = initSlider;
})();
