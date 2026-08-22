<?php

use yii\caching\TagDependency;
use yii\db\Migration;

/**
 * ให้ role director เห็น Dashboard ผู้บริหาร
 *
 * ผูกเป็น director -> executiveViewer แบบเดียวกับที่ m260816_100000 ผูก admin
 * เพื่อให้ได้ทั้ง permission executiveDashboardView (ด่านคอนโทรลเลอร์ + เงื่อนไขแสดงเมนู)
 * และ route /executive/* (ด่าน mdm\admin) ในความสัมพันธ์เดียว
 */
class m260819_100000_grant_director_executive_dashboard extends Migration
{
    public function safeUp()
    {
        $this->ensureChild('director', 'executiveViewer');
        $this->invalidateAuthCache();
    }

    public function safeDown()
    {
        $this->delete('{{%auth_item_child}}', ['parent' => 'director', 'child' => 'executiveViewer']);
        $this->invalidateAuthCache();
    }

    private function ensureChild(string $parent, string $child): void
    {
        $parentExists = (new \yii\db\Query())->from('{{%auth_item}}')->where(['name' => $parent])->exists();
        $childExists = (new \yii\db\Query())->from('{{%auth_item}}')->where(['name' => $child])->exists();
        $exists = (new \yii\db\Query())->from('{{%auth_item_child}}')->where(['parent' => $parent, 'child' => $child])->exists();
        if ($parentExists && $childExists && !$exists) {
            $this->insert('{{%auth_item_child}}', ['parent' => $parent, 'child' => $child]);
        }
    }

    /** ล้างทั้งแคช RBAC และแคช route ต่อผู้ใช้ของ mdm\admin (FileCache ค้างข้ามคำขอ) */
    private function invalidateAuthCache(): void
    {
        Yii::$app->authManager->invalidateCache();
        if (Yii::$app->has('cache') && Yii::$app->cache !== null) {
            TagDependency::invalidate(Yii::$app->cache, \mdm\admin\components\Configs::CACHE_TAG);
        }
    }
}
