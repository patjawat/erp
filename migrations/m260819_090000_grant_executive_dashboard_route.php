<?php

use yii\caching\TagDependency;
use yii\db\Migration;

/**
 * ให้ role executiveViewer เข้าถึง route /executive/* ได้จริง
 *
 * เดิม m260816_100000 สร้างแค่ permission executiveDashboardView ซึ่งใช้ได้กับ
 * AccessControl ในคอนโทรลเลอร์เท่านั้น แต่ทั้งแอปมี as access ของ mdm\admin
 * ที่ตรวจสิทธิ์ระดับ route (auth_item type=2 ชื่อขึ้นต้นด้วย /) ก่อนเสมอ
 * เมื่อไม่มี route item ผู้ใช้จึงโดน 403 ก่อนถึงคอนโทรลเลอร์
 */
class m260819_090000_grant_executive_dashboard_route extends Migration
{
    private const ROUTE = '/executive/*';

    public function safeUp()
    {
        $exists = (new \yii\db\Query())
            ->from('{{%auth_item}}')
            ->where(['name' => self::ROUTE])
            ->exists();

        if (!$exists) {
            $now = time();
            $this->insert('{{%auth_item}}', [
                'name' => self::ROUTE,
                'type' => 2,
                'description' => 'Dashboard ผู้บริหาร',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->ensureChild('executiveViewer', self::ROUTE);
        $this->invalidateAuthCache();
    }

    public function safeDown()
    {
        $this->delete('{{%auth_item_child}}', ['parent' => 'executiveViewer', 'child' => self::ROUTE]);
        $this->delete('{{%auth_item}}', ['name' => self::ROUTE]);
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
