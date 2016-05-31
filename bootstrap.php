<?php



require __DIR__.'/config.php';
require __DIR__.'/autoload.php';
require __DIR__.'/functions.php';


\Core\Daemon::checkins();
\Core\Daemon::start();

while(true){
    pcntl_signal_dispatch();
}

