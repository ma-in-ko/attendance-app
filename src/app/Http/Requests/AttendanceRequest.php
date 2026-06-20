<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
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
            'requested_clock_in' => ['required'],
            'requested_clock_out' => [
                'required',
                'after:requested_clock_in',
                ],
            'reason' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return[
            'requested_clock_in.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'reason.required' => '備考を入力してください',   
        ];
    }
}
