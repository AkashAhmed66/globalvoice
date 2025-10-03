<?php

namespace App\Trait;

trait ActionButtonTrait
{
  public function statusButton($status, $id): string
  {
    if (!empty($status) && !empty($id)) {
      $st = (ucfirst(strtolower($status)) == 'Active') ? 'primary' : 'danger';
      return '<span class="cursor-pointer text-center status_change_id_' . $id . ' ' . $st . '" id="status" data-id="' . $id . '">' . ucfirst(
          strtolower($status)
        ) . '</span>';
    } else {
      return '';
    }
  }

  public function viewButton($route, $id): string
  {
    if (!empty($id)) {
      return '<a href="' . route($route, $id) . '" class="primary mr-1 ajax-form"><i class="fa fa-eye"></i></a>';
    } else {
      return '';
    }
  }

  public function editButton($route, $id): string
  {
    if (!empty($id)) {
         return '<button class="btn btn-sm btn-icon edit-record btn-text-secondary rounded-pill waves-effect" data-id="'.$id.'" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddRecord"><i class="ri-edit-box-line ri-20px"></i></button>';
//      return '<a href="' . route($route, $id) . '" class="warning ajax-form"><i class="fa fa-pencil"></i></a>';
    } else {
      return '';
    }
  }
  public function loginAsButton($route, $id): string
  {
    if (!empty($id)) {
      return '<a href="' . route($route, $id) . '" class="btn btn-sm btn-icon sign-in-record btn-text-secondary rounded-pill waves-effect" data-id="'.$id.'"><i class="ri-login-box-line ri-20px"></i></a>';
//      return '<a href="' . route($route, $id) . '" class="warning ajax-form"><i class="fa fa-pencil"></i></a>';
    } else {
      return '';
    }
  }

  public function deleteButton($route, $id): string
  {
    if (!empty($id)) {
      //return '<button class="btn btn-sm btn-icon delete-record btn-text-secondary rounded-pill waves-effect" data-id="'.$id.'"><i class="ri-delete-bin-7-line ri-20px"></i></button>';
      //return '<a href="' . route($route, $id) . '" class="danger ajax-form"><i class="fa fa-trash"></i></a>';
      return '<button class="btn btn-sm btn-icon delete-record btn-text-secondary rounded-pill waves-effect" data-id="' . $id . '" data-route="' . route($route, $id) . '"><i class="ri-delete-bin-7-line ri-20px"></i></button>';

} else {
      return '';
    }
  }

  public function addNewButton($buttonName): string
  {
    return "text: '<i class=\"ri-add-line ri-16px me-0 me-sm-2 align-baseline\"></i><span class=\"d-none d-sm-inline-block\">" . $buttonName . "</span>',
        className: 'add-new btn btn-primary waves-effect waves-light',
        attr: {
          'data-bs-toggle': 'offcanvas',
          'data-bs-target': '#offcanvasAddUser'
        }";
  }



  public function groupButton($route, $id): string
  {
    if (!empty($id)) {
         return '<button class="btn btn-sm btn-icon edit-record btn-text-secondary rounded-pill waves-effect" data-id="3" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddRecord"><i class="ri-edit-box-line ri-20px"></i></button> <button class="btn btn-sm btn-icon edit-record btn-text-secondary rounded-pill waves-effect" data-id="3" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddRecord"><i class="ri-delete-box-line ri-20px"></i></button>';
//      return '<a href="' . route($route, $id) . '" class="warning ajax-form"><i class="fa fa-pencil"></i></a>';
    } else {
      return '';
    }
  }
}
