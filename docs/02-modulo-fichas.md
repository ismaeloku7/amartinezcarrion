# Módulo Fichas ("El arte de dar forma a tu cerebro")

Especificación funcional del módulo que digitaliza el programa de 42 fichas del libro
de crecimiento personal (apartado "Arcilla" y fichas del PDF adjunto).

## Resumen del método
- El libro tiene 42 apartados y cada uno lleva una ficha (anverso: título, imagen y
  frase; reverso: instrucción "si se escoge esta ficha...").
- Lectura a ritmo libre. Tras leer cada apartado se "incorpora" su ficha al programa.
- Las fichas se agrupan de 7 en 7 en 6 grupos: 1-7, 8-14, 15-21, 22-28, 29-35, 36-42.
- Cada mañana se repasan las fichas incorporadas de un grupo y se escoge una ficha del
  día entre ellas. Los grupos rotan: hoy el 1, mañana el 2, pasado el 3... y vuelta al 1.
  Solo entran en la rueda los grupos que ya tienen alguna ficha incorporada.
- Un día fijo a la semana es de descanso.
- Cuando los 6 grupos están completos, cada día de la semana tiene su grupo fijo
  (día 1 -> grupo 1 ... día 6 -> grupo 6, día 7 descanso).
- La ficha del día se repasa tres veces (antes de comer, cenar y dormir). Antes de
  mirarla hay que intentar recordarla de memoria.
- Por la noche se valora el uso de la ficha: 1 Apenas, 2 Bastante, 3 Mucho, en una
  planilla mensual. Obligatorio el primer mes, opcional después.
- Duración mínima recomendada: 6 meses.
- Fichas con regla especial: la 7 (El SARA) nunca se escoge, solo se repasa. La 31
  (La visualización) se repasa pero no se escoge; se aplica junto a la ficha elegida.

## Decisiones tomadas
| Tema | Decisión |
|---|---|
| Contenido de las fichas | Dentro de la app, con anverso y reverso digitales, traducible. Imágenes extraídas del PDF subido |
| Modo | Solo digital. La app sustituye por completo a las fichas de papel. No hay modo ligero |
| Día de descanso | Configurable, sábado por defecto |
| Planilla nocturna | Activa el primer mes; después el usuario decide si la mantiene |
| Resumen del apartado del libro | Campo opcional por ficha, editable y traducible desde el panel |
| Fichas no seleccionables | Solo la 7 (El SARA) y la 31 (La visualización) |
| Versión infantil | Fuera de alcance |
| Cobro | Producto propio con suscripción propia, agrupable en planes con otros productos (ver 00-comun.md) |
| PWA | Instalable por separado, con su manifest y notificaciones |

## Flujo diario
1. **Mañana** (aviso push): la app indica el grupo que toca y muestra sus fichas
   extendidas para escoger una. Se puede abrir cualquiera para verla por las dos caras
   antes de escogerla, sin recorrer el grupo ficha a ficha. Las no seleccionables se
   muestran pero no se pueden elegir. La ficha escogida es la única que se repasa.
2. **Ficha del día**: pantalla con anverso, reverso y, si existe, el resumen del
   apartado. Si la 31 ya está incorporada, se recuerda aplicar la visualización.
3. **Tres repasos** (avisos push): la notificación no muestra la ficha. Al abrirla,
   primero una pantalla "Intenta recordarla" y después la ficha. Cada repaso se marca.
4. **Noche** (aviso push tras el tercer repaso): valoración 1-3 que rellena la
   planilla del mes.
5. **Día de descanso**: sin repasos ni elección. La app lo indica.

## Incorporación de fichas
- Botón "He leído el apartado N" que incorpora la siguiente ficha. Se pueden
  incorporar varias el mismo día.
- La ficha recién incorporada se muestra completa al incorporarla.
- Un grupo entra en la rotación en cuanto tiene una ficha incorporada.
- Marcador visible de "hasta dónde he llegado" (equivale al separador de la caja).

## Rotación de grupos
- Mientras no estén los 6 grupos completos: en cada día activo (no descanso) se pasa al
  siguiente grupo disponible en orden cíclico, partiendo del último repasado.
- Cuando los 6 grupos están completos: grupo fijo por día de la semana, contando el
  día 1 como el siguiente al día de descanso.
- Si un día no se hace el repaso, al día siguiente se continúa con el grupo que
  tocaba (no se salta).

## Notificaciones (Web Push)
| Aviso | Cuándo |
|---|---|
| Elección de la ficha (mañana) | Hora fija configurable |
| Repasos 1, 2, 3 | Tres horas configurables (por defecto antes de comer, cenar, dormir) |
| Valoración nocturna | Tras la hora del tercer repaso, solo si la planilla está activa |
| Nueva ficha pendiente | Opcional: recordatorio semanal de seguir leyendo si no se incorpora ninguna ficha en 7 días |
| Descanso | Sin avisos ese día |

## Pantallas
1. Alta: hora de mañana, horas de repaso, día de descanso, idioma.
2. Hoy: grupo del día, estado de repasos, ficha elegida, valoración pendiente.
3. Repaso de grupo (tarjetas giratorias) y selección.
4. Ficha del día (con modo "recordar antes de ver").
5. Caja de fichas: las 42 en 6 grupos, con las incorporadas destacadas y las
   pendientes bloqueadas. Botón de incorporar.
6. Planilla mensual (grid 31 días x 3 niveles) e histórico de meses.
7. Progreso: días activos, meses completados, barra hacia los 6 meses.
8. Ajustes y suscripción.

## Modelo de datos
- `cards`: number (1-42), group_index (1-6), title, front_text, back_text, image_path,
  selectable (bool), section_summary (nullable). Campos de texto traducibles.
- `cards_enrollments`: user_id, rest_weekday, morning_time,
  review_times (json), timezone, started_at, unlocked_until (0-42),
  last_group_reviewed, tracking_enabled (bool), tracking_prompted_at.
- `cards_days`: enrollment_id, date, group_index, reviewed_at, chosen_card_id (nullable),
  reviews_done (0-3), rating (1-3, nullable), is_rest_day.
- `push_subscriptions`: compartida (app = cards).
- Colores de grupo según el PDF: rojo, azul, verde, verde agua, naranja, marrón.
  Se derivan de group_index.

## Multiidioma
Igual que el MDP: interfaz con archivos de idioma; fichas y textos editables con
columnas traducibles gestionadas desde el panel de administración. Las imágenes de las
fichas se extraen del PDF y son comunes a todos los idiomas.
