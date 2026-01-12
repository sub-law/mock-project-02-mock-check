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

            // 出勤・退勤の形式チェック（return しない）
            if (!$this->isValidTime($this->clock_in)) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }
            if (!$this->isValidTime($this->clock_out)) {
                $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // clock_in / clock_out が不正なら後続チェックはスキップ
            if ($validator->errors()->has('clock_in') || $validator->errors()->has('clock_out')) {
                return;
            }

            $clockIn  = Carbon::createFromFormat('H:i', $this->clock_in);
            $clockOut = Carbon::createFromFormat('H:i', $this->clock_out);

            // 出勤 >= 退勤
            if ($clockIn->gte($clockOut)) {
                $validator->errors()->add('clock_in', '出勤時間が不適切な値です');
            }

            // 休憩チェック
            $starts = $this->break_start ?? [];
            $ends   = $this->break_end ?? [];

            foreach ($starts as $i => $start) {

                $end = $ends[$i] ?? null;

                // 形式チェック
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

                // 開始 >= 終了 → 休憩として成立しないので後続チェック不要
                if ($startTime && $endTime) {

                    if ($startTime->gte($endTime)) {
                        $validator->errors()->add("break_end.$i", '休憩時間もしくは退勤時間が不適切な値です');
                        continue; 
                    }

                    if ($startTime->lt($clockIn) || $startTime->gt($clockOut)) {
                        $validator->errors()->add("break_start.$i", '休憩時間が不適切な値です');
                    }

                    if ($endTime->lt($clockIn) || $endTime->gt($clockOut)) {
                        $validator->errors()->add("break_end.$i", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }

            // ★ 休憩同士の重複チェック
            $breakRanges = [];

            foreach ($starts as $i => $start) {
                $end = $ends[$i] ?? null;

                if ($start && $end) {
                    $breakRanges[] = [
                        'index' => $i,
                        'start' => Carbon::createFromFormat('H:i', $start),
                        'end'   => Carbon::createFromFormat('H:i', $end),
                    ];
                }
            }

            for ($i = 0; $i < count($breakRanges); $i++) {
                for ($j = $i + 1; $j < count($breakRanges); $j++) {

                    $a = $breakRanges[$i];
                    $b = $breakRanges[$j];

                    // 重複判定：A.start < B.end && A.end > B.start
                    if ($a['start']->lt($b['end']) && $a['end']->gt($b['start'])) {

                        $validator->errors()->add("break_start.{$a['index']}", '休憩時間が他の休憩と重複しています');
                        $validator->errors()->add("break_end.{$a['index']}",   '休憩時間が他の休憩と重複しています');

                        $validator->errors()->add("break_start.{$b['index']}", '休憩時間が他の休憩と重複しています');
                        $validator->errors()->add("break_end.{$b['index']}",   '休憩時間が他の休憩と重複しています');
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
        return preg_match('/^\d{2}:\d{2}$/', $value);
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
