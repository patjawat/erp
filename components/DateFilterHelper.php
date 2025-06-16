<?php

namespace app\components;

class DateFilterHelper
{
    /**
     * คืนช่วงวันที่ตาม key ที่กำหนด
     * @param string $rangeKey เช่น today, this_week, last_week, this_month, last_month
     * @return array|null [start, end] หรือ null ถ้าไม่พบ
     */
    public static function getRange(string $rangeKey): ?array
    {
        switch ($rangeKey) {
            case 'today':
                return [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')];

            case 'yesterday':
                return [
                    date('Y-m-d 00:00:00', strtotime('-1 day')),
                    date('Y-m-d 23:59:59', strtotime('-1 day'))
                ];

            case 'this_week':
                $monday = date('Y-m-d 00:00:00', strtotime('monday this week'));
                $sunday = date('Y-m-d 23:59:59', strtotime('sunday this week'));
                return [$monday, $sunday];

            case 'last_week':
                $monday = date('Y-m-d 00:00:00', strtotime('monday last week'));
                $sunday = date('Y-m-d 23:59:59', strtotime('sunday last week'));
                return [$monday, $sunday];

            case 'this_month':
                return [
                    date('Y-m-01 00:00:00'),
                    date('Y-m-t 23:59:59'),
                ];

            case 'last_month':
                return [
                    date('Y-m-01 00:00:00', strtotime('first day of last month')),
                    date('Y-m-t 23:59:59', strtotime('last day of last month')),
                ];

            default:
                return null;
        }
    }

    /**
     * คืนรายการ dropdown สำหรับใช้ในฟอร์ม
     */
    public static function getDropdownItems(): array
    {
        return [
            '' => 'ทั้งหมด',
            'today' => 'วันนี้',
            'yesterday' => 'เมื่อวาน',
            'this_week' => 'อาทิตย์นี้',
            'last_week' => 'อาทิตย์ที่แล้ว',
            'this_month' => 'เดือนนี้',
            'last_month' => 'เดือนที่แล้ว',
        ];
    }
}
