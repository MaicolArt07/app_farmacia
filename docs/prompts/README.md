# Prompts del proyecto

Esta carpeta guarda los prompts reutilizables que hemos ido creando con Claude durante
el desarrollo de este sistema. La idea es que, cuando se necesite repetir un patrón ya
resuelto (ej. aplicarlo a un módulo nuevo), se use el prompt guardado aquí en vez de
re-explicar todo desde cero, y que cualquier sesión futura de Claude pueda leer esta
carpeta para entender decisiones de diseño/arquitectura ya tomadas.

## Convención

- Un archivo `.md` por tema/patrón, nombrado en kebab-case (ej. `responsive-tablas.md`).
- Cada archivo tiene:
  - **Contexto**: qué problema resuelve y en qué módulos ya se aplicó.
  - **Prompt**: el texto listo para copiar/pegar y pedirle a Claude que lo aplique a un
    módulo nuevo.
  - **Notas**: detalles, excepciones o decisiones de diseño que no son obvias leyendo el
    código (el "por qué").
- Cuando un patrón cambie o se mejore, actualizar el archivo correspondiente en vez de
  crear uno nuevo, para que siempre haya una sola fuente de verdad por tema.

## Índice

- [responsive-tablas.md](responsive-tablas.md) — patrón de tabla responsive (tabla en
  desktop + tarjetas con botón "+" en móvil), sin usar DataTables.
- [verificar-sesion-unica-y-ventas-2026-11-10.md](verificar-sesion-unica-y-ventas-2026-11-10.md)
  — prompt para verificar en otra sesión los cambios del 2026-11-10 (login sin
  falso aviso de sesión activa, historial/filtro de fechas en Ventas y edición
  de pago de venta).
