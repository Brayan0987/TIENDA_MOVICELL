# 📋 Sistema de Facturas MOVIL CELL

## ✨ Características Implementadas

### 1. **Descarga de Facturas en PDF** ✅
- Descargar facturas desde el panel admin `/admin/ventas`
- Descargar facturas desde el panel de usuario `/panel`
- Botones intuitivos con iconos y colores
- Archivo se descarga con nombre: `Factura_Pedido_[ID]_[FECHA].pdf`

### 2. **Ver Facturas en Navegador** ✅
- Ver factura embebida directamente en el navegador
- Botones "Ver PDF" en admin y usuario
- URL: `/factura/ver?id=[PEDIDO_ID]`

### 3. **Diseño Premium de Facturas** ✅
- Gradiente púrpura/azul moderno (#667eea → #764ba2)
- Header elegante con efectos de luz
- Metadata en grid de 3 columnas
- Tabla de productos con bordes mejorados
- Imágenes de productos (90×90px con sombra)
- Estados de pedido con colores dinámicos:
  - 🟡 **Pendiente**: #fbbf24 (amarillo)
  - 🔵 **Enviado**: #3b82f6 (azul)
  - 🟢 **Entregado**: #10b981 (verde)
  - 🔴 **Cancelado**: #ef4444 (rojo)

### 4. **Imágenes de Productos en Facturas** ✅
- Las imágenes se incluyen automáticamente desde la BD
- Tamaño optimizado para PDF (90×90px)
- Bordes redondeados con sombra
- Placeholder automático si no hay imagen

### 5. **Panel de Usuario Mejorado** ✅
- Tabla de pedidos con 3 botones por fila:
  - 👁️ **Detalle**: Ver detalles completos
  - 📄 **Ver**: Ver factura en navegador (pestaña nueva)
  - ⬇️ **PDF**: Descargar factura en PDF

### 6. **Admin Ventas Mejorado** ✅
- 4 botones por pedido:
  - 👁️ **Ver**: Detalle del pedido
  - 📄 **Ver Factura**: Abrir en navegador
  - ⬇️ **Descargar**: PDF
  - ✏️ **Editar Estado**: Cambiar estado inline

### 7. **Seguridad de Acceso** ✅
- Usuarios solo ven/descargan sus propios pedidos
- Admins pueden ver todos los pedidos
- Verificación de autenticación antes de generar PDF
- Retorna error 403 si no tiene permisos

---

## 🚀 Rutas Disponibles

| Ruta | Método | Descripción | Requiere Auth |
|------|--------|-------------|---------------|
| `/factura/ver?id=[ID]` | GET | Ver factura en navegador | ✅ |
| `/factura/descargar?id=[ID]` | GET | Descargar PDF de factura | ✅ |
| `/factura/reenviar` | POST | Reenviar factura por email | ✅ |

---

## 📁 Archivos Creados/Modificados

### Creados:
```
App/Controllers/InvoiceController.php         (270 líneas)
tools/test_invoice_download.php               (Prueba)
docs/INVOICE_SYSTEM.md                        (Este archivo)
```

### Modificados:
```
App/Core/Routes/Web.php                       (+3 rutas)
App/Core/InvoiceGenerator.php                 (Completamente reescrito con nuevo diseño)
App/Views/Admin/ventas.php                    (Agregados botones descarga/PDF/vista)
App/Views/auth/panel.php                      (Agregados botones descarga para usuarios)
Public/assets/Css/Admin/ventas.css            (Nuevos estilos de botones)
```

---

## 💻 Uso en Desarrollo

### Desde Panel de Usuario:
1. Ir a `/panel` (requiere login)
2. En la tabla "Mis Pedidos", clickear botones:
   - **Ver**: Ir a detalles
   - **Ver PDF**: Abre factura en pestaña nueva
   - **PDF**: Descarga automáticamente

### Desde Panel Admin:
1. Ir a `/admin/ventas`
2. En cada fila de pedido, usar botones:
   - **👁️ Ojo**: Ver detalles
   - **📄 PDF**: Ver en navegador
   - **⬇️ Descarga**: Descargar PDF
   - **✏️ Editar**: Cambiar estado

### Prueba Directa (Terminal/URL):
```bash
# Ver factura en navegador
http://localhost/TIENDA_MOVICELL/Public/index.php?r=/factura/ver&id=20

# Descargar PDF
http://localhost/TIENDA_MOVICELL/Public/index.php?r=/factura/descargar&id=20
```

---

## 🎨 Diseño de Factura (Premium)

### Estructura:
```
┌─────────────────────────────────────────┐
│  🔋 MOVIL CELL          | FACTURA      │
│  Premium Devices        | Pedido #20   │
├─────────────────────────────────────────┤
│ 📋 Detalles   │ 👤 Cliente  │ 📍 Envío│
├─────────────────────────────────────────┤
│ Imagen | Producto | Cant | Precio | Total│
│  [90px]  Celular    1    $299.99   $299.99│
├─────────────────────────────────────────┤
│                       Subtotal: $299.99  │
│                       TOTAL: $299.99     │
├─────────────────────────────────────────┤
│  ¡Gracias por tu compra en MOVIL CELL! │
└─────────────────────────────────────────┘
```

### Características CSS:
- ✅ Gradiente en header (purple → blue)
- ✅ Efecto glassmorphism en badge
- ✅ Grid responsivo para metadata
- ✅ Tabla con hover effects
- ✅ Colores dinámicos por estado
- ✅ Print-optimized (@media print)

---

## 🔒 Control de Acceso

### Verificación en `InvoiceController::verificarAcceso()`

```php
// Admins (cualquier rol con 'admin'):
- Ver TODOS los pedidos
- Descargar TODAS las facturas

// Usuarios normales:
- Ver SOLO sus propios pedidos
- Descargar SOLO sus propias facturas
- Si intenta otro pedido: Error 403 (Forbidden)
```

---

## 📊 Estructura de Datos

### Tabla `pedidos`:
```sql
- id_pedido (PK)
- id_usuario (FK a users)
- id_estado (FK a estados)
- nombre (cliente)
- telefono
- correo
- direccion
- ciudad
- total
- fecha
- estado (enum o FK)
```

### Tabla `detalle_pedidos`:
```sql
- id_detalle (PK)
- id_pedido (FK)
- producto_id (FK a celulares/producto)
- cantidad
- precio_unitario
- imagen (path a imagen del producto)
```

---

## 🧪 Pruebas Realizadas

✅ Descargar PDF de pedido #20  
✅ Ver factura en navegador  
✅ Verificar acceso de usuarios  
✅ Verificar acceso de admins  
✅ Generar PDF con imágenes  
✅ Imágenes con placeholder si faltan  
✅ Estados con colores correctos  
✅ Datos del cliente correctos  
✅ Cálculo de totales correcto  

---

## 🐛 Solución de Problemas

### ❌ "Error: ID de pedido no proporcionado"
**Solución**: Verificar que la URL incluya `?id=NUMERO`

### ❌ "Error: Pedido no encontrado"
**Solución**: El pedido no existe o el ID es inválido

### ❌ "Error: No tienes permiso para ver este pedido"
**Solución**: 
- Usuario intenta acceder a pedido de otro usuario
- Solución: Iniciar sesión como el dueño del pedido o como admin

### ❌ "Error: session_status() not defined"
**Solución**: Verificar PHP >= 5.4

### ❌ Imágenes no aparecen en PDF
**Solución**:
- Verificar que `pr.imagen` tiene URLs válidas
- Si son rutas relativas, convertir a absolutas
- mPDF solo acepta: HTTP URLs, file:// paths, o base64

---

## 🚀 Próximas Mejoras (Opcionales)

- [ ] Guardar historial de facturas descargadas
- [ ] Email con resend invoice (POST `/factura/reenviar`)
- [ ] QR en factura con link de descarga
- [ ] Múltiples idiomas (ES/EN)
- [ ] Firma digital en PDF
- [ ] Integración con impresora térmica
- [ ] Factura en formato XML
- [ ] API para integración con otros sistemas

---

## 📞 Soporte

Para reportar problemas o sugerencias:
1. Verificar archivo de logs en `storage/logs/`
2. Revisar consola del navegador (F12)
3. Revisar logs de servidor PHP

---

**Generado**: 2025-12-05  
**Versión**: 1.0  
**Estado**: ✅ Producción
