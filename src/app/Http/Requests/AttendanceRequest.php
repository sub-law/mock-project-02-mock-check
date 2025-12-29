<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in'  => ['required'],
            'clock_out' => ['required'],

            'break_start'   => ['array'],
            'break_start.*' => ['nullable', 'required_with:break_end.*'],

            'break_end'     => ['array'],
            'break_end.*'   => ['nullable', 'required_with:break_start.*'],

            'note' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            // 出勤・退勤の形式チェック
            if (!$this->isValidTime($this->clock_in)) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
                return;
            }
            if (!$this->isValidTime($this->clock_out)) {
                $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
                return;
            }

            $clockIn  = Carbon::createFromFormat('H:i', $this->clock_in);
            $clockOut = Carbon::createFromFormat('H:i', $this->clock_out);

            // 出勤 >= 退勤
            if ($clockIn->gte($clockOut)) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 休憩チェック
            $starts = $this->break_start ?? [];
            $ends   = $this->break_end ?? [];

            foreach ($starts as $i => $start) {

                $end = $ends[$i] ?? null;

                // 形式チェック（片側だけ入力されている場合もここで拾える）
                if ($start && !$this->isValidTime($start)) {
                    $validator->errors()->add("break_start.$i", '休憩時間が不適切な値です');
                    continue;
                }
                if ($end && !$this->isValidTime($end)) {
                    $validator->errors()->add("break_end.$i", '休憩時間が不適切な値です');
                    continue;
                }

                $startTime = $start ? Carbon::createFromFormat('H:i', $start) : null;
                $endTime   = $end   ? Carbon::createFromFormat('H:i', $end)   : null;

                // 両方入力されている場合のみチェック
                if ($startTime && $endTime) {

                    // 開始 < 出勤
                    if ($startTime->lt($clockIn)) {
                        $validator->errors()->add("break_start.$i", '休憩時間が不適切な値です');
                    }

                    // 開始 > 退勤
                    if ($startTime->gt($clockOut)) {
                        $validator->errors()->add("break_start.$i", '休憩時間が不適切な値です');
                    }

                    // 終了 > 退勤
                    if ($endTime->gt($clockOut)) {
                        $validator->errors()->add("break_end.$i", '休憩時間が不適切な値です');
                    }

                    // 終了 < 出勤
                    if ($endTime->lt($clockIn)) {
                        $validator->errors()->add("break_end.$i", '休憩時間が不適切な値です');
                    }

                    // 開始 >= 終了
                    if ($startTime->gte($endTime)) {
                        $validator->errors()->add("break_end.$i", '休憩時間が不適切な値です');
                    }
                }
            }
        });
    }

    /**
     * H:i 形式チェック（Laravel の date_format より柔軟）
     */
    private function isValidTime($value)
    {
        // 完全一致（例: 12:34）
        if (preg_match('/^\d{2}:\d{2}$/', $value)) {
            return true;
        }

        // 不完全な値（例: 12:）は false
        return false;
    }

    public function messages()
    {
        return [
            'clock_in.required'  => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required' => '出勤時間もしくは退勤時間が不適切な値です',

            'break_start.*.required_with' => '休憩開始時間を入力してください',
            'break_end.*.required_with'   => '休憩終了時間を入力してください',

            'note.required' => '備考を記入してください',
        ];
    }
}
