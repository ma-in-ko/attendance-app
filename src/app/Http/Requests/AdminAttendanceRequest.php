<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AdminAttendanceRequest extends FormRequest
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
            'clock_in' => ['required'],

            'clock_out' => [
                'required',
                'after:clock_in',
            ],

            'breaks.*.break_start' => [
                'nullable',
            ],

            'breaks.*.break_end' => [
                'nullable',
            ],

            'note' => [
                'required',
                'string',
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $clockIn = $this->clock_in;
            $clockOut = $this->clock_out;

            if (!$clockIn || !$clockOut) {
                return;
            }

            foreach ($this->breaks ?? [] as $index => $break) {

                if (
                    empty($break['break_start']) &&
                    empty($break['break_end'])
                ) {
                    continue;
                }

                //休憩開始 < 出勤
                if (
                    !empty($break['break_start']) &&
                    Carbon::parse($break['break_start'])
                    ->lt(Carbon::parse($clockIn))
                ) {
                    $validator->errors()->add(
                        "breaks.$index.break_start",
                        '休憩時間が不適切な値です'
                    );
                }


                //休憩開始 > 退勤
                if (
                    !empty($break['break_start']) &&
                    Carbon::parse($break['break_start'])
                    ->gt(Carbon::parse($clockOut))
                ) {
                    $validator->errors()->add(
                        "breaks.$index.break_start",
                        '休憩時間が不適切な値です'
                    );
                }

                //休憩終了 > 退勤
                if (
                    !empty($break['break_end']) &&
                    Carbon::parse($break['break_end'])
                    ->gt(Carbon::parse($clockOut))
                ) {
                    $validator->errors()->add(
                        "breaks.$index.break_end",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }

                //休憩終了 < 休憩開始
                if (
                    !empty($break['break_start']) &&
                    !empty($break['break_end']) &&
                    Carbon::parse($break['break_end'])
                    ->lt(Carbon::parse($break['break_start']))
                ) {
                    $validator->errors()->add(
                        "breaks.$index.break_end",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [

            'clock_in.required'
            => '出勤時間を入力してください',

            'clock_out.required'
            => '退勤時間を入力してください',

            'clock_out.after'
            => '出勤時間もしくは退勤時間が不適切な値です',

            'note.required'
            => '備考を記入してください',
        ];
    }
}
