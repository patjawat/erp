<?php

namespace app\modules\hr\components;

/**
 * ทะเบียนชนิดเอกสารสำหรับการเบิกค่าใช้จ่ายในการเดินทางไปราชการ
 *
 * ระยะแรกเก็บ metadata ไว้ที่เดียวเพื่อให้หน้าเลือกเอกสารและหน้าตั้งค่าใช้
 * รายการเดียวกัน เมื่อระบบแม่แบบพร้อมสามารถย้าย body/version ไปฐานข้อมูล
 * โดยคง code เหล่านี้เป็นรหัสอ้างอิงถาวรได้
 */
final class DevelopmentDocumentCatalog
{
    public const STATUS_SOURCE_READY = 'source_ready';
    public const STATUS_PLANNED = 'planned';

    public static function all(): array
    {
        return [
            [
                'code' => 'travel_expense_8708_part_1',
                'name' => 'แบบ 8708 ส่วนที่ 1',
                'description' => 'ใบเบิกค่าใช้จ่ายในการเดินทางไปราชการ',
                'source_format' => 'PDF แบบกรอกข้อมูลได้',
                'orientation' => 'แนวตั้ง',
                'pages' => 2,
                'status' => self::STATUS_SOURCE_READY,
            ],
            [
                'code' => 'travel_expense_8708_part_2',
                'name' => 'แบบ 8708 ส่วนที่ 2',
                'description' => 'หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางเป็นคณะ',
                'source_format' => 'PDF แบบกรอกข้อมูลได้',
                'orientation' => 'แนวนอน',
                'pages' => 1,
                'status' => self::STATUS_SOURCE_READY,
            ],
            [
                'code' => 'travel_expense_bk_111',
                'name' => 'แบบ บก.111',
                'description' => 'ใบรับรองแทนใบเสร็จรับเงิน',
                'source_format' => 'Microsoft Word รุ่นเก่า',
                'orientation' => 'แนวตั้ง',
                'pages' => 1,
                'status' => self::STATUS_SOURCE_READY,
            ],
            [
                'code' => 'travel_expense_payment_approval',
                'name' => 'บันทึกขออนุมัติจ่าย',
                'description' => 'บันทึกข้อความเสนอขออนุมัติเบิกจ่ายตามรูปแบบของงานการเงิน',
                'source_format' => null,
                'orientation' => null,
                'pages' => null,
                'status' => self::STATUS_PLANNED,
            ],
        ];
    }

    public static function statusLabel(string $status): string
    {
        switch ($status) {
            case self::STATUS_SOURCE_READY:
                return 'ได้รับต้นฉบับแล้ว';
            case self::STATUS_PLANNED:
                return 'รอแม่แบบเพิ่มเติม';
            default:
                return 'ไม่ทราบสถานะ';
        }
    }

    public static function find(string $code): ?array
    {
        foreach (self::all() as $item) {
            if ($item['code'] === $code) {
                return $item;
            }
        }

        return null;
    }
}
