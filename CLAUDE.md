# Farmacia FarmaFamilia

Sistema de gestión para farmacia hecho en Laravel 12 + Livewire 3 + Flux UI,
con permisos/roles (Spatie), seguridad de sesión única por usuario, y
facturación electrónica SIAT (pendiente de activar en producción).

## App de escritorio: se intentó con NativePHP y se descartó

En noviembre 2026 se probó empaquetar el sistema como app de escritorio con
NativePHP Desktop v2 + Electron. Se descartó por problemas de empaquetado en
Windows (symlinks/winCodeSign, conflictos de versión de Node) que no se
pudieron resolver de forma estable. Todo el código relacionado (paquete
composer, carpeta `nativephp/`, config, provider) fue removido del proyecto.

Si se retoma la idea de app de escritorio más adelante, hay que evaluar otra
estrategia (ver conversación de esa fecha para el detalle de lo que se probó
y por qué no funcionó).

## Multi-sucursal (Casa Matriz + Sucursal La Moscú) — en progreso

Desde noviembre 2026 el sistema soporta dos sucursales reales bajo el mismo
NIT: "Casa Matriz" y "Sucursal La Moscú". El catálogo de productos sigue
compartido; lo que se separa por sucursal es `users`/`lotes`/`cajas`/`ventas`
(columna `id_sucursal`, FK nullable). Permiso `Super Admin` (no un rol —
permiso suelto asignado directo al usuario, nunca al rol `admin` compartido)
ve/administra todas las sucursales; los demás quedan limitados a la suya.
Herramienta de migración inicial (un solo uso, solo Super Admin): menú
**Configuraciones > Sucursales**. Todavía falta que los módulos existentes
(Lotes, Compras, Ventas, Caja, Kardex, Contabilidad, Reportes, Inicio)
filtren realmente por la sucursal del usuario logueado — por ahora solo se
guarda el dato en los registros nuevos. Detalle completo y qué falta: ver
memoria del proyecto (`multisucursal_migracion`) o
`database/sql/2026_11_09_multisucursal_setup.sql`.

## Instalación en una máquina nueva (auto-arranque)

En noviembre 2026 se definió la forma de dejar el sistema andando en una
máquina nueva sin empaquetarlo como app de escritorio: WAMP corriendo como
servicio de Windows (arranque automático) + un acceso directo en modo
`chrome --app=` apuntando a `http://farmafamilia.local`, creado con la
herramienta `add_vhost.php` de WAMP (no editar `httpd-vhosts.conf` ni el
archivo `hosts` a mano). Guía paso a paso completa, con credenciales de
administrador y trabajador incluidas: `docs/instalacion-maquina-nueva.md`.

## Respaldos de la base de datos

Desde noviembre 2026 la base `bd_farmacia` se respalda cada hora en
`D:\respaldos_farmafamilia` (zip, 30 días de retención) con
`scripts/backup-db.ps1`, lanzado por la tarea programada de Windows
"FarmaFamilia - Respaldo BD". Cómo revisar y restaurar:
`docs/respaldos-base-de-datos.md`.

En esta máquina el sitio responde en `http://farmacia.local` (vhost de WAMP);
el acceso directo del escritorio `FarmaFamilia.lnk` abre
`chrome --app=http://farmacia.local`.

## Convenciones del proyecto

- Diseño "plomo": paleta neutra zinc + azul (`--color-primary`) como único
  acento. Botones de acción secundaria usan contorno, no relleno sólido
  (excepto el botón principal del login).
- Tablas responsive: desde octubre 2026 los módulos usan tabla en escritorio
  (`hidden md:block`) + tarjetas con botón "+/-" en móvil
  (`md:hidden`). Ver `docs/prompts/responsive-tablas.md` para el prompt
  reutilizable al aplicar el patrón a un módulo nuevo.
- Prompts reutilizables de este proyecto: `docs/prompts/` (ver su README).
- Diagramas de base de datos: `docs/diagrams/` (`database-schema.drawio` con
  relaciones, `database-schema-sin-relaciones.drawio` sin ellas). Se
  regeneran con los scripts Python en esa misma carpeta si el esquema cambia.
- Nombres de columnas FK: todo en español desde octubre 2026
  (`id_usuario`, `id_producto`, `id_cliente`, etc. — ver el historial si se
  necesita el mapeo completo viejo→nuevo).
- Zona horaria: `America/La_Paz` (config/app.php, sobreescribible con
  `APP_TIMEZONE`). Hasta noviembre 2026 estaba en UTC y las fechas se
  guardaban 4 horas adelantadas; no volver a UTC.
- Ventas: métodos de pago Efectivo, QR y Mixto (Tarjeta desactivado). El
  método arranca VACÍO (obligatorio elegir). Cada venta guarda el reparto
  `monto_efectivo` + `monto_qr` (Mixto: QR indicado, el resto efectivo; el
  cambio es solo sobre la parte en efectivo). Para sumar efectivo/QR usar
  `Venta::SQL_CASH` / `Venta::SQL_QR` o `cashPart()` / `qrPart()`, nunca
  filtrar solo por `metodo_pago`.
  Además:
  sin descuento, precio no editable (se toma del LOTE / servicio), fecha
  siempre la de hoy, campo cliente oculto (consumidor final), sin campo
  observación (el único texto es el motivo obligatorio al cancelar).
  Listado sin historial: solo ventas de cajas abiertas y del día de hoy,
  con totales Efectivo / QR / total. El detalle agrega lo nuevo arriba.
- Caja: `monto_esperado` es SOLO el efectivo del cajón = monto inicial +
  ventas en efectivo + ingresos manuales − egresos manuales. El QR va a la
  cuenta y NO se cuenta en los cortes. Se calcula siempre con
  `Caja::cashBreakdown()` / `recalculateExpected()` (no sumar a mano).
  Precio por LOTE (desde nov 2026): cada ingreso puede traer su propio
  precio; en Ventas se cobra el precio del lote que se vende (el más antiguo
  primero; al agotarse en la venta pasa al siguiente). Si el admin edita el
  precio del producto, se copia solo a los lotes con stock que tenían el
  precio anterior.
  Cierre en DOS verificaciones (modal en 2 pasos): 1) QR — lo que llegó a
  la cuenta vs. ventas QR del sistema (`monto_qr_esperado`,
  `monto_qr_verificado`, `diferencia_qr`); 2) Efectivo con cortes
  (`monto_cierre`, `diferencia`).
- Lotes: cada ingreso de mercadería va en un lote propio
  (`INGRESO-AAAA-MM-DD-<código>`), separado de la carga inicial
  (`CARGA-INICIAL-<código>`). Al vender se toma primero el lote que ENTRÓ
  antes (orden de ingreso = id), sin importar el vencimiento: así lo pidió
  el usuario en nov 2026 (antes los lotes sin fecha quedaban al final).
- Paquete/unidad: un producto paquete se vincula a su producto unidad con
  `productos.id_producto_unidad` + `unidades_por_paquete`. "Abrir paquete"
  (`App\Services\AperturaPaquete`) descuenta 1 paquete y crea un lote de N
  unidades (Kardex `PACKAGE_OPEN`). En Ventas se ofrece solo al agregar una
  unidad sin sueltos; en Productos hay botón para el admin.
- Devolución parcial de productos (solo admin): botón "Devolver" en
  Caja → 🧾 Ventas de la caja (`LivewireCajaVentas`), lógica en
  `App\Services\DevolucionVenta`. La venta queda con total NETO (baja
  `quantity`/`subtotal` de la línea, `total`, `monto_efectivo`/`monto_qr`;
  guarda `cantidad_devuelta` y `ventas.monto_devuelto`), el stock vuelve a su
  mismo lote (Kardex RETURN / SALE_RETURN), la caja de la venta se recalcula
  aunque esté cerrada (esperado, diferencia, QR) y se agrega nota automática
  a su observación. Historial en `devoluciones` / `devolucion_detalles`.
- Lotes: botón "Cuadre del lote" (`LivewireLote::cuadre()`) = entradas −
  ventas ± devoluciones ± ajustes según el Kardex vs. `cantidad_disponible`.
  Desde nov 2026 "Nuevo lote" registra IN/STOCK_ENTRY y "Editar lote" registra
  ADJUSTMENT si cambia el disponible (antes no dejaban Kardex: 36 lotes
  quedaron descuadrados por ediciones manuales anteriores).
- Reporte "Productos más vendidos" (solo rol admin, middleware `role:admin`):
  `/reports/top-products` (`LivewireTopProducts`). Primero se consulta en
  pantalla y recién ahí se exporta a PDF/Excel con las mismas fechas. Los
  datos salen de `App\Support\TopProductsReport` (pantalla, PDF y Excel
  usan la misma consulta; solo ventas ACTIVAS, sin servicios).
- Campos numéricos sin flechas ni cambio por rueda del mouse (estilo y
  script globales en `partials/head.blade.php`).
- Comando `php artisan pharmacy:upgrade-schema`: idempotente, endurece/migra
  el esquema de instancias MySQL ya desplegadas (no aplica a SQLite).
- `php artisan pharmacy:import-inventory`: carga única **destructiva** de
  catálogo/inventario desde un CSV — vacía productos/lotes/compras/ventas/etc.
  antes de recargar. Pedir confirmación explícita siempre antes de correrlo.

