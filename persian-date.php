<?php

class JalaliDate {
    private $g_days_in_month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    private $j_days_in_month = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
    private $week_days = ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه'];
    private $months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    public function getCurrentDate() {
        date_default_timezone_set('Asia/Tehran');
        // دریافت تاریخ میلادی
        $timestamp = time();
        $gregorianDate = date('Y-n-j', $timestamp);
        list($g_y, $g_m, $g_d) = explode('-', $gregorianDate);
        // تبدیل به شمسی
        list($j_y, $j_m, $j_d) = $this->toJalali($g_y, $g_m, $g_d);
        // محاسبه روز هفته
        $dayOfWeek = $this->getDayOfWeek($timestamp);
        return [
            'day_name' => $this->week_days[$dayOfWeek],
            'day' => $j_d,
            'month_name' => $this->months[$j_m - 1],
            'month' => $j_m,
            'year' => $j_y,
            'full_date' => $this->week_days[$dayOfWeek] . ' ' . $j_d . ' ' . $this->months[$j_m - 1] . ' ' . $j_y,
            'time' => date('H:i:s', $timestamp)
        ];
    }
    private function getDayOfWeek($timestamp) {
        return (date('w', $timestamp) + 1) % 7;
    }
    private function toJalali($g_y, $g_m, $g_d) {
        $gy = $g_y - 1600;
        $gm = $g_m - 1;
        $gd = $g_d - 1;
        $g_day_no = 365 * $gy + floor(($gy + 3) / 4) - floor(($gy + 99) / 100) + floor(($gy + 399) / 400);
        for ($i = 0; $i < $gm; ++$i) {
            $g_day_no += $this->g_days_in_month[$i];
        }
        if ($gm > 1 && (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0))) {
            $g_day_no++;
        }
        $g_day_no += $gd;
        $j_day_no = $g_day_no - 79;
        $j_np = floor($j_day_no / 12053);
        $j_day_no %= 12053;
        $jy = 979 + 33 * $j_np + 4 * floor($j_day_no / 1461);
        $j_day_no %= 1461;
        if ($j_day_no >= 366) {
            $jy += floor(($j_day_no - 1) / 365);
            $j_day_no = ($j_day_no - 1) % 365;
        }
        for ($i = 0; $i < 11 && $j_day_no >= $this->j_days_in_month[$i]; ++$i) {
            $j_day_no -= $this->j_days_in_month[$i];
        }
        // تنظیم اسفند در سال کبیسه
        if ($i == 11 && $j_day_no >= 29) {
            $this->j_days_in_month[11] = $this->isLeapJalaliYear($jy) ? 30 : 29;
        }
        return [$jy, $i + 1, $j_day_no + 1];
    }
    private function isLeapJalaliYear($jy) {
        $breaks = [-61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210, 1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178];
        $bl = count($breaks);
        $jp = $breaks[0];
        for ($i = 1; $i < $bl; $i++) {
            $jm = $breaks[$i];
            $jump = $jm - $jp;
            if ($jy < $jm) {
                $break = $i;
                $break_y = $jy - $jp;
                break;
            }
            $jp = $jm;
        }
        return ($break_y % 33 == 4 || $break_y % 33 == 29);
    }
}

// نحوه استفاده
$date = new JalaliDate();
$today = $date->getCurrentDate();
print_r($today);
