<?php

date_default_timezone_set('Europe/Madrid');

error_reporting(E_ALL);
ini_set('display_errors', 'On');

define ('DAEMON_LOGDIR','/tmp/phpDaemon' );
define ('DAEMON_OUT', DAEMON_LOGDIR.'/debug.log');
define ('DAEMON_ERR', DAEMON_LOGDIR.'/error.log');
define ('DAEMON_ROOTDIR' , __DIR__);


define ('DEBUG' , true);

define ('MIN_RESTART_SECONDS', 10);

