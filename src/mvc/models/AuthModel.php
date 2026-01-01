<?php
require_once __DIR__ . '/../Model.php';

class AuthModel extends Model {
    public function getData() {
        // Для страницы авторизации данных из модели не требуется
        return [];
    }
}

