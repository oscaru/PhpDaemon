<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$logDir = __DIR__."/log/";
umask(0); // § 1
$pid = pcntl_fork(); // § 2

ini_set('error_log', $logDir.'/error.log'); // set log file

if ($pid < 0) {
    print('fork failed');
    exit (1); 
}



if ($pid > 0) { // the parent process
    echo "daemon process started\n";
    exit; // Exit
} 
// (pid = 0) child process
 
$sid = posix_setsid();// § 3
if ($sid < 0) exit (2);

chdir(__DIR__); // § 4


// Closes an open file descriptors system STDIN, STDOUT, STDERR
fclose(STDIN);   
fclose(STDOUT);
fclose(STDERR);

// redirect stdin to /dev/null
$STDIN = fopen('/dev/null', 'r'); 
// redirect stdout to a log file
$STDOUT = fopen($logDir.'/application.log', 'ab');
// redirect stderr to a log file
$STDERR = fopen($logDir.'/error.log', 'ab');

file_put_contents($logDir.'/debug.log', "DAEMON PID :". getmypid() ."\n", FILE_APPEND ); // § 6
echo getmypid();




while(true){}; // cycle start data
