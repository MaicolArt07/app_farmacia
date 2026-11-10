-- ============================================================================
-- Farmacia FarmaFamilia — Tablas nuevas y modificaciones de esquema acumuladas
-- entre el 2026-11-03 y el 2026-11-09 (NO incluye multi-sucursal, que ya
-- tiene su propio script: database/sql/2026_11_09_multisucursal_setup.sql).
--
-- SOLO ESQUEMA (CREATE TABLE / ALTER TABLE) + catálogos (permisos, roles,
-- métodos de pago). NO contiene ninguna venta, compra, lote ni dato de
-- prueba/ficticio: a propósito, para que el conteo de ventas reales de aquí
-- en adelante se pueda comparar limpio contra lo que había antes de correr
-- esto.
--
-- Si el proyecto se gestiona con `php artisan migrate`, todo esto ya está
-- aplicado (son las migraciones de esas fechas en database/migrations/).
-- Este .sql es para poder aplicar lo mismo a mano en otra instancia (por
-- ejemplo producción sin acceso a Artisan, o phpMyAdmin) sin correr Artisan.
-- Si una instancia YA tiene estas columnas/tablas (porque corrió las
-- migraciones), vas a ver errores de "ya existe" — es seguro ignorarlos ahí,
-- o comentar esa sección antes de correr el resto.
-- ============================================================================


-- ----------------------------------------------------------------------------
-- 1) Métodos de pago: solo Efectivo, QR y Mixto (Tarjeta queda desactivado)
--    "Transferencia" (codigo_sin 7) pasa a llamarse "QR".
-- ----------------------------------------------------------------------------

UPDATE `metodos_pago`
SET `nombre` = 'QR', `estado` = 1, `updated_at` = NOW()
WHERE `nombre` = 'Transferencia';

INSERT INTO `metodos_pago` (`nombre`, `codigo_sin`, `estado`, `created_at`, `updated_at`)
SELECT 'QR', 7, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `metodos_pago` WHERE `nombre` = 'QR');

UPDATE `metodos_pago` SET `estado` = 0, `updated_at` = NOW() WHERE `nombre` = 'Tarjeta';
-- "Mixto" se reactiva en la sección 6 (pago mixto), una vez que `ventas` ya
-- tiene las columnas monto_efectivo/monto_qr que ese método necesita.


-- ----------------------------------------------------------------------------
-- 2) Permisos nuevos: "Cerrar Caja" y "Ver Alertas Inventario"
--    (el Trabajador pasa a solo ver/abrir/cerrar su caja; pierde ver
--    movimientos, registrar movimientos, ampliar vigencia y cambiar estado).
-- ----------------------------------------------------------------------------

INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT 'Cerrar Caja', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `name` = 'Cerrar Caja' AND `guard_name` = 'web');

INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT 'Ver Alertas Inventario', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `name` = 'Ver Alertas Inventario' AND `guard_name` = 'web');

-- Asignar "Cerrar Caja" y "Ver Alertas Inventario" a admin y Trabajador.
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p
JOIN `roles` r ON r.name IN ('admin', 'Trabajador') AND r.guard_name = 'web'
WHERE p.name IN ('Cerrar Caja', 'Ver Alertas Inventario') AND p.guard_name = 'web'
  AND NOT EXISTS (
      SELECT 1 FROM `role_has_permissions` rhp
      WHERE rhp.permission_id = p.id AND rhp.role_id = r.id
  );

-- Quitarle al Trabajador: ver/crear movimientos de caja, ampliar vigencia,
-- cambiar estado (el admin conserva todo esto).
DELETE rhp FROM `role_has_permissions` rhp
JOIN `roles` r ON r.id = rhp.role_id AND r.name = 'Trabajador' AND r.guard_name = 'web'
JOIN `permissions` p ON p.id = rhp.permission_id
WHERE p.name IN ('Ver Movimientos Caja', 'Crear Movimientos Caja', 'Editar Caja', 'Cambiar Estado Caja');


-- ----------------------------------------------------------------------------
-- 3) Tabla nueva: servicios (ej. inyectables, curaciones) + columnas en
--    detalles_venta para poder vender un servicio además de productos.
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `servicios` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(150) NOT NULL,
    `descripcion` TEXT NULL,
    `precio` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `estado` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `detalles_venta`
    ADD COLUMN `id_servicio` BIGINT UNSIGNED NULL AFTER `id_venta`,
    ADD COLUMN `cantidad_servicio` DECIMAL(10,2) NULL AFTER `id_servicio`,
    ADD CONSTRAINT `detalles_venta_id_servicio_foreign`
        FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id`);

-- Una línea de detalle ahora puede ser SOLO servicio (sin producto/lote).
ALTER TABLE `detalles_venta`
    MODIFY `id_producto` BIGINT UNSIGNED NULL,
    MODIFY `id_lote` BIGINT UNSIGNED NULL;


-- ----------------------------------------------------------------------------
-- 4) Productos "paquete" vinculados a su producto "unidad" (ej. pañales
--    PAQUETE x20 -> UNIDAD), para poder "abrir paquete" y vender sueltos.
-- ----------------------------------------------------------------------------

ALTER TABLE `productos`
    ADD COLUMN `id_producto_unidad` BIGINT UNSIGNED NULL AFTER `id_codigo_producto_sin`,
    ADD COLUMN `unidades_por_paquete` INT UNSIGNED NULL AFTER `id_producto_unidad`,
    ADD CONSTRAINT `fk_productos_producto_unidad`
        FOREIGN KEY (`id_producto_unidad`) REFERENCES `productos` (`id`) ON DELETE SET NULL;


-- ----------------------------------------------------------------------------
-- 5) Cierre de caja en dos verificaciones: columnas para el cuadre de QR
--    (lo que el sistema registró vs. lo que llegó a la cuenta).
-- ----------------------------------------------------------------------------

ALTER TABLE `cajas`
    ADD COLUMN `monto_qr_esperado` DECIMAL(10,2) NULL AFTER `diferencia`,
    ADD COLUMN `monto_qr_verificado` DECIMAL(10,2) NULL AFTER `monto_qr_esperado`,
    ADD COLUMN `diferencia_qr` DECIMAL(10,2) NULL AFTER `monto_qr_verificado`;


-- ----------------------------------------------------------------------------
-- 6) Pago MIXTO: reparto efectivo/QR por venta. Las columnas quedan NULL
--    para ventas futuras hasta que se registre su reparto; el UPDATE de abajo
--    es solo para que las ventas YA EXISTENTES (reales, no de prueba) queden
--    coherentes con su método de pago actual — no inserta ninguna venta.
-- ----------------------------------------------------------------------------

ALTER TABLE `ventas`
    ADD COLUMN `monto_efectivo` DECIMAL(12,2) NULL AFTER `total`,
    ADD COLUMN `monto_qr` DECIMAL(12,2) NULL AFTER `monto_efectivo`;

-- Backfill de ventas reales ya existentes según su metodo_pago actual
-- (quitar/omitir este UPDATE si no se quiere tocar el histórico).
UPDATE `ventas` SET `monto_efectivo` = `total`, `monto_qr` = 0
WHERE UPPER(`metodo_pago`) = 'EFECTIVO';

UPDATE `ventas` SET `monto_efectivo` = 0, `monto_qr` = `total`
WHERE UPPER(`metodo_pago`) = 'QR';

-- Reactivar "Mixto" ahora que `ventas` ya soporta el reparto.
UPDATE `metodos_pago` SET `estado` = 1, `updated_at` = NOW() WHERE `nombre` = 'Mixto';


-- ----------------------------------------------------------------------------
-- 7) Devolución parcial de productos de una venta (solo admin): historial en
--    devoluciones / devolucion_detalles + columnas de devuelto en ventas.
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `devoluciones` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_venta` BIGINT UNSIGNED NOT NULL,
    `id_caja` BIGINT UNSIGNED NULL,
    `id_usuario` BIGINT UNSIGNED NOT NULL,
    `monto` DECIMAL(12,2) NOT NULL,
    `monto_efectivo` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `monto_qr` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `motivo` TEXT NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_devoluciones_venta` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`),
    CONSTRAINT `fk_devoluciones_caja` FOREIGN KEY (`id_caja`) REFERENCES `cajas` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_devoluciones_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `devolucion_detalles` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_devolucion` BIGINT UNSIGNED NOT NULL,
    `id_detalle_venta` BIGINT UNSIGNED NOT NULL,
    `id_producto` BIGINT UNSIGNED NULL,
    `id_servicio` BIGINT UNSIGNED NULL,
    `id_lote` BIGINT UNSIGNED NULL,
    `cantidad` INT NOT NULL,
    `precio_venta` DECIMAL(10,2) NOT NULL,
    `subtotal` DECIMAL(12,2) NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_devdet_devolucion` FOREIGN KEY (`id_devolucion`) REFERENCES `devoluciones` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_devdet_detalle` FOREIGN KEY (`id_detalle_venta`) REFERENCES `detalles_venta` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `detalles_venta`
    ADD COLUMN `cantidad_devuelta` INT NOT NULL DEFAULT 0 AFTER `quantity`;

ALTER TABLE `ventas`
    ADD COLUMN `monto_devuelto` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `monto_qr`;


-- ----------------------------------------------------------------------------
-- 8) Módulo Contabilidad: libro DEBE/HABER + catálogo de gastos comunes.
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `gastos_comunes` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(150) NOT NULL,
    `descripcion` VARCHAR(255) NULL,
    `monto_referencia` DECIMAL(10,2) NULL,
    `estado` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `movimientos_contables` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `fecha` DATE NOT NULL,
    `tipo` VARCHAR(10) NOT NULL,          -- DEBE | HABER
    `concepto` VARCHAR(255) NOT NULL,
    `monto` DECIMAL(12,2) NOT NULL,
    `origen` VARCHAR(30) NOT NULL,        -- COMPRA | CIERRE_CAJA | GASTO_COMUN | MANUAL
    `reference_id` BIGINT UNSIGNED NULL,
    `id_gasto_comun` BIGINT UNSIGNED NULL,
    `id_usuario` BIGINT UNSIGNED NULL,
    `observacion` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `movimientos_contables_origen_reference_id_index` (`origen`, `reference_id`),
    KEY `movimientos_contables_fecha_index` (`fecha`),
    CONSTRAINT `movimientos_contables_id_gasto_comun_foreign`
        FOREIGN KEY (`id_gasto_comun`) REFERENCES `gastos_comunes` (`id`) ON DELETE SET NULL,
    CONSTRAINT `movimientos_contables_id_usuario_foreign`
        FOREIGN KEY (`id_usuario`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Permiso del módulo (si no se cargó ya por `pharmacy:upgrade-schema`).
INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT 'Ver Contabilidad', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `name` = 'Ver Contabilidad' AND `guard_name` = 'web');

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p
JOIN `roles` r ON r.name = 'admin' AND r.guard_name = 'web'
WHERE p.name = 'Ver Contabilidad' AND p.guard_name = 'web'
  AND NOT EXISTS (
      SELECT 1 FROM `role_has_permissions` rhp
      WHERE rhp.permission_id = p.id AND rhp.role_id = r.id
  );

-- ============================================================================
-- FIN. No hay ninguna fila de ventas/compras/lotes insertada por este script:
-- las columnas nuevas quedan en NULL/0/default hasta que el sistema las use
-- en operaciones reales a partir de ahora.
-- ============================================================================
