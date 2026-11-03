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

## Instalación en una máquina nueva (auto-arranque)

En noviembre 2026 se definió la forma de dejar el sistema andando en una
máquina nueva sin empaquetarlo como app de escritorio: WAMP corriendo como
servicio de Windows (arranque automático) + un acceso directo en modo
`chrome --app=` apuntando a `http://farmafamilia.local`, creado con la
herramienta `add_vhost.php` de WAMP (no editar `httpd-vhosts.conf` ni el
archivo `hosts` a mano). Guía paso a paso completa, con credenciales de
administrador y trabajador incluidas: `docs/instalacion-maquina-nueva.md`.

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
- Comando `php artisan pharmacy:upgrade-schema`: idempotente, endurece/migra
  el esquema de instancias MySQL ya desplegadas (no aplica a SQLite).
- `php artisan pharmacy:import-inventory`: carga única **destructiva** de
  catálogo/inventario desde un CSV — vacía productos/lotes/compras/ventas/etc.
  antes de recargar. Pedir confirmación explícita siempre antes de correrlo.

