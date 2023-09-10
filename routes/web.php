<?php

use Eva\Controller\Web\AuthController;
use Eva\Controller\Web\AdminController;
use Eva\Controller\Web\ApartmentController;
use Eva\Middleware\AuthMiddleware;

$webRouter = &$router;

$webRouter->get('/', function(\Symfony\Component\HttpFoundation\Request $request) {
    return redirectTo('/auth/login');
});

$webRouter->group('/admin', function($webRouter){
    $webRouter->get('', [AdminController::class, 'index']);
    $webRouter->get('/apartments', [ApartmentController::class, 'index']);
    $webRouter->get('/apartments/create', [ApartmentController::class, 'create']);
    $webRouter->post('/apartments/create', [ApartmentController::class, 'store']);
    $webRouter->get('/apartments/remove/:id', [ApartmentController::class, 'destroy']);

    $webRouter->get('/apartments/edit/:id', [ApartmentController::class, 'edit']);
    $webRouter->post('/apartments/edit/:id', [ApartmentController::class, 'update']);

}, ['before'=>AuthMiddleware::class]);

$webRouter->get('/auth/login', [AuthController::class, 'login']);
$webRouter->post('/auth/login', [AuthController::class, 'check']);

$webRouter->get('/auth/logout', [AuthController::class, 'logout']);