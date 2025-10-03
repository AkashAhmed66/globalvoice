<?php

namespace App\Http\Controllers;

abstract class Controller
{
  public function ajaxDatatable(): bool
  {
    return request()->ajax() && request()->exists('draw');
  }
}
