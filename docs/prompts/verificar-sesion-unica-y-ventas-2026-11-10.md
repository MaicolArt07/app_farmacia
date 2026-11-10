# Prompt de verificación — Sesión única, historial de Ventas y edición de pago (2026-11-10)

## Contexto

Prompt para pegar en una sesión nueva de Claude Code sobre este mismo
proyecto, cuando se quiera retomar, verificar o seguir trabajando sobre los
cambios hechos el 2026-11-10. El detalle completo de qué se hizo y por qué
está en `docs/actualizaciones/2026-11-10-sesion-unica-y-ventas.md` — leer
ese archivo primero.

## Prompt

```
Lee docs/actualizaciones/2026-11-10-sesion-unica-y-ventas.md para tener el
contexto completo de lo que se hizo en la sesión anterior (2026-11-10):

1. Se quitó la confirmación de "sesión activa en otro dispositivo" del login
   (quedaba un falso positivo si alguien cerraba el navegador sin usar el
   botón "Cerrar sesión").
2. En Ventas, el administrador ahora ve el historial completo (no solo
   caja abierta + hoy) con filtro de fechas desde/hasta.
3. Se agregó edición de pago de una venta (método/monto), solo para admin y
   solo mientras la caja de esa venta siga abierta.

Con ese contexto, quiero que verifiques lo siguiente en este orden:

### A) Estado del código (sin tocar nada todavía)
- Confirma que estos archivos existen y siguen con los cambios descritos:
  app/Livewire/Auth/Login.php, resources/js/app.js,
  app/Livewire/LivewireVenta.php, routes/web.php,
  resources/views/livewire/venta/index.blade.php,
  resources/views/livewire/venta/form.blade.php.
- Corre `php artisan test` completo y reportá si algo quedó roto desde
  entonces (por ejemplo por cambios posteriores de otra sesión).
- Si `php artisan test` falla, NO asumas que el código de esta sesión está
  mal: revisá primero si el fallo viene de otro cambio posterior.

### B) Verificación manual en el navegador (pedime confirmación de las
credenciales de admin/trabajador si no las tenés a mano, no las asumas)
1. Login: entrá, salí con "Cerrar sesión", volvé a entrar con el mismo
   usuario — NO debe aparecer el modal de "sesión activa en otro
   dispositivo". Confirmá también que si de verdad hay dos sesiones
   abiertas al mismo tiempo (dos navegadores distintos, mismo usuario), la
   más vieja SÍ se cierra sola al hacer cualquier click (con su aviso).
2. Ventas, como admin: confirmá que ves ventas de ayer o de una caja ya
   cerrada (si no hay datos de prueba, generá una venta, cerrá esa caja, y
   verificá que sigue apareciendo en el listado). Probá el filtro de fechas
   desde/hasta y el botón de quitar filtros.
3. Ventas, como trabajador (no admin): confirmá que SOLO ve las ventas de su
   caja abierta de hoy, sin filtro de fechas visible, igual que antes del
   2026-11-10.
4. Edición de pago: con una caja abierta y una venta activa en ella, como
   admin, hacé click en el ícono de lápiz junto al de "ver", cambiá el
   método de pago (ej. de Efectivo a QR) y guardá. Confirmá que:
   - el cambio se refleja en el listado de Ventas,
   - el "monto esperado" de esa caja (módulo Caja) se recalculó bien,
   - si cerrás esa caja y volvés a Ventas, el ícono de lápiz YA NO aparece
     para esa venta.
5. Como trabajador, confirmá que no hay forma de ver el ícono de lápiz ni de
   entrar a /ventas/{id}/edit a mano (debe dar 403).

### C) Reporte final
Decime, para cada punto de A y B, si quedó: OK / falló (con el detalle) /
no se pudo probar (y por qué). No corrijas nada todavía salvo que te lo
pida explícitamente después de ver el reporte.
```

## Notas

- Este prompt es solo de **verificación**, a propósito no pide corregir nada
  por su cuenta — así se evita que una sesión nueva, sin el contexto fino de
  esta, reinterprete o "mejore" algo sin preguntar.
- Si en el futuro se quiere reutilizar este mismo patrón (doc de cambios +
  prompt de verificación) para otra sesión de trabajo, seguir la misma
  estructura: un archivo en `docs/actualizaciones/AAAA-MM-DD-tema.md` con el
  detalle, y un prompt acá en `docs/prompts/` que primero pida leer ese
  archivo y después liste pasos concretos de verificación.
