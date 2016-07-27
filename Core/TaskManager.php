<?php

namespace Core ;

class TaskManager {
    
    protected $tasks = array();
    protected $db;
    
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    
    public function socketRequest($command){
        
    }
    
    public function register(Task $task){
        $this->tasks[] = $task;
    }
    
    
    public function dispatch(Array $event, Array $args = array()) {
        
        
    }
    
    
}

