<?php

//killall -s SIGUSR2 php

require __DIR__.'/config.php';
require __DIR__.'/autoload.php';
require __DIR__.'/functions.php';



\Core\Daemon::start();

