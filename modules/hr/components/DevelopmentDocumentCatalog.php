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
                'code' => 'travel_permission_memo',
                'name' => 'บันทึกขออนุญาตไปราชการ',
                'description' => 'บันทึกข้อความขออนุญาตไปราชการนอกสำนักงาน พร้อมผู้รับมอบงาน',
                'source_format' => 'Microsoft Excel',
                'orientation' => 'แนวตั้ง',
                'pages' => 1,
                'status' => self::STATUS_SOURCE_READY,
            ],
            [
                'code' => 'travel_expense_8708_part_1',
                'name' => 'แบบ 8708',
                'description' => 'ใบเบิกค่าใช้จ่ายในการเดินทางไปราชการ (2 หน้าในฉบับเดียว)',
                'source_format' => 'PDF แบบกรอกข้อมูลได้',
                'orientation' => 'แนวตั้ง',
                'pages' => 2,
                'status' => self::STATUS_SOURCE_READY,
            ],
            [
                // รหัสยังเป็น _8708_part_2 ตามของเดิม เพราะเป็นรหัสอ้างอิงของเอกสารที่
                // สร้างไว้แล้วในฐานข้อมูล ส่วนชื่อที่แสดงยึดตามแบบฟอร์มจริง (แบบ 8707)
                'code' => 'travel_expense_8708_part_2',
                'name' => 'หลักฐานการจ่ายเงินหมู่คณะ (แบบ 8707)',
                'description' => 'หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปราชการเป็นหมู่คณะ ส่วนที่ 2',
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
                'code' => 'travel_expense_cover_sheet',
                'name' => 'ใบหน้างบสำคัญ',
                'description' => 'ใบปะหน้าชุดเบิกค่าพาหนะเดินทางภายในประเทศ (ใช้คู่กับ แบบ บก.111)',
                'source_format' => 'Microsoft Excel',
                'orientation' => 'แนวตั้ง',
                'pages' => 1,
                'status' => self::STATUS_SOURCE_READY,
            ],
            [
                'code' => 'travel_expense_payment_approval',
                'name' => 'บันทึกขออนุมัติจ่าย',
                'description' => 'บันทึกข้อความขออนุมัติจ่ายเงินบำรุงตามใบเบิกค่าใช้จ่ายเดินทางไปราชการ',
                'source_format' => 'Microsoft Excel',
                'orientation' => 'แนวตั้ง',
                'pages' => 1,
                'status' => self::STATUS_SOURCE_READY,
            ],
            [
                'code' => 'travel_registration_memo',
                'name' => 'บันทึกขออนุมัติจ่ายค่าลงทะเบียน',
                'description' => 'บันทึกข้อความขอเบิกเงินบำรุงจ่ายค่าลงทะเบียนอบรม แยกยอดจากค่าเดินทาง',
                'source_format' => 'Microsoft Excel',
                'orientation' => 'แนวตั้ง',
                'pages' => 1,
                'status' => self::STATUS_SOURCE_READY,
            ],
        ];
    }

    /**
     * เอกสารพิมพ์สำเร็จรูปของระบบเดิม ที่ยังไม่ได้ทำเป็นแม่แบบแก้ไขได้
     *
     * ย้ายมารวมไว้ในหน้าพิมพ์เอกสารเพื่อไม่ให้เมนูในทะเบียนยาวเกินไป ปุ่มจะเรียก
     * เส้นทางเดิมตรง ๆ open = tab เปิดแท็บใหม่ (PDF) / modal เปิดในหน้าต่างซ้อน
     */
    public static function legacyPrints(): array
    {
        return [
            [
                'code' => 'print_travel_request',
                'name' => 'ใบขอไปราชการ (PDF)',
                'description' => 'ใบขออนุมัติเดินทางไปราชการ พิมพ์จากข้อมูลทะเบียนโดยตรง',
                'route' => ['/hr/development/print'],
                'open' => 'tab',
                'icon' => 'bi-file-earmark-pdf',
            ],
            [
                'code' => 'print_personal_vehicle',
                'name' => 'ใบขอใช้รถยนต์ส่วนตัว',
                'description' => 'ใช้แม่แบบที่ตั้งค่าไว้สำหรับ «ขอใช้รถยนต์ส่วนตัวเดินทางไปราชการ»',
                'route' => ['/hr/development/print-personal-vehicle'],
                'open' => 'tab',
                'icon' => 'bi-car-front',
            ],
            [
                'code' => 'print_permit_request',
                'name' => 'ใบขออนุญาต',
                'description' => 'ใบขออนุญาตของผู้เดินทาง เปิดตรวจแก้ก่อนพิมพ์ในหน้าต่างซ้อน',
                'route' => ['/hr/development/print-permit-request'],
                'open' => 'modal',
                'modal_size' => 'modal-xl',
                'icon' => 'bi-file-earmark-text',
            ],
            [
                'code' => 'print_academic_form',
                'name' => 'ใบตอบรับเป็นวิทยากร',
                'description' => 'ใช้เฉพาะกรณีได้รับเชิญเป็นวิทยากร เปิดตรวจแก้ก่อนพิมพ์',
                'route' => ['/hr/development/print-academic-form'],
                'open' => 'modal',
                'modal_size' => 'modal-xl',
                'icon' => 'bi-easel',
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
