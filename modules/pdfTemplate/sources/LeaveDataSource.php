<?php

namespace app\modules\pdfTemplate\sources;

use app\modules\pdfTemplate\contracts\DataSourceInterface;

/**
 * Data source for PDF template "ใบลา" (Leave).
 * ชุดฟิลด์ตรงกับ modules/hr/controllers/DocumentController (LT1/LT4) และ leave/SettingController.
 * ใช้เมื่อสร้างเทมเพลต PDF สำหรับพิมพ์ใบลา จากโมดูล leave หรือ HR.
 */
class LeaveDataSource implements DataSourceInterface
{
    public function getLabel(): string
    {
        return 'ใบลา';
    }

    public function getFieldDefinitions(): array
    {
        return [
            ['source' => 'org_name', 'label' => 'ชื่อหน่วยงาน'],
            ['source' => 'title', 'label' => 'ประเภทการลา (หัวเรื่อง)'],
            ['source' => 'createDate', 'label' => 'วันที่ยื่นคำขอ'],
            ['source' => 'level_name', 'label' => 'ระดับตำแหน่ง'],
            ['source' => 'department', 'label' => 'หน่วยงาน/แผนก'],
            ['source' => 'emp_department', 'label' => 'หน่วยงานผู้ขอลา'],
            ['source' => 'dateStart', 'label' => 'วันที่เริ่มลา'],
            ['source' => 'dateEnd', 'label' => 'วันที่สิ้นสุด'],
            ['source' => 'lastDateStart', 'label' => 'ลาครั้งก่อน ตั้งแต่วันที่'],
            ['source' => 'lastDateEnd', 'label' => 'ลาครั้งก่อน ถึงวันที่'],
            ['source' => 'last_days', 'label' => 'ลามาแล้ว'],
            ['source' => 'days', 'label' => 'จำนวนวันที่ลา'],
            ['source' => 'total_days', 'label' => 'รวมเป็น'],
            ['source' => 'ld', 'label' => 'วันลาสะสมประจำปี'],
            ['source' => 'sum', 'label' => 'รวมวันลาที่ใช้ได้'],
            ['source' => 'reason', 'label' => 'เหตุผลการลา'],
            ['source' => 'leaveType', 'label' => 'ประเภทการลา'],
            ['source' => 'address', 'label' => 'ที่อยู่ที่ติดต่อได้'],
            ['source' => 'status', 'label' => 'สถานะ (อนุญาต/ไม่อนุญาต)'],
            ['source' => 'emp_fullname', 'label' => 'ชื่อ-นามสกุลผู้ขอลา'],
            ['source' => 'emp_position', 'label' => 'ตำแหน่งผู้ขอลา'],
            ['source' => 'phone', 'label' => 'เบอร์โทรติดต่อ'],
            ['source' => 'emp_sign', 'label' => 'ลายเซ็นผู้ขอลา'],
            ['source' => 'send_fullname', 'label' => 'ชื่อผู้ปฏิบัติหน้าที่แทน'],
            ['source' => 'send_position', 'label' => 'ตำแหน่งผู้ปฏิบัติหน้าที่แทน'],
            ['source' => 'send_sign', 'label' => 'ลายเซ็นผู้ปฏิบัติหน้าที่แทน'],
            // การอนุมัติแบบใบขอไปราชการ — เลือกระดับ 1–4 ได้ในตั้งค่าฟิลด์
            ['source' => 'approver_fullname', 'label' => 'ผู้อนุมัติ (ชื่อ-นามสกุล)'],
            ['source' => 'approver_position', 'label' => 'ผู้อนุมัติ (ตำแหน่ง)'],
            ['source' => 'approver_approve_date', 'label' => 'ผู้อนุมัติ (วันที่อนุมัติ)'],
            ['source' => 'approver_signature', 'label' => 'ผู้อนุมัติ (ลายเซ็น)'],
            ['source' => 'approval_status', 'label' => 'สถานะผู้อนุมัติ'],
        ];
    }
}
