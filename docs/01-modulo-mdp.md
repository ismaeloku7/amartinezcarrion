# Módulo MDP (Método Devocional Proyectivo)

Especificación funcional del módulo que digitaliza el método del libro
"Dios como mi terapeuta a través de la Biblia" (cap. VII, págs. 185-202).

## Decisiones tomadas

| Tema | Decisión |
|---|---|
| Stack | Laravel (última versión) desplegado con Forge. Portal único, no SaaS |
| Biblia en pantalla | No. El usuario lee en papel; la app no muestra texto bíblico |
| Día de descanso (sábado) | Configurable por usuario (día de la semana), por defecto sábado |
| Versión para niños | Fuera de alcance |
| Idiomas | Interfaz y contenido multiidioma |
| Compartir texto | No se implementa |
| Datos | Privados por usuario. Cada usuario se da de alta y gestiona su propia práctica |
| Cobro | La práctica guiada va bajo suscripción mensual/anual (Cashier + Stripe) |
| PWA | Instalable por separado con su propio manifest, service worker y notificaciones push |

## Modos de uso

Ambos modos comparten modelo de datos. El modo ligero es un subconjunto del completo.
El usuario elige el modo en el alta y puede cambiarlo después sin perder datos.

### Modo completo (la app sustituye a la libreta)
- Temporizador de lectura de 15-20 min con aviso suave a los 15 y a los 20.
- Encabezado del día: referencia de inicio y de fin de lectura, fecha.
- Entrada de textos: literal, 8-10 palabras, aviso al superar 10 y bloqueo a partir de 12.
- Subrayado táctil de la parte más significativa de cada texto.
- Selección del texto del día (rojo).
- Pantalla de visualización (pausa guiada sobre el texto elegido).
- Oración escrita con la expresión "en este día" sugerida y subrayado de una frase.
- Ficha semanal con los textos del día en colores alternos.
- Flujo de día de descanso: repaso del día anterior, subrayado de una palabra por texto,
  selección del texto de la semana (amarillo). Solo se hace el día de descanso; el resto
  de la semana la ficha muestra los textos del día, sin texto de la semana.
- Cierre de mes: las 4 fichas juntas, selección del texto del mes (verde). Solo el día que
  se cierra el mes, que es el día de descanso de la cuarta semana, después de elegir el
  texto de esa semana.
- Galería anual con los 12 textos del mes.
- Opcional: anotaciones Rf / Pd / Il (reflexión, predicación, ilustración) en violeta.
- Orden de lectura sugerido (41 libros) intercalado con el AT histórico, con progreso.

### Modo ligero (la libreta sigue en papel)
- Registro de los textos del día: el usuario puede apuntar todos los textos que va
  sacando o únicamente el texto elegido. Es el mismo flujo: si el día tiene un solo
  texto, ese queda marcado automáticamente como texto del día. Si tiene varios, el
  usuario elige uno.
- Selección de texto de la semana y del mes.
- Recordatorios.
- Sin temporizador, sin oración escrita, sin subrayado.

### Regla de selección automática (ambos modos)
- Día con un único texto: se marca como texto del día sin pedir selección.
- La misma regla se aplica a la semana (si solo hay un texto del día en la semana) y
  al mes (si solo hay un texto de la semana en el mes), por ejemplo cuando el usuario
  empieza a mitad de periodo. Pero solo en su día: la semana el día de descanso y el mes
  el día que se cierra.

## Fases
- Fase 1 (semanas 1-3): pasos básicos. La app muestra solo las pantallas de esta fase.
- Fase 2: método completo. Paso automático a las 3 semanas, con opción de adelantarlo
  o retrasarlo.

## Notificaciones (Web Push)
| Aviso | Cuándo |
|---|---|
| Lectura | Hora fija elegida por el usuario |
| Repaso 1, 2, 3 | Tres horas configurables. Por defecto, si la lectura es de mañana: antes de comer, antes de cenar, antes de dormir. Si es de noche: al levantarse, antes de comer, media tarde |
| Día de descanso | A la hora de lectura, con el flujo semanal |
| Cierre de mes | Al completar la cuarta semana |
| Mínimo del día | Si a cierta hora no se ha registrado el devocional: "10 minutos, o incluso 5". No hay un «modo mínimo»: el día se termina igual y la libreta marca «Menos tiempo» si la lectura no llegó a 15 minutos. El tiempo se puede corregir desde la libreta |
| Repaso del día anterior | Junto al aviso de lectura |

Cada aviso de repaso muestra únicamente el texto del día actual (nunca los anteriores).

## Modelo de datos (tablas propias del módulo)

- `mdp_enrollments`: user_id, mode (full|light), phase (1|2), phase_changed_at,
  rest_weekday (0-6), reading_time, review_times (json, 3 horas), minimum_alert_time,
  timezone, started_at, reading_order_position.
- `mdp_days`: enrollment_id, date, started_at, duration_seconds, reading_from,
  reading_to, prayer (cifrada), prayer_underline (json), status (pending|done|rest),
  reviews_done (0-3), previous_reviewed (bool).
- `mdp_texts`: day_id, position, body, underline (json rangos), is_day_text (bool),
  word_count, note_type (null|rf|pd|il), note.
- `mdp_weeks`: enrollment_id, starts_on, ends_on, week_text_id (-> mdp_texts),
  rest_day_done_at, word_underlines (json: text_id -> palabra), memorize_key,
  memorized (bool).
- `mdp_months`: enrollment_id, index, starts_on, month_text_id (-> mdp_texts), closed_at.
- `mdp_years`: enrollment_id, index, starts_on, closed_at.
- `push_subscriptions`: compartida con el resto de módulos (endpoint, claves, user_id,
  app (mdp|book2|general)).
- `mdp_reading_order`: tabla estática con el orden sugerido de libros, traducible.

Colores: rojo = texto del día, amarillo = texto de la semana, verde = texto del mes,
violeta = anotaciones. Se derivan de las relaciones, no se almacenan.

## Pantallas principales
1. Alta: modo, hora de lectura, horas de repaso, día de descanso, idioma.
2. Hoy: estado del día y acceso al devocional o al repaso.
3. Devocional (paso a paso según fase y modo).
4. Ficha de la semana.
5. Día de descanso (flujo semanal).
6. Cierre de mes.
7. Galería anual.
8. Historial / libreta (todas las páginas por fecha).
9. Orden de lectura y progreso.
10. Ajustes y suscripción.

## Multiidioma
- Interfaz: archivos de idioma de Laravel.
- Contenido editable (explicaciones del método, orden de lectura, textos de
  notificaciones): columnas traducibles (spatie/laravel-translatable) gestionadas
  desde el panel de administración.
