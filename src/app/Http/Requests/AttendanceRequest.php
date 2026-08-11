<?php

namespace App\Http\Requests;

use Carbon\Carbon;
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

            'breaks.*.break_start' => [
                'nullable',
            ],

            'breaks.*.break_end' => [
                'nullable',
            ],

            'reason' => ['required', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $clockIn = $this->requested_clock_in;
            $clockOut = $this->requested_clock_out;

            if (! $clockIn || ! $clockOut) {
                return;
            }

            foreach ($this->breaks ?? [] as $index => $break) {

                if (
                    empty($break['break_start']) &&
                    empty($break['break_end'])
                ) {
                    continue;
                }

                // 休憩開始が勤務開始前
                if (
                    ! empty($break['break_start']) &&
                    Carbon::parse($break['break_start'])
                        ->lt(Carbon::parse($clockIn))
                ) {
                    $validator->errors()->add(
                        "breaks.$index.break_start",
                        '休憩時間が不適切な値です'
                    );
                }

                // 休憩開始が勤務終了後
                if (
                    ! empty($break['break_start']) &&
                    Carbon::parse($break['break_start'])
                        ->gt(Carbon::parse($clockOut))
                ) {
                    $validator->errors()->add(
                        "breaks.$index.break_start",
                        '休憩時間が不適切な値です'
                    );
                }

                // 休憩終了が勤務終了後
                if (
                    ! empty($break['break_end']) &&
                    Carbon::parse($break['break_end'])
                        ->gt(Carbon::parse($clockOut))
                ) {
                    $validator->errors()->add(
                        "breaks.$index.break_end",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }

                // 休憩終了が休憩開始前
                if (
                    ! empty($break['break_start']) &&
                    ! empty($break['break_end']) &&
                    Carbon::parse($break['break_end'])
                        ->lt(Carbon::parse($break['break_start']))
                ) {
                    $validator->errors()->add(
                        "breaks.$index.break_end",
                        '休憩時間が不適切な値です'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'requested_clock_in.required' => '出勤時間を入力してください',
            'requested_clock_out.required' => '退勤時間を入力してください',
            'requested_clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'reason.required' => '備考を記入してください',
        ];
    }
}
