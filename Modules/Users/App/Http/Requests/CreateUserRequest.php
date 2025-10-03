<?php

namespace Modules\Users\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
  /**
   * Get the validation rules that apply to the request.
   */
  public function rules(): array
  {
    return [
      'name' => 'string|required|max:250',
      'username' => 'string|required|max:250',
      //'tps' => 'numeric|required',
      'mobile' => 'string|required',
      'email' => 'string|required',
      'address' => 'string|required',
      'password' => 'string|required',
      'user_group_id' => 'numeric|required',
      'sms_rate_id' => 'numeric|required'
    ];
  }

  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }
}
