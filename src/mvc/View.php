<?php

abstract class View {
    protected $data;
    
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    abstract public function render();
    
    protected function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

