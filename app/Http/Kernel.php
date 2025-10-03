<?php


namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
  protected $middlewareGroups = [
    'web' => [
      // includes CSRF
      \App\Http\Middleware\VerifyCsrfToken::class,
    ],

    'api' => [
      'throttle:api',
      \Illuminate\Routing\Middleware\SubstituteBindings::class,
      // DOES NOT include CSRF by default!
    ],
  ];
}
