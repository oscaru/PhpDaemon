<?php

//killall -s SIGUSR2 php



ini_set("log_errors", 1);
ini_set("error_log", "/tmp/php-error.log");




require __DIR__.'/config.php';
require __DIR__.'/autoload.php';
require __DIR__.'/functions.php';
require __DIR__.'/Core/ezsql/ez_sql_core.php';
require __DIR__.'/Core/ezsql/ez_sql_mysqli.php';


/*
 * Parametros CLI
 */

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
        


/**
 *  SETEAMOS EL CONFIG
 */

\Core\Config::setArray($config);




date_default_timezone_set(\Core\Config::get('timezone'));

/**
 *  Si opcion --debug 
 */

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



$dbuser = \Core\Config::get('dbuser');
$dbpassword = \Core\Config::get('dbpassword');
$dbname = \Core\Config::get('dbname');
$dbhost = \Core\Config::get('dbhost');

$db = new ezSQL_mysqli($dbuser, $dbpassword, $dbname, $dbhost);
$db->query("SET NAMES 'utf8'");
$db->query("SET time_zone = '+00:00';");




$taskManager = new \Core\TaskManager($db);

$daemon = \Core\Daemon::getInstance();
$daemon->setTaskManager($taskManager);

$daemon->start();

