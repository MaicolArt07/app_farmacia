-- ============================================================================
-- PLANTILLA — Repartir los datos actuales entre "Moscú" y "Casa Matriz"
--
-- ⚠️ NO CORRER TAL CUAL. Reemplazá los valores marcados con <<...>> por los
-- datos reales (sobre todo numero_sucursal y punto_venta, que te los da el
-- SIN para cada local — usarlos mal rompe la facturación electrónica).
--
-- Alternativa más segura (recomendada): usar la pantalla
-- Configuraciones > Sucursales dentro del sistema, con un usuario que tenga
-- el permiso "Super Admin". Esa pantalla hace exactamente esto mismo pero
-- con validación, dentro de una transacción, y pidiendo confirmación antes
-- de tocar nada. Este .sql es solo para quien prefiera correrlo a mano.
-- ============================================================================

START TRANSACTION;

-- 1) La sucursal que existe hoy (id=1) es, en realidad, la sucursal "Moscú":
--    todo lo cargado hasta ahora (productos, lotes, ventas, cajas) es de ahí.
UPDATE `sucursales`
SET
    `nombre` = 'Sucursal La Moscú',
    `numero_sucursal` = <<NUMERO_SUCURSAL_MOSCU>>,   -- ej: 1
    `punto_venta` = <<PUNTO_VENTA_MOSCU>>,           -- el que te dio el SIN
    `municipio` = '<<MUNICIPIO_MOSCU>>',
    `direccion` = '<<DIRECCION_MOSCU>>',
    `telefono` = '<<TELEFONO_MOSCU>>'
WHERE `id` = 1;

-- 2) Crear la Casa Matriz real (nueva, sin stock todavía), bajo la misma
--    empresa/NIT que la sucursal Moscú.
INSERT INTO `sucursales`
    (`id_empresa`, `nombre`, `numero_sucursal`, `punto_venta`, `municipio`, `direccion`, `telefono`, `estado`, `created_at`, `updated_at`)
SELECT
    s.id_empresa,
    'Casa Matriz',
    <<NUMERO_SUCURSAL_MATRIZ>>,   -- normalmente 0 para la Casa Matriz, según el SIN
    <<PUNTO_VENTA_MATRIZ>>,
    '<<MUNICIPIO_MATRIZ>>',
    '<<DIRECCION_MATRIZ>>',
    '<<TELEFONO_MATRIZ>>',
    1,
    NOW(),
    NOW()
FROM `sucursales` s
WHERE s.id = 1;

-- Guardate el id de la sucursal Moscú (debería ser 1) para los UPDATE de abajo.
SET @id_moscu = 1;

-- 3) Repartir TODO lo que existe hoy y no tiene sucursal asignada, a Moscú.
UPDATE `users`  SET `id_sucursal` = @id_moscu WHERE `id_sucursal` IS NULL;
UPDATE `lotes`  SET `id_sucursal` = @id_moscu WHERE `id_sucursal` IS NULL;
UPDATE `cajas`  SET `id_sucursal` = @id_moscu WHERE `id_sucursal` IS NULL;
UPDATE `ventas` SET `id_sucursal` = @id_moscu WHERE `id_sucursal` IS NULL;

-- Revisá los resultados antes de confirmar:
-- SELECT * FROM sucursales;
-- SELECT COUNT(*) FROM users  WHERE id_sucursal IS NULL;
-- SELECT COUNT(*) FROM lotes  WHERE id_sucursal IS NULL;
-- SELECT COUNT(*) FROM cajas  WHERE id_sucursal IS NULL;
-- SELECT COUNT(*) FROM ventas WHERE id_sucursal IS NULL;

COMMIT;
-- Si algo se ve mal arriba, usá ROLLBACK; en vez de COMMIT;
