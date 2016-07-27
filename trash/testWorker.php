<?php

require __DIR__."/../Core/Task.php";
 
function proceso( $resultado,$tiempo) {
    usleep($tiempo);
    exit($resultado);
}
 
$thread1 = new \Core\Task('proceso');
$thread2 = new \Core\Task('proceso');
$thread3 = new \Core\Task('proceso');
 
$thread1->start(3, 10);
$thread2->start(2, 40);
$thread3->start(1, 30);
 
while ($thread1->isAlive() || $thread2->isAlive() || $thread3->isAlive());
 
echo "Resultado del hilo 1 (debe ser 3): " . $thread1->getExitCode() . "\n";
echo "Resultado del hilo 2 (debe ser 2): " . $thread2->getExitCode() . "\n";
echo "Resultado del hilo 3 (debe ser 1): " . $thread3->getExitCode() . "\n";

