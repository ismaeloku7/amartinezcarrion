# Módulo Materiales (contenidos)

## Estructura
- `topics`: temáticas con posible temática superior (dos niveles), color y orden.
- `contents`: artículos o cursos dentro de una temática. Título, entradilla y cuerpo traducibles;
  autor, minutos de lectura, color de portada, fecha de publicación (vacía = borrador), producto de
  pago opcional y opción "solo usuarios registrados".

## Diseño
Cabecera a color con degradado por temática, tipografía de lectura, títulos con marca de color,
citas destacadas, tarjetas (`cards`, `card warn`), pasos (`steps`) y tablas. El estilo está en
`resources/css/app.css` bajo `.article`.

## Bloques especiales (shortcodes en el cuerpo)
| Bloque | Qué muestra |
|---|---|
| `[[eneatipos]]` | Los nueve tipos con pestañas: ficha, zona sana, sombra y malsana. Se alimenta de los perfiles del test `eneagrama`, así que editarlos en el panel actualiza también el artículo |
| `[[eneagrama-tabla]]` | Tabla resumen: peligro, deseo, temor, queja oculta, botón de acción |
| `[[eneagrama-diagrama]]` | El círculo del eneagrama con sus líneas |
| `[[test:clave]]` | Llamada a un test (`eneagrama`, `rheti`, `johari`) |

## Contenidos sembrados
- "Los nueve eneatipos: una guía para conocerte" (temática Autoconocimiento › Eneagrama),
  construido a partir de la presentación del autor.
- "La ventana de Johari" (temática Autoconocimiento).

## Pendiente
Los contenidos por temáticas que Antonio vaya entregando se cargan desde el panel, en Materiales.
Los cursos (fase 2) usarán la misma tabla con `type = course` y, cuando llegue el momento,
lecciones y progreso.
