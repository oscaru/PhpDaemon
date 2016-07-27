<?php

namespace Core ;

class SocketServer
{
    protected $socket;
    protected $clients = [];
    protected $changed;
    
    protected $taskManager = NULL;
   
    function __construct($host = '127.0.0.1', $port = 9000)
    {
        set_time_limit(0);
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

        //bind socket to specified host
        socket_bind($socket, 0, $port);
        //listen to port
        socket_listen($socket);
        $this->socket = $socket;
    }
   
    public function setTaskManager(TaskManager $taskManager){
        $this->taskManager = $taskManager;
    }
    
    function __destruct()
    {
        foreach($this->clients as $client) {
            socket_close($client);
        }
        socket_close($this->socket);
    }
   
    function SockectLoop()
    {
            $this->waitForChange();
            $this->checkNewClients();
            $this->checkMessageRecieved();
            $this->checkDisconnect();
    }
   
    function checkDisconnect()
    {
        foreach ($this->changed as $changed_socket) {
            $buf = socket_read($changed_socket, 1024, PHP_NORMAL_READ);
            if ($buf !== false) { // check disconnected client
                continue;
            }
            // remove client for $clients array
            $found_socket = array_search($changed_socket, $this->clients);
            socket_getpeername($changed_socket, $ip);
            unset($this->clients[$found_socket]);
            $response = 'client ' . $ip . ' has disconnected';
            $this->sendMessage($response,$this->clients);
        }
    }
   
    function checkMessageRecieved()
    {
        foreach ($this->changed as $key => $socket) {
            $buffer = null;
            if(socket_recv($socket, $buffer, 1024, 0) >= 1) {
                
                if(FALSE && $this->taskManager){
                    $response = $this->taskManager->socketRequest($buffer);
                    $this->sendMessage($response, array($socket));
                }else{
                    $this->sendMessage(trim($buffer) . PHP_EOL, $this->getOtherClients($socket));
                }
                unset($this->changed[$key]);
                break;
            }
        }
    }
   
    function waitForChange()
    {
        //reset changed
        $this->changed = array_merge([$this->socket], $this->clients);
        //variable call time pass by reference req of socket_select
        $null = null;
        //this next part is blocking so that we dont run away with cpu
        //$this->changed sera modifiaca para indicar lo sockets que cambiaran
        socket_select($this->changed, $null, $null, null);
    }
   
    function checkNewClients()
    {
        //si socket esta en $this->changed hay una nueva conexion
        if (!in_array($this->socket, $this->changed)) {
            return; //no new clients
        }
        $socket_new = socket_accept($this->socket); //accept new socket
        //$first_line = socket_read($socket_new, 1024);
        $this->sendMessage('a new client has connected' . PHP_EOL,$this->clients);
        //$this->sendMessage('the new client says ' . trim($first_line) . PHP_EOL);
        $this->clients[] = $socket_new;
        unset($this->changed[0]);
    }
   
   
    function sendMessage($msg,$destinatarios = array())
    {
        if(empty($destinatarios)) return;
        
        error_log( $msg );
        foreach($destinatarios as $client)
        {
            socket_write($client,$msg,strlen($msg));
        }
        return true;
    }
    
    protected function getOtherClients($actual){
        $others = array();
        foreach ($this->clients as $client){
            if($client == $actual) continue;
            $others[] = $client;
        }
        return $others;
    }
}

