# Farmacia FarmaFamilia — Sistema Administrativo

> **Para quien retome este proyecto en otra sesión (IA o humano):** este documento describe el estado **real y actual** del sistema, no un plan aspiracional. Si algo aquí no coincide con el código, confía en el código y corrige este archivo.

Sistema de gestión para una farmacia individual (ventas, compras, inventario por lotes, caja, usuarios/permisos) construido en **Laravel 12 + Livewire 3 + Tailwind CSS v4 + Flux UI + Spatie Permission**, con una integración real en curso con el **SIN (Bolivia)** para facturación electrónica.

---

## 1. Instalación

```bash
git clone https://github.com/MaicolArt07/app_farmacia.git
cd app_farmacia
composer install
npm install

cp .env.example .env
php artisan key:generate
# Editar .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD

php artisan migrate --force
npm run build
php artisan serve
```

Usuarios iniciales (crear manualmente si el seeder no corrió, ver `app/Console/Commands/UpgradeSchemaCommand.php`):

- `admin@farmafamilia.com` — todos los permisos.
- `empleado@farmafamilia.com` — permisos de operación diaria (ventas, caja, consulta de productos/clientes).

> ⚠️ Este proyecto **NO usa roles** (Spatie `HasRoles`) — usa **permisos directos por usuario** (`syncPermissions()`). El módulo "Roles" (`LivewireRoles.php`, permiso `Ver Roles`) es código **huérfano** de una versión anterior, sin ruta activa; no lo uses como referencia.
>
> ⚠️ `app/Livewire/LivewireGlobalSearch.php` también existe pero **no se usa en ninguna vista actualmente** (se quitó el buscador global del layout en un rediseño del sidebar) — está ahí por si se retoma, no lo tomes como una funcionalidad activa.

### ⚠️ Gotcha de infraestructura: MySQL y motor de tablas

Este proyecto se ha topado con servidores MySQL cuyo motor **por defecto es MyISAM**, no InnoDB — eso rompe cualquier migración con `foreignId()->constrained()` con un error `1824` o, peor, lo crea sin FK real sin avisar. **Toda migración nueva que cree una tabla debe especificar explícitamente:**

```php
Schema::create('mi_tabla', function (Blueprint $table) {
    $table->engine = 'InnoDB'; // <- SIEMPRE, no asumir el default del servidor
    $table->id();
    // ...
});
```

### ⚠️ Gotcha de hosting compartido (InfinityFree, producción actual)

- `DB_HOST` **no es `localhost`** en InfinityFree — es un host remoto tipo `sqlXXX.infinityfree.com` (lo da su panel, sección MySQL Databases).
- El escáner de archivos de InfinityFree a veces **borra silenciosamente** `vendor/composer/autoload_static.php` (lo detecta como sospechoso por ser un PHP con arrays masivos). Si pasa, hay un parche manual aplicado en `vendor/composer/autoload_real.php` que evita depender de ese archivo (usa el autoload "clásico" vía `autoload_namespaces.php`/`autoload_psr4.php`/`autoload_classmap.php`/`autoload_files.php`). **Ojo:** si vuelves a correr `composer dump-autoload`, ese parche se pierde y hay que reaplicarlo antes de subir a producción.
- Subir `vendor/` completo por FTP (no por el File Manager web) para archivos grandes, o por partes.

---

## 2. Stack técnico

| Capa | Tecnología |
|---|---|
| Backend | Laravel 12, PHP 8.3 |
| Frontend reactivo | Livewire 3 (Form Objects en `app/Livewire/Form/`) |
| UI | Tailwind CSS v4, Flux UI (`flux:*` components), Bootstrap Icons |
| Interactividad cliente | Alpine.js (incluido con Livewire) |
| Alertas/confirmaciones | SweetAlert2 (`window.confirmAction()` global, ver `resources/js/app.js`) |
| Permisos | `spatie/laravel-permission` — **permisos directos por usuario, sin roles** |
| Base de datos | MySQL (InnoDB obligatorio, ver gotcha arriba) |
| Reportes | `maatwebsite/excel`, `barryvdh/laravel-dompdf` |
| Gráficos dashboard | Chart.js (vía CDN, cargado en la vista de Inicio) |

---

## 3. Convenciones del proyecto

- **`state`** (boolean/int 1-0): activo/inactivo — eliminación lógica, nunca se borra físicamente un registro de operación.
- **`status`** (enum string): estado operativo de un documento (`ACTIVE`/`CANCELLED` en Ventas y Compras). No confundir con `state`.
- **CRUD sin modales**: todos los módulos usan **páginas completas** (`/modulo`, `/modulo/create`, `/modulo/{id}/edit`), no modales superpuestos — excepto Caja, que mantiene modales pequeños y contextuales (registrar movimiento, cerrar caja, ampliar vigencia) porque son acciones puntuales dentro del mismo listado.
- **Confirmaciones destructivas**: usar `x-on:click.prevent="confirmAction({...}).then((ok) => { if (ok) $wire.metodo(...) })"` (SweetAlert2), **nunca** `wire:confirm` nativo ni `onclick="confirm(...)"` — ya se migraron todas las instancias viejas.
- **Selectores de catálogo grandes**: usar `<x-search-select>` (buscador con filtro cliente-side en Alpine) en vez de `<select>` plano cuando hay más de ~15 opciones (categorías, laboratorios, presentaciones, código de producto SIN, etc.).
- **Layout**: `resources/views/components/layouts/app/sidebar.blade.php` — sidebar de escritorio es un `<aside>` plano controlado por Alpine (`sidebarCollapsed`/`toggleSidebar()`, persistido en `localStorage`), **no** usa `flux:sidebar` para escritorio (sí para el menú móvil). Cada sección del menú tiene **3 variantes** que hay que mantener sincronizadas: desktop colapsado (solo íconos), desktop expandido (`<details>` colapsable), y móvil (`flux:navlist`).

---

## 4. Módulos del sistema (con sus rutas)

### Menú Principal
| Módulo | Rutas | Permiso base |
|---|---|---|
| Inicio (dashboard) | `/` | `Ver Inicio` |
| Personas | `/person`, `/person/create`, `/person/{id}/edit` | `Ver Persona` |
| Caja | `/cash-registers`, `/cash-registers/create` | `Ver Caja` |
| Clientes | `/clients`, `/clients/create`, `/clients/{id}/edit` | `Ver Clientes` |
| Productos | `/products`, `/products/create` (wizard 2 pasos), `/products/{id}/edit` | `Ver Productos` |
| Compras | `/purchases`, `/purchases/create`, `/purchases/{id}/edit`, `/purchases/{id}/view` | `Ver Compras` |
| Ventas | `/sales`, `/sales/create`, `/sales/{id}/view` (no editable, solo anular) | `Ver Ventas` |

### Grupo "Farmacia"
| Módulo | Rutas | Permiso |
|---|---|---|
| Proveedores | `/suppliers` + create/edit | `Ver Proveedores` |
| Laboratorios | `/laboratories` + create/edit | `Ver Laboratorios` |
| **Empresa** | `/company` (página única, no lista) | `Ver Empresa` |
| **Sucursales** | `/branches` + create/edit | `Ver Sucursales` |
| **Métodos de Pago** | `/payment-methods` + create/edit | `Ver Metodos Pago` |
| Categorías | `/categories` + create/edit | `Ver Categorias` |
| Presentaciones | `/presentations` + create/edit | `Ver Presentaciones` |
| Lotes | `/lots` + create/edit | `Ver Lotes` |
| Ajustes de Inventario | `/inventory-adjustments` + create | `Ver Ajustes Inventario` |
| Facturación | `/invoices`, `/invoices/{id}/pdf` | `Ver Facturas` |
| Kardex | `/kardex` | `Ver Kardex` |
| Alertas de Inventario | `/inventory-alerts` | `Ver Alertas Inventario` (accesible vía campanita 🔔, ya no está en el menú lateral) |
| Reportes | `/reports` + exports (daily-book, inventory-alerts, kardex, purchases, sales, stock) | `Ver Reportes` |

### Gestión de Usuarios
| Módulo | Rutas | Permiso |
|---|---|---|
| Usuarios | `/users` + create/edit (permisos agrupados con "marcar todo") | `Ver Usuarios` |
| Permisos | `/permissions` + create/edit | `Ver Permisos` |

**Negrita** = módulos nuevos agregados para la integración de facturación (ver sección 6).

---

## 5. Módulos de negocio — detalles importantes

### Ventas
- **Wizard de carrito**: buscador de cliente (opcional, "Consumidor final" si se deja vacío) + buscador de productos con tarjetas descriptivas (nombre, laboratorio, presentación, stock, código).
- **Monto recibido / Cambio**: si el método de pago es "Efectivo", exige monto recibido ≥ total y calcula el cambio en vivo.
- **Validación de caja**: no deja guardar si el usuario no tiene una caja abierta, o si la tiene pero está **vencida** (ver Caja abajo) — banner de aviso + botón deshabilitado.
- **No editable**: una vez guardada, una venta solo se puede **anular** (revierte stock y ajusta caja), nunca editar — decisión explícita para evitar inconsistencias entre stock/caja ya descontados.
- **`id_payment_method`**: FK real a la tabla `payment_methods` (ver sección 6), reemplazó el `<select>` de opciones fijas.
- **`id_cash_register`**: cada venta queda vinculada a la caja exacta donde se registró (antes se inferia por fecha/usuario, ahora es un FK real).

### Caja
- **Vigencia configurable**: al abrir caja eliges Diaria/Semanal/Mensual (`validity_period`); el sistema calcula `expires_at` automáticamente.
- **Ampliar vigencia**: modal con fecha de inicio (solo lectura) + fecha final editable (`datetime-local`) + atajos rápidos (+1 día/semana/mes).
- **Detalle de caja**: modal con desglose de ventas por método de pago, movimientos manuales, y diferencia (si ya está cerrada).
- El cálculo de `expected_amount` se actualiza **en tiempo real** con cada venta/movimiento, no se recalcula recién al cerrar.

### Productos
- **Wizard de 2 pasos al crear**: paso 1 = datos del producto, paso 2 (opcional, con botón "Omitir por ahora") = registrar stock inicial (crea el primer Lote + movimiento de inventario automáticamente).
- **`id_sin_product_code`**: FK opcional al catálogo oficial de productos del SIN (ver sección 6) — necesario antes de poder facturar ese producto de verdad.

### Inventario / Lotes
- Cada producto puede tener varios lotes (precio de compra, cantidad, vencimiento).
- Ventas descuentan por FEFO (lote más próximo a vencer primero).
- Ajustes de Inventario permiten correcciones manuales con trazabilidad (`stock_before`/`stock_after`).

---

## 6. Integración de Facturación Electrónica (SIN/SIAT Bolivia) — EN CURSO

**Esto es lo más importante que hay que saber si retomas el proyecto.** Hay una integración real (no un mock) con el Servicio de Impuestos Nacionales de Bolivia, modalidad **"Facturación Computarizada en Línea"**, sistema tipo **Propio**, actualmente probándose contra el **ambiente Piloto**.

📄 **Ver `FACTURACION_PENDIENTES.md`** en la raíz del proyecto para el checklist de tareas pendientes (trámites, datos reales que faltan, decisiones de negocio).

### Qué existe y funciona hoy

- **`app/Models/Company.php`** + `/company`: datos del contribuyente (NIT, razón social, código de sistema, CUIS, ambiente, las 4 URLs WSDL del SIN).
- **`app/Models/Branch.php`** + `/branches`: sucursales/puntos de venta (número de sucursal, punto de venta, **municipio** — obligatorio para el XML).
- **`app/Models/PaymentMethod.php`** + `/payment-methods`: catálogo de métodos de pago con su código oficial SIN.
- **`app/Models/SinProductCode.php`**: catálogo de **1382 productos oficiales** del SIN para la actividad económica de la farmacia (CAEB 4772100), usado para mapear `Product.id_sin_product_code`.
- **`app/Services/Facturacion/CufGenerator.php`**: genera el CUF (Código Único de Factura) — **algoritmo validado al 100%** contra el ejemplo oficial del SIN (PDF "GENERACION-CUF.pdf").
- **`app/Services/Facturacion/FacturaXmlBuilder.php`**: arma el XML completo de la factura (`facturaComputarizadaCompraVenta`), **validado contra el XSD oficial** (`resources/facturacion/xsd/`).
- **Comandos Artisan** (todos probados contra el ambiente Piloto real del SIN):
  - `php artisan pharmacy:siat-cuis [--dry]` — pide el CUIS.
  - `php artisan pharmacy:siat-cufd [--dry]` — pide el CUFD (vence cada 24h, se cachea).
  - `php artisan pharmacy:siat-sync {operacion} [--dry]` — sincroniza cualquiera de los ~19 catálogos genéricos del SIN (solo imprime, no persiste).
  - `php artisan pharmacy:siat-sync-productos` — descarga y GUARDA el catálogo de 1382 productos en `sin_product_codes`.
  - `php artisan pharmacy:siat-preview-xml {sale} [--save=ruta.xml]` — arma y valida localmente el XML de una venta existente, **sin enviarlo al SIN**.

### Qué falta (lo grande)

- **Conectar el envío real** del XML al servicio SOAP `recepcionFactura` (`ServicioFacturacionCompraVenta`) — hoy solo se genera/valida localmente.
- Automatizar la renovación diaria del CUFD (scheduler).
- Modo de contingencia (facturación offline).
- Terminar `app/Services/Facturacion/SinFacturacionService.php` (hoy es un stub que lanza excepción a propósito).
- Asignar código SIN a todos los productos existentes (hoy casi ninguno lo tiene).

### Credenciales sensibles

- El **Token Delegado Piloto** (JWT) vive **solo en `.env`** como `FACTURACION_TOKEN_DELEGADO` — nunca en una tabla ni en una pantalla visible.
- `config/facturacion.php` controla el modo (`simulado`/`real`) y los datos del emisor — en modo `simulado` (default), las facturas se generan localmente sin contactar al SIN, para poder probar el flujo venta→factura→PDF sin credenciales reales.

---

## 7. Memoria persistente de Claude (si retomas con Claude Code)

Este proyecto tiene memoria guardada en `~/.claude/projects/.../memory/` con el detalle técnico completo de la integración SIAT (algoritmos validados, códigos oficiales confirmados, decisiones tomadas y por qué, gotchas encontrados). Si eres Claude retomando este proyecto, **consulta esa memoria antes de reinventar algo que ya se investigó** — archivos clave: `facturacion_siat_integracion.md`, `facturacion_siat_referencias.md`.

---

## 8. Seguridad y archivos que NUNCA deben subirse

- `.env` (gitignored).
- `database/*.sql`, `database/backups/` (dumps con datos reales/hashes de contraseñas).
- `Inventario.csv`, `Manual de Usuario - Sistema de Farmacia.docx` (documentos internos del negocio).
- Scripts de diagnóstico temporales (`public/test-*.php`) — se usaron para depurar producción, se deben borrar después de usarlos, nunca dejarlos commiteados.
- Cualquier archivo con el Token Delegado, CUIS o CUFD en texto plano — solo deben vivir en `.env` del servidor.

---

## 9. Despliegue a producción

1. Subir el código (`git pull` o FTP) — **sin** los archivos listados en la sección 8.
2. `composer install --no-dev -o` (si el hosting lo bloquea, ver gotcha de InfinityFree arriba).
3. Copiar/editar `.env` en el servidor con los datos reales de producción (`DB_HOST` real, no `localhost`).
4. `php artisan migrate --force`.
5. Subir `public/build/` (compilado con `npm run build` — no se commitea a git, va directo al servidor).
6. Verificar permisos de escritura en `storage/` y `bootstrap/cache/`.
