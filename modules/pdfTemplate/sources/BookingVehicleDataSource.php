<?php

namespace app\modules\pdfTemplate\sources;

use app\modules\pdfTemplate\contracts\DataSourceInterface;

/**
 * Data source สำหรับ "ขอใช้รถยนต์ส่วนกลาง" (ระบบจองรถ).
 *
 * รองรับทั้ง payload แบบ flat key และ dot path จาก model Vehicle
 * เช่น employee.fullname, leader.positionName, data_json.phone
 */
class BookingVehicleDataSource implements DataSourceInterface
{
    public function getLabel(): string
    {
        return 'ขอใช้รถยนต์ส่วนกลาง';
    }

    public function getFieldDefinitions(): array
    {
        // ช่องผู้ร่วมเดินทางแยกตามลำดับเลข (companion_1 ... companion_10)
        // สำหรับเทมเพลตที่พิมพ์เลขลำดับไว้แล้วและวางชื่อทีละช่องแนวตั้ง
        $companionSlots = [];
        for ($i = 1; $i <= 10; $i++) {
            $companionSlots[] = ['source' => 'companion_' . $i, 'label' => 'ผู้ร่วมเดินทาง คนที่ ' . $i];
        }

        return array_merge([
            // ข้อมูลเอกสารคำขอ
            ['source' => 'code', 'label' => 'เลขที่คำขอ'],
            ['source' => 'thai_year', 'label' => 'ปีงบประมาณ'],
            ['source' => 'created_at', 'label' => 'วันที่บันทึกคำขอ'],
            ['source' => 'status', 'label' => 'สถานะ'],
            ['source' => 'vehicleStatus.title', 'label' => 'สถานะ (ชื่อแสดงผล)'],

            // ข้อมูลการเดินทาง
            ['source' => 'vehicle_type_id', 'label' => 'ประเภทรถ'],
            ['source' => 'carType.title', 'label' => 'ประเภทรถ (ชื่อแสดงผล)'],
            ['source' => 'go_type', 'label' => 'ลักษณะการเดินทาง'],
            ['source' => 'urgent', 'label' => 'ความเร่งด่วน'],
            ['source' => 'license_plate', 'label' => 'ทะเบียนรถ'],
            ['source' => 'location', 'label' => 'สถานที่ไป'],
            ['source' => 'reason', 'label' => 'เหตุผลการใช้รถ'],
            ['source' => 'date_start', 'label' => 'วันที่เริ่มเดินทาง'],
            ['source' => 'time_start', 'label' => 'เวลาเริ่มเดินทาง'],
            ['source' => 'time_start', 'label' => 'เวลาไป'],
            ['source' => 'date_end', 'label' => 'วันที่สิ้นสุดเดินทาง'],
            ['source' => 'time_end', 'label' => 'เวลาสิ้นสุดเดินทาง'],
            ['source' => 'time_end', 'label' => 'เวลากลับ'],

            // ผู้ขอ / ผู้อนุมัติ / คนขับ
            ['source' => 'emp_id', 'label' => 'รหัสผู้ขอ'],
            ['source' => 'employee.fullname', 'label' => 'ชื่อผู้ขอ'],
            ['source' => 'employee.positionName', 'label' => 'ตำแหน่งผู้ขอ'],
            ['source' => 'employee_type', 'label' => 'ประเภทพนักงาน'],
            ['source' => 'employee.employeeTypeName', 'label' => 'ประเภทพนักงานผู้ขอ'],
            ['source' => 'employee.departmentName', 'label' => 'หน่วยงานผู้ขอ'],
            ['source' => 'emp_signature', 'label' => 'ลายเซ็นผู้ขอใช้รถ'],
            ['source' => 'requester_signature', 'label' => 'ลายเซ็นผู้ขอใช้รถ (alias)'],
            ['source' => 'leader_id', 'label' => 'รหัสหัวหน้ารับรอง'],
            ['source' => 'leader.fullname', 'label' => 'ชื่อหัวหน้ารับรอง'],
            ['source' => 'leader.positionName', 'label' => 'ตำแหน่งหัวหน้ารับรอง'],
            ['source' => 'leader.employeeTypeName', 'label' => 'ประเภทพนักงานหัวหน้ารับรอง'],
            ['source' => 'driver_id', 'label' => 'รหัสพนักงานขับ'],
            ['source' => 'driver.fullname', 'label' => 'ชื่อพนักงานขับ'],
            ['source' => 'driver.employeeTypeName', 'label' => 'ประเภทพนักงานขับ'],
            ['source' => 'approver_fullname', 'label' => 'ผู้อนุมัติ (ชื่อ-นามสกุล)'],
            ['source' => 'approver_position', 'label' => 'ผู้อนุมัติ (ตำแหน่ง)'],
            ['source' => 'approver_employee_type', 'label' => 'ผู้อนุมัติ (ประเภทพนักงาน)'],
            ['source' => 'approver_approve_date', 'label' => 'ผู้อนุมัติ (วันที่อนุมัติ)'],
            ['source' => 'approver_signature', 'label' => 'ผู้อนุมัติ (ลายเซ็น)'],
            ['source' => 'approval_status', 'label' => 'สถานะผู้อนุมัติ'],

            // ข้อมูลเพิ่มเติมจาก data_json
            ['source' => 'data_json.phone', 'label' => 'เบอร์โทรผู้ขอ (data_json)'],
            ['source' => 'companion_names_vertical', 'label' => 'รายชื่อผู้ร่วมเดินทาง (แนวตั้ง 1 คน/บรรทัด) — ตั้ง line height ได้'],
            ['source' => 'companion_names_numbered', 'label' => 'รายชื่อผู้ร่วมเดินทาง (แนวตั้ง มีเลขนำ 1./2./3.)'],
            ['source' => 'travel_party_list', 'label' => 'รายชื่อผู้ร่วมเดินทาง (loop แนวตั้ง ชื่อ+ตำแหน่งแยกคอลัมน์)'],
            ['source' => 'companion_names', 'label' => 'รายชื่อผู้ร่วมเดินทาง (แนวนอน, คั่นด้วย ,)'],
            ['source' => 'companion_total', 'label' => 'จำนวนผู้ร่วมเดินทาง'],
            ['source' => 'data_json.companion_names', 'label' => 'รายชื่อผู้ร่วมเดินทาง (data_json)'],
            ['source' => 'data_json.companion_names_vertical', 'label' => 'รายชื่อผู้ร่วมเดินทาง แนวตั้ง (data_json)'],
            ['source' => 'data_json.companion_total', 'label' => 'จำนวนผู้ร่วมเดินทาง (data_json)'],
            ['source' => 'data_json.passenger_total', 'label' => 'จำนวนผู้โดยสาร (data_json)'],
            ['source' => 'data_json.passenger_name', 'label' => 'รายชื่อผู้โดยสาร (data_json)'],
            ['source' => 'data_json.note', 'label' => 'หมายเหตุ / ผู้ร่วมเดินทาง (data_json.note)'],
            ['source' => 'data_json.req_driver_id', 'label' => 'พนักงานขับที่ร้องขอ (data_json)'],
        ], $companionSlots);
    }
}
