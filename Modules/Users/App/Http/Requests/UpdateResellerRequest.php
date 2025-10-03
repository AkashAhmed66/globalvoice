<?php

namespace Modules\Users\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResellerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
			'reseller_name'=>'string|required|max:250',
			'tps'=>'numeric|required',
			'phone'=>'string|required',
            'email'=>'string|required',
            'address'=>'string|required',
            'thana'=>'string|required',
            'district'=>'string|required',
            'sms_rate_id'=>'numeric|required',
            'url'=>'string|required',
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
