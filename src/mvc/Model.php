<?php

abstract class Model {
    protected $data;
    
    public function __construct() {
        $this->data = [];
    }
    
    // Метод getData() должен быть реализован в дочерних классах
    // Каждый дочерний класс может иметь свою сигнатуру с нужными параметрами
    
    protected function setData($key, $value) {
        $this->data[$key] = $value;
    }
    
    protected function getDataValue($key, $default = null) {
        return $this->data[$key] ?? $default;
    }
}

