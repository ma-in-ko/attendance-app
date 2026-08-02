<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class IndexAttendanceRecordRequest extends FormRequest
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
            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'date' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'month' => [
                'nullable',
                'date_format:Y-m',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.integer' => 'ユーザーIDは整数で指定してください。',
            'user_id.exists' => '指定されたユーザーIDは存在しません。',

            'date.date_format' => '日付は YYYY-MM-DD 形式で指定してください。',

            'month.date_format' => '年月は YYYY-MM 形式で指定してください。',

            'per_page.integer' => 'per_page は整数で指定してください。',
            'per_page.min' => 'per_page は1以上で指定してください。',
            'per_page.max' => 'per_page は100以下で指定してください。',
        ];
    }
}
