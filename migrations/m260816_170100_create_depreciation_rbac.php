<?php

use yii\db\Migration;

/**
 * สิทธิ์งานค่าเสื่อมราคา — เดิมทุกหน้าเปิดให้ผู้ล็อกอินทุกคน (roles = ['@'])
 * ทั้งที่เป็นการตั้งเกณฑ์บัญชีที่กระทบมูลค่าทรัพย์สินทั้งองค์กร
 *
 * แบ่งเป็น 3 สิทธิ์ตามลักษณะงาน:
 *   depreciationView  — ดูข้อมูล/รายงาน
 *   depreciationSetup — ตั้งเกณฑ์ ผูกเข้าลำดับชั้น ตรึงเกณฑ์ให้ทะเบียน (งานพัสดุร่วมกับบัญชี)
 *   depreciationRun   — เปิดงวด คำนวณ บันทึกบัญชี ปรับปรุง/กลับรายการ (งานบัญชี)
 */
class m260816_170100_create_depreciation_rbac extends Migration
{
    private array $permissions = [
        'depreciationView' => 'ดูข้อมูลค่าเสื่อมราคา',
        'depreciationSetup' => 'ตั้งเกณฑ์ค่าเสื่อมและผูกเข้าลำดับชั้นทรัพย์สิน',
        'depreciationRun' => 'คำนวณและบันทึกบัญชีค่าเสื่อมประจำงวด',
    ];

    /** role => permissions ที่ได้รับ */
    private array $grants = [
        'asset' => ['depreciationView', 'depreciationSetup'],          // พัสดุ: กำหนดอายุ/เกณฑ์ของครุภัณฑ์
        'sm' => ['depreciationView', 'depreciationSetup'],             // บริหารพัสดุ
        'accountingViewer' => ['depreciationView'],
        'accountingMaker' => ['depreciationView', 'depreciationRun'],  // บัญชี: เดินงวด
        'accountingApprover' => ['depreciationView', 'depreciationRun'],
        'accountingAdmin' => ['depreciationView', 'depreciationSetup', 'depreciationRun'],
        'director' => ['depreciationView'],
        'admin' => ['depreciationView', 'depreciationSetup', 'depreciationRun'],
    ];

    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        foreach ($this->permissions as $name => $description) {
            if ($auth->getPermission($name) === null) {
                $item = $auth->createPermission($name);
                $item->description = $description;
                $auth->add($item);
            }
        }

        foreach ($this->grants as $roleName => $permNames) {
            $role = $auth->getRole($roleName);
            if ($role === null) {
                echo "    > ข้าม role {$roleName} (ไม่มีในระบบ)\n";
                continue;
            }
            foreach ($permNames as $permName) {
                $perm = $auth->getPermission($permName);
                if ($perm !== null && !$auth->hasChild($role, $perm)) {
                    $auth->addChild($role, $perm);
                }
            }
        }

        $auth->invalidateCache();
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        foreach (array_keys($this->permissions) as $name) {
            $perm = $auth->getPermission($name);
            if ($perm !== null) {
                $auth->remove($perm);
            }
        }
        $auth->invalidateCache();
    }
}
