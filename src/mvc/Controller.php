<?php

abstract class Controller {
    protected $model;
    protected $view;
    
    public function __construct() {
        $this->initialize();
    }
    
    protected function initialize() {
        // Переопределяется в дочерних классах для инициализации модели и представления
    }
    
    abstract public function handle();
}

