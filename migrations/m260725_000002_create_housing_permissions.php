<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260725_000002_create_housing_permissions extends Migration
{
    private array $permissions = [
        'housing.user' => 'ใช้งานระบบบ้านพักและสวัสดิการ',
        'housing.staff' => 'จัดการข้อมูลบ้านพักและผู้พัก',
        'housing.committee.recorder' => 'บันทึกผลมติคณะกรรมการบ้านพัก',
        'housing.guest.approver' => 'อนุญาตบุคคลภายนอกเข้าพัก',
        'housing.finance' => 'จัดทำใบแจ้งค่าใช้จ่าย',
        'housing.cashier' => 'รับชำระและออกใบเสร็จ',
        'housing.receipt.void' => 'ยกเลิกใบเสร็จบ้านพัก',
        'housing.maintenance' => 'จัดการงานซ่อมของบ้านพัก',
        'housing.report' => 'ดูรายงานบ้านพัก',
        'housing.admin' => 'บริหารระบบบ้านพักทั้งหมด',
    ];

    public function safeUp(): void
    {
        $auth = Yii::$app->authManager ?? null;
        if ($auth === null) {
            echo "authManager is not configured; skip housing permissions.\n";
            return;
        }
        foreach ($this->permissions as $name => $description) {
            if ($auth->getPermission($name) !== null) {
                continue;
            }
            $permission = $auth->createPermission($name);
            $permission->description = $description;
            $auth->add($permission);
        }
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager ?? null;
        if ($auth === null) {
            return;
        }
        foreach (array_keys($this->permissions) as $name) {
            if (($permission = $auth->getPermission($name)) !== null) {
                $auth->remove($permission);
            }
        }
    }
}
