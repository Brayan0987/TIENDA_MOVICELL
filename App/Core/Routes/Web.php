<?php

// Rutas de autenticación
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/panel', 'AuthController@panel');
$router->post('/panel/profile', 'AuthController@updateProfile');
$router->post('/panel/password', 'AuthController@updatePassword');
$router->post('/account/delete', 'AuthController@deleteAccount');
$router->get('/logout', 'AuthController@logout');

// Rutas de productos y home
$router->get('/', 'ProductController@home');
$router->get('/productos', 'ProductController@index');

// Admin pedidos: actualizar estado vía AJAX
$router->post('/admin/pedido_update_status', 'PedidosController@updateStatus');
// Obtener lista de estados (AJAX)
$router->get('/admin/estados_list', 'PedidosController@getEstados');
$router->get('/search', 'ProductController@search');

// Rutas de facturas
$router->get('/factura/descargar', 'InvoiceController@download');
$router->get('/factura/ver', 'InvoiceController@view');
$router->post('/factura/reenviar', 'InvoiceController@resend');

// APIs para AJAX
$router->get('/api/suggest', 'SearchController@suggest');
$router->get('/api/search', 'SearchController@search');

// Recuperación de contraseña
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/forgot-password', 'AuthController@showForgotPassword');

$router->post('/reset-password', 'AuthController@resetPassword');
$router->get('/reset-password', 'AuthController@showResetPassword');
