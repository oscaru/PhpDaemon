<?php

function debug($string){
    if(!DEBUG) return;
    $now = date('Y-m-d H:i:s');
    echo "{$now} >> DEBUG : {$string} \n";
}


function daemonize (){

    /*
     * 1 - Resetting the file mode creation mask to 0 function umask(), 
     * to mask some bits of access rights from the starting process.
     */
    umask(0); // § 1

    /*
     * 2 - Cause fork() and finish the parent process. 
     * This is done so that if the process was launched as a group, 
     * the shell believes that the group finished at the same time, 
     * the child inherits the process group ID of the parent and gets its own process ID. 
     * This ensures that it will not become process group leader.
     */
    $pid = pcntl_fork(); // § 2

    if ($pid < 0) {
        print('fork failed');
        exit (1); 
    }

    if ($pid > 0) {// the parent process
        echo "daemon process started\n";
        exit; // Exit
    } 
    // (pid = 0) child process

    $sid = posix_setsid();// § 3
    if ($sid < 0) exit (2);

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

    debug  ('Daemon pid : '. getmypid());


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
   init_data();// reread the configuration file and initialize the data again
     break;
  default:
     echo ('Capturada señal '.$signo );
 }
}

