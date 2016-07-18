<?php

$ip   = "127.0.0.1";
$port =  8080;


$commonProtocol = getprotobyname("tcp");
$socket = socket_create(AF_INET, SOCK_STREAM, $commonProtocol);
socket_bind($socket, $ip, $port);
socket_listen($socket);


// Initialize the buffer
$buffer = "NO DATA";

while(true) {
    // Accept any connections coming in on this socket
    $connection = socket_accept($socket);
    printf("Socket connected\r\n");
    
    // Check to see if there is anything in the buffer

    if($buffer != ""){
        printf("Something is in the buffer...sending data...\n");
        socket_write($connection, $buffer . "\r\n");
        printf("Wrote to socket \n");
    } else {
        printf("No Data in the buffer\r\n");
    }
    // Get the input
    while($data = socket_read($connection, 1024, PHP_NORMAL_READ)){
        $buffer = $data;
        socket_write($connection, "Information Received\r\n");
        printf("Buffer: " . $buffer . "\r\n");
    }
    
    socket_close($connection);
    printf("Closed the socket\r\n\r\n");

}