<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SocketService
{
  private $socket;
  private $host;
  private $port;
  private $connected = false;

  public function __construct()
  {
    $this->host = env('SOCKET_SERVER_HOST', '127.0.0.1');
    $this->port = env('SOCKET_SERVER_PORT', 4000);

    Log::channel('sms')->info("SocketService initialized with host:");
    if (getenv('APP_TYPE') != 'Aggregator'){
      Log::channel('sms')->info("Socket connecting");
      $this->connect();
    }
  }

  private function connect()
  {
    $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($this->socket === false) {
      Log::channel('sms')->error("Socket create failed: ");
      $this->connected = false;
    }

    if (socket_connect($this->socket, $this->host, $this->port) === false) {
      Log::channel('sms')->error("Socket connect failed: ");
      $this->connected = false;
    }else{
      $this->connected = true;
      Log::channel('sms')->info("Socket connected successfully");
    }
  }

  public function sendData($data)
  {
   Log::channel('sms')->info("Sending data to socket: ");
    if (!$this->connected) {
      $this->connect(); // reconnect if disconnected
    }

    Log::channel('sms')->info("Socket connected: $this->connected");

    $sent = socket_write($this->socket, $data, strlen($data));
    if ($sent === false) {
      Log::channel('sms')->error("Socket write failed:");
      $this->connected = false; // mark as disconnected
      sleep(1);
      $this->sendData($data);
    }
  }

  public function disconnect()
  {
    if ($this->connected && $this->socket) {
      socket_close($this->socket);
      $this->connected = false;
    }
  }

  public function __destruct()
  {
    $this->disconnect(); // ensure socket closes when object is destroyed
  }
}
