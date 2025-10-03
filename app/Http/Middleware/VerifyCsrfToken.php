<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Support\Facades\Log;

class VerifyCsrfToken extends Middleware
{
  /**
   * The URIs that should be excluded from CSRF verification.
   *
   * @var array<int, string>
   */
  protected $except = [
    // These routes are now properly handled by API middleware
    // No CSRF exclusions needed as they're in api.php
  ];

  // This is only for debugging to see what paths are hitting CSRF middleware
  public function handle($request, Closure $next)
  {
    Log::info('CSRF path: ' . $request->path());
    return parent::handle($request, $next);
  }
}
