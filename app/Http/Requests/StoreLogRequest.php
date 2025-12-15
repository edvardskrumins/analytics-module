<?php

namespace App\Http\Requests;

use App\Models\ContentLog;
use Illuminate\Foundation\Http\FormRequest;

class StoreLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content_id' => 'nullable|integer',
            'action' => ['required', 'string', 'in:'.implode(',', ContentLog::ACTIONS)],
        ];
    }
}

