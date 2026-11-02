# Pagos con PayPal

El portal cobra con la API REST de PayPal, sin paquetes externos:

| Qué | API de PayPal | Dónde en el código |
|---|---|---|
| Pago único (`interval = once`) | Orders v2: se crea la orden con el importe del plan y se captura al volver | `App\Services\PayPal\PayPalBilling::createOrder / captureOrder` |
| Suscripción (`month` / `year`) | Subscriptions v1: se crea la suscripción sobre el plan de PayPal (`P-…`) del plan | `PayPalBilling::createSubscription / syncSubscription` |
| Alta de planes | Catalog Products v1 + Billing Plans v1: el panel crea el producto y el plan al guardar | `App\Services\PayPal\PayPalPlans`, `php artisan paypal:sync-plans` |
| Confirmación | Webhooks firmados (`/paypal/webhook`) | `App\Http\Controllers\PayPalWebhookController` |
| Cancelación por el usuario | `POST /v1/billing/subscriptions/{id}/cancel` desde Mi cuenta | `CheckoutController::cancelSubscription` |

Flujo: el usuario pulsa «Pagar con PayPal» en `/planes/{producto}` → se le envía a PayPal →
vuelve a `/checkout/success` → el portal captura la orden (o relee la suscripción) y concede
el acceso. Los webhooks repiten la misma sincronización, así que el acceso se concede aunque
el usuario cierre la pestaña antes de volver, y se cierra cuando PayPal cancela, suspende o
expira la suscripción (se conserva hasta `paid_through_at`, el final del periodo pagado).

Mientras falten `PAYPAL_CLIENT_ID` o `PAYPAL_SECRET`, el portal muestra «Pago en línea no
disponible todavía» y la ruta de checkout devuelve 404. Un plan mensual o anual sin
`paypal_plan_id` tampoco se puede comprar; un plan de pago único solo necesita precio.

## Variables de entorno

```dotenv
PAYPAL_CLIENT_ID=        # Client ID de la app (sandbox o live)
PAYPAL_SECRET=           # Secret de la misma app
PAYPAL_MODE=sandbox      # sandbox mientras se prueba, live en producción
PAYPAL_WEBHOOK_ID=       # ID del webhook creado en esa app (empieza por números/letras, p. ej. 8PT597110X687430LKGECATA)
PAYPAL_CURRENCY=EUR
PAYPAL_PLAN_MDP_MONTH=   # P-… (opcional: solo se usan en el primer seed; después se editan en el panel)
PAYPAL_PLAN_MDP_YEAR=
PAYPAL_PLAN_CARDS_MONTH=
PAYPAL_PLAN_CARDS_YEAR=
PAYPAL_PLAN_BOOKS_YEAR=
```

## Configuración en PayPal, paso a paso

Hay que hacerlo dos veces: primero en **Sandbox** (para probar con dinero ficticio) y después
en **Live** con la cuenta Business real. Los pasos son idénticos; solo cambia el conmutador
Sandbox/Live del panel de desarrollador y los valores de `.env`.

### 1. Cuenta y app

1. Entrar en https://developer.paypal.com/dashboard/ con la cuenta PayPal **Business** del
   titular (si solo tiene cuenta personal, convertirla a Business desde paypal.com → Configuración).
2. Menú **Apps & Credentials**. Arriba, elegir **Sandbox** o **Live**.
3. **Create App** → nombre `Portal Antonio Martínez` → tipo **Merchant** → Create App.
4. Copiar **Client ID** y **Secret** (botón *Show*). Van a `PAYPAL_CLIENT_ID` y `PAYPAL_SECRET`.
5. En la misma pantalla de la app, en **Features**, dejar marcados **Accept payments** y
   **Subscriptions** (en Live aparecen como *Accept payments* → *Subscriptions*). Guardar.

### 2. Webhook

1. En la pantalla de la app, bajar a **Webhooks** → **Add Webhook**.
2. Webhook URL: `https://TU-DOMINIO/paypal/webhook` (debe ser HTTPS y estar publicado; para
   sandbox vale un dominio de staging).
3. Event types: marcar exactamente estos:
   - `Billing subscription activated`
   - `Billing subscription updated`
   - `Billing subscription re-activated`
   - `Billing subscription cancelled`
   - `Billing subscription suspended`
   - `Billing subscription expired`
   - `Billing subscription payment failed`
   - `Payment sale completed`
   - `Checkout order approved`
   - `Payment capture completed`
4. Save. Copiar el **Webhook ID** que aparece en la lista → `PAYPAL_WEBHOOK_ID`.

### 3. Planes de suscripción: los crea el portal

No hay que crear nada a mano en PayPal. Con las credenciales en `.env`, cada vez que se
guarda un plan mensual o anual en `/admin/plans` el portal:

1. Crea (una sola vez) un producto digital en el catálogo de PayPal (`paypal_product_id`).
2. Crea el plan de suscripción con el mismo importe, moneda, periodicidad y días de prueba
   (ciclo TRIAL gratuito) y guarda su `P-…` en **PayPal plan ID**.
3. Si después cambias el precio, la periodicidad, la moneda o los días de prueba, crea un
   plan nuevo y desactiva el anterior (PayPal no permite cambiar el precio de un plan; los
   suscriptores existentes siguen pagando el precio antiguo). Si solo cambias el nombre o la
   descripción, actualiza el plan existente. Si desactivas el plan en el panel, lo desactiva
   también en PayPal.

El botón **Sincronizar con PayPal** (en la lista y en la edición de cada plan) repite esa
operación a mano, por ejemplo tras configurar las credenciales o tras pasar de sandbox a live.

Para los cinco planes del catálogo inicial, o tras cambiar de cuenta de PayPal, basta con:

```bash
php artisan paypal:sync-plans
```

El pago único no necesita nada en PayPal: se cobra por importe en el momento.

### 4. Probar en sandbox

1. Developer → **Sandbox → Accounts**: hay una cuenta *Personal* de prueba (comprador) con su
   contraseña (botón ⋯ → *View/Edit account*). Si no hay saldo, editar la cuenta y añadirlo.
2. Con `PAYPAL_MODE=sandbox` y las credenciales sandbox en `.env`, entrar en el portal con un
   usuario normal, ir a `/planes/mdp` y pulsar «Pagar con PayPal». Pagar con la cuenta personal
   de sandbox. Al volver debe aparecer «Pago completado. Ya tienes acceso.» y el acceso en
   `/cuenta`.
3. En Developer → **Webhooks → Events** (o en la app → Webhooks → *View events*) se ve cada
   evento recibido y la respuesta del portal (`200 OK`). Si la respuesta es `400`, el
   `PAYPAL_WEBHOOK_ID` no es el de esa app; si es `404`, faltan credenciales en `.env`.
4. Cancelar la suscripción desde `/cuenta` y comprobar que pasa a «Cancelada» y que el acceso
   queda «hasta» la fecha del siguiente cobro.

### 5. Pasar a producción

1. Repetir los pasos 1 y 2 con el conmutador en **Live** y con la cuenta Business real.
2. En el servidor: `PAYPAL_MODE=live`, credenciales live y `PAYPAL_WEBHOOK_ID` del webhook live.
3. `php artisan optimize` tras cambiar `.env` y después `php artisan paypal:sync-plans`: los
   `P-…` de sandbox no existen en live, así que el portal los crea de nuevo.
4. Hacer una compra real de un plan mensual y cancelarla después para verificar el ciclo completo.

## Cupones y descuentos

PayPal no tiene cupones: el descuento se calcula en el portal y a PayPal solo le llega el
importe final. No hay que crear ni configurar nada en PayPal para los cupones.

- Se gestionan en `/admin/coupons`: código, porcentaje o importe fijo, planes en los que
  vale (vacío = todos), usos máximos, un uso por usuario, fechas de validez y, para las
  suscripciones, si descuenta solo el primer periodo o todos.
- El usuario lo escribe en la página de planes («¿Tienes un cupón?») o llega por enlace:
  `https://dominio/planes/mdp?cupon=VERANO20`. Los precios se muestran ya rebajados y el
  botón de pago lleva el cupón.
- **Pago único**: la orden de PayPal se crea por el importe rebajado, con el precio
  completo y el descuento desglosados en el recibo. Un cupón del 100 % concede el acceso
  directamente, sin pasar por PayPal.
- **Suscripciones**: como los planes de PayPal tienen precio fijo, el portal crea una
  variante del plan (`plan_paypal_variants`) con el precio rebajado: un primer ciclo al
  precio del cupón y después el precio normal («solo el primer periodo»), o el precio
  rebajado en todos los ciclos («todos los periodos»). Se crea una sola vez por combinación
  de precios y se reutiliza; el plan base no cambia. Un cupón que deje una suscripción a
  0 € en todos los periodos se rechaza: ese acceso se da a mano desde el panel (Accesos).
- Un uso solo cuenta cuando PayPal confirma el pago (`coupon_redemptions.confirmed_at`);
  los pagos abandonados no gastan el cupón.

## Notas de funcionamiento

- El id del usuario viaja a PayPal en `custom_id`, y el id del plan del portal en
  `reference_id` (órdenes) o se resuelve por `plan_id` (suscripciones). Así los webhooks
  encuentran al usuario aunque no exista registro previo.
- `paypal_subscriptions.paid_through_at` guarda `next_billing_time`. Al cancelar, el acceso
  termina en esa fecha; al suspenderse por impago, termina de inmediato y vuelve con la
  reactivación.
- Las compras y suscripciones son idempotentes: repetir la vuelta de PayPal o recibir el
  mismo webhook varias veces no duplica registros.
- Cambiar el precio de un plan en el panel crea un plan nuevo en PayPal y desactiva el
  anterior; los suscriptores existentes conservan su precio hasta que cancelen.
- `PAYPAL_PLAN_*` en `.env` sigue sirviendo para pegar un `P-…` creado a mano antes del primer
  seed, pero ya no es necesario.
