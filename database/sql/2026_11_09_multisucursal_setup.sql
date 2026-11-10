-- ============================================================================
-- Farmacia FarmaFamilia — Base para soporte multi-sucursal
-- Fecha: 2026-11-09
--
-- Qué hace este script:
--   1) Agrega la columna id_sucursal (nullable, FK a sucursales) a las
--      tablas operativas: users, lotes, cajas, ventas.
--      El catálogo de productos (tabla `productos`) NO se toca: sigue
--      compartido entre sucursales a propósito.
--   2) Crea el permiso "Super Admin" (Spatie) — ve/administra TODAS las
--      sucursales. No se asigna automáticamente al rol "admin": cada admin
--      de sucursal solo debe ver la suya.
--   3) Asigna el permiso "Super Admin" directamente al usuario dueño del
--      sistema (maicolarteaga0711@gmail.com).
--
-- Lo que este script NO hace (a propósito):
--   - NO renombra la sucursal actual ni crea la "Casa Matriz", ni reparte
--     los usuarios/lotes/cajas/ventas existentes entre sucursales. Esa
--     parte depende de datos reales que debe confirmar un humano (el
--     número de sucursal y punto de venta que asignó el SIN a cada local,
--     para no romper la facturación electrónica), así que se hace desde la
--     app, en Configuraciones > Sucursales, con un usuario que tenga el
--     permiso "Super Admin". Esa pantalla valida, usa una transacción y
--     muestra una confirmación antes de tocar nada — hacerlo a ciegas por
--     SQL es justamente lo que se quiere evitar acá.
--
-- Este script es idempotente donde fue posible (columnas con IF NOT EXISTS
-- vía comprobación manual recomendada antes de correr en un entorno ya
-- migrado por Laravel — ver nota al final).
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1) Columnas id_sucursal (nullable, con FK) en las tablas operativas
-- ----------------------------------------------------------------------------

ALTER TABLE `users`
    ADD COLUMN `id_sucursal` BIGINT UNSIGNED NULL AFTER `id_persona`,
    ADD CONSTRAINT `users_id_sucursal_foreign`
        FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`)
        ON DELETE SET NULL;

ALTER TABLE `lotes`
    ADD COLUMN `id_sucursal` BIGINT UNSIGNED NULL AFTER `id_producto`,
    ADD CONSTRAINT `lotes_id_sucursal_foreign`
        FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`)
        ON DELETE SET NULL;

ALTER TABLE `cajas`
    ADD COLUMN `id_sucursal` BIGINT UNSIGNED NULL AFTER `id_usuario`,
    ADD CONSTRAINT `cajas_id_sucursal_foreign`
        FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`)
        ON DELETE SET NULL;

ALTER TABLE `ventas`
    ADD COLUMN `id_sucursal` BIGINT UNSIGNED NULL AFTER `id_caja`,
    ADD CONSTRAINT `ventas_id_sucursal_foreign`
        FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`)
        ON DELETE SET NULL;

-- ----------------------------------------------------------------------------
-- 2) Permiso "Super Admin" (catálogo de permisos de Spatie)
-- ----------------------------------------------------------------------------

INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT 'Super Admin', 'web', NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `permissions` WHERE `name` = 'Super Admin' AND `guard_name` = 'web'
);

-- ----------------------------------------------------------------------------
-- 3) Asignar "Super Admin" directamente al usuario dueño del sistema
--    (permiso directo al usuario, no vía rol — así los demás admins de
--    sucursal, que comparten el rol "admin", NO lo heredan).
-- ----------------------------------------------------------------------------

INSERT INTO `model_has_permissions` (`permission_id`, `model_type`, `model_id`)
SELECT p.id, 'App\\Models\\User', u.id
FROM `permissions` p
JOIN `users` u ON u.email = 'maicolarteaga0711@gmail.com'
WHERE p.name = 'Super Admin' AND p.guard_name = 'web'
  AND NOT EXISTS (
      SELECT 1 FROM `model_has_permissions` mhp
      WHERE mhp.permission_id = p.id
        AND mhp.model_type = 'App\\Models\\User'
        AND mhp.model_id = u.id
  );

-- ============================================================================
-- NOTA: si este proyecto se gestiona con `php artisan migrate`, este mismo
-- cambio de esquema ya quedó aplicado por la migración
-- `2026_11_09_160004_add_id_sucursal_to_operational_tables.php`, y el
-- permiso "Super Admin" + su asignación al dueño del sistema ya se hizo con
-- `php artisan pharmacy:upgrade-schema` + una asignación directa. Este .sql
-- es para poder aplicar lo mismo a mano (por ejemplo en otra instancia, o
-- por phpMyAdmin) sin pasar por Artisan. Si lo vas a correr en una base que
-- YA tiene estas columnas (por haber corrido las migraciones), vas a ver
-- errores de "columna duplicada" — es seguro ignorarlos ahí, o comenta esas
-- líneas antes de correr el resto.
-- ============================================================================
