<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use App\Console\Kernel as AppConsoleKernel;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    api: __DIR__ . '/../routes/api.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    // Add your global middleware here
    // $middleware->web(SomeMiddleware::class);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    // Configure your exception handling here
  })
  ->withBindings([
    ConsoleKernelContract::class => AppConsoleKernel::class,
  ])
  ->create();
