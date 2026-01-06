<?php

abstract class Controller {
    protected $model;
    protected $view;
    
    public function __construct() {
        $this->initialize();
    }
    
    protected function initialize() {
    }
    
    abstract public function handle();
}

