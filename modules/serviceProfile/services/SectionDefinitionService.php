<?php

namespace app\modules\serviceProfile\services;

class SectionDefinitionService
{
    public static function columns(string $type): array
    {
        return [
            'goal_ha_table' => ['goal' => 'เป้าหมาย', 'ha_standard' => 'มาตรฐาน HA ที่เชื่อมโยง'],
            'service_scope_table' => ['service' => 'งาน/หน่วยบริการ', 'scope' => 'ขอบเขตและกิจกรรมสำคัญ'],
            'stakeholder_table' => ['stakeholder' => 'ผู้รับผลงาน', 'requirements' => 'ความต้องการที่สำคัญ', 'response' => 'การตอบสนอง'],
            'year_series_table' => ['indicator' => 'สถิติ/ลักษณะงาน', 'unit' => 'หน่วย', 'values' => 'ข้อมูลรายปี'],
            'quality_dimension_table' => ['organization' => 'ด้านองค์กร', 'customer' => 'ด้านผู้รับบริการ', 'provider' => 'ด้านผู้ให้บริการ'],
            'challenge_risk_table' => ['challenge' => 'ความท้าทาย', 'risk' => 'ความเสี่ยงสำคัญ', 'focus' => 'จุดเน้นในการพัฒนา'],
            'staffing_table' => ['staff_type' => 'ประเภทบุคลากร', 'required' => 'จำเป็น', 'actual' => 'มีจริง', 'gap' => 'ส่วนขาด', 'management' => 'การบริหารจัดการ'],
            'key_process_table' => ['process' => 'กระบวนการสำคัญ', 'expectation' => 'สิ่งที่คาดหวัง', 'risk' => 'ความเสี่ยงสำคัญ', 'kpi' => 'ตัวชี้วัด'],
            'cqi_review_table' => ['activity' => 'กิจกรรมทบทวน', 'event' => 'เหตุการณ์/เรื่องสำคัญ', 'result' => 'ผลลัพธ์ที่เกิดขึ้น'],
            'kpi_table' => ['indicator' => 'ตัวชี้วัด', 'operator' => 'เกณฑ์', 'target' => 'เป้าหมาย', 'unit' => 'หน่วย', 'values' => 'ผลรายปี', 'note' => 'แนวโน้ม/หมายเหตุ'],
            'pppp_process' => ['name' => 'ชื่อกระบวนงาน', 'purpose' => 'Purpose', 'problem' => 'Problem', 'process' => 'Process', 'performance' => 'Performance'],
            'development_plan_table' => ['opportunity' => 'โอกาส/แผนพัฒนา', 'activity' => 'กิจกรรม/วิธีการ', 'period' => 'ระยะเวลา', 'responsible' => 'ผู้รับผิดชอบ'],
            'risk_incident_table' => ['incident' => 'อุบัติการณ์ความเสี่ยง', 'values' => 'จำนวนรายปี'],
            'risk_control_table' => ['risk' => 'รายการความเสี่ยง', 'level' => 'ระดับ', 'control' => 'มาตรการป้องกัน'],
            'integration_table' => ['organization' => 'หน่วยงาน/ทีม', 'integration' => 'ลักษณะการบูรณาการ'],
            'team_responsibility_table' => ['team_role' => 'ทีม/บทบาท', 'responsibility' => 'หน้าที่รับผิดชอบ', 'responsible_person' => 'ผู้รับผิดชอบหลัก'],
            'service_guideline_table' => ['recipient_group' => 'กลุ่มผู้รับบริการ', 'situation' => 'สถานการณ์สำคัญ', 'guideline' => 'แนวทางการให้บริการ'],
            'risk_profile_table' => ['risk' => 'รายการความเสี่ยง', 'level' => 'ระดับความเสี่ยง', 'prevention' => 'มาตรการป้องกัน', 'monitoring' => 'การติดตามผล'],
            'competency_table' => ['role' => 'ตำแหน่ง/บทบาท', 'competency' => 'สมรรถนะที่จำเป็น', 'development' => 'แนวทางพัฒนา', 'evaluation' => 'วิธีประเมิน'],
            'document_reference_table' => ['document_code' => 'รหัสเอกสาร', 'document_name' => 'ชื่อเอกสาร', 'usage' => 'การนำไปใช้/หมายเหตุ'],
            'reference_table' => ['reference' => 'เอกสาร/แหล่งอ้างอิง', 'detail' => 'รายละเอียดหรือการเชื่อมโยง'],
        ][$type] ?? [];
    }
}
