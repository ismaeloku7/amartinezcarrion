// Install banner: uses the native prompt where available (Chrome, Edge, Android)
// and shows manual instructions on iOS Safari. Dismissal is remembered per app for 14 days.
const DAYS = 14;
let deferredPrompt = null;

const banner = () => document.getElementById('pwa-install');
const key = () => 'pwa-dismissed-' + (banner()?.dataset.app || 'portal');
const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
const isIos = () => /iphone|ipad|ipod/i.test(navigator.userAgent) && !window.MSStream;
const dismissed = () => {
    try { const t = localStorage.getItem(key()); return t && Date.now() - Number(t) < DAYS * 86400000; } catch (e) { return false; }
};

function show(mode) {
    const el = banner();
    if (!el || isStandalone() || dismissed()) return;
    if (mode === 'ios') {
        document.getElementById('pwa-install-hint').textContent = 'En Safari: pulsa Compartir y luego «Añadir a pantalla de inicio».';
        document.getElementById('pwa-install-btn').style.display = 'none';
    }
    el.classList.remove('hidden');
}

function hide(remember = true) {
    banner()?.classList.add('hidden');
    if (remember) { try { localStorage.setItem(key(), String(Date.now())); } catch (e) {} }
}

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    show('native');
});

window.addEventListener('appinstalled', () => hide(true));

document.addEventListener('DOMContentLoaded', () => {
    if (isIos() && !isStandalone()) show('ios');
    document.getElementById('pwa-install-btn')?.addEventListener('click', async () => {
        if (!deferredPrompt) return hide(true);
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        deferredPrompt = null;
        hide(outcome === 'accepted');
    });
    document.getElementById('pwa-install-close')?.addEventListener('click', () => hide(true));
});
