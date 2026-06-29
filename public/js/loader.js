(function () {
    const loader = document.getElementById('pageLoader');
    if (!loader) return;

    const hideLoader = () => {
        if (!loader.classList.contains('hidden')) {
            loader.classList.add('hidden');
            setTimeout(() => {
                if (loader && loader.parentNode) {
                    loader.parentNode.removeChild(loader);
                }
            }, 500);
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        loader.style.opacity = '1';
    });

    window.addEventListener('load', () => {
        setTimeout(hideLoader, 200);
    });

    // Fallback hide after 5s if load never fires
    setTimeout(hideLoader, 5000);
})();
