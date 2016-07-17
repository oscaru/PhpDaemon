<?php

namespace Core ;

class TaskManager {
    
    protected $tasks = array();
    
    
    public function register(\Core\Interfaces\Task $task){
        $this->tasks[] = $task;
    }
    
    
    public function dispatch(Array $event, Array $args = array()) {
        
        
    }
    
    
}

