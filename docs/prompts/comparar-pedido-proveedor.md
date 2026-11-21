# Comparar un pedido de proveedor (lista a mano) con los lotes anteriores

## Contexto

El proveedor entrega una nota de pedido escrita a mano (foto) con **producto, cantidad y
precio**. Se necesita:

1. Pasar la foto a una tabla.
2. Ubicar cada producto en el sistema.
3. Calcular la ganancia con el precio de venta actual.
4. Compararla con lo que se pagó y ganó en el **lote anterior** del mismo producto, para
   decidir qué conviene comprar a este proveedor y qué no.

Se hizo por primera vez el 18/11/2026 con la nota de "Creaciones Walter" (23 productos).

## Prompt

Copiar todo el bloque y pegarlo en la sesión nueva, junto con la foto del pedido:

```text
Te paso la foto de un pedido de proveedor escrito a mano (producto, cantidad y precio).
Es para la farmacia "FarmaFamilia" (sistema Laravel en d:\wampp\www\app_farmafamilia).
Trabaja SOLO EN LECTURA: no modifiques la base de datos ni el código, salvo que te lo
pida después de ver los resultados.

Datos del sistema:
- Base de datos MySQL: la del .env del proyecto (DB_DATABASE), usuario root sin
  contraseña. Cliente: D:\wampp\bin\mysql\mysql8.4.7\bin\mysql.exe
  (usa --default-character-set=utf8mb4).
- productos: id, code, nombre (nombre limpio, SIN laboratorio), id_laboratorio,
  precio_venta, estado.
- laboratorios: id, nombre, tipo ('LABORATORIO' o 'MARCA'). El nombre visible del
  producto es nombre + " LAB.<laboratorio>" (tipo LABORATORIO) o + " <marca>" (tipo
  MARCA); en PHP es App\Models\Producto::nombre_completo.
- lotes: id, id_producto, codigo_lote, precio_compra (costo), precio_venta (precio al
  que se vende ESE lote), cantidad_ingresada, cantidad_disponible, created_at,
  id_sucursal. Cada ingreso de mercadería es un lote propio (INGRESO-AAAA-MM-DD-<código>);
  la carga inicial es CARGA-INICIAL-<código>. En ventas se cobra el precio del LOTE.

Pasos:

1. TRANSCRIBIR la foto a una tabla: #, producto tal como está escrito, cantidad,
   precio. Marca con "¿?" lo que esté tachado, corregido o dudoso (y di qué otra lectura
   es posible). No inventes valores que no se lean.

2. UBICAR cada producto en el sistema (buscar por palabras del nombre, tolerando
   errores de escritura: "Terbocyl" = TERBOCYL, "Bill 13" = BIL-13, "Día D" = DIA D).
   Indica id y nombre_completo. Si hay varias presentaciones (crema/spray, sobre/
   comprimido, 150 mg/200 mg), muestra las opciones y no elijas a ciegas. Si no
   existe, dilo: habrá que crearlo.

3. Asume que el precio de la lista es COSTO POR UNIDAD (compra), salvo que los
   números indiquen lo contrario (compáralo con el precio de venta para verificarlo).

4. GANANCIA con el precio de venta actual: para cada producto calcula venta del
   sistema, ganancia por unidad (venta − costo), % de ganancia sobre el costo,
   total venta (cant × venta) y total ganancia. Al final, totales de costo, venta y
   ganancia. Los que no están en el sistema van en una tabla aparte (sin precio de
   venta); si hay un producto parecido, úsalo solo como referencia.

5. COMPARAR con el LOTE ANTERIOR del mismo producto (el último lote; mira también
   los anteriores si los hay): costo anterior → venta del lote (ganancia y %) frente a
   costo nuevo → venta (ganancia y %), diferencia de costo por unidad y veredicto:
   ✅ más barato, ➖ igual, ❌ más caro. Ordena de mayor a menor ahorro.
   Calcula el ahorro total del pedido: suma de (costo anterior − costo nuevo) × cantidad.

6. ALERTAS a confirmar antes de comprar o registrar:
   - Costo nuevo muy distinto del anterior (por ejemplo, menos de la mitad o más del
     doble): puede ser otra marca o presentación, o un error al anotar.
   - Producto cuyo lote actual se vende a un precio distinto del precio_venta del
     producto.
   - Ganancia menor al 30%.
   - Cantidades o precios corregidos a mano.

7. Entrégame:
   - La tabla de transcripción.
   - La tabla de ganancia.
   - La tabla comparativa con el resumen: ahorro total, cuántos salen más baratos,
     iguales y más caros, y cuáles conviene comprar en otro lado.
   - La lista de puntos a confirmar.
   Montos en Bs con coma decimal. Si te pido un margen para los productos nuevos,
   calcula su precio de venta sugerido = costo × (1 + margen).

Después de que yo confirme los datos, recién puedes ofrecer registrar la compra en el
sistema (módulo Compras: un lote nuevo por producto con su costo y precio de venta).
Haz siempre una copia de seguridad (mysqldump) antes de escribir en la base.
```

## Notas

- **El precio de la lista es costo, no venta.** Se comprobó comparando con los precios
  de venta del sistema: casi todos los costos quedaban entre 25% y 150% por debajo.
- **Comparar contra el lote, no solo contra el producto.** El sistema cobra el precio
  de cada lote. Por ejemplo, Terbocyl Forte tenía `precio_venta` 58 en el producto pero
  su lote se vendía a 53.
- **Diferencias de costo muy grandes suelen ser otra presentación.** En el primer uso,
  Metamizol ampolla pasó de 7,00 a 1,40 y el algodón de 10 g se comparaba con otro
  tamaño. Por eso hay que confirmarlas antes de registrar la compra.
- **Nombres en el sistema.** Desde el 17/11/2026 `productos.nombre` guarda el nombre sin
  laboratorio. Para mostrarlo se usa `nombre_completo` (nombre + LAB.xxx o marca).
