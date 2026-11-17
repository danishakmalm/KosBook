/**
 * KosBook - Vanilla JavaScript
 * Mengatur interaktivitas UI dasar
 */

document.addEventListener('DOMContentLoaded', function () {
    // Smooth scroll untuk link anchor lokal
    const localLinks = document.querySelectorAll('a[href^="#"]:not([data-bs-toggle])');
    localLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId && targetId !== '#') {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
});
