<?php

use yii\db\Migration;

/**
 * สร้าง role งานซักฟอกให้ผูกสิทธิ์กับพนักงานได้ (เดิมมีแต่ admin ที่มี laundry.*)
 *   - laundry          : เจ้าหน้าที่ซักฟอก (บันทึกงาน) => laundry.manage (ครอบ laundry.view)
 *   - laundryApprover  : ผู้ตรวจ/อนุมัติ (แยกจากผู้บันทึกตามหลัก segregation) => laundry.approve + laundry.view
 * idempotent: มีอยู่แล้วข้าม (ปลอดภัยกับทุกสถานะฐาน)
 */
class m260917_180000_create_laundry_roles extends Migration
{
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $manage = $auth->getPermission('laundry.manage');
        $view = $auth->getPermission('laundry.view');
        $approve = $auth->getPermission('laundry.approve');

        $laundry = $auth->getRole('laundry');
        if ($laundry === null) {
            $laundry = $auth->createRole('laundry');
            $laundry->description = 'เจ้าหน้าที่ซักฟอก (บันทึกงาน)';
            $auth->add($laundry);
        }
        if ($manage && !$auth->hasChild($laundry, $manage)) {
            $auth->addChild($laundry, $manage);
        }

        $approver = $auth->getRole('laundryApprover');
        if ($approver === null) {
            $approver = $auth->createRole('laundryApprover');
            $approver->description = 'ผู้ตรวจ/อนุมัติงานซักฟอก';
            $auth->add($approver);
        }
        if ($approve && !$auth->hasChild($approver, $approve)) {
            $auth->addChild($approver, $approve);
        }
        if ($view && !$auth->hasChild($approver, $view)) {
            $auth->addChild($approver, $view);
        }

        $auth->invalidateCache();
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        foreach (['laundry', 'laundryApprover'] as $name) {
            if ($role = $auth->getRole($name)) {
                $auth->remove($role);
            }
        }
        $auth->invalidateCache();
    }
}
