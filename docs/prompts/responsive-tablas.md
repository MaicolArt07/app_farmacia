# Responsive de tablas (tabla + tarjetas con botón "+")

## Contexto

En móvil, las tablas del sistema (estilo `overflow-auto` + `<table>`) se veían mal:
texto cortado, scroll horizontal incómodo. Se pidió un comportamiento tipo
"DataTables Responsive" (columnas secundarias colapsadas + botón para expandir),
pero **sin usar la librería DataTables**, hecho a mano con Tailwind + Alpine.

Primero se probó como piloto en el módulo **Persona**, con un botón "Ver más" abajo
de la tarjeta. El usuario pidió cambiarlo a un botón circular "+" a la izquierda de
la tarjeta (que pasa a "−" al expandir) — ver captura de referencia de ese momento.
Luego se probó en **Producto** y, al gustar, se replicó en los demás módulos.

Ya aplicado en (octubre 2026): persona, producto, compra, roles, venta, factura, caja,
categoria, usuarios, ajuste-inventario, cliente, permisos, presentacion, laboratorio,
lote, proveedor, metodo-pago, sucursal, kardex.

No aplica (no tienen tabla, son dashboard/listas de tarjetas/reportes): alertas-inventario,
reportes, inicio.

## Prompt

```
Aplica el patrón de tabla responsive (estilo DataTables-Responsive, SIN usar la librería DataTables)
al módulo [NOMBRE_MODULO] (resources/views/livewire/[modulo]/index.blade.php).

Referencia de implementación ya existente: persona/index.blade.php y producto/index.blade.php.

Pasos:
1. Al div contenedor de la tabla (el que tiene `overflow-auto rounded border ...`) agregarle
   las clases `hidden` y `md:block`, para que la tabla solo se muestre desde md hacia arriba.
   No tocar la tabla en sí.

2. Justo después del cierre de ese div, agregar un nuevo bloque:
   <div class="flex flex-col gap-2 md:hidden"> ... </div>
   que recorra el mismo @forelse/@empty que usa la tabla, mostrando una tarjeta por registro
   (visible solo en móvil, oculto en md+).

3. Cada tarjeta:
   - x-data="{ open: false }" en el div contenedor.
   - Si el módulo tiene modal de detalle (método view($id) / $viewingId), agregar
     wire:click="view({{ $item->id }})" y class="cursor-pointer ...".
     Si no tiene detalle, la tarjeta no es clickeable.
   - Fila superior (flex items-center gap-2):
     a) Botón circular "+/-" a la izquierda:
        <button type="button" x-on:click.stop="open = !open"
            class="flex h-6 w-6 shrink-0 cursor-pointer items-center justify-center rounded-full
                   border border-primary text-primary transition hover:bg-primary hover:text-white">
            <i class="bi text-xs" :class="open ? 'bi-dash' : 'bi-plus'"></i>
        </button>
     b) Bloque min-w-0 flex-1 con el campo principal (nombre/código) + una línea secundaria
        (el segundo dato más identificable de esa tabla).
     c) Los mismos botones de acciones (editar/eliminar/activar) copiados tal cual de la
        columna "Opciones" de la tabla, envueltos en onclick="event.stopPropagation()"
        si la tarjeta es clickeable.
   - Debajo, el panel colapsable:
     <div x-show="open" x-collapse x-cloak onclick="event.stopPropagation()"
          class="mt-2 grid grid-cols-2 gap-2 border-t border-zinc-200 pt-2 pl-8 dark:border-zinc-800">
     con el resto de columnas de la tabla que no se mostraron en la cabecera de la tarjeta,
     cada una como:
       <div>
         <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Label</span>
         <p class="text-sm text-zinc-800 dark:text-zinc-100">valor</p>
       </div>
     (mantener badges de estado con los mismos colores que en la tabla).

4. @empty: mismo mensaje "No hay X registrados." en una tarjeta centrada simple.

Restricciones:
- No tocar nada más del archivo (modales, backend, otras secciones).
- La tabla de escritorio queda 100% igual salvo el hidden md:block.
- Si el módulo no usa tabla (es dashboard, lista de tarjetas, reporte), no forzar el patrón.
- Usar Read + Edit (no Write).
```

## Notas

- `x-collapse` depende del plugin `@alpinejs/collapse`; si en algún momento se quita ese
  plugin del bundle de Alpine, este patrón deja de animarse (aunque el `x-show` seguiría
  funcionando sin animación).
- Al probar con Playwright a nivel head­less, el click sobre el botón "+/-" a veces lanza
  un `TimeoutError` de "esperando que el elemento quede estable" por la transición de
  `x-collapse` — no es un bug real, el click sí se aplica (confirmado con screenshot/snapshot
  después del click).
- Módulos sin acciones de editar/eliminar en la tabla (ej. kardex, que es histórico de solo
  lectura) no llevan esos botones ni modal de detalle en la tarjeta — solo el toggle "+/-".
- Para módulos nuevos que se agreguen más adelante con tabla + CRUD, repetir este mismo
  prompt apuntando al archivo nuevo.
