<?php

namespace Core ;

class Daemon 
{
    
    
    protected static $signHandlers = array();
    protected static $signalAllowed = array(
        SIGTERM, SIGHUP, SIGCHLD, SIGUSR1, SIGUSR2
    );


    /**
     *  preventing instantiation 
    */
    protected function __construct()
    {}

    public  static function start()
    {
        set_error_handler(array('\Core\Daemon', 'errorHandler'), E_ALL);
        self::checkins();
        self::daemonize();
    }
    
    public static function checkins()
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
    
    protected static function daemonize()
    {
         /*
         * 1 - Resetting the file mode creation mask to 0 function umask(), 
         * to mask some bits of access rights from the starting process.
         */
        umask(0); // § 1

        declare(ticks = 5);

        /*
         * 2 - Cause fork() and finish the parent process. 
         * This is done so that if the process was launched as a group, 
         * the shell believes that the group finished at the same time, 
         * the child inherits the process group ID of the parent and gets its own process ID. 
         * This ensures that it will not become process group leader.
         */
        $pid = pcntl_fork(); // § 2

        if ($pid < 0) {
            self::emerg('fork failed');
            exit (1); 
        }

        if ($pid > 0) {// the parent process
            self::debug("daemon process started");
            exit; // Exit
        } 
        // (pid = 0) child process

        $sid = posix_setsid();// § 3
        if ($sid < 0) exit (2);

        
        //signals
        declare(ticks = 5);

        // Setup signal handlers
        // Handlers for individual signals can be overrulled with
        // setSigHandler()
        foreach (self::$signalAllowed as $signal ) {
            $handler = (!empty(self::$signHandlers[$signal]))? 
                    self::$signHandlers[$signal] 
                    : array('\Core\Daemon', 'defaultSignHandler');
            
            
            
            if ($handler && !is_callable($handler) && $handler != SIG_IGN && $handler != SIG_DFL) {
                return self::emerg(
                    'You want to assign signal %s to handler %s but ' .
                    'it\'s not callable',
                    $signal,
                    $handler
                );
            } else if (!pcntl_signal($signal, $handler)) {
                return self::emerg(
                    'Unable to reroute signal handler: %s',
                    $signal
                );
            }
        }
        
        chdir(DAEMON_ROOTDIR); // § 4

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

        self::debug  ('Daemon pid : '. getmypid());

    }
    
    protected static function debug($string)
    {
        if(!DEBUG) return;
        $now = date('Y-m-d H:i:s');
        echo "{$now} >> DEBUG : {$string} \n";
    }
    
    public static function emerg()
    {
        $arguments = json_encode(func_get_args()); 
        $now = date('Y-m-d H:i:s');
        echo "{$now} >> EMERG : {$arguments} \n";
    }
    
    
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        self::debug(" ERROR : $errno, $errstr, $errfile, $errline ");
        return true;
    }
    
    
    public static function defaultSignHandler($signo)
    {
        // Must be public or else will throw a
        // fatal error: Call to protected method
        self::debug('Received signal: %s', $signo);

        switch ($signo) {
        case SIGTERM:
            // Handle shutdown tasks
            if (self::isInBackground()) {
                self::_die();
            } else {
                exit;
            }
            break;
        case SIGHUP:
            // Handle restart tasks
            self::debug('Received signal: restart');
            break;
        case SIGCHLD:
            // A child process has died
            self::debug('Received signal: child');
            while (pcntl_wait($status, WNOHANG OR WUNTRACED) > 0) {
                usleep(1000);
            }
            break;
        default:
            // Handle all other signals
            break;
        }
    }
}
