# Briefing: test tipo Ventana de Johari

Documento para definir cómo se construye en la plataforma un test de tipo Johari:
autoevaluación más valoración de terceros, con listas de palabras configurables.

## 1. Qué es

Modelo de autoconocimiento de Joseph Luft y Harry Ingham (1955). Cruza dos ejes:
lo que yo conozco de mí y lo que los demás conocen de mí. Resultan cuatro áreas:

| | Conocido por mí | Desconocido por mí |
|---|---|---|
| **Conocido por los demás** | Área abierta (pública) | Área ciega |
| **Desconocido por los demás** | Área oculta | Área desconocida |

El ejercicio clásico usa una lista de adjetivos. La persona elige unos pocos que la
describen. Varias personas de su entorno eligen, de la misma lista, los que creen que
la describen. El cruce coloca cada palabra en un área:

- Elegida por mí y por al menos otra persona: **abierta**.
- Elegida solo por otros: **ciega**.
- Elegida solo por mí: **oculta**.
- No elegida por nadie: **desconocida**.

No hay puntuación numérica ni respuestas correctas. El valor está en la distribución
de palabras y en cómo cambia con el tiempo.

## 2. Lista de palabras habitual

Las 56 palabras de la versión original, con una traducción al castellano de uso
habitual. Esta es la lista por defecto de la plataforma. Es editable y traducible.

| # | Inglés | Castellano |
|---|---|---|
| 1 | able | capaz |
| 2 | accepting | receptivo |
| 3 | adaptable | adaptable |
| 4 | bold | audaz |
| 5 | brave | valiente |
| 6 | calm | tranquilo |
| 7 | caring | atento con los demás |
| 8 | cheerful | alegre |
| 9 | clever | listo |
| 10 | complex | complejo |
| 11 | confident | seguro de sí mismo |
| 12 | dependable | fiable |
| 13 | dignified | digno |
| 14 | empathetic | empático |
| 15 | energetic | enérgico |
| 16 | extroverted | extrovertido |
| 17 | friendly | amistoso |
| 18 | giving | generoso |
| 19 | happy | feliz |
| 20 | helpful | servicial |
| 21 | idealistic | idealista |
| 22 | independent | independiente |
| 23 | ingenious | ingenioso |
| 24 | intelligent | inteligente |
| 25 | introverted | introvertido |
| 26 | kind | amable |
| 27 | knowledgeable | instruido |
| 28 | logical | lógico |
| 29 | loving | cariñoso |
| 30 | mature | maduro |
| 31 | modest | modesto |
| 32 | nervous | nervioso |
| 33 | observant | observador |
| 34 | organized | organizado |
| 35 | patient | paciente |
| 36 | powerful | con fuerza |
| 37 | proud | orgulloso |
| 38 | quiet | callado |
| 39 | reflective | reflexivo |
| 40 | relaxed | relajado |
| 41 | religious | religioso |
| 42 | responsive | sensible a los demás |
| 43 | searching | inquieto, buscador |
| 44 | self-assertive | asertivo |
| 45 | self-conscious | cohibido |
| 46 | sensible | sensato |
| 47 | sentimental | sentimental |
| 48 | shy | tímido |
| 49 | silly | bromista |
| 50 | spontaneous | espontáneo |
| 51 | sympathetic | comprensivo |
| 52 | tense | tenso |
| 53 | trustworthy | digno de confianza |
| 54 | warm | cálido |
| 55 | wise | sabio |
| 56 | witty | ocurrente |

Notas sobre la lista:
- Mezcla rasgos positivos, neutros y algunos incómodos (nervioso, tenso, cohibido,
  orgulloso). Esto es intencionado: si todas fueran positivas, el área ciega apenas
  diría nada.
- Las traducciones son orientativas. Conviene que el autor revise cada palabra y
  ajuste matices. Por ejemplo "silly" se suele traducir como "tonto", pero en el
  contexto del test significa más bien "que hace el payaso, bromista".
- Si se desea una lista más corta, 30 o 40 palabras funcionan bien y aceleran la
  respuesta de los invitados.

## 3. Listas alternativas

La plataforma permite crear tantas listas como se quiera y asignar una a cada test.
Casos de uso:

- **Lista propia del autor**: palabras alineadas con los conceptos de sus libros
  (por ejemplo, rasgos relacionados con las 42 fichas: paciente, responsable,
  agradecido, reactivo, perseverante...).
- **Lista por ámbito**: una para familia, otra para trabajo, otra para iglesia o
  comunidad. El mismo usuario podría hacer el test con cada una y comparar.
- **Lista para jóvenes**: vocabulario más sencillo.
- **Lista en otro idioma**: cada palabra tiene traducción por idioma. El invitado
  responde en su idioma y el cruce se hace por identificador de palabra, no por texto.

Requisitos de una lista:
- Entre 20 y 80 palabras. Recomendado 40 a 56.
- Cada palabra es un identificador único con una traducción por idioma activo.
- Opcional: descripción corta por palabra (aparece al mantener pulsado) para evitar
  ambigüedades.
- Opcional: etiqueta de tono (positivo, neutro, incómodo) para mostrar estadísticas o
  para validar que la lista está equilibrada.
- Se puede duplicar una lista existente para crear una variante.

## 4. Parámetros configurables por test

| Parámetro | Valor por defecto | Rango |
|---|---|---|
| Lista de palabras | Lista Johari clásica | Cualquier lista activa |
| Palabras que elige el usuario | 6 | 3 a 10 |
| Palabras que elige cada invitado | 6 | 3 a 10 (puede ser distinto del usuario) |
| Mínimo de respuestas para ver resultados | 3 | 1 a 10 |
| Máximo de invitados | 15 | 1 a 50 |
| Caducidad del enlace de invitación | 30 días | 1 a 90 días |
| Repetición permitida | Sí | Sí / No |
| Tiempo mínimo entre repeticiones | 90 días | 0 a 365 |
| Anonimato de los invitados | Siempre | Fijo, no configurable |
| Invitado puede ver su propia respuesta al final | No | Sí / No |
| Contenido general al finalizar | Vacío | Texto o contenido del catálogo |
| Contenidos condicionales por resultado | Ninguno | Ver sección 7 |

## 5. Flujo del usuario

1. **Inicio**. Explicación breve del test, qué va a necesitar (invitar a personas de
   confianza) y tiempo estimado. Si el test es de pago, comprobación de acceso.
2. **Autoevaluación**. Lista de palabras en tarjetas. El usuario marca exactamente N.
   Contador visible. No puede continuar con menos ni con más.
3. **Invitaciones**. El usuario genera un enlace único y lo envía por el medio que
   quiera (WhatsApp, correo, copiar). Puede generar un enlace por persona o uno
   general con límite de usos. Opcional: etiquetar a quién se lo envía (familia,
   trabajo, amigos) para poder filtrar después. La etiqueta la pone el usuario, el
   invitado no la ve.
4. **Espera**. Pantalla con el número de respuestas recibidas sobre el mínimo
   requerido. Botón para reenviar enlace. Aviso push cuando llega una respuesta y
   cuando se alcanza el mínimo.
5. **Resultados**. Se desbloquean al alcanzar el mínimo. Siguen actualizándose si
   llegan más respuestas hasta la caducidad del enlace.
6. **Cierre**. El usuario puede cerrar el test cuando quiera. A partir de ahí los
   enlaces dejan de funcionar y el resultado queda fijado como una "toma" con fecha.

## 6. Flujo del invitado

1. Abre el enlace. No necesita registrarse.
2. Ve el nombre del usuario que le invita y una explicación de dos líneas.
3. Elige exactamente N palabras que, en su opinión, describen a esa persona.
4. Envía. Mensaje de gracias. Se le indica que su respuesta es anónima.
5. Un mismo enlace personal solo se puede usar una vez. El enlace general registra
   un identificador de navegador para dificultar respuestas duplicadas.
6. Opcional: al terminar, se le ofrece hacer su propio test (puerta de entrada de
   nuevos usuarios al portal).

## 7. Resultados

### Ventana
Cuatro cuadrantes con las palabras colocadas. Cada palabra del área abierta y ciega
muestra cuántos invitados la eligieron (por ejemplo "paciente x4"). El área
desconocida muestra las palabras restantes en gris.

Opcional: tamaño de cada cuadrante proporcional al número de palabras, para dar una
imagen visual del perfil.

### Indicadores derivados
Para poder condicionar contenidos y comparar en el tiempo, se calculan:

- Número de palabras en cada área.
- Porcentaje de cada área sobre el total de la lista.
- Área dominante (la que más palabras tiene, excluyendo la desconocida).
- Grado de coincidencia: palabras del usuario que al menos un invitado también
  eligió, dividido entre N.
- Palabras ciegas más repetidas (las que más invitados eligieron y el usuario no).
- Si hay etiquetas de ámbito: ventana filtrada por ámbito y comparación entre ámbitos.

### Interpretación
Texto por área, editable desde el panel y traducible. Y texto adicional según
condiciones, por ejemplo:

- Área ciega con 4 o más palabras: contenido sobre recibir feedback.
- Área oculta con 4 o más palabras: contenido sobre apertura y confianza.
- Coincidencia igual o superior al 70 por ciento: contenido sobre autoconocimiento
  consolidado.
- Siempre: contenido general al finalizar.

Las condiciones se definen en el panel como regla (indicador, operador, valor) más
contenido a mostrar. Un resultado puede cumplir varias reglas y mostrar varios
contenidos.

### Evolución
Cada vez que el usuario repite el test se guarda una toma nueva. La pantalla de
evolución muestra:

- Línea temporal de tomas con fecha.
- Comparación entre dos tomas: palabras que han cambiado de área, con flechas.
- Gráfico del tamaño de cada área a lo largo del tiempo.
- Si se usaron listas distintas en dos tomas, solo se comparan los indicadores
  globales, no las palabras.

## 8. Privacidad

- El usuario nunca ve qué invitado eligió qué palabra. Solo totales.
- Con menos respuestas que el mínimo no se muestra nada, para que no se pueda deducir
  la respuesta de una persona concreta.
- El invitado no deja nombre ni correo. Si el usuario ha etiquetado el enlace, la
  etiqueta la ve solo el usuario.
- Los enlaces caducan y se pueden revocar.
- El usuario puede borrar una toma completa, incluidas las respuestas recibidas.

## 9. Modelo de datos

- `word_lists`: name (traducible), description, active, is_default.
- `words`: word_list_id, key, label (traducible), hint (traducible, opcional),
  tone (positive|neutral|uncomfortable, opcional), position.
- `tests`: ya existe en el módulo de tests. Para Johari: type = johari, word_list_id y
  los parámetros de la sección 4 en configuración.
- `test_takes` (genérica): user_id, test_id, status (self_pending|collecting|results|closed),
  data (json: self_words, min_responses, word_list_id), result (json: áreas e indicadores),
  started_at, completed_at, closed_at.
- `johari_invitations`: take_id, token, label (opcional), max_uses, uses, expires_at,
  revoked_at.
- `johari_responses`: take_id, invitation_id, words (json), fingerprint (cookie aleatoria del
  navegador, sin IP ni datos personales), created_at.
- El resultado se guarda en `test_takes.result` y se recalcula al recibir cada respuesta.
- Reglas de contenido condicional: compartidas con el módulo de tests
  (`test_result_rules`: test_id, indicator, operator, value, content_id).

## 10. Panel de administración

- Crear, duplicar, traducir y activar listas de palabras.
- Crear un test Johari eligiendo lista y parámetros.
- Editar textos de interpretación por área.
- Definir reglas de contenido condicional.
- Ver estadísticas agregadas: tomas realizadas, media de respuestas por toma, palabras
  más frecuentes en cada área (sin identificar usuarios).

## 11. Preguntas para el autor

1. ¿Quiere usar la lista clásica, una propia, o ambas como tests distintos?
2. ¿Cuántas palabras debe elegir cada persona? El clásico son 5 o 6.
3. ¿Qué mínimo de respuestas considera necesario antes de mostrar la ventana?
4. ¿Quiere textos de interpretación propios por área? Si los tiene escritos, se cargan
   directamente.
5. ¿Qué contenidos del catálogo quiere asociar a cada condición de resultado?
6. ¿Tiene sentido la etiqueta de ámbito (familia, trabajo...) o prefiere algo más
   simple?
