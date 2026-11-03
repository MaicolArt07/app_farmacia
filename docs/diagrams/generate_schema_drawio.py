#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Genera docs/diagrams/database-schema.drawio a partir de la estructura
real de la base de datos (columnas + foreign keys), agrupada por dominio.
Script de uso puntual, no forma parte de la app.
"""
import html

# column tuples: (name, type, flag) flag in {"PK","FK","UQ",""}
TABLES = {
    "paises": [
        ("id", "int", "PK"),
        ("nombre", "varchar(50)", ""),
        ("estado", "tinyint", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ],
    "personas": [
        ("id", "bigint", "PK"),
        ("id_pais", "int", "FK"),
        ("nombre", "varchar(50)", ""),
        ("apellido", "varchar(50)", ""),
        ("ci", "varchar(20)", ""),
        ("telefono", "varchar(20)", ""),
        ("direccion", "text", ""),
        ("estado", "tinyint", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ],
    "clientes": [
        ("id", "bigint", "PK"),
        ("id_persona", "bigint", "FK"),
        ("code", "varchar(30)", "UQ"),
        ("tipo", "enum", ""),
        ("nit", "varchar(30)", ""),
        ("email", "varchar(100)", ""),
        ("estado", "tinyint", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ],
    "proveedores": [
        ("id", "bigint", "PK"),
        ("id_persona", "bigint", "FK"),
        ("razon_social", "varchar(150)", ""),
        ("cargo_contacto", "varchar(100)", ""),
        ("nit", "varchar(30)", ""),
        ("email", "varchar(100)", ""),
        ("estado", "tinyint", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ],
    "users": [
        ("id", "bigint", "PK"),
        ("id_persona", "bigint", "FK"),
        ("name", "varchar(191)", ""),
        ("email", "varchar(191)", "UQ"),
        ("email_verified_at", "timestamp", ""),
        ("password", "varchar(191)", ""),
        ("remember_token", "varchar(100)", ""),
        ("session_token", "varchar(64)", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ],
    "roles": [
        ("id", "bigint", "PK"),
        ("name", "varchar(125)", ""),
        ("guard_name", "varchar(125)", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ],
    "permissions": [
        ("id", "bigint", "PK"),
        ("name", "varchar(125)", ""),
        ("guard_name", "varchar(125)", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ],
    "model_has_roles": [
        ("role_id", "bigint", "FK/PK"),
        ("model_type", "varchar(191)", "PK"),
        ("model_id", "bigint", "FK/PK"),
    ],
    "model_has_permissions": [
        ("permission_id", "bigint", "FK/PK"),
        ("model_type", "varchar(191)", "PK"),
        ("model_id", "bigint", "FK/PK"),
    ],
    "role_has_permissions": [
        ("permission_id", "bigint", "FK/PK"),
        ("role_id", "bigint", "FK/PK"),
    ],
    "login_logs": [
        ("id", "bigint", "PK"),
        ("id_usuario", "bigint", "FK"),
        ("session_token", "varchar(64)", ""),
        ("action", "enum", ""),
        ("ip_address", "varchar(45)", ""),
        ("user_agent", "text", ""),
        ("browser", "varchar(100)", ""),
        ("platform", "varchar(100)", ""),
        ("device", "varchar(50)", ""),
        ("created_at", "timestamp", ""),
    ],
    "categorias": [
        ("id", "bigint", "PK"),
        ("nombre", "varchar(150)", ""),
        ("descripcion", "varchar(255)", ""),
        ("estado", "tinyint", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ],
    "laboratorios": [
        ("id", "bigint", "PK"),
        ("nombre", "varchar(150)", ""),
        ("descripcion", "varchar(255)", ""),
        ("estado", "tinyint", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ],
    "presentaciones": [
        ("id", "bigint", "PK"),
        ("nombre", "varchar(150)", ""),
        ("descripcion", "varchar(255)", ""),
        ("codigo_sin", "int", ""),
        ("estado", "tinyint", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ],
    "codigos_producto_sin": [
        ("id", "bigint", "PK"),
        ("codigo_actividad", "varchar(20)", ""),
        ("codigo_producto", "bigint", ""),
        ("descripcion", "varchar(255)", ""),
        ("nandina", "json", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ],
    "productos": [
        ("id", "bigint", "PK"),
        ("id_categoria", "bigint", "FK"),
        ("id_laboratorio", "bigint", "FK"),
        ("id_presentacion", "bigint", "FK"),
        ("id_codigo_producto_sin", "bigint", "FK"),
        ("code", "varchar(50)", "UQ"),
        ("barcode", "varchar(100)", ""),
        ("nombre", "varchar(150)", ""),
        ("nombre_generico", "varchar(150)", ""),
        ("concentracion", "varchar(100)", ""),
        ("descripcion", "text", ""),
        ("precio_venta", "decimal(10,2)", ""),
        ("stock_minimo", "int", ""),
        ("requiere_receta", "tinyint", ""),
        ("estado", "tinyint", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ],
    "lotes": [
        ("id", "bigint", "PK"),
        ("id_producto", "bigint", "FK"),
        ("codigo_lote", "varchar(100)", ""),
        ("precio_compra", "decimal(10,2)", ""),
        ("precio_venta", "decimal(10,2)", ""),
        ("cantidad_ingresada", "int", ""),
        ("cantidad_disponible", "int", ""),
        ("fecha_vencimiento", "date", ""),
        ("ubicacion", "varchar(100)", ""),
        ("estado", "tinyint", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ],
    "movimientos_inventario": [
        ("id", "bigint", "PK"),
        ("id_producto", "bigint", "FK"),
        ("id_lote", "bigint", "FK"),
        ("id_usuario", "bigint", "FK"),
        ("tipo_movimiento", "enum", ""),
        ("reference_type", "varchar(50)", ""),
        ("reference_id", "bigint", ""),
        ("quantity", "int", ""),
        ("stock_before", "int", ""),
        ("stock_after", "int", ""),
        ("descripcion", "text", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ],
    "ajustes_inventario": [
        ("id", "bigint", "PK"),
        ("id_producto", "bigint", "FK"),
        ("id_lote", "bigint", "FK"),
        ("id_usuario", "bigint", "FK"),
        ("type", "enum", ""),
        ("quantity", "int", ""),
        ("motivo", "text", ""),
        ("stock_before", "int", ""),
        ("stock_after", "int", ""),
        ("estado", "tinyint", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ],
    "compras": [
        ("id", "bigint", "PK"),
        ("id_proveedor", "bigint", "FK"),
        ("id_usuario", "bigint", "FK"),
        ("cancelled_by", "bigint", "FK"),
        ("numero_factura", "varchar(50)", ""),
        ("fecha_compra", "date", ""),
        ("subtotal", "decimal(12,2)", ""),
        ("total", "decimal(12,2)", ""),
        ("observacion", "text", ""),
        ("estado", "tinyint", ""),
        ("status", "enum", ""),
        ("cancelled_at", "timestamp", ""),
        ("cancel_reason", "text", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ],
    "detalles_compra": [
        ("id", "bigint", "PK"),
        ("id_compra", "bigint", "FK"),
        ("id_producto", "bigint", "FK"),
        ("codigo_lote", "varchar(100)", ""),
        ("fecha_vencimiento", "date", ""),
        ("quantity", "int", ""),
        ("precio_compra", "decimal(10,2)", ""),
        ("precio_venta", "decimal(10,2)", ""),
        ("subtotal", "decimal(12,2)", ""),
        ("created_at", "timestamp", ""),
    ],
    "metodos_pago": [
        ("id", "bigint", "PK"),
        ("nombre", "varchar(100)", ""),
        ("codigo_sin", "int", ""),
        ("estado", "tinyint", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ],
    "cajas": [
        ("id", "bigint", "PK"),
        ("id_usuario", "bigint", "FK"),
        ("fecha_apertura", "date", ""),
        ("monto_apertura", "decimal(12,2)", ""),
        ("periodo_validez", "varchar(20)", ""),
        ("expires_at", "timestamp", ""),
        ("last_extended_at", "timestamp", ""),
        ("monto_cierre", "decimal(12,2)", ""),
        ("monto_esperado", "decimal(12,2)", ""),
        ("diferencia", "decimal(12,2)", ""),
        ("status", "enum", ""),
        ("observacion", "text", ""),
        ("estado", "tinyint", ""),
        ("opened_at", "timestamp", ""),
        ("closed_at", "timestamp", ""),
        ("created_at", "timestamp", ""),
    ],
    "movimientos_caja": [
        ("id", "bigint", "PK"),
        ("id_caja", "bigint", "FK"),
        ("id_usuario", "bigint", "FK"),
        ("type", "enum", ""),
        ("concepto", "varchar(150)", ""),
        ("monto", "decimal(12,2)", ""),
        ("reference_type", "varchar(50)", ""),
        ("reference_id", "bigint", ""),
        ("observacion", "text", ""),
        ("state", "tinyint", ""),
        ("created_at", "timestamp", ""),
    ],
    "ventas": [
        ("id", "bigint", "PK"),
        ("id_cliente", "bigint", "FK"),
        ("id_usuario", "bigint", "FK"),
        ("id_caja", "bigint", "FK"),
        ("id_metodo_pago", "bigint", "FK"),
        ("cancelled_by", "bigint", "FK"),
        ("fecha_venta", "datetime", ""),
        ("metodo_pago", "varchar(50)", ""),
        ("subtotal", "decimal(12,2)", ""),
        ("descuento", "decimal(12,2)", ""),
        ("total", "decimal(12,2)", ""),
        ("monto_pagado", "decimal(12,2)", ""),
        ("monto_cambio", "decimal(12,2)", ""),
        ("observacion", "text", ""),
        ("estado", "tinyint", ""),
        ("status", "enum", ""),
        ("created_at", "timestamp", ""),
    ],
    "detalles_venta": [
        ("id", "bigint", "PK"),
        ("id_venta", "bigint", "FK"),
        ("id_producto", "bigint", "FK"),
        ("id_lote", "bigint", "FK"),
        ("quantity", "int", ""),
        ("precio_venta", "decimal(10,2)", ""),
        ("subtotal", "decimal(12,2)", ""),
        ("created_at", "timestamp", ""),
    ],
    "empresas": [
        ("id", "bigint", "PK"),
        ("nit", "varchar(50)", ""),
        ("razon_social", "varchar(200)", ""),
        ("nombre_comercial", "varchar(150)", ""),
        ("actividad_economica", "varchar(150)", ""),
        ("email", "varchar(150)", ""),
        ("telefono", "varchar(50)", ""),
        ("direccion", "varchar(255)", ""),
        ("codigo_sistema", "varchar(100)", ""),
        ("cuis", "varchar(100)", ""),
        ("ambiente", "varchar(20)", ""),
        ("soap_version", "varchar(5)", ""),
        ("estado", "tinyint", ""),
        ("created_at", "timestamp", ""),
    ],
    "sucursales": [
        ("id", "bigint", "PK"),
        ("id_empresa", "bigint", "FK*"),
        ("numero_sucursal", "int", ""),
        ("punto_venta", "int", ""),
        ("nombre", "varchar(150)", ""),
        ("municipio", "varchar(25)", ""),
        ("direccion", "varchar(255)", ""),
        ("telefono", "varchar(50)", ""),
        ("estado", "tinyint", ""),
        ("created_at", "timestamp", ""),
    ],
    "facturas": [
        ("id", "bigint", "PK"),
        ("id_venta", "bigint", "FK/UQ"),
        ("id_usuario", "bigint", "FK"),
        ("modo", "enum", ""),
        ("ambiente", "enum", ""),
        ("nit_cliente", "varchar(30)", ""),
        ("razon_social", "varchar(150)", ""),
        ("numero_factura", "bigint", ""),
        ("cuf", "varchar(100)", "UQ"),
        ("cuis", "varchar(50)", ""),
        ("cufd", "varchar(50)", ""),
        ("codigo_control", "varchar(50)", ""),
        ("total", "decimal(12,2)", ""),
        ("estado", "enum", ""),
        ("anulado_at", "timestamp", ""),
        ("created_at", "timestamp", ""),
    ],
}

# (from_table, from_col, to_table, to_col, real_fk) real_fk=False => logico, sin constraint en BD
RELATIONS = [
    ("personas", "id_pais", "paises", "id", True),
    ("clientes", "id_persona", "personas", "id", True),
    ("proveedores", "id_persona", "personas", "id", True),
    ("users", "id_persona", "personas", "id", True),
    ("login_logs", "id_usuario", "users", "id", True),
    ("productos", "id_categoria", "categorias", "id", True),
    ("productos", "id_laboratorio", "laboratorios", "id", True),
    ("productos", "id_presentacion", "presentaciones", "id", True),
    ("productos", "id_codigo_producto_sin", "codigos_producto_sin", "id", True),
    ("lotes", "id_producto", "productos", "id", True),
    ("movimientos_inventario", "id_producto", "productos", "id", True),
    ("movimientos_inventario", "id_lote", "lotes", "id", True),
    ("movimientos_inventario", "id_usuario", "users", "id", True),
    ("ajustes_inventario", "id_producto", "productos", "id", True),
    ("ajustes_inventario", "id_lote", "lotes", "id", True),
    ("ajustes_inventario", "id_usuario", "users", "id", True),
    ("compras", "id_proveedor", "proveedores", "id", True),
    ("compras", "id_usuario", "users", "id", True),
    ("compras", "cancelled_by", "users", "id", True),
    ("detalles_compra", "id_compra", "compras", "id", True),
    ("detalles_compra", "id_producto", "productos", "id", True),
    ("cajas", "id_usuario", "users", "id", True),
    ("movimientos_caja", "id_caja", "cajas", "id", True),
    ("movimientos_caja", "id_usuario", "users", "id", True),
    ("ventas", "id_cliente", "clientes", "id", True),
    ("ventas", "id_usuario", "users", "id", True),
    ("ventas", "cancelled_by", "users", "id", True),
    ("ventas", "id_caja", "cajas", "id", True),
    ("ventas", "id_metodo_pago", "metodos_pago", "id", True),
    ("detalles_venta", "id_venta", "ventas", "id", True),
    ("detalles_venta", "id_producto", "productos", "id", True),
    ("detalles_venta", "id_lote", "lotes", "id", True),
    ("facturas", "id_venta", "ventas", "id", True),
    ("facturas", "id_usuario", "users", "id", True),
    ("sucursales", "id_empresa", "empresas", "id", False),
    ("model_has_roles", "role_id", "roles", "id", False),
    ("model_has_permissions", "permission_id", "permissions", "id", False),
    ("role_has_permissions", "role_id", "roles", "id", False),
    ("role_has_permissions", "permission_id", "permissions", "id", False),
]

GROUPS = [
    ("Geografia y Personas", "#F5F5F5", "#666666", ["paises", "personas", "clientes", "proveedores"]),
    ("Usuarios y Seguridad", "#FFE6CC", "#D79B00", ["users", "roles", "permissions", "model_has_roles", "model_has_permissions", "role_has_permissions", "login_logs"]),
    ("Catalogo de Productos", "#D5E8D4", "#82B366", ["categorias", "laboratorios", "presentaciones", "codigos_producto_sin", "productos"]),
    ("Inventario", "#DAE8FC", "#6C8EBF", ["lotes", "movimientos_inventario", "ajustes_inventario"]),
    ("Compras", "#E1D5E7", "#9673A6", ["compras", "detalles_compra"]),
    ("Ventas y Caja", "#FFF2CC", "#D6B656", ["ventas", "detalles_venta", "cajas", "movimientos_caja", "metodos_pago"]),
    ("Facturacion SIAT", "#F8CECC", "#B85450", ["empresas", "sucursales", "facturas"]),
]

TABLE_WIDTH = 240
ROW_HEIGHT = 20
HEADER_HEIGHT = 26
TABLE_GAP = 30
GROUP_PAD_TOP = 40
GROUP_PAD_SIDE = 20
GROUP_GAP_X = 60
GROUP_GAP_Y = 60
MAX_ROW_WIDTH = 1700  # ancho antes de pasar a la siguiente fila de dominios

def esc(s):
    return html.escape(str(s), quote=True)

def table_height(name):
    return HEADER_HEIGHT + len(TABLES[name]) * ROW_HEIGHT

def group_height(tables):
    return GROUP_PAD_TOP + sum(table_height(t) + TABLE_GAP for t in tables) - TABLE_GAP + GROUP_PAD_SIDE

cells = []
cell_id = 2

def new_id():
    global cell_id
    cell_id += 1
    return f"c{cell_id}"

group_positions = {}  # group title -> (x, y, w, h)
table_positions = {}  # table name -> (x, y, w, h) absolute

cur_x = 40
cur_y = 40
row_max_h = 0

for title, fill, stroke, tnames in GROUPS:
    w = TABLE_WIDTH + GROUP_PAD_SIDE * 2
    h = group_height(tnames)

    if cur_x + w > MAX_ROW_WIDTH and cur_x != 40:
        cur_x = 40
        cur_y += row_max_h + GROUP_GAP_Y
        row_max_h = 0

    group_positions[title] = (cur_x, cur_y, w, h)
    row_max_h = max(row_max_h, h)
    cur_x += w + GROUP_GAP_X

for title, fill, stroke, tnames in GROUPS:
    gx, gy, gw, gh = group_positions[title]
    gid = new_id()
    cells.append(f'<mxCell id="{gid}" value="{esc(title)}" style="rounded=0;whiteSpace=wrap;html=1;fillColor=none;strokeColor={stroke};verticalAlign=top;fontStyle=1;fontSize=16;dashed=1;" vertex="1" parent="1"><mxGeometry x="{gx}" y="{gy}" width="{gw}" height="{gh}" as="geometry"/></mxCell>')

    ty = gy + GROUP_PAD_TOP
    for tname in tnames:
        tw = TABLE_WIDTH
        th = table_height(tname)
        tx = gx + GROUP_PAD_SIDE
        table_positions[tname] = (tx, ty, tw, th)

        tbl_id = f"tbl_{tname}"
        cells.append(
            f'<mxCell id="{tbl_id}" value="{esc(tname)}" '
            f'style="swimlane;html=1;childLayout=stackLayout;horizontal=1;startSize={HEADER_HEIGHT};'
            f'horizontalStack=0;resizeParent=0;resizeParentMax=0;collapsible=0;marginBottom=0;'
            f'whiteSpace=wrap;fillColor={fill};strokeColor={stroke};fontStyle=1;fontSize=13;align=center;" '
            f'vertex="1" parent="1">'
            f'<mxGeometry x="{tx}" y="{ty}" width="{tw}" height="{th}" as="geometry"/></mxCell>'
        )

        for i, (col, ctype, flag) in enumerate(TABLES[tname]):
            row_id = f"{tbl_id}__{col}"
            label = col
            if flag:
                label = f"{col}  ({flag})"
            label_full = f"{label} : {ctype}"
            font_style = "fontStyle=1;" if "PK" in flag else ("fontStyle=4;" if "FK" in flag else "")
            cells.append(
                f'<mxCell id="{row_id}" value="{esc(label_full)}" '
                f'style="text;html=1;align=left;verticalAlign=middle;spacingLeft=6;spacingRight=4;'
                f'overflow=hidden;portConstraint=eastwest;{font_style}fontSize=11;" '
                f'vertex="1" parent="{tbl_id}">'
                f'<mxGeometry y="{HEADER_HEIGHT + i * ROW_HEIGHT}" width="{tw}" height="{ROW_HEIGHT}" as="geometry"/></mxCell>'
            )

        ty += th + TABLE_GAP

for from_table, from_col, to_table, to_col, real_fk in RELATIONS:
    src = f"tbl_{from_table}__{from_col}"
    dst = f"tbl_{to_table}__{to_col}"
    eid = new_id()
    style = (
        "edgeStyle=entityRelationEdgeStyle;rounded=0;html=1;startArrow=ERmany;endArrow=ERone;"
        "exitX=1;exitY=0.5;entryX=0;entryY=0.5;jettySize=auto;orthogonalLoop=1;strokeWidth=1.5;"
    )
    if not real_fk:
        style += "dashed=1;strokeColor=#999999;"
    else:
        style += "strokeColor=#333333;"
    cells.append(
        f'<mxCell id="{eid}" style="{style}" edge="1" parent="1" source="{src}" target="{dst}">'
        f'<mxGeometry relative="1" as="geometry"/></mxCell>'
    )

xml = f'''<mxfile host="app.diagrams.net" modified="2026-10-23T00:00:00.000Z" agent="schema-generator" version="24.0.0">
  <diagram name="Esquema de Base de Datos" id="schema">
    <mxGraphModel dx="2000" dy="1400" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="1700" pageHeight="2200" math="0" shadow="0">
      <root>
        <mxCell id="0"/>
        <mxCell id="1" parent="0"/>
        {''.join(cells)}
      </root>
    </mxGraphModel>
  </diagram>
</mxfile>
'''

with open("database-schema.drawio", "w", encoding="utf-8") as f:
    f.write(xml)

print("OK, tablas:", len(TABLES), "relaciones:", len(RELATIONS))
print("Grupos:", [(g[0], group_positions[g[0]]) for g in GROUPS])
