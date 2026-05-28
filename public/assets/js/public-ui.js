(function () {
    const root = document.documentElement;
    const body = document.body;
    const storageKeys = {
        fontScale: 'mapi-font-scale',
        highContrast: 'mapi-high-contrast',
        readingMode: 'mapi-reading-mode'
    };
    const minScale = 0;
    const maxScale = 3;

    let currentScale = parseInt(localStorage.getItem(storageKeys.fontScale) || '0', 10);

    if (Number.isNaN(currentScale)) {
        currentScale = 0;
    }

    applyFontScale(currentScale);
    applyStoredClass('high-contrast', storageKeys.highContrast);
    applyStoredClass('reading-mode', storageKeys.readingMode);
    syncPressedStates();

    document.querySelectorAll('[data-font-scale]').forEach(function (button) {
        button.addEventListener('click', function () {
            const step = Number(button.getAttribute('data-font-scale') || '0');
            currentScale = Math.max(minScale, Math.min(maxScale, currentScale + step));
            localStorage.setItem(storageKeys.fontScale, String(currentScale));
            applyFontScale(currentScale);
        });
    });

    document.querySelectorAll('[data-toggle-contrast]').forEach(function (button) {
        button.addEventListener('click', function () {
            const enabled = !body.classList.contains('high-contrast');
            body.classList.toggle('high-contrast', enabled);
            localStorage.setItem(storageKeys.highContrast, enabled ? '1' : '0');
            syncPressedStates();
        });
    });

    document.querySelectorAll('[data-toggle-reading]').forEach(function (button) {
        button.addEventListener('click', function () {
            const enabled = !body.classList.contains('reading-mode');
            body.classList.toggle('reading-mode', enabled);
            localStorage.setItem(storageKeys.readingMode, enabled ? '1' : '0');
            syncPressedStates();
        });
    });

    document.querySelectorAll('[data-share-card]').forEach(function (card) {
        const title = card.getAttribute('data-title') || document.title;
        const url = card.getAttribute('data-url') || window.location.href;
        const nativeButton = card.querySelector('[data-share-native]');
        const copyButton = card.querySelector('[data-copy-link]');
        const feedback = card.querySelector('[data-copy-feedback]');

        if (nativeButton) {
            nativeButton.addEventListener('click', async function () {
                if (navigator.share) {
                    try {
                        await navigator.share({
                            title: 'MAPI CONECTA | ' + title,
                            text: 'Conheca ' + title + ' no MAPI CONECTA.',
                            url: url
                        });
                    } catch (error) {
                    }
                    return;
                }

                window.open('https://wa.me/?text=' + encodeURIComponent('Conheca ' + title + ' no MAPI CONECTA: ' + url), '_blank', 'noopener');
            });
        }

        if (copyButton) {
            copyButton.addEventListener('click', async function () {
                try {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        await navigator.clipboard.writeText(url);
                    } else {
                        const input = document.createElement('input');
                        input.value = url;
                        document.body.appendChild(input);
                        input.select();
                        document.execCommand('copy');
                        document.body.removeChild(input);
                    }

                    if (feedback) {
                        feedback.hidden = false;
                        window.setTimeout(function () {
                            feedback.hidden = true;
                        }, 2500);
                    }
                } catch (error) {
                }
            });
        }
    });

    function applyFontScale(scale) {
        root.style.setProperty('--font-scale', String(scale));
    }

    function applyStoredClass(className, storageKey) {
        if (localStorage.getItem(storageKey) === '1') {
            body.classList.add(className);
        }
    }

    function syncPressedStates() {
        document.querySelectorAll('[data-toggle-contrast]').forEach(function (button) {
            button.setAttribute('aria-pressed', body.classList.contains('high-contrast') ? 'true' : 'false');
        });

        document.querySelectorAll('[data-toggle-reading]').forEach(function (button) {
            button.setAttribute('aria-pressed', body.classList.contains('reading-mode') ? 'true' : 'false');
        });
    }
})();
