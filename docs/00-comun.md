# Plataforma común

Base compartida por todos los módulos: libro MDP, libro Fichas, contenidos, tests y,
en fase 2, cursos.

## Stack
- Laravel (última versión), desplegado con Forge. Convenciones estándar de Laravel.
- Portal único con suscripciones. No es un SaaS: no hay multi-tenant, ni organizaciones,
  ni espacios por cliente. Un solo sitio, un solo catálogo, usuarios finales.
- Pagos y suscripciones: PayPal (API REST: Orders para pago único, Subscriptions para
  mensual/anual). Ver 05-paypal.md.
- Multiidioma: archivos de idioma para la interfaz; spatie/laravel-translatable para
  contenido editable. Idiomas gestionados desde el panel.
- Notificaciones: Web Push (VAPID), suscripciones por dispositivo y por app.
- PWA: una app general (portal) y una app por libro, cada una con su manifest, icono
  y service worker. Login único compartido.
- Panel de administración para gestionar contenidos, fichas, tests, productos, planes
  y traducciones.

## Productos, planes y acceso

El cobro se modela con tres conceptos separados para poder vender cada cosa por
separado y también agrupada:

- **Producto**: cada cosa a la que se puede dar acceso. Ejemplos: práctica MDP,
  práctica Fichas, un test concreto, un contenido de pago, un curso.
- **Plan**: lo que el usuario compra. Un plan tiene un precio (mensual, anual o pago
  único) y da acceso a uno o varios productos. Ejemplos:
  - "MDP mensual" -> producto MDP.
  - "Fichas anual" -> producto Fichas.
  - "Los dos libros anual" -> MDP + Fichas.
  - "Test de temperamento" (pago único) -> ese test.
  - "Todo incluido" -> todos los productos marcados como incluibles.
- **Acceso** (entitlement): relación usuario-producto con fecha de inicio y fin. Se
  genera a partir de las suscripciones y compras activas. Cada módulo solo comprueba
  "¿tiene este usuario acceso al producto X?", sin saber qué plan lo ha dado.

Reglas:
- Un producto puede estar en varios planes a la vez.
- Al añadir un producto a un plan desde el panel, los suscriptores activos de ese plan
  obtienen el acceso automáticamente.
- Las compras de pago único generan acceso sin fecha de fin.
- Cupones de descuento por plan (ver 05-paypal.md); PayPal solo recibe el importe final.
- Puede haber productos gratuitos (acceso para cualquier usuario registrado) y
  contenidos públicos (sin registro).
- Períodos de prueba configurables por plan; se trasladan al plan de PayPal como ciclo TRIAL
  gratuito cuando el panel lo crea.

## Modelo de datos común
- `users`: datos de cuenta, locale, timezone.
- `products`: key, type (practice|test|content|course), name (traducible), free (bool).
- `plans`: name (traducible), paypal_plan_id (P-…) y paypal_product_id (recurrentes, los crea
  el panel), interval
  (month|year|once), trial_days, active.
- `plan_product`: plan_id, product_id.
- `entitlements`: user_id, product_id, source (subscription|purchase|free|manual),
  source_id, starts_at, ends_at (nullable).
- `paypal_subscriptions`: user_id, plan_id, paypal_id (I-…), status, paid_through_at.
- `purchases`: user_id, plan_id, paypal_order_id, paypal_capture_id, paid_at.
- `coupons` (+ `coupon_plan`): code, type (percent|fixed), value, duration (once|forever),
  max_redemptions, once_per_user, starts_at, expires_at, active.
- `coupon_redemptions`: coupon_id, user_id, plan_id, importes, paypal_order_id o
  paypal_subscription_id, confirmed_at.
- `plan_paypal_variants`: plan_id, first_cents, recurring_cents, paypal_plan_id (planes de
  PayPal derivados para cupones).
- `push_subscriptions`: user_id, app, endpoint, keys, locale.
- `languages`: code, name, active, default.

## Módulos
- 01-modulo-mdp.md: libro "Dios como mi terapeuta a través de la Biblia". Implementado.
- 02-modulo-fichas.md: libro "El arte de dar forma a tu cerebro". Implementado.
- 03-modulo-contenidos.md: pendiente de recibir los contenidos.
- 04a-test-johari.md: test Ventana de Johari. Implementado sobre el motor genérico de tests.
- 05-paypal.md: configuración de la cuenta de PayPal y flujo de cobro.
- Resto de tests: pendientes de recibir el material.
- Cursos: fase 2.

## Estructura del código
- `app/Models/{Mdp,Cards,Tests}`: modelos por módulo. Comunes en `app/Models`.
- `app/Services/{Mdp,Cards,Tests,Reminders}`: reglas de negocio (calendario MDP, rotación de
  grupos, cálculo Johari, recordatorios). Son las clases con tests automáticos.
- `app/Livewire/{Portal,Mdp,Cards,Tests}`: pantallas. Vistas en `resources/views/livewire`.
- `app/Filament/Resources`: panel de administración.
- `routes/{web,mdp,cards,tests}.php`: rutas por módulo. Cada libro va bajo su prefijo y
  tiene su propio manifest en `/pwa/{app}/manifest.webmanifest`.
- `public/sw.js`: service worker compartido, registrado con un scope distinto por app.

## Motor de tests
- `tests`: definición (tipo, textos, lista de palabras, producto de pago, parámetros en `config`).
- `test_result_rules`: reglas (indicador, operador, valor) con contenido traducible.
- `test_takes`: cada vez que un usuario hace un test. `data` guarda la entrada específica del
  tipo y `result` el resultado calculado. Sirve para la evolución en el tiempo.
- Tablas específicas de Johari: `johari_invitations`, `johari_responses`, `word_lists`, `words`.
- Para añadir otro tipo de test (puntuación por escalas, temperamento...) se añade un tipo,
  su servicio de cálculo y sus pantallas; `tests`, `test_takes` y las reglas se reutilizan.
