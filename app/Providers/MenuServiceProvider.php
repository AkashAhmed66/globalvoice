<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Routing\Route;

use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    View::composer('*', function ($view) {
      $userGroupId = Auth::user();
      if ($userGroupId && $userGroupId->user_group_id == 4) {
        $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenuForCustomer.json'));
      } else {
        $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
      }
      $verticalMenuData = json_decode($verticalMenuJson);
      $horizontalMenuJson = file_get_contents(base_path('resources/menu/horizontalMenu.json'));
      $horizontalMenuData = json_decode($horizontalMenuJson);

      // Share all menuData to all the views
      $this->app->make('view')->share('menuData', [$verticalMenuData, $horizontalMenuData]);
    });
  }
}
