<?php

namespace App\Services;

class SocketServiceBackup
{
  protected $socket;

  public function sendData($data)
  {
    try {
      $host = env('SOCKET_SERVER_HOST', '127.0.0.1');
      $port = env('SOCKET_SERVER_PORT', 4000);
      $socket = socket_create(AF_INET, SOCK_STREAM, 0);
      if ($socket === false) {
        throw new \Exception("Socket create failed: " . socket_strerror(socket_last_error()));
      }

      // Connect to server
      if (socket_connect($socket, $host, $port) === false) {
        throw new \Exception("Socket connect failed: " . socket_strerror(socket_last_error($socket)));
      }
      socket_write($socket, $data, strlen($data));
      socket_close($socket);
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }
}
