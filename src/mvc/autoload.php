<?php
// Автозагрузка MVC классов
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/View.php';
require_once __DIR__ . '/Model.php';

// Загружаем все контроллеры
require_once __DIR__ . '/controllers/MainController.php';
require_once __DIR__ . '/controllers/SearchController.php';
require_once __DIR__ . '/controllers/TourController.php';
require_once __DIR__ . '/controllers/BookingController.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/RegistrationController.php';
require_once __DIR__ . '/controllers/MeController.php';
require_once __DIR__ . '/controllers/AboutController.php';
require_once __DIR__ . '/controllers/HelpController.php';

