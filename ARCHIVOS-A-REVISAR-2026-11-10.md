# Archivos a revisar — actualización 2026-11-10

Checklist general de todo lo que cambió en esta actualización (sesión
única sin falso aviso, historial/filtro de fechas en Ventas, edición de
pago de venta). El detalle de qué hace cada cambio y por qué está en
`docs/actualizaciones/2026-11-10-sesion-unica-y-ventas.md` — este archivo
es solo el mapa de "qué tocar/mirar" si necesitás revisar, probar o seguir
trabajando sobre esto.

## 📄 Documentación (leer primero)

- [ ] `docs/actualizaciones/2026-11-10-sesion-unica-y-ventas.md` — changelog
  completo de esta actualización.
- [ ] `docs/prompts/verificar-sesion-unica-y-ventas-2026-11-10.md` — prompt
  listo para pegar en otra sesión y verificar todo esto paso a paso.
- [ ] `database/sql/2026_11_10_tablas_y_modificaciones_pendientes.sql` — SQL
  de esquema (tablas/columnas nuevas) de los días previos, sin datos.

## 🔐 Sesión única (login)

- [ ] `app/Livewire/Auth/Login.php` — ya no pide confirmación; entra directo.
- [ ] `app/Http/Middleware/ForceSingleSession.php` — sigue cerrando solo la
  sesión vieja cuando hay dos sesiones reales a la vez (sin tocar).
- [ ] `app/Services/SessionSecurityService.php` — `hasActiveSession()` quedó
  sin uso (no se borró, por si se reintroduce un aviso más adelante).
- [ ] `resources/js/app.js` — se quitó el listener de `confirm-session-override`.
- [ ] `tests/Feature/Security/SingleSessionTest.php` — actualizado.

## 🧾 Ventas: historial, filtro de fechas y edición de pago

- [ ] `routes/web.php` — nueva ruta `ventas.edit` (`role:admin`).
- [ ] `app/Livewire/LivewireVenta.php` — `render()` (historial completo para
  admin + filtro `dateFrom`/`dateTo`), `mount()` (guard de `ventas.edit`),
  `saveEdit()` (nuevo).
- [ ] `resources/views/livewire/venta/index.blade.php` — inputs de fecha
  (solo admin) e ícono de editar (lápiz) condicionado a caja abierta.
- [ ] `resources/views/livewire/venta/form.blade.php` — modo edición
  (`$editMode`): campos de pago habilitados, botón "Guardar cambios".
- [ ] `tests/Feature/VentaHistorialYEdicionTest.php` — 12 tests nuevos.

## ✅ Antes de dar por cerrado

- [ ] Correr `php artisan test` completo (al cierre de esta sesión: 50 tests,
  131 assertions, 0 fallos).
- [ ] Probar en el navegador como **admin**: historial completo, filtro de
  fechas, editar pago de una venta con caja abierta, y confirmar que el
  ícono de editar desaparece al cerrar esa caja.
- [ ] Probar en el navegador como **trabajador**: todo debe verse exactamente
  igual que antes de esta actualización (sin filtro de fechas, sin ícono de
  editar, sin acceso a `/ventas/{id}/edit`).
- [ ] Si se va a desplegar en otra instancia sin Artisan: correr
  `database/sql/2026_11_10_tablas_y_modificaciones_pendientes.sql` (y antes,
  si corresponde, `database/sql/2026_11_09_multisucursal_setup.sql`).

## ⚠️ Pendiente de sesiones anteriores (no se tocó ahora)

- [ ] Bug conocido: `cancelSale()` en `LivewireVenta.php` ajusta la caja del
  usuario que anula (`Auth::id()`) en vez de la caja original de la venta
  (`$sale->id_caja`).
- [ ] Retrofit de todos los módulos para filtrar por `id_sucursal`
  (multi-sucursal) — ver memoria `multisucursal_migracion` o
  `database/sql/2026_11_09_multisucursal_setup.sql`.
