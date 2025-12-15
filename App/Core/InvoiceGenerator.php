<?php
namespace App\Core;

class InvoiceGenerator
{
    /**
     * Genera HTML profesional y premium de factura con imágenes de productos
     */
    public static function generateInvoiceHtml($order, $items = []): string
    {
        $orderId = $order['id_pedido'] ?? $order['id'] ?? 'N/A';
        $clientName = htmlspecialchars($order['nombre'] ?? '');
        $clientPhone = htmlspecialchars($order['telefono'] ?? '');
        $clientEmail = htmlspecialchars($order['correo'] ?? '');
        $clientAddress = htmlspecialchars($order['direccion'] ?? '');
        $orderDate = $order['fecha'] ?? date('Y-m-d H:i:s');
        $orderState = htmlspecialchars($order['estado'] ?? 'Pendiente');
        $orderTotal = floatval($order['total'] ?? 0);

        $itemsHtml = '';
        $subtotal = 0;

        foreach ($items as $item) {
            $productName = htmlspecialchars($item['nombre'] ?? $item['producto_nombre'] ?? 'Producto');
            $productDesc = htmlspecialchars($item['descripcion'] ?? '');
            $quantity = intval($item['cantidad'] ?? 1);
            $price = floatval($item['precio_unitario'] ?? $item['precio'] ?? 0);
            $lineTotal = $quantity * $price;
            $subtotal += $lineTotal;

            // Intentar obtener imagen del producto
            $imageHtml = '';
            if (!empty($item['imagen'])) {
                $imagePath = $item['imagen'];
                
                // Convertir ruta relativa a absoluta para mPDF
                if (strpos($imagePath, 'http') !== 0 && strpos($imagePath, '/') !== 0) {
                    // Es una ruta relativa como "assets/Imagenes/..."
                    $imagePath = __DIR__ . '/../../Public/' . $imagePath;
                }
                
                $imageHtml = '<img src="' . htmlspecialchars($imagePath) . '" style="max-width: 90px; max-height: 90px; border-radius: 12px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.15);" alt="' . $productName . '" />';
            } else {
                $imageHtml = '<div style="width: 90px; height: 90px; background: linear-gradient(135deg, #f5f7fa 0%, #ecf0f1 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #bdc3c7; font-size: 11px; text-align: center; padding: 8px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">Sin imagen</div>';
            }

            $itemsHtml .= '
            <tr style="background: #fafbfc; transition: all 0.2s ease;">
                <td style="padding: 20px 16px; border-bottom: 2px solid #ecf0f1; text-align: center; vertical-align: middle;">
                    ' . $imageHtml . '
                </td>
                <td style="padding: 20px 16px; border-bottom: 2px solid #ecf0f1; vertical-align: middle;">
                    <div style="font-weight: 700; color: #1f2937; margin-bottom: 6px; font-size: 15px; letter-spacing: -0.3px;">' . $productName . '</div>
                    <div style="font-size: 12px; color: #9ca3af; line-height: 1.4;">' . $productDesc . '</div>
                </td>
                <td style="padding: 20px 16px; border-bottom: 2px solid #ecf0f1; text-align: center; font-weight: 700; color: #374151; vertical-align: middle; font-size: 15px;">' . $quantity . '</td>
                <td style="padding: 20px 16px; border-bottom: 2px solid #ecf0f1; text-align: right; color: #059669; font-weight: 800; vertical-align: middle; font-size: 15px;">$' . number_format($price, 0, ',', '.') . '</td>
                <td style="padding: 20px 16px; border-bottom: 2px solid #ecf0f1; text-align: right; color: #1f2937; font-weight: 800; vertical-align: middle; font-size: 16px;">$' . number_format($lineTotal, 0, ',', '.') . '</td>
            </tr>';
        }

        // Calcular badge de estado con colores mejorados
        $statusColor = '#fbbf24'; // amarillo por defecto
        if (stripos($orderState, 'Entregado') !== false) {
            $statusColor = '#10b981'; // verde
        } elseif (stripos($orderState, 'Enviado') !== false) {
            $statusColor = '#3b82f6'; // azul
        } elseif (stripos($orderState, 'Cancelado') !== false) {
            $statusColor = '#ef4444'; // rojo
        } elseif (stripos($orderState, 'Procesando') !== false) {
            $statusColor = '#0ea5e9'; // azul claro
        }

        $html = <<<'HTMLTEMPLATE'
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura Pedido</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        html,body{width:100%;height:100%;font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;background:#fff;color:#111}
        .invoice-wrapper{max-width:980px;margin:18px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 10px 30px rgba(16,24,40,0.08)}

        /* Header */
        .invoice-header{display:flex;justify-content:space-between;align-items:center;padding:28px 36px;background:linear-gradient(90deg,#0f172a 0%,#111827 100%);color:#fff}
        .brand{display:flex;align-items:center;gap:14px}
        .brand-logo{width:64px;height:64px;border-radius:12px;background:linear-gradient(135deg,#06b6d4,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:28px;box-shadow:0 6px 18px rgba(15,23,42,0.25)}
        .brand-info h1{font-size:20px;letter-spacing:-0.3px;margin-bottom:4px}
        .brand-info p{font-size:13px;color:rgba(255,255,255,0.8)}

        .invoice-meta{text-align:right}
        .invoice-meta h2{font-size:28px;margin-bottom:6px}
        .invoice-id{display:inline-block;background:rgba(255,255,255,0.08);padding:8px 14px;border-radius:999px;font-weight:700;border:1px solid rgba(255,255,255,0.06)}

        /* Grid */
        .invoice-metadata{display:grid;grid-template-columns:1fr 1fr;gap:20px;padding:24px 36px;border-bottom:1px solid #eef2ff;background:linear-gradient(180deg,#fbfdff 0,#fff 100%)}
        .meta-card{background:#fff;padding:18px;border-radius:10px;border:1px solid #f1f5f9}
        .meta-card h3{font-size:12px;color:#0f172a;margin-bottom:8px;text-transform:uppercase}
        .meta-card p{font-size:14px;color:#334155;line-height:1.5}

        .meta-grid{display:flex;gap:12px;align-items:center;justify-content:space-between}
        .status-badge{display:inline-block;padding:6px 12px;border-radius:999px;color:#fff;font-weight:800;font-size:12px}

        /* Items */
        .items-section{padding:28px 36px}
        .items-table{width:100%;border-collapse:collapse;font-size:14px}
        .items-table thead th{background:linear-gradient(90deg,#eef2ff,#f8fafc);text-align:left;padding:12px;border-bottom:1px solid #e6edf3;color:#0f172a;font-weight:700;font-size:12px}
        .items-table thead th.right{text-align:right}
        .items-table tbody td{padding:16px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
        .product-name{font-weight:700;color:#0f172a}
        .product-desc{font-size:12px;color:#64748b}
        .product-image{width:84px;height:64px;object-fit:cover;border-radius:8px}

        /* Summary */
        .summary-section{display:flex;justify-content:flex-end;padding:18px 36px 36px}
        .summary-box{width:360px;background:linear-gradient(180deg,#ffffff,#fbfdff);padding:20px;border-radius:12px;border:1px solid #eef2ff}
        .summary-row{display:flex;justify-content:space-between;padding:8px 0;font-size:14px;color:#374151}
        .summary-row.total{font-weight:900;font-size:18px;color:#0f172a;border-top:2px dashed #e6edf3;padding-top:14px;margin-top:8px}

        /* Footer */
        .invoice-footer{background:#fff;padding:18px 36px;border-top:1px solid #f1f5f9;text-align:center;color:#64748b;font-size:13px}

        @media print{.invoice-wrapper{box-shadow:none;border-radius:0}}
    </style>
</head>
<body>
    <div class="invoice-wrapper">
        <div class="invoice-header">
            <div class="brand">
                <div class="brand-logo">MC</div>
                <div class="brand-info">
                    <h1>MOVIL CELL</h1>
                    <p>Tienda de Dispositivos Móviles · Soporte: +57 313 5187288</p>
                </div>
            </div>
            <div class="invoice-meta">
                <h2>FACTURA</h2>
                <div class="invoice-id">Pedido #%ORDER_ID%</div>
            </div>
        </div>

        <div class="invoice-metadata">
            <div class="meta-card">
                <h3>Detalles del Pedido</h3>
                <div class="meta-grid">
                    <div>
                        <p><strong>Fecha:</strong><br>%ORDER_DATE%</p>
                        <p style="margin-top:8px"><strong>Estado:</strong><br><span class="status-badge" style="background:%STATUS_COLOR%">%ORDER_STATE%</span></p>
                    </div>
                    <div style="text-align:right">
                        <p><strong>ID:</strong><br>%ORDER_ID%</p>
                        <p style="margin-top:8px"><strong>Método de pago:</strong><br>%PAYMENT_METHOD%</p>
                    </div>
                </div>
            </div>

            <div class="meta-card">
                <h3>Datos del Cliente</h3>
                <p><strong>%CLIENT_NAME%</strong></p>
                <p>📞 %CLIENT_PHONE% · 📧 %CLIENT_EMAIL%</p>
                <p style="margin-top:8px"><strong>Dirección:</strong><br>%CLIENT_ADDRESS% · %CITY%</p>
            </div>
        </div>

        <div class="items-section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width:110px">Imagen</th>
                        <th>Producto</th>
                        <th class="right" style="width:90px">Cantidad</th>
                        <th class="right" style="width:140px">Precio Unit.</th>
                        <th class="right" style="width:140px">Total</th>
                    </tr>
                </thead>
                <tbody>
                    %ITEMS_HTML%
                </tbody>
            </table>

            <div class="summary-section">
                <div class="summary-box">
                    <div class="summary-row"><span>Subtotal</span><span>$ %SUBTOTAL%</span></div>
                    <div class="summary-row"><span>Envío</span><span>$ %SHIPPING%</span></div>
                    <div class="summary-row"><span>Impuestos</span><span>$ %TAXES%</span></div>
                    <div class="summary-row total"><span>TOTAL A PAGAR</span><span>$ %TOTAL%</span></div>
                </div>
            </div>
        </div>

        <div class="invoice-footer">
            <div style="font-weight:700;color:#0f172a;margin-bottom:6px">¡Gracias por comprar en MOVIL CELL!</div>
            <div>Si tienes dudas, responde este correo o visita movicell.example.com · Soporte: +57 313 5187288</div>
                <p style="margin-top: 12px; font-size: 10px; color: #d1d5db;">
                    Este documento es una factura electrónica oficial. Conservalo para tu referencia y soporte futuro.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
HTMLTEMPLATE;

        // Reemplazar placeholders
        $html = str_replace('%ORDER_ID%', $orderId, $html);
        $html = str_replace('%ORDER_DATE%', $orderDate, $html);
        $html = str_replace('%ORDER_STATE%', $orderState, $html);
        $html = str_replace('%STATUS_COLOR%', $statusColor, $html);
        $html = str_replace('%CLIENT_NAME%', $clientName, $html);
        $html = str_replace('%CLIENT_PHONE%', $clientPhone, $html);
        $html = str_replace('%CLIENT_EMAIL%', $clientEmail, $html);
        $html = str_replace('%CLIENT_ADDRESS%', $clientAddress, $html);
        $html = str_replace('%CITY%', htmlspecialchars($order['ciudad'] ?? 'N/A'), $html);
        $html = str_replace('%ITEMS_HTML%', $itemsHtml, $html);
        $html = str_replace('%SUBTOTAL%', number_format($subtotal, 0, ',', '.'), $html);
        $html = str_replace('%TOTAL%', number_format($orderTotal, 0, ',', '.'), $html);

        // Campos opcionales: método de pago, envío, impuestos
        $paymentMethod = htmlspecialchars($order['metodo_pago'] ?? $order['payment_method'] ?? 'Método no especificado');
        $shippingVal = floatval($order['envio'] ?? $order['shipping'] ?? 0);
        $taxesVal = floatval($order['impuestos'] ?? $order['taxes'] ?? 0);

        $html = str_replace('%PAYMENT_METHOD%', $paymentMethod, $html);
        $html = str_replace('%SHIPPING%', number_format($shippingVal, 0, ',', '.'), $html);
        $html = str_replace('%TAXES%', number_format($taxesVal, 0, ',', '.'), $html);

        return $html;
    }
}
