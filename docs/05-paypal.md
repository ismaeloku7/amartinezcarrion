# Pagos con PayPal

El portal cobra con la API REST de PayPal, sin paquetes externos:

| Qué | API de PayPal | Dónde en el código |
|---|---|---|
| Pago único (`interval = once`) | Orders v2: se crea la orden con el importe del plan y se captura al volver | `App\Services\PayPal\PayPalBilling::createOrder / captureOrder` |
| Suscripción (`month` / `year`) | Subscriptions v1: se crea la suscripción sobre el plan de PayPal (`P-…`) del plan | `PayPalBilling::createSubscription / syncSubscription` |
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

### 3. Productos y planes de suscripción

Solo para los planes mensuales y anuales. El pago único no necesita nada aquí.

1. Ir a https://www.paypal.com/billing/plans (en sandbox: entrar en https://www.sandbox.paypal.com
   con la cuenta *Business* de sandbox que aparece en Developer → **Sandbox → Accounts**, y usar
   la misma ruta `/billing/plans`).
2. **Create plan** → *Create product*:
   - Product name: `Devocional (Método Devocional Proyectivo)` · Type: **Digital** · Category: *Software*.
   - Repetir después para `Programa de las 42 fichas` y `Los dos libros`.
3. Para cada producto, crear los planes. Los importes y periodos deben coincidir con los del
   panel (`/admin/plans`); por defecto:

   | Clave en el panel | Producto en PayPal | Plan name | Precio | Ciclo |
   |---|---|---|---|---|
   | `mdp-month` | Devocional | Devocional mensual | 4,99 EUR | cada 1 mes |
   | `mdp-year` | Devocional | Devocional anual | 39,99 EUR | cada 1 año |
   | `cards-month` | Fichas | Fichas mensual | 4,99 EUR | cada 1 mes |
   | `cards-year` | Fichas | Fichas anual | 39,99 EUR | cada 1 año |
   | `books-year` | Los dos libros | Los dos libros, anual | 59,99 EUR | cada 1 año |

   En el asistente: **Fixed pricing** → precio y moneda **EUR** → Billing cycle: *Month* o *Year*,
   «Continue until cancelled» → sin setup fee → **Auto-bill outstanding amounts: ON** → payment
   failure threshold 2 → si el plan tiene días de prueba, añadir un **Trial period** gratuito de
   esos días. Impuestos: según corresponda (normalmente «price includes tax»).
4. Al guardar, cada plan muestra un **Plan ID** `P-XXXXXXXXXXXXXXXXXXXX`. Copiarlo.
5. En el portal: `/admin/plans` → editar el plan → pegar el `P-…` en **PayPal plan ID** → Guardar.
   (O ponerlos en `PAYPAL_PLAN_*` de `.env` antes del primer `db:seed`.)

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

1. Repetir los pasos 1 a 3 con el conmutador en **Live** y con la cuenta Business real.
2. En el servidor: `PAYPAL_MODE=live`, credenciales live, `PAYPAL_WEBHOOK_ID` del webhook live,
   y volver a pegar los `P-…` live en el panel (los de sandbox no sirven en live).
3. `php artisan config:cache` (o `php artisan optimize`) tras cambiar `.env`.
4. Hacer una compra real de un plan mensual y cancelarla después para verificar el ciclo completo.

## Notas de funcionamiento

- El id del usuario viaja a PayPal en `custom_id`, y el id del plan del portal en
  `reference_id` (órdenes) o se resuelve por `plan_id` (suscripciones). Así los webhooks
  encuentran al usuario aunque no exista registro previo.
- `paypal_subscriptions.paid_through_at` guarda `next_billing_time`. Al cancelar, el acceso
  termina en esa fecha; al suspenderse por impago, termina de inmediato y vuelve con la
  reactivación.
- Las compras y suscripciones son idempotentes: repetir la vuelta de PayPal o recibir el
  mismo webhook varias veces no duplica registros.
- Cambiar el precio de un plan exige crear un plan nuevo en PayPal (los planes de PayPal no
  cambian de precio para los suscriptores existentes) y pegar el nuevo `P-…` en el panel.
