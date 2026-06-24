<?php

namespace app\modules\pdfTemplate\sources;

use app\modules\pdfTemplate\contracts\DataSourceInterface;

/**
 * Data source สำหรับระบบงานซ่อม (Helpdesk2).
 *
 * โครงสร้าง field เหล่านี้ต้องตรงกับ key ใน payload ที่ action พิมพ์ใบซ่อมจะส่งให้ PdfTemplateService
 * เช่น controller ฝั่ง helpdesk2 ควรเตรียม array แบบ:
 *
 * [
 *   'repair_number' => 'RP-6601-001',
 *   'title' => 'เครื่องคอมฯ เปิดไม่ติด',
 *   'device_type_name' => 'คอมพิวเตอร์',
 *   'asset_number' => '4000-00-0001',
 *   'request_repair_date' => '2026-01-16',
 *   'receive_date' => '2026-01-17',
 *   'repair_result' => 'ซ่อมได้',
 *   'repair_type' => 'ซ่อมภายในหน่วยงาน',
 *   'status_title' => 'เสร็จสิ้น',
 *   'org_name' => 'งานคอมพิวเตอร์',
 *   'requester_fullname' => 'นายสมชาย แจ้งซ่อม',
 *   'requester_position' => 'นักวิชาการคอมพิวเตอร์',
 *   'technician_fullname' => 'นายช่างเทคนิค ตัวอย่าง',
 *   'technician_position' => 'ช่างคอมพิวเตอร์',
 *   'location' => 'ห้องคอมพิวเตอร์ ชั้น 2',
 *   'problem_detail' => 'เปิดไม่ติด มีเสียงติ๊ด 3 ครั้ง',
 *   'solution_detail' => 'เปลี่ยน RAM + ทำความสะอาด',
 *   'remark' => 'ทดสอบแล้วใช้งานได้ปกติ',
 * ]
 */
class HelpdeskDataSource implements DataSourceInterface
{
    public function getLabel(): string
    {
        return 'ระบบงานซ่อม (Helpdesk2)';
    }

    public function getFieldDefinitions(): array
    {
        return [
            // ข้อมูลทั่วไปของงานซ่อม
            ['source' => 'repair_number', 'label' => 'รหัสงานซ่อม'],
            ['source' => 'title', 'label' => 'หัวข้อ/ปัญหา'],
            ['source' => 'device_type_name', 'label' => 'ประเภทอุปกรณ์'],
            ['source' => 'asset_number', 'label' => 'หมายเลขครุภัณฑ์'],
            ['source' => 'asset_code', 'label' => 'รหัสครุภัณฑ์'],
            ['source' => 'org_name', 'label' => 'หน่วยงานที่แจ้งซ่อม'],

            // วันที่และสถานะ
            ['source' => 'notice_date', 'label' => 'วันที่แจ้งซ่อม'],
            ['source' => 'request_repair_date', 'label' => 'วันที่ต้องการให้ซ่อม'],
            ['source' => 'receive_date', 'label' => 'วันที่รับเรื่อง'],
            ['source' => 'send_repair_date', 'label' => 'วันที่ส่งซ่อม'],
            ['source' => 'urgency', 'label' => 'ความเร่งด่วน'],
            ['source' => 'repair_type', 'label' => 'ประเภทการซ่อม'],
            ['source' => 'repair_result', 'label' => 'ผลการซ่อม'],
            ['source' => 'status_title', 'label' => 'สถานะงานซ่อม'],

            // ผู้แจ้งซ่อม
            ['source' => 'requester_fullname', 'label' => 'ชื่อผู้แจ้งซ่อม'],
            ['source' => 'requester_position', 'label' => 'ตำแหน่งผู้แจ้งซ่อม'],
            ['source' => 'employee_type', 'label' => 'ประเภทพนักงาน'],
            ['source' => 'requester_employee_type', 'label' => 'ประเภทพนักงานผู้แจ้งซ่อม'],
            ['source' => 'requester_phone', 'label' => 'เบอร์ติดต่อผู้แจ้งซ่อม'],
            // ข้อมูลที่ระบบ helpdesk2 เก็บใน data_json
            ['source' => 'phone', 'label' => 'โทร'],
            ['source' => 'note', 'label' => 'หมายเหตุเพิ่มเติม'],
            ['source' => 'sender_signature', 'label' => 'ลายเซ็นผู้ส่งซ่อม'],

            // ช่างผู้ดำเนินการ
            ['source' => 'technician_fullname', 'label' => 'ชื่อช่างผู้ดำเนินการ'],
            ['source' => 'technician_position', 'label' => 'ตำแหน่งช่างผู้ดำเนินการ'],
            ['source' => 'technician_employee_type', 'label' => 'ประเภทพนักงานช่างผู้ดำเนินการ'],
            ['source' => 'technician_department', 'label' => 'แผนกช่าง'],

            // รายละเอียดปัญหาและการแก้ไข
            ['source' => 'location', 'label' => 'สถานที่'],
            ['source' => 'problem_detail', 'label' => 'รายละเอียดปัญหา'],
            ['source' => 'solution_detail', 'label' => 'รายละเอียดการแก้ไข'],
            ['source' => 'remark', 'label' => 'หมายเหตุ'],
        ];
    }
}
