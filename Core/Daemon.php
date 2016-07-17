<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// signal handler
namespace Core ;



class Daemon {
    
    protected  $signHandlers = array();
    protected  $signalAllowed = array(
        SIGTERM, SIGHUP, SIGCHLD, SIGUSR1, SIGUSR2
    );
    protected  $taskManager;
    protected static  $intance = null;
    
    static function getInstance(){
        if(empty(self::$intance)){
            self::$intance = new Daemon();
        }
        return self::$intance;
    }
    
    private function __construct() {
       
    }
    
    public   function start()
    {
        //set_error_handler(array('\Core\Daemon', 'errorHandler'), E_ALL);
        $this->checkins();
        $this->daemonize();
        
    }
    
    
    
    
    public  function checkins()
    {
        
        
        
        // Check the PHP configuration
        if (!defined('SIGHUP')) {
            $msg = 'PHP is compiled without --enable-pcntl directive';
            trigger_error($msg, E_USER_ERROR);
        }

        // Check for CLI
        if ((php_sapi_name() !== 'cli')) {
            $msg = 'You can only create daemon from the command line (CLI-mode)';
            trigger_error($msg, E_USER_ERROR);
        }

        // Check for POSIX
        if (!function_exists('posix_getpid')) {
            $msg = 'PHP is compiled without --enable-posix directive';
            trigger_error($msg, E_USER_ERROR);
        }

        // Enable Garbage Collector (PHP >= 5.3)
        if (function_exists('gc_enable')) {
            gc_enable();
        }
        
       
    }
    
    

     function daemonize(){
       
        /*
         * 1 - Resetting the file mode creation mask to 0 function umask(), 
         * to mask some bits of access rights from the starting process.
         */
        umask(0); // § 1
        
        /*
         * 2 - Cause fork() and finish the parent process. 
         * This is done so that if the process was launched as a group, 
         * the shell believes that the group finished at the same time, 
         * the child inherits the process group ID of the parent and gets its own process ID. 
         * This ensures that it will not become process group leader.
         */
        $pid = pcntl_fork(); // § 2

        //ini_set('error_log', $logDir.'/error.log'); // set log file

        if ($pid < 0) {
            print('fork failed');
            exit (1); 
        }



        if ($pid > 0) { // the parent process
            exit; // Exit
        } 
        // (pid = 0) child process
        
        $this->debug( "daemon process started ".  getmypid() );    
        $sid = posix_setsid();// § 3
        if ($sid < 0) exit (2);

        //chdir(DAEMON_ROOTDIR); // § 4


        // Closes an open file descriptors system STDIN, STDOUT, STDERR
        fclose(STDIN);   
        fclose(STDOUT);
        fclose(STDERR);
        
        
         /*STDOUT ya está cerrado, si intentamos imprimir cualquier cosa o interpolar HTML 
         * después de este punto, la secuencia de comandosfllará. 
         * Del mismo modo, cualquier mensaje de error no tendrían a dónde ir, 
         * y cualquier intento de entrada inadvertida para leer terminaría mal. 
         * 
         * Así que en su  lugar vamos a recrear estas tres corrientes, pero con destinos 
         * sensibles. Cuando se cierran los flujos estándares, y como no  se puede volver 
         * a definir una constante, PHP simplemente asigna los flujos estándares para 
         * los tres siguientes descriptores de fichero que se abran 
         *  (como se llamen y dondequiera que apuntan a). 
         * 
         * Así, el siguiente código también actúa para proteger los otros streams
         * que se puedan abrir
         *  */

        // redirect stdin to /dev/null
        $STDIN = fopen('/dev/null', 'r'); 
        // redirect stdout to a log file
        $STDOUT = fopen(DAEMON_OUT, 'ab');
        // redirect stderr to a log file
        $STDERR = fopen(DAEMON_ERR, 'ab');
        
        error_reporting(E_ALL);
        ini_set('error_log', DAEMON_ERR); // set log file
        echo $er;
        $this->debug(  "DAEMON PID :". getmypid() );
        
        declare(ticks = 5);
        
        foreach ($this->signalAllowed as $signal ) {
            $handler = (!empty($this->signHandlers[$signal]))? 
                    $this->signHandlers[$signal] 
                    : array('\Core\Daemon', 'defaultSignHandler');
            
            
            
            if ($handler && !is_callable($handler) && $handler != SIG_IGN && $handler != SIG_DFL) {
                return $this->debug(
                    'You want to assign signal %s to handler %s but ' .
                    'it\'s not callable'
                   
                );
            } else if (!pcntl_signal($signal, $handler)) {
                return $this->debug('Unable to reroute signal handler: '.$signal );
            }
        }
        
        
        
        while(true){  
            pcntl_signal_dispatch();
            
        }; // cycle start data
    }
    
    protected  function debug($string)
    {
        if(!DEBUG) return;
        $now = date('Y-m-d H:i:s');
        echo "{$now} >> DEBUG : {$string} \n";
    }
    
    
    
     function defaultSignHandler($signo)
    {
        
        switch ($signo) {
            case SIGTERM:
   
                break;
            default:
                $this->debug(   "echo CORE STATIC SIGAL PID :". $signo );
    
        }
    }

}





