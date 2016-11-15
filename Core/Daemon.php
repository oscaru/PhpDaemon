<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// signal handler
namespace Core ;



class Daemon {
    

    protected static  $instance = null;
    protected $socketHost = '127.0.0.1';
    protected $socketPort = '9000';

    protected  $signHandlers = array();
    protected  $signalAllowed = array(
        SIGTERM, SIGHUP, SIGCHLD, SIGUSR1, SIGUSR2
    );

    protected  $taskManager;    
    protected  $socketServer = null;        
    
    private function __construct() {
        
    }
    
    public static function getInstance(){
        if(!self::$instance){
            self::$instance = new Daemon();
        }
         return self::$instance;
    }
   
    public function setTaskManager(TaskManager $taskManager){
        $this->taskManager =$taskManager;
        
    }
    
    public   function start() {
        //set_error_handler(array('\Core\Daemon', 'errorHandler'), E_ALL);
        
        $this->checkins();
        $this->daemonize();
          
        $server = new \Core\SocketServer();
        $server->setCallBack(array($this,'command'));
        $this->socketServer = $server;
            
        
        
     
        $this->runLoop();
        
    }
    
    /**
     * Comprobaciones de requirimientos
     */
    public function checkins()

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
    
    
    public function command($message){
        try {
            
        }catch (Exception $e){
            
        }
        
    }


    public  function daemonize()
    {

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
        
       
        ini_set('display_errors', \Core\Config::get('display_errors'));
        ini_set('error_log', DAEMON_ERR); // set log file


        $this->debug(  "DAEMON PID :". getmypid() );
        
        declare(ticks = 5);
        
        foreach ($this->signalAllowed as $signal ) {
            $handler = (!empty($this->signHandlers[$signal]))? 
                    $this->signHandlers[$signal] 
                    : array('\Core\Daemon', 'defaultSignHandler');
            
            
            
            if ($handler && !is_callable($handler) && $handler != SIG_IGN && $handler != SIG_DFL) {
                $this->debug(
                    'You want to assign signal %s to handler %s but ' .
                    'it\'s not callable'
                   
                );
                return FALSE;
                
            } else if (!pcntl_signal($signal, $handler)) {
               $this->debug('Unable to reroute signal handler: '.$signal );
               return FALSE;
            }
        }
        
        return TRUE;
    }

    function runLoop(){        
        
        while(true){  
            pcntl_signal_dispatch();
            $this->socketServer->SockectLoop(); //socket loop;
            
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



