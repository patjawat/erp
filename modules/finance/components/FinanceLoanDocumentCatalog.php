<?php

namespace app\modules\finance\components;

/**
 * ทะเบียนชนิดเอกสารของงานเงินยืม
 *
 * เก็บ metadata ไว้ที่เดียวเพื่อให้หน้าเลือกเอกสารกับตัวสร้างเนื้อหาอ้างรายการเดียวกัน
 * รหัส code เป็นรหัสอ้างอิงถาวร ห้ามเปลี่ยนหลังมีเอกสารบันทึกไว้แล้ว เพราะ snapshot
 * ในฐานข้อมูลผูกกับรหัสนี้ ถ้าเปลี่ยนแล้วเอกสารเก่าจะกลายเป็นชนิดที่ไม่รู้จัก
 */
final class FinanceLoanDocumentCatalog
{
    public const STATUS_SOURCE_READY = 'source_ready';
    public const STATUS_PLANNED = 'planned';

    public const ESTIMATE = 'loan_expense_estimate';
    public const CONTRACT = 'loan_contract_8500';
    public const SETTLEMENT_SHEET = 'loan_settlement_sheet';
    public const EVIDENCE_MEMO = 'loan_evidence_memo';
    public const PAYMENT_MEMO = 'loan_payment_approval_memo';
    public const FOLLOWUP_MEMO = 'loan_followup_memo';

    public static function all(): array
    {
        return [
            [
                'code' => self::ESTIMATE,
                'name' => 'ใบประมาณการค่าใช้จ่ายในการเดินทางไปราชการ',
                'description' => 'แนบพร้อมบันทึกขออนุญาตเดินทางไปราชการ · ดึงบรรทัดประมาณการมาจัดตามหัวข้อ 1–5',
                'orientation' => 'แนวตั้ง',
                'pages' => 1,
                'emblem' => 'none',
                'status' => self::STATUS_SOURCE_READY,
            ],
            [
                'code' => self::CONTRACT,
                'name' => 'สัญญายืมเงิน แบบ 8500 (หน้า 1)',
                'description' => 'สัญญายืมเงินพร้อมช่องลงนาม 4 ช่อง — ผู้ยืม ผู้ตรวจสอบ ผู้อนุมัติ และใบรับเงิน',
                'orientation' => 'แนวตั้ง',
                'pages' => 1,
                'emblem' => 'none',
                'status' => self::STATUS_SOURCE_READY,
            ],
            [
                'code' => self::SETTLEMENT_SHEET,
                'name' => 'รายการส่งใช้เงินยืม (หน้า 2)',
                'description' => 'ด้านหลังสัญญา ตารางส่งใช้รายครั้งพร้อมยอดคงค้างและช่องลงนามผู้รับ',
                'orientation' => 'แนวตั้ง',
                'pages' => 1,
                'emblem' => 'none',
                'status' => self::STATUS_SOURCE_READY,
            ],
            [
                'code' => self::EVIDENCE_MEMO,
                'name' => 'บันทึกนำส่งหลักฐานใบสำคัญชดใช้เงินยืม',
                'description' => 'บันทึกข้อความของผู้ยืม พร้อมช่องติ๊ก 6 ข้อ และช่องความเห็นของงานการเงิน',
                'orientation' => 'แนวตั้ง',
                'pages' => 1,
                'emblem' => 'small',
                'status' => self::STATUS_SOURCE_READY,
            ],
            [
                'code' => self::PAYMENT_MEMO,
                'name' => 'บันทึกขออนุมัติจ่ายเงินบำรุง',
                'description' => 'บันทึกของงานการเงินขออนุมัติจ่ายเงินตามสัญญายืมที่ได้รับอนุมัติแล้ว',
                'orientation' => 'แนวตั้ง',
                'pages' => 1,
                'emblem' => 'small',
                'status' => self::STATUS_SOURCE_READY,
            ],
            [
                'code' => self::FOLLOWUP_MEMO,
                'name' => 'บันทึกขอติดตามลูกหนี้เงินยืม',
                'description' => 'หนังสือทวงถามพร้อมเลข “ครั้งที่” และวันกำหนดใหม่ — ออกจากหน้ารายละเอียดใบยืม',
                'orientation' => 'แนวตั้ง',
                'pages' => 1,
                'emblem' => 'small',
                'status' => self::STATUS_SOURCE_READY,
                // ใบยืมหนึ่งใบออกหนังสือได้หลายฉบับ แต่ละฉบับมีเลขครั้งที่ของตัวเอง
                // จึงเปิดจากรายการติดตามในหน้าใบยืม ไม่ใช่จากหน้ารวมการพิมพ์
                'per_letter' => true,
            ],
        ];
    }

    /** รหัสเอกสารของหนังสือติดตามฉบับหนึ่ง — ต่อท้ายด้วยเลขครั้งที่เพื่อให้เก็บแยกฉบับได้ */
    public static function letterCode(int $letterSeq): string
    {
        return self::FOLLOWUP_MEMO . '#' . $letterSeq;
    }

    /** รหัสฐานของเอกสาร ตัดเลขครั้งที่ของหนังสือติดตามออก */
    public static function baseCode(string $code): string
    {
        $position = strpos($code, '#');
        return $position === false ? $code : substr($code, 0, $position);
    }

    public static function find(string $code): ?array
    {
        $base = self::baseCode($code);
        foreach (self::all() as $type) {
            if ($type['code'] === $base) {
                return $type;
            }
        }
        return null;
    }

    /** ชนิดที่เปิดได้จากหน้ารวมการพิมพ์ — ไม่รวมหนังสือที่ต้องออกทีละฉบับ */
    public static function selectable(): array
    {
        return array_values(array_filter(self::all(), static fn($type) => empty($type['per_letter'])));
    }

    public static function statusLabel(string $status): string
    {
        return $status === self::STATUS_SOURCE_READY ? 'พร้อมใช้งาน' : 'ยังไม่พร้อม';
    }
}
