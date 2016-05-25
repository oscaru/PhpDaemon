<?php

/* A tick is an event that occurs for every N low-level tickable statements 
 * executed by the parser within the declare block.
 * A low ticks value can degrade performance, 
 * while a high ticks value can mean that you have to wait 
 * for your signal handler to be called.
 * 
 * pcntl_signal_dispatch forced PHP to process any pending signals immediatly
 */
declare(ticks = 5);

class Signals {

    /**
     * Events can be attached to each state using the on() method
     * @var integer
     */
    const ON_ERROR          = 0;    // error() or fatal_error() is called
    const ON_SIGNAL         = 1;    // the daemon has received a signal
    const ON_INIT           = 2;    // the library has completed initialization, your setup() method is about to be called. Note: Not Available to Worker code.
    const ON_PREEXECUTE     = 3;    // inside the event loop, right before your execute() method
    const ON_POSTEXECUTE    = 4;    // and right after
    const ON_FORK           = 5;    // in a background process right after it has been forked from the daemon
    const ON_PIDCHANGE      = 6;    // whenever the pid changes -- in a background process for example
    const ON_IDLE           = 7;    // called when there is idle time at the end of a loop_interval, or at the idle_probability when loop_interval isn't used
    const ON_REAP           = 8;    // notification from the OS that a child process of this application has exited
    const ON_SHUTDOWN       = 10;   // called at the top of the destructor
    
    
    public function __construct() {
        debug('Construct signals');
    }
    
    public function init(){
        debug ('Signal Init');
        $signals = array (
            // Handled by Core_Daemon:
            SIGTERM, SIGINT, SIGUSR1, SIGUSR2, SIGHUP, SIGCHLD,

            // Ignored by Core_Daemon -- register callback ON_SIGNAL to listen for them.
            // Some of these are duplicated/aliased, listed here for completeness
            //SIGUSR2, SIGCONT, SIGQUIT, SIGILL, SIGTRAP, SIGABRT, SIGIOT, SIGBUS, SIGFPE, SIGSEGV, SIGPIPE, SIGALRM,
            //SIGCONT, SIGTSTP, SIGTTIN, SIGTTOU, SIGURG, SIGXCPU, SIGXFSZ, SIGVTALRM, SIGPROF,
            //SIGWINCH, SIGIO, SIGSYS, SIGBABY
        );

        if (defined('SIGPOLL'))     $signals[] = SIGPOLL;
        if (defined('SIGPWR'))      $signals[] = SIGPWR;
        if (defined('SIGSTKFLT'))   $signals[] = SIGSTKFLT;
        
        //handle de señales
        foreach(array_unique($signals) as $signal) {
            pcntl_signal($signal, array($this, 'signal'));
        }
       debug ('Signal start');
    }
    
    
     /**
     * When a signal is sent to the process it'll be handled here
     * @param integer $signal
     * @return void
     */
    public function signal($signal) {
        //kill -s SIGNAL PID
        debug ('Recibida señal '.$signal);
        switch ($signal)  {
            case SIGUSR1:
                // kill -10 [pid]
                //$this->dump();
                debug ( ' SEÑAL SIGUSR1 ') ;
                break;
            case SIGUSR2:
                // kill -10 [pid]
                //$this->dump();
                debug ( ' SEÑAL SIGUSR2 ') ;
                break;
            case SIGHUP:
                // kill -1 [pid]
                //$this->restart();
                debug ( ' SEÑAL SIGHUP ') ;
                break;
            case SIGINT:
                debug ( ' SEÑAL SIGINT ') ;
                break;
            case SIGTERM:
                debug ( ' SEÑAL SIGTERM ') ;
                //$this->set('shutdown', true);
                
                break;
        }

        //pcntl_signal_dispatch forced PHP to process any pending signals immediatly.
       
    }

}