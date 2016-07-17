<?php

//killall -s SIGUSR2 php

require __DIR__.'/config.php';
require __DIR__.'/autoload.php';
require __DIR__.'/functions.php';

$opts = getopt('hHiI:o:dp:', array('install', 'recoverworkers', 'debug', 'verbose','config:'));

if (isset($opts['H']) || isset($opts['h'])){
    //show_help();
    
}
    

if (isset($opts['i'])){
    //show_install_instructions();
   
}
    
 
if (isset($opts['debug'])) {
    $debug = true;
}else {
    $debug = false;
}



if (isset($opts['config'])) {
    require  $opts['config'];
}
        

\Core\Config::setArray($config);


date_default_timezone_set(\Core\Config::get('timezone'));



if ($debug){
    ini_set('display_errors', \Core\Config::get('display_errors'));
    error_reporting(E_ALL);
}

define ('DEBUG' , $debug);


define ('DAEMON_LOGDIR', \Core\Config::get('log_dir') );
define ('DAEMON_OUT', DAEMON_LOGDIR.'/debug.log');
define ('DAEMON_ERR', DAEMON_LOGDIR.'/error.log');
define ('DAEMON_ROOTDIR' , __DIR__);


define ('MIN_RESTART_SECONDS', 10);



\Core\Daemon::getInstance()->start();

