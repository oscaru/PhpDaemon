<?php



require __DIR__.'/config.php';
require __DIR__.'/functions.php';
require DAEMON_ROOTDIR.'/signals.php';

daemonize();

 // setting a signal handler
pcntl_signal(SIGTERM, "sig_handler");
pcntl_signal(SIGHUP, "sig_handler");
pcntl_signal(SIGUSR1, "sig_handler");
//$signal->init();

while(true){
    pcntl_signal_dispatch();
    
};



