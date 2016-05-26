<?php

function debug($string){
    if(!DEBUG) return;
    $now = date('Y-m-d H:i:s');
    echo "{$now} >> DEBUG : {$string} \n";
}


function daemonize (){

  



}


function sig_handler($signo)
{
 
 switch ($signo) {
  case SIGTERM:
    // actions SIGTERM signal processing
   
   unlink($pidfile); // destroy pid-file
     exit;
     break;
  case SIGHUP:
    // actions SIGHUP handling
   // reread the configuration file and initialize the data again
      debug ('Adios mundo cruel');
      die;
     break;
  default:
     debug ('Capturada señal '.$signo );
 }
}

