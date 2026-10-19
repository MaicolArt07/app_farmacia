# Facturación SIAT — Historia y Pendientes

Este archivo es tu checklist personal de todo lo que falta **hacer tú mismo** (trámites, datos reales, decisiones) para que la facturación computarizada funcione en producción. El trabajo técnico (código) se va registrando aparte en la memoria de Claude; esto es lo que depende de ti.

---

## 🔴 Urgente — corregir datos de prueba

- [ ] **Corregir la Sucursal "Casa Matriz"** en `/branches` — se creó con datos de prueba inventados:
  - Municipio puesto: `Santa Cruz de la Sierra` (verificar si es correcto)
  - Dirección puesta: `Av. Principal #123` (⚠️ esto es inventado, poner la dirección real de la farmacia)
- [ ] Revisar que el **NIT, razón social y dirección** en `/company` coincidan exactamente con lo que tienes registrado ante el SIN.

## 🟡 Trámite en SIAT (portal del SIN)

- [ ] Confirmar que la solicitud **N° 9889** (registro del sistema "FarmaFamili") haya quedado aprobada — revisar en "Seguimiento de Sistemas Informáticos".
- [ ] Verificar si falta completar la **Fase 1** (pruebas técnicas automatizadas, 11 etapas) formalmente ante el SIN, o si ya se dio por completada solo con las pruebas que hicimos.
- [ ] Averiguar qué implica la **Fase 2 (Inspección)** — quién coordina contigo del lado del SIN, y si hay que agendar algo (presencial o virtual).
- [ ] Confirmar la vigencia del **Token Delegado Piloto** — cuándo vence y cómo renovarlo (inactivar con la X + "Generar Nuevo Token" en el portal).

## 🟢 Datos del negocio que hay que revisar/decidir

- [ ] **Asignar código SIN a todos los productos existentes** (hoy solo "Paracetamol" lo tiene, de prueba). Entrar a cada producto en `/products` y buscar su equivalente en el catálogo de 1382 productos oficiales.
- [ ] Decidir qué hacer con **ventas a "Consumidor Final"** (sin NIT/CI) — el SIN exige nominatividad (documento del comprador) en toda venta, sin importar el monto. Revisar si hay que pedir siempre un documento, o usar el `codigoExcepcion` del XML.
- [ ] Revisar el mapeo de **"QR"/pagos móviles** — se decidió registrarlos como "Efectivo" por ahora; confirmar que esto es aceptable para el negocio a largo plazo.
- [ ] Si la farmacia abre una **segunda sucursal real**, recordar que cada una necesita su propio CUIS/CUFD (no se comparte entre sucursales).

## 🔵 Decisiones pendientes sobre el código (para hablar con Claude)

- [ ] ¿Conectar el envío real del XML al SIN (`recepcionFactura`)? Hoy solo se genera y valida localmente, no se envía.
- [ ] ¿Automatizar la renovación diaria del CUFD? (via `php artisan schedule`, no implementado aún)
- [ ] ¿Implementar el modo de contingencia (facturación offline cuando no hay internet)?
- [ ] ¿Agregar el selector de **motivo de anulación** en Ventas? (el SIN exige reportar uno de 4 motivos oficiales al anular una factura, hoy no se pide)
- [ ] Revisar las "leyendas" rotativas del pie de factura — hoy solo hay 3 fijas a mano, confirmar si el SIN tiene un catálogo oficial más completo.

---

## Historial resumido (qué se hizo y cuándo)

| Fecha | Qué se logró |
|---|---|
| 2026-10-19 | Registro del sistema "FarmaFamili" ante el SIN (Solicitud N° 9889), tipo Propio, modalidad Computarizada en Línea. |
| 2026-10-19 | CUIS obtenido (`976ACFF5`) y CUFD probado — primera conexión real con el SIN funcionando. |
| 2026-10-19 | Sincronizados los catálogos oficiales esenciales (unidad de medida, método de pago, documento de identidad, motivo de anulación, tipos de factura, actividad económica 4772100, 1382 códigos de producto). |
| 2026-10-19 | Nuevas tablas: `companies`, `branches`, `payment_methods`, `sin_product_codes` — con sus módulos CRUD en el sistema. |
| 2026-10-19 | Algoritmo CUF implementado y validado 100% contra el ejemplo oficial del SIN. |
| 2026-10-19 | Generador de XML de factura construido — **pasó la validación oficial del XSD** con una venta de prueba real. |

*(Para el detalle técnico completo de cada punto, la memoria de Claude para este proyecto lo tiene todo — pregúntame cuando quieras retomar cualquier tema.)*
