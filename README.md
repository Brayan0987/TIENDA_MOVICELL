# TIENDA_MOVICELL - Instrucciones de correo y facturas

Este README explica cómo configurar el envío de correos SMTP y la generación de facturas en PDF.

Requisitos mínimos
- PHP 8.0+ (se recomienda 8.1/8.2)
- Composer
- Extensiones recomendadas: `gd` (para mPDF), `mbstring`, `xml`.

Configurar SMTP
1. Edita `App/Core/config.php` y configura las constantes:
   - `MAIL_USE_SMTP` -> `true`
   - `MAIL_SMTP_HOST`, `MAIL_SMTP_PORT`, `MAIL_SMTP_USER`, `MAIL_SMTP_PASS`, `MAIL_SMTP_SECURE`
   - `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`

2. Para pruebas use Mailtrap o una cuenta SMTP de prueba.

Instalar dependencias
Desde la raíz del proyecto ejecuta:
```powershell
cd 'C:\xampp\htdocs\TIENDA_MOVICELL'
composer install
```

Instalar mPDF (recomendado para PDF)
- Si tu PHP tiene `ext-gd` habilitado, simplemente:
```powershell
composer require mpdf/mpdf
```
- Si no puedes habilitar `gd` en el CLI, puedes forzar la instalación (no recomendado):
```powershell
composer require mpdf/mpdf --ignore-platform-req=ext-gd
```

Pruebas locales
- Para probar el envío/guardado de factura ejecuta:
```powershell
php tools/test_send_invoice.php
```
- Revisa `storage/mails/` para ver el HTML o PDF generado.

Notas
- En XAMPP es común que `mail()` no funcione; por eso `Mailer::sendInvoice()` guarda la factura en `storage/mails/` como fallback.
- Si habilitas SMTP, `Mailer::sendInvoice()` enviará el correo con adjunto (si PHPMailer está disponible y `MAIL_USE_SMTP` = true).

¿Quieres que configure un ejemplo de `.env` y un archivo `mail.example.php` con instrucciones de Mailtrap?

Habilitar `ext-gd` en XAMPP (Windows)
1. Verifica qué `php.ini` usa la CLI:
   ```powershell
   php --ini
   ```
   Busca la línea `Loaded Configuration File` (normalmente `C:\xampp\php\php.ini`).
2. Abrir el archivo `php.ini` en un editor (Notepad/VS Code):
   ```powershell
   notepad 'C:\xampp\php\php.ini'
   ```
3. Dentro de `php.ini` busca la línea relacionada con `gd` (puede aparecer como `;extension=gd` o `;extension=gd2`).
   - Si encuentra `;extension=gd` o `;extension=gd2` elimine el punto y coma `;` al inicio para descomentarla:
     ```ini
     extension=gd
     ```
   - Si no existe, añade `extension=gd` en la sección de extensiones.
4. Guarda el archivo y reinicia Apache desde el XAMPP Control Panel (Stop → Start). Si usas la línea de comandos para Apache, reinícialo también.
5. Verifica que `gd` aparece en los módulos de PHP CLI:
   ```powershell
   php -m | Select-String gd
   ```
6. Vuelve a ejecutar la prueba de facturas para comprobar la generación PDF con mPDF:
   ```powershell
   php tools/test_send_invoice.php
   ```

Notas:
- A veces la SAPI Apache y la CLI usan distintos `php.ini`. Si `php -m` no muestra `gd` tras habilitar en `C:\xampp\php\php.ini`, revisa la ruta devuelta por `php --ini` y edita ese archivo.
- Si no deseas editar archivos manualmente, puedo aplicar el cambio por ti (requiere permisos de administrador en Windows). ¿Quieres que lo haga yo aquí?
