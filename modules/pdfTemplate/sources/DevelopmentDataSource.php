<?php

namespace app\modules\pdfTemplate\sources;

use app\modules\pdfTemplate\contracts\DataSourceInterface;

/**
 * Data source for PDF template "ใบขอไปราชการ" (Development / travel request).
 * Exposes fields: flat keys (officer_name, topic, …) and nested paths (createdByEmp.fullname, data_json.location).
 * Used by HR development print; payload from DevelopmentController::actionPrint().
 */
class DevelopmentDataSource implements DataSourceInterface
{
    public function getLabel(): string
    {
        return 'ใบขอไปราชการ';
    }

    public function getFieldDefinitions(): array
    {
        return [
            ['source' => 'organization_name', 'label' => 'ชื่อหน่วยงาน'],
            ['source' => 'reference_document', 'label' => 'หนังสืออ้างอิง'],
            ['source' => 'document_number', 'label' => 'เลขที่หนังสือ'],
            ['source' => 'thai_year', 'label' => 'ปีงบประมาณ'],
            ['source' => 'custom_text', 'label' => 'ข้อความกำหนดเอง'],
            ['source' => 'officer_name', 'label' => 'ชื่อผู้รับผิดชอบ'],
            ['source' => 'officer_name', 'label' => 'ชื่อผู้ขอ'],
            ['source' => 'officer_position', 'label' => 'ตำแหน่งผู้ขอ'],
            ['source' => 'officer_signature', 'label' => 'ลายเซ็นผู้ขอ'],
            ['source' => 'assigned_to_fullname', 'label' => 'ชื่อสกุลผู้มอบหมายงาน'],
            ['source' => 'assigned_to_position', 'label' => 'ตำแหน่งผู้มอบหมายงาน'],
            ['source' => 'assigned_to_signature', 'label' => 'ลายเซ็นผู้มอบหมายงาน'],
            ['source' => 'document_date', 'label' => 'วันที่เอกสาร'],
            ['source' => 'topic', 'label' => 'เรื่อง'],
            ['source' => 'location', 'label' => 'สถานที่'],
            ['source' => 'location', 'label' => 'สถานที่จัดงาน'],
            ['source' => 'location_org', 'label' => 'หน่วยงานที่จัด'],
            ['source' => 'province_name', 'label' => 'จังหวัด'],
            ['source' => 'vehicle_type_title', 'label' => 'พาหนะเดินทาง'],
            ['source' => 'license_plate', 'label' => 'ทะเบียนพาหนะเดินทาง'],
            ['source' => 'distance', 'label' => 'ระยะทาง'],
            ['source' => 'total_expense', 'label' => 'รวมค่าใช้จ่าย'],
            ['source' => 'registration_amount', 'label' => 'ค่าลงทะเบียน'],
            ['source' => 'accommodation_amount', 'label' => 'ค่าที่พัก'],
            ['source' => 'vehicle_amount', 'label' => 'ค่ายานพาหนะ'],
            ['source' => 'allowance_amount', 'label' => 'ค่าเบี้ยเลี้ยง'],
            ['source' => 'other_amount', 'label' => 'ค่าอื่น ๆ'],
            ['source' => 'date_start', 'label' => 'วันที่เริ่ม'],
            ['source' => 'date_end', 'label' => 'วันที่สิ้นสุด'],
            ['source' => 'travel_party', 'label' => 'คณะเดินทาง'],
            ['source' => 'travel_party_list', 'label' => 'รายการคณะเดินทาง (loop)'],
            ['source' => 'createdByEmp.fullname', 'label' => 'ชื่อผู้ขอ (จาก relation)'],
            ['source' => 'data_json.location', 'label' => 'สถานที่ (จาก data_json)'],
            ['source' => 'data_json.travel_party', 'label' => 'คณะเดินทาง (จาก data_json)'],
            ['source' => 'vehicle_date_start', 'label' => 'วันออกเดินทาง'],
            ['source' => 'vehicle_time_start', 'label' => 'เวลาออกเดินทาง'],
            ['source' => 'vehicle_date_end', 'label' => 'วันกลับ'],
            ['source' => 'vehicle_time_end', 'label' => 'เวลากลับ'],
            ['source' => 'trip_days', 'label' => 'นับวัน'],
            ['source' => 'status', 'label' => 'สถานะ'],
            ['source' => 'approver_fullname', 'label' => 'ผู้อนุมัติ (ชื่อ-นามสกุล)'],
            ['source' => 'approver_position', 'label' => 'ผู้อนุมัติ (ตำแหน่ง)'],
            ['source' => 'approver_approve_date', 'label' => 'ผู้อนุมัติ (วันที่อนุมัติ)'],
            ['source' => 'approver_signature', 'label' => 'ผู้อนุมัติ (ลายเซ็น)'],
            ['source' => 'approval_status', 'label' => 'สถานะผู้อนุมัติ'],
        ];
    }
}
