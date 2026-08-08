<?php

namespace app\modules\pm\models;

use app\modules\hr\models\Employees;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * ผู้รับผิดชอบโครงการ (ข้อ 11)
 *
 * @property int $id
 * @property int $project_id
 * @property int $sort
 * @property string|null $role owner=ผู้รับผิดชอบ / director=ผู้บังคับบัญชา
 * @property int|null $emp_id
 * @property string|null $fullname
 * @property string|null $position
 * @property string|null $phone
 */
class ProjectResponsible extends ActiveRecord
{
    public const ROLE_OWNER = 'owner';
    public const ROLE_DIRECTOR = 'director';

    public static function tableName()
    {
        return '{{%project_responsibles}}';
    }

    public function rules()
    {
        return [
            [['fullname'], 'required'],
            [['project_id', 'sort', 'emp_id'], 'integer'],
            [['role'], 'string', 'max' => 20],
            [['fullname', 'position'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 30],
            [['sort'], 'default', 'value' => 0],
            [['role'], 'default', 'value' => self::ROLE_OWNER],
        ];
    }

    public function attributeLabels()
    {
        return [
            'role' => 'ประเภท',
            'fullname' => 'ชื่อ-สกุล',
            'position' => 'ตำแหน่ง',
            'phone' => 'เบอร์โทรศัพท์',
        ];
    }

    public static function roleList(): array
    {
        return [
            self::ROLE_OWNER => 'ผู้รับผิดชอบงาน',
            self::ROLE_DIRECTOR => 'ผู้บังคับบัญชา (ผอ.รพ.สต./สสอ./ผอ.รพ./หน.กลุ่มงาน)',
        ];
    }

    /** ชื่อ-ตำแหน่ง-เบอร์โทร ของบุคลากรที่ยังปฏิบัติงาน สำหรับเติมค่าอัตโนมัติในฟอร์ม */
    public static function activeEmployees(): array
    {
        // ชื่อตำแหน่งอยู่ที่ employee_position.title — คอลัมน์ employees.position_name เก็บเป็นรหัส
        $rows = (new Query())
            ->select(['e.id', 'e.prefix', 'e.fname', 'e.lname', 'e.phone', 'position_title' => 'p.title'])
            ->from(['e' => 'employees'])
            ->leftJoin(['p' => 'employee_position'], 'p.id = e.employee_position_id')
            ->where(['e.status' => Employees::STATUS_WORKING])
            ->orderBy(['e.fname' => SORT_ASC, 'e.lname' => SORT_ASC])
            ->all();

        $people = [];
        foreach ($rows as $row) {
            $people[(int) $row['id']] = [
                'fullname' => trim(($row['prefix'] ?? '') . ($row['fname'] ?? '') . ' ' . ($row['lname'] ?? '')),
                'position' => trim((string) ($row['position_title'] ?? '')),
                'phone' => (string) ($row['phone'] ?? ''),
            ];
        }
        return $people;
    }

    /** ข้อมูลบุคลากรรายเดียว ใช้เติมแถวเริ่มต้นของฟอร์ม */
    public static function employeeInfo(?int $empId): ?array
    {
        if (!$empId) {
            return null;
        }
        $row = (new Query())
            ->select(['e.prefix', 'e.fname', 'e.lname', 'e.phone', 'position_title' => 'p.title'])
            ->from(['e' => 'employees'])
            ->leftJoin(['p' => 'employee_position'], 'p.id = e.employee_position_id')
            ->where(['e.id' => $empId])
            ->one();
        if (!$row) {
            return null;
        }
        return [
            'fullname' => trim(($row['prefix'] ?? '') . ($row['fname'] ?? '') . ' ' . ($row['lname'] ?? '')),
            'position' => trim((string) ($row['position_title'] ?? '')),
            'phone' => (string) ($row['phone'] ?? ''),
        ];
    }

    /**
     * ตัวเลือกบุคลากรสำหรับ dropdown
     *
     * รายชื่อหลักคือผู้ที่ยังปฏิบัติงาน แต่แถวเดิมของโครงการปีเก่าอาจอ้างถึงคนที่
     * พ้นจากการปฏิบัติงานไปแล้ว จึงต้องเติมคนเหล่านั้นกลับเข้าตัวเลือกด้วย
     * มิฉะนั้นการเปิดฟอร์มแก้ไขจะทำให้ค่าเดิมหายไปเงียบ ๆ
     *
     * @param ProjectResponsible[] $rows แถวที่กำลังแสดงในฟอร์ม
     */
    public static function employeeOptions(array $rows = [], ?array $people = null): array
    {
        $people = $people ?? self::activeEmployees();
        $options = [];
        foreach ($people as $id => $info) {
            $options[$id] = $info['fullname']; // ตำแหน่งมีฟิลด์ของตัวเองแล้ว ไม่ต้องต่อท้ายชื่อ
        }

        foreach ($rows as $row) {
            $empId = (int) ($row->emp_id ?? 0);
            if ($empId <= 0 || isset($options[$empId])) {
                continue;
            }
            $name = trim((string) $row->fullname) ?: 'บุคลากรรหัส ' . $empId;
            $options[$empId] = $name . ' (พ้นจากการปฏิบัติงาน)';
        }
        return $options;
    }
}
