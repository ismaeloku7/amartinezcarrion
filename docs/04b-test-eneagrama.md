# Test de eneagrama (cuestionarios puntuados)

## Instrumentos
- **Las nueve personalidades** (`eneagrama`): 162 enunciados, 18 por tipo, en el orden del
  cuestionario original de Antonio (fuente: hoja de cálculo "Las nueve personalidades. Test en 3 págs").
- **Indicador Riso-Hudson, adaptación** (`rheti`): 75 pares de enunciados, 363 enunciados en total;
  cada enunciado puntúa en uno o varios tipos según los asteriscos del documento original.
  Reutiliza los perfiles del cuestionario principal.

## Respuesta y recuento
- Cada enunciado se responde por separado: **Sí** (1 punto), **Sí, totalmente** (el "sí redondeado",
  cuenta doble), **No**, **?** (se contabiliza aparte) o en blanco (no se entiende).
- Los puntos se suman por escala (tipo 1 a 9). Resultado: diagrama de barras con los colores del
  autor (rojo 2, 3, 7, 8 extrovertidos · azul 1, 4, 5, 6 introvertidos · verde 9), tipo principal,
  secundario y diferencia entre ambos.
- Indicadores disponibles para reglas: `top_type`, `second_type`, `gap`, `unknown_count`,
  `blank_count`, `answered`, `score_N`, `pct_N` (N = 1..9), `group_extrovert_pct`,
  `group_introvert_pct`, `group_balanced_pct`.

## Perfiles por tipo (editables en el panel)
Título, rasgo central, carácter, temperamento, peligro, deseo básico, temor básico, queja oculta,
origen infantil, integración, desintegración, zona sana, zona de sombra, zona malsana (un rasgo por
línea), botón de acción para el crecimiento, notas y **conclusión del autor**. Se muestran en los
resultados (pestañas por zona) y en el artículo de materiales mediante el bloque `[[eneatipos]]`.

## Conclusiones condicionales
Reglas (indicador, operador, valor) con texto, como en Johari. Sembradas: dos tipos empatados
(`gap <= 2`), muchas respuestas con `?`, predominio extrovertido o introvertido (>= 65 %).
Antonio puede añadir las suyas: por ejemplo `top_type = 2` con una conclusión para los ayudadores.

## Análisis con IA
- Se activa por test en el panel (interruptor "Ofrecer análisis personal con IA") y requiere
  `ANTHROPIC_API_KEY` en el servidor. Modelo por defecto `claude-opus-5-5`, configurable con
  `ANTHROPIC_MODEL`; esfuerzo con `ANTHROPIC_EFFORT`.
- La IA recibe únicamente: el marco del autor (sin diagnósticos, no hay tipos mejores ni peores,
  espíritu de servicio), los perfiles de los nueve tipos tal como están en el panel, las
  conclusiones del autor que se cumplen, las puntuaciones de la persona y las instrucciones
  adicionales que Antonio escriba en el campo "Instrucciones adicionales para la IA".
- Estructura del análisis: lo que sugieren los resultados, zona sana, sombras a vigilar, dirección
  de crecimiento (integración y botón de acción), tres pasos concretos y cierre.
- Se genera en segundo plano (cola) al pulsar "Generar mi análisis"; la pantalla se actualiza sola.
  Se guarda en la toma y no se vuelve a generar salvo que se borre la toma.

## Evolución
Cada toma guarda las nueve puntuaciones. La pantalla de evolución compara tomas y muestra el
cambio por tipo entre la primera y la última.

## Avisos antes de hacer un test
- **Artículo recomendado**: cada test puede enlazar un material ("Conviene leerlo antes"), que se
  muestra en la página del test. Se elige en el panel, en el campo "Artículo recomendado antes del
  test". Sembrado: los dos cuestionarios del eneagrama enlazan "Los nueve eneatipos"; Johari enlaza
  "La ventana de Johari".
- **Consideraciones generales**: al pulsar "Empezar" en cualquier test se abre un aviso con el
  material "Consideraciones antes de hacer un test" (no responder lo que nos gustaría ser, pensar
  en cómo somos en general y no en un marco concreto, identificarse con el enunciado completo…).
  Es un contenido normal del panel con el slug `consideraciones-antes-de-un-test`, oculto de los
  listados. Antonio lo edita como cualquier artículo.
