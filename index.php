<?php

$route = $_GET['r'] ?? 'dashboard';

switch ($route) {
    case 'api/update':
        require_once __DIR__ . '/app/controllers/ApiController.php';

        $controller = new ApiController();
        $controller->update();
        break;

    case 'api/latest':
        require_once __DIR__ . '/app/controllers/ApiController.php';

        $controller = new ApiController();
        $controller->latest();
        break;

    case 'dashboard':
    default:
        require_once __DIR__ . '/app/controllers/DashboardController.php';

        $controller = new DashboardController();
        $controller->index();
        break;
}