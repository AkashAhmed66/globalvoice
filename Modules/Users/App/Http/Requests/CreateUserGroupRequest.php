<?php

namespace Modules\Users\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserGroupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
			'title'=>'string|required|max:250',
			'comment'=>'string|required',
			'status'=>'string|required',
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
