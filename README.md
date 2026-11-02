# Portal Antonio Martínez

Portal con suscripciones para los libros, prácticas y tests de Antonio Martínez.
Laravel 13 · Livewire 4 · Tailwind 4 · Filament 5 · PayPal (REST API) · Web Push.

## Módulos

| Módulo | Ruta | Descripción |
|---|---|---|
| Portal | `/` | Inicio, planes, cuenta, tests |
| Devocional (MDP) | `/mdp` | Método Devocional Proyectivo. PWA instalable por separado |
| Fichas | `/fichas` | Programa de las 42 fichas. PWA instalable por separado |
| Tests | `/tests` | Motor de tests; implementado el tipo Ventana de Johari |
| Admin | `/admin` | Panel Filament: usuarios, idiomas, productos, planes, accesos, fichas, listas de palabras, tests |

La especificación funcional está en `docs/`.

## Puesta en marcha local

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan webpush:vapid          # claves VAPID para notificaciones push
touch database/database.sqlite
php artisan migrate --seed         # idiomas, catálogo, 42 fichas, test Johari, admin@example.com / password
npm run build
php artisan serve
```

Para los recordatorios en local: `php artisan reminders:send` (o `php artisan schedule:work`).

## Tests

```bash
php artisan test
```

## Despliegue en Forge

1. PHP 8.3 o superior con las extensiones `bcmath` o `gmp` (necesarias para Web Push).
2. Base de datos MySQL o PostgreSQL: ajustar `DB_*` en `.env`.
3. Script de despliegue:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   php artisan migrate --force
   php artisan db:seed --force      # idempotente: solo crea lo que falta
   php artisan optimize
   ```
4. Programador de tareas (cron de Forge): `php artisan schedule:run` cada minuto. Envía los recordatorios push.
5. Cola: `QUEUE_CONNECTION=database` con un worker de Forge, o `sync` si no se quiere worker.
6. PayPal (guía paso a paso en `docs/05-paypal.md`):
   - `PAYPAL_CLIENT_ID`, `PAYPAL_SECRET`, `PAYPAL_MODE` (`sandbox` o `live`) y `PAYPAL_WEBHOOK_ID` en `.env`.
     Mientras falten el client id o el secret, el portal oculta el pago en línea.
   - Webhook en la app de PayPal apuntando a `https://dominio/paypal/webhook` con los eventos
     `BILLING.SUBSCRIPTION.*` (activated, updated, cancelled, suspended, expired, payment.failed),
     `PAYMENT.SALE.COMPLETED`, `CHECKOUT.ORDER.APPROVED` y `PAYMENT.CAPTURE.COMPLETED`.
   - Los planes mensuales y anuales se crean solos en PayPal al guardarlos en el panel (Planes). Para
     los del catálogo inicial: `php artisan paypal:sync-plans`. Los planes de pago único no necesitan
     nada en PayPal: se cobran por importe.
7. Web Push: `php artisan webpush:vapid` una vez y guardar `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`
   y `VAPID_SUBJECT` en `.env`. Las PWA requieren HTTPS.
8. Primer administrador: crear el usuario y marcar `is_admin` desde tinker, o editar
   `admin@example.com` creado por el seeder en entorno local.

## Cómo funciona el acceso

`products` son las cosas con acceso (práctica MDP, práctica Fichas, un test de pago…).
`plans` son lo que se compra: precio, periodicidad y uno o varios productos.
`entitlements` es la relación usuario-producto con fechas, generada desde PayPal
(vuelta del pago y webhooks), desde una compra única o manualmente desde el panel. Los módulos solo
preguntan `hasAccess(user, 'mdp')` a través del middleware `access:mdp`.
