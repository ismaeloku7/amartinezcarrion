// Registers the service worker of the current app and manages the Web Push subscription.
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;
const publicKey = () => document.querySelector('meta[name="push-public-key"]')?.content;
const appContext = () => document.querySelector('meta[name="app-context"]')?.content || 'portal';

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw = window.atob(base64);
    return Uint8Array.from([...raw].map((c) => c.charCodeAt(0)));
}

function setStatus(text) {
    const el = document.getElementById('push-status');
    if (el) el.textContent = text;
}

async function registration() {
    if (!('serviceWorker' in navigator)) return null;
    const scope = { portal: '/', mdp: '/mdp/', cards: '/fichas/' }[appContext()] || '/';
    return navigator.serviceWorker.register('/sw.js', { scope });
}

async function subscribe(app) {
    try {
        if (!('PushManager' in window)) return setStatus('Este navegador no admite notificaciones.');
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') return setStatus('Permiso de notificaciones denegado.');
        const reg = await registration();
        await navigator.serviceWorker.ready;
        const sub = await reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: urlBase64ToUint8Array(publicKey()) });
        const json = sub.toJSON();
        const res = await fetch('/push/subscribe', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' },
            body: JSON.stringify({ endpoint: json.endpoint, keys: json.keys, contentEncoding: (PushManager.supportedContentEncodings || ['aesgcm'])[0], app: app || appContext() }),
        });
        setStatus(res.ok ? 'Notificaciones activadas en este dispositivo.' : 'No se pudo guardar la suscripción.');
    } catch (e) {
        console.error(e);
        setStatus('No se pudieron activar las notificaciones.');
    }
}

async function unsubscribe() {
    try {
        const reg = await registration();
        const sub = await reg?.pushManager.getSubscription();
        if (!sub) return setStatus('No había notificaciones activas.');
        await fetch('/push/unsubscribe', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' },
            body: JSON.stringify({ endpoint: sub.endpoint }),
        });
        await sub.unsubscribe();
        setStatus('Notificaciones desactivadas.');
    } catch (e) {
        console.error(e);
    }
}

// Header bell: green dot when this device is subscribed; click toggles.
async function isSubscribed() {
    try {
        const reg = await navigator.serviceWorker?.getRegistration();
        return !!(await reg?.pushManager.getSubscription()) && Notification.permission === 'granted';
    } catch (e) { return false; }
}

async function refreshBell() {
    const dot = document.getElementById('push-bell-dot');
    const bell = document.getElementById('push-bell');
    if (!dot || !bell) return;
    const on = await isSubscribed();
    dot.classList.toggle('hidden', !on);
    bell.title = on ? 'Notificaciones activas en este dispositivo' : 'Activar notificaciones';
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('push-bell')?.addEventListener('click', async (e) => {
        const app = e.currentTarget.dataset.app;
        if (await isSubscribed()) {
            if (confirm('¿Desactivar las notificaciones en este dispositivo?')) await unsubscribe();
        } else {
            await subscribe(app);
        }
        await refreshBell();
    });
});

window.appPush = { subscribe, unsubscribe, refreshBell };
window.addEventListener('load', () => registration().then(refreshBell).catch(() => {}));
