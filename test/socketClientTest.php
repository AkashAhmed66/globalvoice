<?php


$host = '127.0.0.1';
$port = 4000;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
  echo "Socket creation failed: " . socket_strerror(socket_last_error()) . PHP_EOL;
  exit;
}

if (!socket_connect($socket, $host, $port)) {
  echo "Socket connect failed: " . socket_strerror(socket_last_error($socket)) . PHP_EOL;
  socket_close($socket);
  exit;
}

// Change this string to test different messages
$testMessage = "RETRY|01734183130\n";
//$testMessage = "SMS_STATUS|123|6|982\n";
//$testMessage = "INCOMING|01734183130|12345|Hello!|1|1|1|111|0\n";

socket_write($socket, $testMessage, strlen($testMessage));
socket_close($socket);

echo "Message sent: $testMessage\n";
