<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in'  => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i'],

            'break_start' => ['array'],
            'break_start.*' => ['nullable', 'date_format:H:i'],

            'break_end' => ['array'],
            'break_end.*' => ['nullable', 'date_format:H:i'],

            'note' => ['required', 'string', 'max:255'], // 備考必須
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $clockIn  = $this->clock_in;
            $clockOut = $this->clock_out;

            // 出勤 > 退勤
            if ($clockIn && $clockOut && $clockIn >= $clockOut) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $starts = $this->break_start ?? [];
            $ends   = $this->break_end ?? [];

            foreach ($starts as $i => $start) {
                $end = $ends[$i] ?? null;

                // 休憩開始が出勤より前 or 退勤より後
                if ($start) {
                    if ($clockIn && $start < $clockIn) {
                        $validator->errors()->add("break_start.$i", '休憩時間が不適切な値です');
                    }
                    if ($clockOut && $start > $clockOut) {
                        $validator->errors()->add("break_start.$i", '休憩時間が不適切な値です');
                    }
                }

                // 休憩終了が退勤より後
                if ($end) {
                    if ($clockOut && $end > $clockOut) {
                        $validator->errors()->add("break_end.$i", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                    // 休憩終了が出勤より前
                    if ($clockIn && $end < $clockIn) {
                        $validator->errors()->add("break_end.$i", '休憩時間が不適切な値です');
                    }
                }

                // 休憩開始 > 休憩終了
                if ($start && $end && $start >= $end) {
                    $validator->errors()->add("break_end.$i", '休憩時間が不適切な値です');
                }
            }
        });
    }

    public function messages()
    {
        return ['note.required' => '備考を記入してください',];
    }
}
