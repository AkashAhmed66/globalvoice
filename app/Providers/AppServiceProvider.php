<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;
use GuzzleHttp\Client;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    $this->app->singleton(Client::class, function () {
      return new Client([
        'base_uri' => 'https://smsc.metro.net.bd',
//        'base_uri' => 'http://localhost:8000', // Change to your local or production base URI
        'timeout' => 5,
        'headers' => [
          'Connection' => 'keep-alive',
          'Accept' => 'application/json',
        ],
        'http_errors' => false,
        'verify' => false, // if SSL issues
        'defaults' => [
          'stream' => false,
          'allow_redirects' => false,
        ],
      ]);
    });
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    Vite::useStyleTagAttributes(function (?string $src, string $url, ?array $chunk, ?array $manifest) {
      if ($src !== null) {
        return [
          'class' => preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?core)-?.*/i", $src) ? 'template-customizer-core-css' :
                    (preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?theme)-?.*/i", $src) ? 'template-customizer-theme-css' : '')
        ];
      }
      return [];
    });
  }
}
