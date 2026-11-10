# Actualización 2026-11-10 — Sesión única, historial de Ventas y edición de pago

## 1) Falso aviso de "sesión activa en otro dispositivo"

**Problema reportado:** al iniciar sesión (incluso recién cerrada correctamente,
o con otro usuario) siempre saltaba el modal "Sesión activa en otro
dispositivo", aunque no hubiera ninguna sesión real abierta en otro lado.

**Causa:** `users.session_token` solo se limpia (`null`) con un logout
explícito (botón "Cerrar sesión"). Si el navegador/pestaña se cierra sin usar
ese botón, el token queda guardado para siempre, y el siguiente login
(de ese mismo usuario o de otro que tenga el mismo problema) siempre
encontraba un token "activo" aunque en los hechos no hubiera nada abierto.

**Solución:** se quitó el paso de confirmación en el login
(`App\Livewire\Auth\Login`). Ahora el login entra directo siempre. La
seguridad de sesión única **sigue intacta**: si de verdad hay otra sesión
abierta en otro dispositivo, esa se sigue cerrando sola en automático la
próxima vez que esa sesión haga una petición (`App\Http\Middleware\ForceSingleSession`,
con su propio aviso "Tu sesión se cerró porque se inició sesión con esta
cuenta desde otro dispositivo."). Solo se eliminó la pregunta previa
innecesaria.

**Archivos:**
- `app/Livewire/Auth/Login.php` — se eliminó `confirmLogin()` y el evento
  `confirm-session-override`; `login()` ahora llama a `finalizeLogin()`
  directo.
- `resources/js/app.js` — se eliminó el listener de `confirm-session-override`
  (SweetAlert2) que ya no se usa.
- `tests/Feature/Security/SingleSessionTest.php` — actualizado: el test de
  "requiere confirmación" se reemplazó por uno que confirma que el login
  entra directo y genera un token nuevo aunque haya uno viejo en la BD.

`App\Services\SessionSecurityService::hasActiveSession()` quedó sin uso (no
se eliminó el método, por si se quiere reintroducir el aviso de otra forma
más adelante, ej. solo informativo post-login).

## 2) Historial completo y filtro de fechas en Ventas (solo administrador)

**Pedido:** el administrador no podía ver ventas de cajas ya cerradas ni de
días anteriores en el módulo Ventas (antes el listado SIEMPRE se limitaba a
"caja abierta + hoy", para todos los roles). Se pidió que el filtro de
fechas viva en el propio módulo Ventas (no en Caja ni en Reportes).

**Solución:**
- Si el usuario logueado tiene el rol `admin`: el listado de Ventas ya NO se
  limita a caja abierta ni al día de hoy — muestra el historial completo,
  con dos campos de fecha "desde/hasta" junto al buscador para filtrar por
  rango (`fecha_venta`). Botón "Quitar filtros" también limpia las fechas.
- Si el usuario NO es admin (trabajador): el comportamiento queda exactamente
  igual que antes — solo ve las ventas de SU caja abierta y del día de hoy.

**Archivos:**
- `app/Livewire/LivewireVenta.php` — `render()`: la query ahora se arma
  distinto según `Auth::user()->hasRole('admin')`; nuevas propiedades
  `$dateFrom`/`$dateTo` + `updatedDateFrom()`/`updatedDateTo()`/`clearDateFilter()`.
- `resources/views/livewire/venta/index.blade.php` — dos `<input type="date">`
  visibles solo `@if($isAdmin)`, junto al buscador; el banner de "mostrando
  ventas filtradas" ahora también menciona el rango de fechas.

## 3) Editar el pago de una venta (solo administrador, solo con caja abierta)

**Pedido:** poder corregir el método de pago o los montos de una venta ya
registrada, pero **solo** mientras la caja de esa venta siga abierta (una vez
cerrada la caja, no se debe poder tocar nada).

**Qué se puede editar:** únicamente método de pago, monto por QR y monto
recibido (efectivo). El detalle de productos/servicios y el total de la
venta **no se tocan** en esta pantalla (para eso existe anular + volver a
vender).

**Reglas de acceso:**
- Solo rol `admin` (middleware `role:admin` en la ruta + chequeo
  `Auth::user()->hasRole('admin')` tanto en `mount()` como en `saveEdit()`,
  por si alguien intenta invocar el método Livewire directamente).
- Solo si la venta está `ACTIVE` (no anulada) y la caja de esa venta
  (`Venta::cashRegister`) sigue con `status = 'OPEN'`. Si la caja ya se
  cerró — incluso si se cerró justo mientras el admin tenía el formulario
  abierto — se bloquea y redirige a la vista de solo lectura con un aviso.
- Las mismas validaciones de una venta nueva aplican al editar: en Efectivo,
  el monto recibido debe cubrir el total; en Mixto, el QR debe ser mayor a 0
  y menor al total, y el efectivo debe cubrir la diferencia.
- Al guardar, se recalcula automáticamente el `monto_esperado` de la caja de
  esa venta (`Caja::recalculateExpected()`), para que el cambio de método de
  pago se refleje correctamente en el cuadre de caja.

**Archivos:**
- `routes/web.php` — nueva ruta `GET /ventas/{sale}/edit` →
  `ventas.edit`, middleware `role:admin`.
- `app/Livewire/LivewireVenta.php` — `mount()` reconoce `ventas.edit`
  (`$editMode`), con los chequeos de permiso/caja-abierta descritos arriba;
  nuevo método `saveEdit()`.
- `resources/views/livewire/venta/form.blade.php` — título "Editar pago de
  venta", aviso de qué se puede/no se puede tocar, los 3 campos de pago
  habilitados en modo edición, botón "Guardar cambios".
- `resources/views/livewire/venta/index.blade.php` — ícono de lápiz junto al
  de "ver" (👁️), solo visible para admin y solo si `status === 'ACTIVE'` y
  `cashRegister->status === 'OPEN'` (tabla de escritorio y tarjetas móviles).

## 4) Tests nuevos

`tests/Feature/VentaHistorialYEdicionTest.php` (12 tests) cubre todo lo de
los puntos 2 y 3: historial completo vs. restringido según rol, filtro de
fechas (incluye/excluye/limpia), acceso a la pantalla de edición (403 para
trabajador, redirect si la caja ya cerró), validaciones de `saveEdit` en
Efectivo y Mixto, el recálculo del monto esperado de caja, la condición de
carrera (caja se cierra mientras se edita) y que un trabajador no pueda
ejecutar `saveEdit` aunque invoque el método Livewire directamente.

`tests/Feature/Security/SingleSessionTest.php` se actualizó por el punto 1.

**Suite completa al cierre de esta sesión: 50 tests, 131 assertions, 0
fallos.**

## SQL de esquema (tablas nuevas y modificaciones, sin datos)

El .sql con las tablas nuevas y columnas agregadas en migraciones recientes
(servicios, paquete/unidad, cierre de caja con QR, pago mixto, devoluciones,
contabilidad) está en un archivo aparte, para no mezclarlo con este
changelog: `database/sql/2026_11_10_tablas_y_modificaciones_pendientes.sql`.
Solo esquema (`CREATE TABLE` / `ALTER TABLE`) y catálogos (permisos, roles,
métodos de pago) — **no inserta ninguna venta, compra ni dato ficticio**, a
propósito, para no ensuciar la comparación contra las ventas reales que se
registren de ahora en más. La parte de multi-sucursal ya tenía su propio
script: `database/sql/2026_11_09_multisucursal_setup.sql`.

## Pendiente / no tocado en esta sesión

- El bug ya identificado (en sesiones anteriores) de que `cancelSale()`
  ajusta la caja del usuario que anula (`Auth::id()`) en vez de la caja
  original de la venta (`$sale->id_caja`) **sigue sin corregirse**.
- El retrofit de todos los módulos para filtrar por `id_sucursal` (multi-sucursal)
  sigue pendiente — ver memoria `multisucursal_migracion`.
