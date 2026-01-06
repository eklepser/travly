<?php

abstract class Model {
    protected $data;
    
    public function __construct() {
        $this->data = [];
    }
    
    protected function setData($key, $value) {
        $this->data[$key] = $value;
    }
    
    protected function getDataValue($key, $default = null) {
        return $this->data[$key] ?? $default;
    }
}

